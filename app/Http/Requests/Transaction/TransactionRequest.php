<?php

namespace App\Http\Requests\Transaction;

use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
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
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            // Required on create, optional on update
            'workflow_id' => $isUpdate ? 'sometimes|string|exists:workflows,id' : 'required|string|exists:workflows,id',
            'assign_staff_id' => $isUpdate ? 'sometimes|exists:assign_staff,id' : 'required|exists:assign_staff,id',

            // Always optional
            'department_id' => 'nullable|exists:departments,id',
            'level_of_urgency' => 'nullable|in:normal,urgent,highly_urgent',

            // Workflow snapshot rules - can be JSON string or array
            'workflow_snapshot' => 'nullable|json',
            
            // Document tags - array of tag IDs
            'document_tag_ids' => 'nullable|array',
            'document_tag_ids.*' => 'nullable|exists:document_tags,id',
            
            // Flag to update workflow default (Head users only)
            'update_workflow_default' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'workflow_id.required' => 'Please select a workflow/transaction type.',
            'workflow_id.exists' => 'The selected workflow is invalid.',
            'assign_staff_id.required' => 'Please assign a staff member.',
            'assign_staff_id.exists' => 'The selected staff member is invalid.',
            'level_of_urgency.in' => 'Invalid urgency level selected.',
            'department_id.exists' => 'The selected department is invalid.',
        ];
    }

    /**
     * Get validated data, filtering out unchanged values on update
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        if ($isUpdate) {
            // Get the transaction from route model binding
            $transaction = $this->route('transaction');

            if ($transaction) {
                $validated = $this->filterUnchangedFields($validated, $transaction);
            }
        } else {
            // Set defaults for create
            $validated['level_of_urgency'] = $validated['level_of_urgency'] ?? 'normal';
        }

        return $validated;
    }

    /**
     * Filter out fields that haven't changed from current values
     */
    protected function filterUnchangedFields(array $validated, $transaction): array
    {
        $filtered = [];

        foreach ($validated as $key => $value) {
            // Handle nested arrays (like workflow_snapshot)
            if (is_array($value) && is_array($transaction->{$key})) {
                if ($value !== $transaction->{$key}) {
                    $filtered[$key] = $value;
                }
                continue;
            }

            // Only include if value is different from current
            if ($transaction->{$key} !== $value) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Check if this is a store (create) request
     */
    public function isStoreRequest(): bool
    {
        return $this->isMethod('POST');
    }

    /**
     * Check if this is an update request
     */
    public function isUpdateRequest(): bool
    {
        return $this->isMethod('PUT') || $this->isMethod('PATCH');
    }

    /**
     * Check if there are any changes to apply
     */
    public function hasChanges(): bool
    {
        return !empty($this->validated());
    }
}
