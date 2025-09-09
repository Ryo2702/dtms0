<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => 'required|in:approve,reject,forward',
            'notes' => 'nullable|string|max:1000',
            'forward_to' => 'required_if:action,forward|exists:departments,id',
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Action is required.',
            'action.in' => 'Action must be approve, reject, or forward.',
            'forward_to.required_if' => 'Please select a department to forward to.',
            'forward_to.exists' => 'Selected department does not exist.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }
}
