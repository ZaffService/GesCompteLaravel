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
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID comme clé primaire
            $table->string('titulaire');
            $table->string('nci')->unique()->nullable();
            $table->string('email')->unique();
            $table->string('telephone')->unique();
            $table->string('adresse')->nullable();
            $table->string('password')->nullable();
            $table->string('code')->nullable();
            $table->timestamps();

            // Index supplémentaires pour recherche rapide
            $table->index(['email', 'telephone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
