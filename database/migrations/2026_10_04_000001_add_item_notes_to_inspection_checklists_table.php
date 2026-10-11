<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspection_checklists', function (Blueprint $table) {
            $table->string('nda_notes', 255)->nullable()->after('nda_status');
            $table->string('strong_password_notes', 255)->nullable()->after('strong_password_status');
            $table->string('screensaver_lock_notes', 255)->nullable()->after('screensaver_lock_status');
            $table->string('special_access_notes', 255)->nullable()->after('special_access');
            $table->string('cleardesk_notes', 255)->nullable()->after('cleardesk_status');
            $table->string('prohibited_files_notes', 255)->nullable()->after('prohibited_files_status');
            $table->string('antivirus_name', 255)->nullable()->after('antivirus_install_status');
            $table->string('antivirus_update_notes', 255)->nullable()->after('antivirus_update_status');
            $table->string('full_scan_schedule_notes', 255)->nullable()->after('full_scan_schedule_status');
            $table->string('os_license_notes', 255)->nullable()->after('os_license_status');
            $table->string('ntp_sync_notes', 255)->nullable()->after('ntp_sync_status');
            $table->string('pc_label_notes', 255)->nullable()->after('pc_label_status');
        });
    }

    public function down(): void
    {
        Schema::table('inspection_checklists', function (Blueprint $table) {
            $table->dropColumn([
                'nda_notes',
                'strong_password_notes',
                'screensaver_lock_notes',
                'special_access_notes',
                'cleardesk_notes',
                'prohibited_files_notes',
                'antivirus_name',
                'antivirus_update_notes',
                'full_scan_schedule_notes',
                'os_license_notes',
                'ntp_sync_notes',
                'pc_label_notes',
            ]);
        });
    }
};
