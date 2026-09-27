<?php

namespace App\Http\Requests\Inspection;

use Illuminate\Foundation\Http\FormRequest;

class ApprovalActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:approve,return'],
            'comment' => ['nullable', 'string'],
            'reason' => ['required_if:action,return', 'nullable', 'string', 'min:5'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required_if' => 'Alasan wajib diisi jika mengembalikan pemeriksaan ke Admin.',
        ];
    }
}
