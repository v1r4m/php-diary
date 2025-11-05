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
        Schema::create('hello_world', function (Blueprint $table) {
            $table->id();
            $table->string('message');
            $table->timestamps();
        });

        // Insert a test record
        DB::table('hello_world')->insert([
            'message' => 'Hello World! Database connection is working!',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hello_world');
    }
};
