<?php

namespace App\Http\Requests\Document_Type;

use Illuminate\Foundation\Http\FormRequest;

class MpocRequest extends FormRequest
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
            'barangay_chairman' => 'required|string|max:255',
            'barangay_name' => 'required|string|max:255',
            'barangay_clearance_date' => 'required|date',
            'resident_name' => 'required|string|max:255',
            'resident_barangay' => 'required|string|max:255',
            'certification_date' => 'nullable|date',
            'requesting_party' => 'required',
            'action' => 'required|in:send_for_review',
            'reviewer_id' => 'required|exists:users,id',
            'process_time' => 'required|integer|min:1|max:10',
            'initial_notes' => 'required|string|max:1000'
        ];
    }
}
