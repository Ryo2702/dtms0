<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class ExecuteActionRequest extends FormRequest
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
            'action' => 'required|string|in:approve,reject,resubmit,cancel',
            'remarks' => 'nullable|string|max:1000',
            'return_to_department_id' => 'nullable|required_if:action,reject|exists:departments,id',
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Please select an action to perform.',
            'action.in' => 'Invalid action selected.',
            'return_to_department_id.required_if' => 'Please select a department to return the transaction to.',
            'remarks.max' => 'Remarks cannot exceed 1000 characters.',
        ];
    }
}
