<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $uniqueUsername = Rule::unique('users', 'username');
        $user = $this->route('user');

        // Ignore only the route-bound model, never an ID supplied in form input.
        if ($user instanceof User) {
            $uniqueUsername->ignore($user);
        }

        return [
            'username' => ['bail', 'required', 'string', 'max:50', $uniqueUsername],
            'full_name' => ['bail', 'required', 'string', 'max:100'],
            'avatar' => $this->isMethod('POST')
                ? ['prohibited']
                : [
                    'bail', 'nullable', 'file', 'image',
                    'mimes:jpg,jpeg,png', 'extensions:jpg,jpeg,png',
                    'max:2048', 'dimensions:max_width=3000,max_height=3000',
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
            'avatar.prohibited' => 'Create the user first, then add a profile picture on the edit page.',
            'avatar.image' => 'Choose a valid JPG or PNG image.',
            'avatar.mimes' => 'Only JPG, JPEG, and PNG images are accepted.',
            'avatar.extensions' => 'The filename must end in .jpg, .jpeg, or .png.',
            'avatar.max' => 'The profile picture must not exceed 2 MB (2048 KB).',
            'avatar.dimensions' => 'Choose an image no larger than 3000 by 3000 pixels.',
        ];
    }
}
