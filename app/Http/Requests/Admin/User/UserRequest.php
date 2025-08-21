<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->type === 'Admin';
    }

    public function rules(): array
    {
        $id = $this->route('user')?->id;

        return [
            'municipal_id' => 'required|string|max:50|unique:users,municipal_id,' . $id,
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $id,
            'password'     => $id ? 'nullable|min:6' : 'required|min:6',
            'department'   => 'nullable|string|max:255',
            'type'         => 'required|in:Staff,Head',
            'status'       => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'municipal_id.required' => 'Municipal ID is required.',
            'municipal_id.unique'   => 'This Municipal ID is already in use.',
            'municipal_id.max'      => 'Municipal ID must not exceed 50 characters.',

            'name.required' => 'Name is required.',
            'name.max'      => 'Name must not exceed 255 characters.',

            'email.required' => 'Email address is required.',
            'email.email'    => 'Please enter a valid email address.',
            'email.unique'   => 'This email address is already registered.',

            'password.required' => 'Password is required.',
            'password.min'      => 'Password must be at least 6 characters long.',

            'department.max' => 'Department must not exceed 255 characters.',

            'type.required' => 'User type is required.',
            'type.in'       => 'User type must be either Staff or Head.',

            'status.boolean' => 'Status must be either active (1) or inactive (0).',
        ];
    }
}
