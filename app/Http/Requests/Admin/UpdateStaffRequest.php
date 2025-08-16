<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email' . $this->user->id,
            'municipal_id' => 'required|string|max:255|unique:users,municipal_id',
            'department' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|exists:roles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'municipal_id' => 'This municipal ID is already taken.',
            'role.exists' => 'The selected role is invalid. '
        ];
    }
}
