<?php

namespace App\Http\Requests\Inspection;

use Illuminate\Foundation\Http\FormRequest;

class ResubmitInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isInspector();
    }

    public function rules(): array
    {
        return [
            'notes' => ['required', 'string', 'min:5'],
            'nda_status' => ['required', 'in:SUDAH,BELUM'],
            'strong_password_status' => ['required', 'in:SUDAH,BELUM'],
            'screensaver_lock_status' => ['required', 'in:SUDAH,BELUM'],
            'special_access' => ['required', 'in:ADMIN,USER'],
            'cleardesk_status' => ['required', 'in:SUDAH,BELUM'],
            'prohibited_files_status' => ['required', 'in:ADA,TIDAK'],
            'antivirus_install_status' => ['required', 'in:SUDAH,BELUM'],
            'antivirus_update_status' => ['required', 'in:SUDAH,BELUM'],
            'full_scan_schedule_status' => ['required', 'in:SUDAH,BELUM'],
            'os_license_status' => ['required', 'in:LICENSE,TIDAK'],
            'ntp_sync_status' => ['required', 'in:YA,TIDAK'],
            'pc_label_status' => ['required', 'in:ADA,TIDAK'],
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
