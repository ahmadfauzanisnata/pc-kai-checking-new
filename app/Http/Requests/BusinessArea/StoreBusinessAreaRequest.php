<?php

namespace App\Http\Requests\BusinessArea;

use Illuminate\Foundation\Http\FormRequest;

class StoreBusinessAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:30', 'unique:business_areas,code'],
            'name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }
}
