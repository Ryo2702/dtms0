<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class DepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', 'unique:departments,name'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', 'boolean'],
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'The title field is required.',
            'title.unique' => 'The title must be unique.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            'logo.image' => 'The logo must be an image.',
            'logo.mime' => 'The logo must be a file of type: jpeg, png, jpg.',
            'logo.max' => 'The logo may not be greater than 2MB.',
        ];   
    }

    public function attributes()
    {
        return [
            'title' => 'Department Title',
            'description' => 'Department Description',
            'logo' => 'Department Logo',
            'status' => 'Department Status',
        ];   
    }
}
