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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code');
            $table->string('country')->default('USA');
            $table->decimal('price', 12, 2);
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->decimal('square_feet', 10, 2)->nullable();
            $table->string('property_type'); // house, apartment, condo, townhouse, land, commercial
            $table->enum('status', ['available', 'sold', 'under_contract', 'off_market'])->default('available');
            $table->json('features')->nullable(); // amenities, special features
            $table->json('images')->nullable(); // array of image URLs
            $table->boolean('is_featured')->default(false);
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
