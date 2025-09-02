<?php

namespace App\Http\Requests\Admin\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->type === 'Admin';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = $this->route('department')?->id;
        $isCreate = $this->isMethod('post');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')->ignore($this->department),
            ],
            'code' => [
                'required',
                'string',
                'max:10',
                'alpha_dash',
                Rule::unique('departments', 'code')->ignore($id),
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048',
            ],
            'color' => [
                'nullable',
                'string',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ],
            'status' => [
                'boolean',
            ],

            'head_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn($q) => $q->where('type', 'Head')),
                Rule::unique('departments', 'head_id')->ignore($id),
            ],

            'staff_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where(fn($q) => $q->where('type', 'Staff')),
            ],

            'staff_ids' => [
                'nullable',
                'array',
            ],

            // Head creation fields (required on create)
            'head_name' => $isCreate
                ? ['required', 'string', 'max:255']
                : ['nullable', 'string', 'max:255'],

            'head_email' => $isCreate
                ? ['required', 'email', 'unique:users,email']
                : ['nullable', 'email', Rule::unique('users', 'email')->ignore($this->route('department')?->head_id)],

            'head_password' => $isCreate
                ? ['required', 'string', 'min:8', 'confirmed']
                : ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * Custom error messages
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Department name is required.',
            'name.max' => 'Department name must not exceed 255 characters.',
            'name.unique' => 'A department with this name already exists in this municipality.',

            'code.required' => 'Department code is required.',
            'code.max' => 'Department code must not exceed 10 characters.',
            'code.alpha_dash' => 'Department code may only contain letters, numbers, dashes and underscores.',
            'code.unique' => 'A department with this code already exists in this municipality.',

            'description.max' => 'Description must not exceed 1000 characters.',

            'logo.image' => 'Logo must be an image file.',
            'logo.mimes' => 'Logo must be jpeg, png, jpg, gif, or svg.',
            'logo.max' => 'Logo file size must not exceed 2MB.',

            'color.regex' => 'Color must be a valid hex color code (e.g., #FF0000 or #F00).',

            'status.boolean' => 'Status must be either active (1) or inactive (0).',

            'head_name.required' => 'Department head name is required when creating a department.',
            'head_name.max' => 'Department head name must not exceed 255 characters.',

            'head_email.required' => 'Department head email is required when creating a department.',
            'head_email.email' => 'Department head email must be a valid email address.',
            'head_email.unique' => 'The head email is already in use.',

            'head_password.required' => 'Department head password is required when creating a department.',
            'head_password.min' => 'Department head password must be at least 8 characters.',
            'head_password.confirmed' => 'Department head password confirmation does not match.',
        ];
    }

    /**
     * Custom attribute labels
     */
    public function attributes(): array
    {
        return [
            'name' => 'department name',
            'code' => 'department code',
            'description' => 'description',
            'logo' => 'logo',
            'color' => 'color',
            'status' => 'status',
            'head_id' => 'department head',
            'staff_ids' => 'department staff',
            'head_name' => 'head name',
            'head_email' => 'head email',
            'head_password' => 'head password',
        ];
    }

    /**
     * Prepare input data for validation
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper($this->code),
            ]);
        }

        if ($this->has('status')) {
            $this->merge([
                'status' => filter_var($this->status, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        // Ensure head_id is nullable if not provided
        if (!$this->filled('head_id')) {
            $this->merge(['head_id' => null]);
        }
    }
}
