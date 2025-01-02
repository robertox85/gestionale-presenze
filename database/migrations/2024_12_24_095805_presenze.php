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
        Schema::create('presenze', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anagrafica_id')->constrained('anagrafiche')->onDelete('cascade');
            $table->date('data');
            $table->time('ora_entrata');
            $table->decimal('coordinate_entrata_lat', 10, 8)->nullable();
            $table->decimal('coordinate_entrata_long', 11, 8)->nullable();
            $table->time('ora_uscita')->nullable();
            $table->decimal('coordinate_uscita_lat', 10, 8)->nullable();
            $table->decimal('coordinate_uscita_long', 11, 8)->nullable();
            $table->boolean('uscita_automatica')->default(false);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presenze');
    }
};
