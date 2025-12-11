<?php

namespace App\Http\Requests\Transaction;

use App\Models\TransactionWorkflow;
use Illuminate\Foundation\Http\FormRequest;

class WorkflowRequests extends FormRequest
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
        $workflowId = $this->route('workflow') ?? $this->route('id');
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        $rules = [
            'department_id' => 'required|exists:departments,id',
            'sequence_order' => 'required|integer|min:1',
            'is_originating' => 'nullable|boolean',
            'process_time_value' => 'required|integer|min:1',
            'process_time_unit' => 'required|in:minutes,days,weeks',
            'next_step_on_approval' => [
                'nullable',
                'exists:transaction_workflows,id',
                function ($attribute, $value, $fail) use ($workflowId) {
                    if ($value == $workflowId) {
                        $fail(
                            'The Workflow cannot reference itself as the next step.'
                        );
                    }
                }
            ],
            'next_step_on_rejection' => [
                'nullable',
                'exists:transaction_workflows,id',
                function ($attribute, $value, $fail) use ($workflowId) {
                    if ($value == $workflowId) {
                        $fail(
                            'The Workflow cannot reference itself as the rejection step.'
                        );
                    }
                }
            ],
            'allow_cycles' => 'nullable|boolean',
            'max_cycle_count' => [
                'nullable',
                'integer',
                'min:1',
                'max:20',
                function ($attribute, $value, $fail) {
                    if ($this->input('allow_cycles') && !$value) {
                        $fail(
                            'The max cycle count is required when cycles are enabled.'
                        );
                    }
                }
            ],
        ];

        if (!$isUpdate) {
            $rules['transaction_type_id'] = 'required|exists:transaction_types_id';
        }

        return $rules;
    }

    // custom messages
    public function messages(): array
    {
        return [
            'transaction_type_id.required' => 'Please select a transaction type.',
            'transaction_type_id.exists' => 'The selected transaction type is invalid.',
            'department_id.required' => 'Please select a department.',
            'department_id.exists' => 'The selected department is invalid.',
            'sequence_order.required' => 'Sequence order is required.',
            'sequence_order.integer' => 'Sequence order must be a number.',
            'sequence_order.min' => 'Sequence order must be at least 1.',
            'process_time_value.required' => 'Process time value is required.',
            'process_time_value.integer' => 'Process time value must be a number.',
            'process_time_value.min' => 'Process time value must be at least 1.',
            'process_time_unit.required' => 'Process time unit is required.',
            'process_time_unit.in' => 'Process time unit must be minutes, days, or weeks.',
            'next_step_on_approval_id.exists' => 'The selected approval step is invalid.',
            'next_step_on_rejection_id.exists' => 'The selected rejection step is invalid.',
            'max_cycle_count.integer' => 'Max cycle count must be a number.',
            'max_cycle_count.min' => 'Max cycle count must be at least 1.',
            'max_cycle_count.max' => 'Max cycle count cannot exceed 10.',
        ];
    }

    // custom attributes
    public function attributes(): array
    {
        return [
            'transaction_type_id' => 'transaction type',
            'department_id' => 'department',
            'sequence_order' => 'sequence order',
            'is_originating' => 'originating step',
            'process_time_value' => 'process time value',
            'process_time_unit' => 'process time unit',
            'next_step_on_approval_id' => 'next step on approval',
            'next_step_on_rejection_id' => 'next step on rejection',
            'allow_cycles' => 'allow cycles',
            'max_cycle_count' => 'max cycle count',
        ];
    }

    // validator
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->input('next_step_on_rejection_id') && !$this->input('allow_cycles')) {
                $validator->errors()->add(
                    'next_step_on_rejection_id',
                    'Cannot set rejection path without enabling cycles.'
                );
            }

            //validate unique sequence order
            $this->validateUniqueSequenceOrder($validator);
            //validate that next steps belong to same transaction


        });
    }

    protected function validateUniqueSequenceOrder($validator)
    {
        $workflowId = $this->route('workflow') ?? $this->route('id');

        $transactionTypeId = $this->input('transaction_type_id');

        if ($transactionTypeId) {
            $exists = TransactionWorkflow::where('transaction_type_id', $transactionTypeId)
                ->where('sequence_order', $this->input('sequence_order'))
                ->when($workflowId, function ($query) use ($workflowId) {
                    $query->where('id', '!=', $workflowId);
                })->exists();
        }
    }

    protected function validateNextStepsTransactionType($validator)
    {

        $workflowId = $this->route('workflow') ?? $this->route('id');

        $transactionTypeId = $this->input('transaction_type_id');

        if ($transactionTypeId) {
            if ($approvedId = $this->input('next_step_on_approval_id')) {
                $approvalStep = TransactionWorkflow::find($approvedId);
                if ($approvalStep && $approvalStep->transaction_type_id != $transactionTypeId) {
                    $validator->errors()->add(
                        'next_step_on_approval_id',
                        'The approval step must belong to the same transaction type.'
                    );
                }
            }

            if ($rejectionId = $this->input('next_step_on_rejection_id')) {
                $rejectionStep = TransactionWorkflow::find($rejectionId);
                if ($rejectionStep && $rejectionStep->transaction_type_id != $transactionTypeId) {
                    $validator->errors()->add(
                        'next_step_on_rejection_id',
                        'The rejection step must belong to the same transaction type.'
                    );
                }
            }
        }
    }

    protected function prepareForValidation()  {
        
        //convert checkbox values to boolean
        $this->merge([
            'is_originating' => $this->boolean('is_originating'),
            'allow_cycles' => $this->boolean('allow_cycles')
        ]);


        //Clean up max cycle count if cycles are not allowed
        if (!$this->input('allow_cycles')) {
            $this->merge(['max_cycle_count' => null]);
        }
    }
}
