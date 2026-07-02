<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * KYC documents uploaded by investors. Files live on the private disk and
     * are served to admins via signed URLs; document_number is encrypted.
     *
     * reviewed_by references the future `admins` table — the FK constraint is
     * added in Phase 2 when that table exists.
     */
    public function up(): void
    {
        Schema::create('kyc_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // KycDocumentType: nid | passport
            $table->text('document_number'); // encrypted
            $table->string('front_image_path');
            $table->string('back_image_path')->nullable();
            $table->string('selfie_path')->nullable();
            $table->string('status')->default('pending'); // KycDocumentStatus
            $table->unsignedBigInteger('reviewed_by')->nullable(); // FK → admins in Phase 2
            $table->timestamp('reviewed_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_documents');
    }
};
