<?php

namespace App\Http\Requests\Inspection;

use Illuminate\Foundation\Http\FormRequest;

class ResubmitInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $inspection = $this->route('inspection');

        if (!$this->user()?->isInspector()) {
            return false;
        }

        if ($inspection instanceof \App\Models\Inspection) {
            return $inspection->status === \App\Enums\InspectionStatus::REVISION_REQUIRED;
        }

        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('special_access')) {
            $val = strtoupper((string) $this->input('special_access'));
            if (in_array($val, ['LOCAL_ADMIN', 'DOMAIN_ADMIN', 'ADMIN'])) {
                $this->merge(['special_access' => 'ADMIN']);
            } elseif (in_array($val, ['NONE', 'USER', 'STANDARD'])) {
                $this->merge(['special_access' => 'USER']);
            }
        }

        if ($this->has('prohibited_files_status')) {
            $val = strtoupper((string) $this->input('prohibited_files_status'));
            if (in_array($val, ['TIDAK_ADA', 'TIDAK'])) {
                $this->merge(['prohibited_files_status' => 'TIDAK']);
            } elseif ($val === 'ADA') {
                $this->merge(['prohibited_files_status' => 'ADA']);
            }
        }

        if ($this->has('os_license_status')) {
            $val = strtoupper((string) $this->input('os_license_status'));
            if (in_array($val, ['YA', 'LICENSE', 'LICENSED'])) {
                $this->merge(['os_license_status' => 'LICENSE']);
            } elseif ($val === 'TIDAK') {
                $this->merge(['os_license_status' => 'TIDAK']);
            }
        }

        if ($this->has('pc_label_status')) {
            $val = strtoupper((string) $this->input('pc_label_status'));
            if (in_array($val, ['YA', 'ADA'])) {
                $this->merge(['pc_label_status' => 'ADA']);
            } elseif ($val === 'TIDAK') {
                $this->merge(['pc_label_status' => 'TIDAK']);
            }
        }
    }

    public function rules(): array
    {
        return [
            'notes' => ['required', 'string', 'min:5'],
            'nda_status' => ['required', 'in:SUDAH,BELUM'],
            'nda_notes' => ['nullable', 'string', 'max:255'],
            'strong_password_status' => ['required', 'in:SUDAH,BELUM'],
            'strong_password_notes' => ['nullable', 'string', 'max:255'],
            'screensaver_lock_status' => ['required', 'in:SUDAH,BELUM'],
            'screensaver_lock_notes' => ['nullable', 'string', 'max:255'],
            'special_access' => ['required', 'in:ADMIN,USER'],
            'special_access_notes' => ['nullable', 'string', 'max:255'],
            'cleardesk_status' => ['required', 'in:SUDAH,BELUM'],
            'cleardesk_notes' => ['nullable', 'string', 'max:255'],
            'prohibited_files_status' => ['required', 'in:ADA,TIDAK'],
            'prohibited_files_notes' => ['nullable', 'string', 'max:255'],
            'antivirus_install_status' => ['required', 'in:SUDAH,BELUM'],
            'antivirus_name' => ['nullable', 'string', 'max:255'],
            'antivirus_update_status' => ['required', 'in:SUDAH,BELUM'],
            'antivirus_update_notes' => ['nullable', 'string', 'max:255'],
            'full_scan_schedule_status' => ['required', 'in:SUDAH,BELUM'],
            'full_scan_schedule_notes' => ['nullable', 'string', 'max:255'],
            'os_license_status' => ['required', 'in:LICENSE,TIDAK'],
            'os_license_notes' => ['nullable', 'string', 'max:255'],
            'ntp_sync_status' => ['required', 'in:YA,TIDAK'],
            'ntp_sync_notes' => ['nullable', 'string', 'max:255'],
            'pc_label_status' => ['required', 'in:ADA,TIDAK'],
            'pc_label_notes' => ['nullable', 'string', 'max:255'],
            'finding_summary' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'notes.required' => 'Catatan perbaikan hasil pemeriksaan wajib diisi.',
            'notes.min' => 'Catatan perbaikan minimal 5 karakter.',
        ];
    }
}
