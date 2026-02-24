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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->integer('floor')->nullable();
            $table->string('number')->nullable()->comment('رقم الوحدة');
            $table->decimal('area', 10, 2)->nullable()->comment('المساحة بالمتر المربع');
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->integer('living_rooms')->nullable();
            $table->integer('kitchens')->nullable();
            $table->string('status')->default('متاحة');
            $table->decimal('rent_price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
