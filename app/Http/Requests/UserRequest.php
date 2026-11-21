<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $creating = ! ($user instanceof User);
        $username = Rule::unique('users', 'username');
        $email = Rule::unique('users', 'email');
        if ($user instanceof User) {
            $username->ignore($user);
            $email->ignore($user);
        }

        return [
            'username' => ['bail', 'required', 'string', 'max:50', $username],
            'full_name' => ['bail', 'required', 'string', 'max:100'],
            'email' => ['bail', 'required', 'string', 'lowercase', 'email', 'max:100', $email],
            'password' => [
                Rule::requiredIf($creating || ($user instanceof User && $user->password === null)),
                'nullable', 'confirmed', Password::defaults(),
            ],
            'current_password' => [
                Rule::requiredIf($user instanceof User && $user->is($this->user()) && $this->filled('password')),
                'nullable', 'current_password',
            ],
            'avatar' => $creating ? ['prohibited'] : [
                'bail', 'nullable', 'file', 'image', 'mimes:jpg,jpeg,png',
                'extensions:jpg,jpeg,png', 'max:2048', 'dimensions:max_width=3000,max_height=3000',
            ],
        ];
    }

    public function attributes(): array
    {
        return ['full_name' => 'full name', 'avatar' => 'profile picture'];
    }

    public function messages(): array
    {
        return [
            'username.unique' => 'That username is already in use. Please choose another.',
            'email.unique' => 'That email address is already in use.',
            'email.lowercase' => 'Enter the email address using lowercase letters.',
            'password.required' => 'Set a password for this account so it can sign in.',
            'avatar.prohibited' => 'Create the user first, then add a picture on the edit page.',
            'avatar.max' => 'The profile picture must not exceed 2 MB (2048 KB).',
            'avatar.dimensions' => 'Choose an image no larger than 3000 by 3000 pixels.',
        ];
    }
}
