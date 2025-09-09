<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MpocRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|string|max:50',
            'employee_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'reason' => 'required|string|max:500',
            'effective_date' => 'required|date|after_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Employee ID is required.',
            'employee_name.required' => 'Employee name is required.',
            'position.required' => 'Position is required.',
            'department.required' => 'Department is required.',
            'reason.required' => 'Reason is required.',
            'effective_date.required' => 'Effective date is required.',
            'effective_date.after_or_equal' => 'Effective date must be today or a future date.',
        ];
    }
}
