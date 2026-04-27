<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEventRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'nokp' => ['required', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:50'],
            'membership_status' => ['required', Rule::in(['member', 'non_member'])],
            'membership_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function participantData(): array
    {
        return [
            'full_name' => (string) $this->input('full_name'),
            'email' => (string) $this->input('email'),
            'nokp' => $this->nokp(),
            'phone' => $this->filled('phone') ? (string) $this->input('phone') : null,
            'membership_status' => (string) $this->input('membership_status'),
            'membership_notes' => $this->filled('membership_notes') ? (string) $this->input('membership_notes') : null,
        ];
    }

    public function nokp(): string
    {
        return preg_replace('/\D+/', '', (string) $this->input('nokp'));
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nokp' => $this->nokp(),
        ]);
    }
}
