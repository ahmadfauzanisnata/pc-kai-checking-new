<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspection_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('inspections')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->enum('attachment_type', ['DEVICE_PHOTO', 'SCREENSHOT', 'SUPPORTING_DOCUMENT', 'OTHER'])->default('SUPPORTING_DOCUMENT');
            $table->string('original_name', 255);
            $table->string('stored_name', 255);
            $table->string('file_path', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspection_attachments');
    }
};
