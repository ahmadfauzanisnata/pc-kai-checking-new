<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('signature_path', 255)->nullable()->after('position');
        });

        Schema::table('inspections', function (Blueprint $table) {
            $table->string('employee_signature_path', 255)->nullable()->after('general_notes');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('signature_path');
        });

        Schema::table('inspections', function (Blueprint $table) {
            $table->dropColumn('employee_signature_path');
        });
    }
};
