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
        Schema::create('festivita', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_id')->constrained('sedi')->onDelete('cascade');
            $table->string('country_code'); // ISO 3166-1 alpha-2 (e.g., IT, US)
            $table->date('data_festivita');
            $table->string('descrizione')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('festivita');
    }
};
