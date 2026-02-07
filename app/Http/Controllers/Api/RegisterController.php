<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Services\RegisterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class RegisterController extends Controller
{
    public function __construct(
        protected RegisterService $registerService
    ) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        try {
            $accountInfo = $request->validated('account_info');
            $companyInfo = $request->validated('company_info');
            $brochure = $request->file('brochure');
            
            $this->registerService->register($accountInfo, $companyInfo, $brochure);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
            ], 201);

        } catch (Throwable $e) {
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again later.',
            ], 500);
        }
    }
}
