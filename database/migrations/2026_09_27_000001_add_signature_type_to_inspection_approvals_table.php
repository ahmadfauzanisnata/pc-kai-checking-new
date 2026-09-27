<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inspection_approvals', function (Blueprint $table) {
            $table->string('signature_type', 20)->default('digital')->after('signature_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('inspection_approvals', function (Blueprint $table) {
            $table->dropColumn('signature_type');
        });
    }
};
