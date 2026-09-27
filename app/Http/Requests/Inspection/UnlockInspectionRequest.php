<?php

namespace App\Http\Requests\Inspection;

use Illuminate\Foundation\Http\FormRequest;

class UnlockInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:5'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan membuka kunci data wajib diisi.',
            'reason.min' => 'Alasan membuka kunci minimal 5 karakter.',
        ];
    }
}
