<?php

namespace App\Http\Requests\Document_Type;

use Illuminate\Foundation\Http\FormRequest;

class MayorClearanceRequest extends FormRequest
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
            'address' => 'required|string|max:1000',
            'fee' => 'nullable|string|max:100',
            'or_number' => 'nullable|string|max:100',
            'date' => 'nullable|string|max:50',
            'purpose' => 'required|string|max:255',
            'action' => 'required|in:send_for_review',
            'reviewer_id' => 'required|exists:users,id',
            'process_time' => 'required|integer|min:1|max:10',
            'initial_notes' => 'required|string|max:1000'
        ];
    }
}
