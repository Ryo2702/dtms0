<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class WorkflowConfigRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Add proper authorization
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'steps' => 'required|array|min:1',
            'steps.*.department_id' => 'required|exists:departments,id',
            'steps.*.can_return_to' => 'nullable|array',
            'steps.*.can_return_to.*' => 'exists:departments,id',
        ];
    }

    public function messages(): array
    {
        return [
            'steps.required' => 'At least one workflow step is required.',
            'steps.min' => 'At least one workflow step is required.',
            'steps.*.department_id.required' => 'Each step must have a department selected.',
            'steps.*.department_id.exists' => 'One or more selected departments are invalid.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateReturnToOnlyEarlierSteps($validator);
            $this->validateNoDuplicateDepartments($validator);
        });
    }

    protected function validateReturnToOnlyEarlierSteps($validator)
    {
        $steps = $this->input('steps', []);
        
        foreach ($steps as $index => $step) {
            $canReturnTo = $step['can_return_to'] ?? [];
            
            foreach ($canReturnTo as $returnToDeptId) {
                $found = false;
                for ($i = 0; $i < $index; $i++) {
                    if (($steps[$i]['department_id'] ?? null) == $returnToDeptId) {
                        $found = true;
                        break;
                    }
                }
                
                if (!$found) {
                    $validator->errors()->add(
                        "steps.{$index}.can_return_to",
                        'A step can only return to earlier steps in the workflow.'
                    );
                }
            }
        }
    }

    protected function validateNoDuplicateDepartments($validator)
    {
        $steps = $this->input('steps', []);
        $deptIds = array_column($steps, 'department_id');
        
        if (count($deptIds) !== count(array_unique($deptIds))) {
            $validator->errors()->add(
                'steps',
                'Each department can only appear once in the workflow.'
            );
        }
    }
}
