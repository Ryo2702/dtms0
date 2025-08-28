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
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $id,
            'password'     => $id ? 'nullable|min:6' : 'required|min:6',
            'department_id' => 'required|exists:departments,id',
            'type'         => 'required|in:Staff,Head,Admin',
            'status'       => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.max'      => 'Name must not exceed 255 characters.',

            'email.required' => 'Email address is required.',
            'email.email'    => 'Please enter a valid email address.',
            'email.unique'   => 'This email address is already registered.',

            'password.required' => 'Password is required.',
            'password.min'      => 'Password must be at least 6 characters long.',

            'department_id.required' => 'Department is required.',
            'department_id.exists'   => 'Selected department does not exist.',

            'type.required' => 'User type is required.',
            'type.in'       => 'User type must be Staff, Head, or Admin.',

            'status.boolean' => 'Status must be either active (1) or inactive (0).',
        ];
    }
}
