<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('asset_number', 100)->unique();
            $table->string('hostname', 100)->nullable();
            $table->enum('device_type', ['PC', 'LAPTOP'])->default('PC');
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('serial_number', 150)->nullable()->unique();
            $table->string('ip_address', 45)->nullable();
            $table->string('operating_system', 100)->nullable();
            $table->string('os_version', 100)->nullable();
            $table->enum('device_status', ['ACTIVE', 'INACTIVE', 'MAINTENANCE', 'RETIRED'])->default('ACTIVE');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
