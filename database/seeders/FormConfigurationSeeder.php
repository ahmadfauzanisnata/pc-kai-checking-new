<?php

namespace Database\Seeders;

use App\Models\FormConfiguration;
use Illuminate\Database\Seeder;

class FormConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        FormConfiguration::updateOrCreate(
            ['form_number' => 'FR.SM/TI/017.002/10-2020'],
            [
                'company_name' => 'PT. KERETA API INDONESIA (PERSERO)',
                'department_name' => 'Sistem Informasi',
                'form_number' => 'FR.SM/TI/017.002/10-2020',
                'publication_date' => '2020-10-12',
                'revision_status' => '002-2020',
                'page_label' => '1 dari 1',
            ]
        );
    }
}
