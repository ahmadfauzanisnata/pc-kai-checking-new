<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_area_id')->constrained('business_areas')->cascadeOnDelete();
            $table->string('name', 150);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['DRAFT', 'ACTIVE', 'CLOSED', 'LOCKED'])->default('DRAFT');
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_periods');
    }
};
