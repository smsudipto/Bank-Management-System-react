<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FundTransferRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'recipient_account_number' => strtoupper(trim((string) $this->input('recipient_account_number'))),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'recipient_account_number' => ['required', 'string', 'regex:/^4829-[A-Z0-9]{8}$/'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999999.99', 'decimal:0,2'],
            'description' => ['nullable', 'string', 'max:255'],
            'idempotency_key' => ['required', 'uuid'],
            'current_password' => ['required', 'current_password'],
            'confirmed' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_account_number.regex' => 'Enter a valid recipient account number.',
            'current_password.current_password' => 'The secure verification password is incorrect.',
            'confirmed.accepted' => 'You must confirm the transfer before submitting it.',
        ];
    }
}
