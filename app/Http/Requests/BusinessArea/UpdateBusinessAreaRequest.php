<?php

namespace App\Http\Requests\BusinessArea;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusinessAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('business_area')?->id ?? $this->route('business_area') ?? $this->route('id');

        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('business_areas', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ];
    }
}
