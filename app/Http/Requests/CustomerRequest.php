<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['bail', 'required', 'string', 'max:100'],
            'email' => ['bail', 'required', 'string', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function attributes(): array
    {
        return ['full_name' => 'full name', 'email' => 'email address', 'phone' => 'phone number'];
    }
}
