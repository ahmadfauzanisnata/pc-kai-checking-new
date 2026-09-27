<?php

namespace App\Http\Requests\Unit;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        $unit = $this->route('unit');
        $unitId = is_object($unit) ? $unit->id : $unit;
        $businessAreaId = $this->user()?->isAdmin() ? $this->user()->business_area_id : ($this->input('business_area_id') ?? $unit?->business_area_id);

        return [
            'business_area_id' => $this->user()?->isSuperAdmin() ? ['required', 'exists:business_areas,id'] : ['nullable'],
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('units')->where(function ($query) use ($businessAreaId) {
                    return $query->where('business_area_id', $businessAreaId);
                })->ignore($unitId),
            ],
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['boolean'],
        ];
    }
}
