<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // dono
            $table->string('brand');
            $table->string('model');
            $table->unsignedSmallInteger('year');
            $table->string('plate', 10)->unique();
            $table->string('color');
            $table->enum('transmission', ['manual', 'automatico'])->default('manual');
            $table->unsignedTinyInteger('seats')->default(5);
            $table->string('city');
            $table->decimal('daily_price', 10, 2);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true); // anunciado ou não
            $table->timestamps();

            $table->index(['city', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
