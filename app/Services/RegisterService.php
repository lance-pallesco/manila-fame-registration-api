<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RegisterService
{
    public function register(array $accountInfo, array $companyInfo, ?UploadedFile $brochure = null): User
    {
        return DB::transaction(function () use ($accountInfo, $companyInfo, $brochure) {
            $user = $this->createUser($accountInfo);
            $brochurePath = null;
            if ($brochure) {
                $brochurePath = $this->storeBrochure($brochure, $user->id);
            }
            $this->createCompany($user, $companyInfo, $brochurePath);
            $user->load('company');

            Log::info('Registration completed successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'company_name' => $user->company->company_name,
            ]);

            return $user;
        });
    }

    protected function createUser(array $accountInfo): User
    {
        return User::create([
            'first_name' => $accountInfo['first_name'],
            'last_name' => $accountInfo['last_name'],
            'email' => $accountInfo['email'],
            'username' => $accountInfo['username'],
            'password' => $accountInfo['password'], 
            'participation_type' => $accountInfo['participation_type'],
        ]);
    }

    protected function createCompany(User $user, array $companyInfo, ?string $brochurePath): Company
    {
        return $user->company()->create([
            'company_name' => $companyInfo['company_name'],
            'address' => $companyInfo['address_line'],
            'city' => $companyInfo['city'],
            'region' => $companyInfo['region'] ?? null,
            'country' => $companyInfo['country'],
            'year_established' => $companyInfo['year_established'],
            'website' => $companyInfo['website'] ?? null,
            'brochure_path' => $brochurePath,
        ]);
    }

    protected function storeBrochure(UploadedFile $brochure, int $userId): string
    {
        $extension = $brochure->getClientOriginalExtension();
        $timestamp = now()->format('Ymd_His');
        $filename = "brochure_{$userId}_{$timestamp}.{$extension}";
        $path = $brochure->storeAs('brochures', $filename, 'public');

        Log::info('Brochure stored successfully', [
            'user_id' => $userId,
            'path' => $path,
            'original_name' => $brochure->getClientOriginalName(),
            'size' => $brochure->getSize(),
        ]);

        return $path;
    }

    public function deleteBrochure(string $path): bool
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }
}
