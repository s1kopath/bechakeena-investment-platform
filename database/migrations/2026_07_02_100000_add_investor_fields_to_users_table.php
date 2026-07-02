<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add investor-account fields (contact, OAuth, KYC, bank, status) to users.
     *
     * Encrypted columns use TEXT because ciphertext is longer than plaintext.
     * Status columns are strings (cast to PHP enums on the model) to stay
     * DB-agnostic across SQLite (dev/test) and MySQL (prod).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Contact & OAuth
            $table->string('phone')->nullable()->unique()->after('email');
            $table->string('google_id')->nullable()->unique()->after('phone');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');

            // Identity (encrypted at rest)
            $table->text('nid_number')->nullable()->after('password');
            $table->text('passport_number')->nullable()->after('nid_number');

            // Bank details (account number encrypted)
            $table->string('bank_account_name')->nullable()->after('passport_number');
            $table->text('bank_account_number')->nullable()->after('bank_account_name');
            $table->string('bank_name')->nullable()->after('bank_account_number');
            $table->string('bank_branch')->nullable()->after('bank_name');
            $table->string('routing_number')->nullable()->after('bank_branch');

            // Lifecycle
            $table->string('kyc_status')->default('pending')->index()->after('routing_number');
            $table->string('status')->default('active')->index()->after('kyc_status');
        });

        // OAuth-only accounts have no password.
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->dropUnique(['google_id']);
            $table->dropColumn([
                'phone',
                'google_id',
                'phone_verified_at',
                'nid_number',
                'passport_number',
                'bank_account_name',
                'bank_account_number',
                'bank_name',
                'bank_branch',
                'routing_number',
                'kyc_status',
                'status',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable(false)->change();
        });
    }
};
