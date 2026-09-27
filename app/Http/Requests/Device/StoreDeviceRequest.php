<?php

namespace App\Http\Requests\Device;

use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isSuperAdmin() || $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        return [
            'employee_id' => ['nullable', 'exists:employees,id'],
            'asset_number' => ['required', 'string', 'max:100', 'unique:devices,asset_number'],
            'hostname' => ['nullable', 'string', 'max:100'],
            'device_type' => ['required', new Enum(DeviceType::class)],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:150', 'unique:devices,serial_number'],
            'ip_address' => ['nullable', 'ip'],
            'operating_system' => ['nullable', 'string', 'max:100'],
            'os_version' => ['nullable', 'string', 'max:100'],
            'device_status' => ['required', new Enum(DeviceStatus::class)],
            'description' => ['nullable', 'string'],
        ];
    }
}
