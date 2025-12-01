<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hash of diary_token for server-side verification
            // diary_token = SHA-256("DIARY_TOKEN::" + password + "::" + user_id)
            $table->string('diary_token_hash')->nullable()->after('password');

            // User-specific salt for client-side key derivation (PBKDF2)
            // Generated once per user at registration
            $table->string('encryption_salt')->nullable()->after('diary_token_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['diary_token_hash', 'encryption_salt']);
        });
    }
};
