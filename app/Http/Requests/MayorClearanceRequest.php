<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MayorClearanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'purpose' => 'required|string|max:255',
            'date_needed' => 'required|date|after_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Full name is required.',
            'address.required' => 'Address is required.',
            'purpose.required' => 'Purpose is required.',
            'date_needed.required' => 'Date needed is required.',
            'date_needed.after_or_equal' => 'Date needed must be today or a future date.',
        ];
    }
}
