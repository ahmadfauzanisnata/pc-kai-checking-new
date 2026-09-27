<?php

namespace App\Http\Requests\Inspection;

use Illuminate\Foundation\Http\FormRequest;

class RequestRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() || $this->user()?->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:5'],
            'flagged_items' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan permintaan perbaikan wajib diisi.',
            'reason.min' => 'Alasan perbaikan minimal 5 karakter.',
        ];
    }
}
