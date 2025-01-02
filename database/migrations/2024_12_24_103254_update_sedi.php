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
        Schema::table('sedi', function (Blueprint $table) {
            $table->string('country_code')->nullable();
            $table->string('indirizzo')->nullable();
            // latitudine and longitudine are now nullable
            $table->decimal('latitudine', 10, 8)->nullable()->change();
            $table->decimal('longitudine', 11, 8)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sedi', function (Blueprint $table) {
            $table->dropColumn('indirizzo');
            $table->decimal('latitudine', 10, 8)->change();
            $table->decimal('longitudine', 11, 8)->change();
        });
    }
};
