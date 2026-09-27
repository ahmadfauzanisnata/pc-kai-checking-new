<?php

namespace App\Http\Requests\Inspection;

use Illuminate\Foundation\Http\FormRequest;

class BatchAssignInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() || $this->user()?->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'inspection_period_id' => ['required', 'exists:inspection_periods,id'],
            'inspector_id' => ['required', 'exists:users,id'],
            'document_date' => ['required', 'date'],
            'inspection_date' => ['nullable', 'date'],
            'device_ids' => ['required', 'array', 'min:1'],
            'device_ids.*' => ['required', 'exists:devices,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'device_ids.required' => 'Pilih minimal satu perangkat untuk penugasan.',
            'device_ids.min' => 'Pilih minimal satu perangkat untuk penugasan.',
        ];
    }
}
