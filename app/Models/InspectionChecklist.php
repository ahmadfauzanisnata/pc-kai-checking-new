<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionChecklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id',
        'nda_status',
        'strong_password_status',
        'screensaver_lock_status',
        'special_access',
        'cleardesk_status',
        'prohibited_files_status',
        'antivirus_install_status',
        'antivirus_update_status',
        'full_scan_schedule_status',
        'os_license_status',
        'ntp_sync_status',
        'pc_label_status',
        'finding_summary',
    ];

    protected function casts(): array
    {
        return [
            'special_access' => \App\Enums\SpecialAccess::class,
        ];
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public static function checklistFields(): array
    {
        return [
            'nda_status' => [
                'label' => 'NDA (Non-Disclosure Agreement)',
                'options' => ['SUDAH' => 'Sudah', 'BELUM' => 'Belum'],
                'negative_value' => 'BELUM',
            ],
            'strong_password_status' => [
                'label' => 'Login Strong Password (Kombinasi Karakter & Simbol)',
                'options' => ['SUDAH' => 'Sudah', 'BELUM' => 'Belum'],
                'negative_value' => 'BELUM',
            ],
            'screensaver_lock_status' => [
                'label' => 'Screensaver Lock Maksimal 5 Menit',
                'options' => ['SUDAH' => 'Sudah', 'BELUM' => 'Belum'],
                'negative_value' => 'BELUM',
            ],
            'special_access' => [
                'label' => 'Hak Akses Khusus',
                'options' => ['ADMIN' => 'Admin', 'USER' => 'User'],
                'negative_value' => null, // Justification only
            ],
            'cleardesk_status' => [
                'label' => 'Cleardesk (Meja Bersih dari Informasi Rahasia)',
                'options' => ['SUDAH' => 'Sudah', 'BELUM' => 'Belum'],
                'negative_value' => 'BELUM',
            ],
            'prohibited_files_status' => [
                'label' => 'File MP3, Video, dan Sejenisnya',
                'options' => ['ADA' => 'Ada', 'TIDAK' => 'Tidak'],
                'negative_value' => 'ADA',
            ],
            'antivirus_install_status' => [
                'label' => 'Antivirus Status Install',
                'options' => ['SUDAH' => 'Sudah', 'BELUM' => 'Belum'],
                'negative_value' => 'BELUM',
            ],
            'antivirus_update_status' => [
                'label' => 'Antivirus Status Update (Signature Terbaru)',
                'options' => ['SUDAH' => 'Sudah', 'BELUM' => 'Belum'],
                'negative_value' => 'BELUM',
            ],
            'full_scan_schedule_status' => [
                'label' => 'Antivirus Full Scan Auto Schedule (Terjadwal Rutin)',
                'options' => ['SUDAH' => 'Sudah', 'BELUM' => 'Belum'],
                'negative_value' => 'BELUM',
            ],
            'os_license_status' => [
                'label' => 'Lisensi Sistem Operasi (Legal & Teraktivasi)',
                'options' => ['LICENSE' => 'License', 'TIDAK' => 'Tidak'],
                'negative_value' => 'TIDAK',
            ],
            'ntp_sync_status' => [
                'label' => 'Sinkronisasi NTP Server (Waktu Akurat)',
                'options' => ['YA' => 'Ya', 'TIDAK' => 'Tidak'],
                'negative_value' => 'TIDAK',
            ],
            'pc_label_status' => [
                'label' => 'Label PC / Nomor Aset Tertempel',
                'options' => ['ADA' => 'Ada', 'TIDAK' => 'Tidak'],
                'negative_value' => 'TIDAK',
            ],
        ];
    }

    public function isNegativeFinding(string $field): bool
    {
        $fields = self::checklistFields();
        if (!isset($fields[$field]) || !$fields[$field]['negative_value']) {
            return false;
        }

        return $this->{$field} === $fields[$field]['negative_value'];
    }

    public function countNegativeFindings(): int
    {
        $count = 0;
        foreach (self::checklistFields() as $field => $config) {
            if ($config['negative_value'] && $this->{$field} === $config['negative_value']) {
                $count++;
            }
        }
        return $count;
    }

    public function getNegativeFindingsCount(): int
    {
        return $this->countNegativeFindings();
    }

    public function hasNegativeFindings(): bool
    {
        return $this->countNegativeFindings() > 0;
    }

    public function getFindingItems(): array
    {
        $findings = [];
        foreach (self::checklistFields() as $field => $config) {
            if ($config['negative_value'] && $this->{$field} === $config['negative_value']) {
                $findings[$field] = [
                    'label' => $config['label'],
                    'value' => $this->{$field},
                ];
            }
        }
        return $findings;
    }

    public function isComplete(): bool
    {
        foreach (array_keys(self::checklistFields()) as $field) {
            if (empty($this->{$field})) {
                return false;
            }
        }
        return true;
    }
}
