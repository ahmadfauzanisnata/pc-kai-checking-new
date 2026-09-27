<?php

namespace App\Http\Requests\Inspection;

use Illuminate\Foundation\Http\FormRequest;

class SubmitInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isInspector() || $this->user()?->isAdmin() || $this->user()?->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'inspection_date' => ['required', 'date'],
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
            'statement' => ['required', 'accepted'],
            'finding_summary' => ['nullable', 'string'],
            'general_notes' => ['nullable', 'string'],
            'comment' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $hasFinding = (
                $this->input('nda_status') === 'BELUM' ||
                $this->input('strong_password_status') === 'BELUM' ||
                $this->input('screensaver_lock_status') === 'BELUM' ||
                $this->input('cleardesk_status') === 'BELUM' ||
                $this->input('prohibited_files_status') === 'ADA' ||
                $this->input('antivirus_install_status') === 'BELUM' ||
                $this->input('antivirus_update_status') === 'BELUM' ||
                $this->input('full_scan_schedule_status') === 'BELUM' ||
                $this->input('os_license_status') === 'TIDAK' ||
                $this->input('ntp_sync_status') === 'TIDAK' ||
                $this->input('pc_label_status') === 'TIDAK'
            );

            if ($hasFinding && empty(trim($this->input('finding_summary') ?? ''))) {
                $validator->errors()->add('finding_summary', 'Terdapat temuan ketidaksesuaian pada butir pemeriksaan. Ringkasan temuan wajib diisi.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'inspection_date.required' => 'Tanggal pelaksanaan pemeriksaan wajib diisi.',
            'statement.accepted' => 'Anda wajib menyetujui pernyataan kebenaran hasil pemeriksaan.',
            '*.required' => 'Seluruh butir checklist pemeriksaan wajib dipilih.',
        ];
    }
}
