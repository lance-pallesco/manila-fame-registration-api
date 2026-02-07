<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $currentYear = date('Y');

        return [
            'account_info' => ['required', 'array'],
            'account_info.first_name' => ['required', 'string', 'max:255'],
            'account_info.last_name' => ['required', 'string', 'max:255'],
            'account_info.email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'account_info.username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:50', 'unique:users,username'],
            'account_info.password' => ['required', 'string', 'min:8', 'confirmed'],
            'account_info.password_confirmation' => ['required', 'string'],
            'account_info.participation_type' => ['required', 'string', 'in:Buyer,Exhibitor,Visitor,buyer,exhibitor,visitor'],

            'company_info' => ['required', 'array'],
            'company_info.company_name' => ['required', 'string', 'max:255'],
            'company_info.address_line' => ['required', 'string', 'max:500'],
            'company_info.city' => ['required', 'string', 'max:255'],
            'company_info.region' => ['nullable', 'string', 'max:255'],
            'company_info.country' => ['required', 'string', 'max:255'],
            'company_info.year_established' => ['required', 'digits:4', 'integer', 'min:1800', 'max:' . $currentYear],
            'company_info.website' => ['nullable', 'url', 'max:255'],

            'brochure' => ['nullable', 'file', 'max:2048', 'mimes:pdf,doc,docx'],
        ];
    }

    public function messages(): array
    {
        return [
            'account_info.first_name.required' => 'First name is required.',
            'account_info.last_name.required' => 'Last name is required.',
            'account_info.email.required' => 'Email address is required.',
            'account_info.email.email' => 'Please enter a valid email address.',
            'account_info.email.unique' => 'This email is already registered.',
            'account_info.username.required' => 'Username is required.',
            'account_info.username.alpha_dash' => 'Username may only contain letters, numbers, dashes, and underscores.',
            'account_info.username.min' => 'Username must be at least 3 characters.',
            'account_info.username.unique' => 'This username is already taken.',
            'account_info.password.required' => 'Password is required.',
            'account_info.password.min' => 'Password must be at least 8 characters.',
            'account_info.password.confirmed' => 'Password confirmation does not match.',
            'account_info.participation_type.required' => 'Please select a participation type.',
            'account_info.participation_type.in' => 'Please select a valid participation type.',

            'company_info.company_name.required' => 'Company name is required.',
            'company_info.address_line.required' => 'Address is required.',
            'company_info.city.required' => 'City is required.',
            'company_info.country.required' => 'Country is required.',
            'company_info.year_established.required' => 'Year established is required.',
            'company_info.year_established.digits' => 'Year must be a 4-digit number.',
            'company_info.year_established.min' => 'Year must be 1800 or later.',
            'company_info.year_established.max' => 'Year cannot be in the future.',
            'company_info.website.url' => 'Please enter a valid website URL.',

            'brochure.file' => 'Brochure must be a valid file.',
            'brochure.max' => 'Brochure file size must not exceed 2MB.',
            'brochure.mimes' => 'Brochure must be a PDF, DOC, or DOCX file.',
        ];
    }

    public function attributes(): array
    {
        return [
            'account_info.first_name' => 'first name',
            'account_info.last_name' => 'last name',
            'account_info.email' => 'email',
            'account_info.username' => 'username',
            'account_info.password' => 'password',
            'account_info.participation_type' => 'participation type',
            'company_info.company_name' => 'company name',
            'company_info.address_line' => 'address',
            'company_info.city' => 'city',
            'company_info.region' => 'region',
            'company_info.country' => 'country',
            'company_info.year_established' => 'year established',
            'company_info.website' => 'website',
        ];
    }

    protected function prepareForValidation(): void
    {
        logger($this->toArray());
        if ($this->has('account_info.participation_type')) {
            $participationType = $this->input('account_info.participation_type');
            $normalized = ucfirst(strtolower($participationType));
            
            $this->merge([
                'account_info' => array_merge(
                    $this->input('account_info', []),
                    ['participation_type' => $normalized]
                ),
            ]);
        }
    }
}
