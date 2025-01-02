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
        Schema::create('sedi', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->decimal('latitudine', 10, 8);
            $table->decimal('longitudine', 11, 8);
            $table->string('fuso_orario');
            $table->time('orario_inizio');
            $table->time('orario_fine');
            $table->boolean('attiva')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sedi');
    }
};
