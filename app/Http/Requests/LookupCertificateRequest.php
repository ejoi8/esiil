<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LookupCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nokp' => ['required', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'nokp.required' => 'No. KP is required.',
        ];
    }

    public function nokp(): string
    {
        return preg_replace('/\D+/', '', (string) $this->input('nokp'));
    }
}
