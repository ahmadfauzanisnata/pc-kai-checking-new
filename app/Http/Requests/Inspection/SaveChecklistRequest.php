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
            'strong_password_status' => ['nullable', 'in:SUDAH,BELUM'],
            'screensaver_lock_status' => ['nullable', 'in:SUDAH,BELUM'],
            'special_access' => ['nullable', 'in:ADMIN,USER'],
            'cleardesk_status' => ['nullable', 'in:SUDAH,BELUM'],
            'prohibited_files_status' => ['nullable', 'in:ADA,TIDAK'],
            'antivirus_install_status' => ['nullable', 'in:SUDAH,BELUM'],
            'antivirus_update_status' => ['nullable', 'in:SUDAH,BELUM'],
            'full_scan_schedule_status' => ['nullable', 'in:SUDAH,BELUM'],
            'os_license_status' => ['nullable', 'in:LICENSE,TIDAK'],
            'ntp_sync_status' => ['nullable', 'in:YA,TIDAK'],
            'pc_label_status' => ['nullable', 'in:ADA,TIDAK'],
            'finding_summary' => ['nullable', 'string'],
            'general_notes' => ['nullable', 'string'],
        ];
    }
}
