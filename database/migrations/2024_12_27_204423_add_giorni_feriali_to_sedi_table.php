<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('sedi', function (Blueprint $table) {
            $table->json('giorni_feriali')->nullable()->after('fuso_orario');
            $table->boolean('esclusione_festivi')->default(true)->after('giorni_feriali');
        });
    }

    public function down()
    {
        Schema::table('sedi', function (Blueprint $table) {
            $table->dropColumn(['giorni_feriali', 'esclusione_festivi']);
        });
    }
};
