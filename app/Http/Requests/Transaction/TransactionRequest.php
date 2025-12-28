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
            'document_tags_id' => $isUpdate ? 'sometimes|exists:document_tags,id' : 'required|exists:document_tags,id',
            'assign_staff_id' => $isUpdate ? 'sometimes|exists:assign_staff,id' : 'required|exists:assign_staff,id',

            // Always optional
            'department_id' => 'nullable|exists:departments,id',
            'level_of_urgency' => 'nullable|in:normal,urgent,highly_urgent',

            // Workflow snapshot rules
            'workflow_snapshot' => 'nullable|array',
            'workflow_snapshot.steps' => 'nullable|array|min:1',
            'workflow_snapshot.steps.*.department_id' => 'required_with:workflow_snapshot.steps|exists:departments,id',
            'workflow_snapshot.steps.*.department_name' => 'required_with:workflow_snapshot.steps|string',
            'workflow_snapshot.steps.*.order' => 'required_with:workflow_snapshot.steps|integer|min:1',
            'workflow_snapshot.steps.*.process_time_value' => 'nullable|integer|min:1',
            'workflow_snapshot.steps.*.process_time_unit' => 'nullable|in:minutes,hours,days,weeks',
            'workflow_snapshot.transitions' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'workflow_id.required' => 'Please select a workflow/transaction type.',
            'workflow_id.exists' => 'The selected workflow is invalid.',
            'document_tags_id.required' => 'Please select a document tag.',
            'document_tags_id.exists' => 'The selected document tag is invalid.',
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
