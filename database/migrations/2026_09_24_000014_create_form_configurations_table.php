<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('form_number', 100);
            $table->date('publication_date');
            $table->string('revision_status', 50);
            $table->string('page_label', 30)->nullable();
            $table->string('company_name', 200)->default('PT. KERETA API INDONESIA (PERSERO)');
            $table->string('department_name', 150)->default('Sistem Informasi');
            $table->string('logo_path', 255)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_configurations');
    }
};
