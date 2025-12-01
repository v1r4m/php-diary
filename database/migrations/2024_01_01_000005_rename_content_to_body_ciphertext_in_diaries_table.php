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
        Schema::table('diaries', function (Blueprint $table) {
            $table->renameColumn('content', 'body_ciphertext');
        });

        // Remove public diary features for simplicity (zero-knowledge focus)
        // Drop indexes first, then columns
        Schema::table('diaries', function (Blueprint $table) {
            // Use explicit index names for PostgreSQL
            $table->dropIndex('diaries_is_public_index');
            $table->dropIndex('diaries_published_at_index');
        });

        Schema::table('diaries', function (Blueprint $table) {
            $table->dropColumn(['is_public', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('diaries', function (Blueprint $table) {
            $table->renameColumn('body_ciphertext', 'content');
            $table->boolean('is_public')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->index('is_public');
            $table->index('published_at');
        });
    }
};
