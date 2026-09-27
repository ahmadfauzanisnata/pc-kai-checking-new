<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('inspection_period_id')->constrained('inspection_periods')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('device_id')->constrained('devices');
            $table->foreignId('inspector_id')->constrained('users');
            $table->string('reference_number', 100)->unique();
            $table->date('document_date');
            $table->date('inspection_date')->nullable();
            $table->string('status', 60);
            $table->text('general_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('admin_verified_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['inspection_period_id', 'device_id']);
            $table->index('status');
            $table->index('inspector_id');
            $table->index('inspection_date');
            $table->index('inspection_period_id');
            $table->index('employee_id');
            $table->index('device_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
