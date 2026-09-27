<?php

namespace App\Http\Requests\InspectionPeriod;

use App\Enums\InspectionPeriodStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInspectionPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        $period = $this->route('inspection_period');
        if ($period && $period->status === InspectionPeriodStatus::LOCKED) {
            return false;
        }

        return $this->user()?->isSuperAdmin() || $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'business_area_id' => $this->user()?->isSuperAdmin() ? ['required', 'exists:business_areas,id'] : ['nullable'],
            'name' => ['required', 'string', 'max:150'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'Tanggal akhir periode tidak boleh lebih awal dari tanggal mulai.',
        ];
    }
}
