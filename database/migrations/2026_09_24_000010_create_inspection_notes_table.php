<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('inspections')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->enum('note_type', [
                'GENERAL',
                'FINDING',
                'ADMIN_REVISION',
                'SUPER_ADMIN_REVISION',
                'APPROVAL',
                'UNLOCK'
            ])->default('GENERAL');
            $table->text('note');
            $table->boolean('is_internal')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_notes');
    }
};
