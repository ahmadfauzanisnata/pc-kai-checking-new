<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->unique()->constrained('inspections')->cascadeOnDelete();
            $table->enum('nda_status', ['SUDAH', 'BELUM'])->nullable();
            $table->enum('strong_password_status', ['SUDAH', 'BELUM'])->nullable();
            $table->enum('screensaver_lock_status', ['SUDAH', 'BELUM'])->nullable();
            $table->enum('special_access', ['ADMIN', 'USER'])->nullable();
            $table->enum('cleardesk_status', ['SUDAH', 'BELUM'])->nullable();
            $table->enum('prohibited_files_status', ['ADA', 'TIDAK'])->nullable();
            $table->enum('antivirus_install_status', ['SUDAH', 'BELUM'])->nullable();
            $table->enum('antivirus_update_status', ['SUDAH', 'BELUM'])->nullable();
            $table->enum('full_scan_schedule_status', ['SUDAH', 'BELUM'])->nullable();
            $table->enum('os_license_status', ['LICENSE', 'TIDAK'])->nullable();
            $table->enum('ntp_sync_status', ['YA', 'TIDAK'])->nullable();
            $table->enum('pc_label_status', ['ADA', 'TIDAK'])->nullable();
            $table->text('finding_summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_checklists');
    }
};
