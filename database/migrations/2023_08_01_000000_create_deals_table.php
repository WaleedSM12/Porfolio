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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type'); // flight, hotel, etc.
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('source_id')->nullable(); // ID from external API
            $table->string('source'); // Name of the external API
            $table->json('details')->nullable(); // Additional details as JSON
            $table->timestamp('valid_until')->nullable();
            $table->string('url')->nullable(); // Direct link to the deal
            $table->timestamps();
            
            // Add index for faster searches
            $table->index(['type', 'price']);
            $table->index('valid_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
}; 