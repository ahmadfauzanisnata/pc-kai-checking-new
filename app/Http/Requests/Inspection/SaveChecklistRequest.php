<?php

namespace App\Http\Requests\Inspection;

use Illuminate\Foundation\Http\FormRequest;

class SaveChecklistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isInspector() || $this->user()?->isAdmin() || $this->user()?->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'inspection_date' => ['nullable', 'date'],
            'nda_status' => ['nullable', 'in:SUDAH,BELUM'],
            'nda_notes' => ['nullable', 'string', 'max:255'],
            'strong_password_status' => ['nullable', 'in:SUDAH,BELUM'],
            'strong_password_notes' => ['nullable', 'string', 'max:255'],
            'screensaver_lock_status' => ['nullable', 'in:SUDAH,BELUM'],
            'screensaver_lock_notes' => ['nullable', 'string', 'max:255'],
            'special_access' => ['nullable', 'in:ADMIN,USER'],
            'special_access_notes' => ['nullable', 'string', 'max:255'],
            'cleardesk_status' => ['nullable', 'in:SUDAH,BELUM'],
            'cleardesk_notes' => ['nullable', 'string', 'max:255'],
            'prohibited_files_status' => ['nullable', 'in:ADA,TIDAK'],
            'prohibited_files_notes' => ['nullable', 'string', 'max:255'],
            'antivirus_install_status' => ['nullable', 'in:SUDAH,BELUM'],
            'antivirus_name' => ['nullable', 'string', 'max:255'],
            'antivirus_update_status' => ['nullable', 'in:SUDAH,BELUM'],
            'antivirus_update_notes' => ['nullable', 'string', 'max:255'],
            'full_scan_schedule_status' => ['nullable', 'in:SUDAH,BELUM'],
            'full_scan_schedule_notes' => ['nullable', 'string', 'max:255'],
            'os_license_status' => ['nullable', 'in:LICENSE,TIDAK'],
            'os_license_notes' => ['nullable', 'string', 'max:255'],
            'ntp_sync_status' => ['nullable', 'in:YA,TIDAK'],
            'ntp_sync_notes' => ['nullable', 'string', 'max:255'],
            'pc_label_status' => ['nullable', 'in:ADA,TIDAK'],
            'pc_label_notes' => ['nullable', 'string', 'max:255'],
            'finding_summary' => ['nullable', 'string'],
            'general_notes' => ['nullable', 'string'],
            'employee_signature_data' => ['nullable', 'string'],
        ];
    }
}
