<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_number',
        'publication_date',
        'revision_status',
        'page_label',
        'company_name',
        'department_name',
        'logo_path',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'publication_date' => 'date',
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): self
    {
        return static::first() ?? static::create([
            'company_name' => 'PT. KERETA API INDONESIA (PERSERO)',
            'department_name' => 'Sistem Informasi',
            'form_number' => 'FR.SM/TI/017.002/10-2020',
            'publication_date' => '2020-10-12',
            'revision_status' => '002-2020',
            'page_label' => '1 dari 1',
        ]);
    }
}
