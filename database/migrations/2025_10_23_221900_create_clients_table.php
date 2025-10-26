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
            $table->string('nci', 13)->unique()->nullable(); // CNI sénégalais : 13 chiffres
            $table->string('email')->unique();
            $table->string('telephone', 13)->unique(); // Format +221XXXXXXXXX
            $table->string('adresse')->nullable();
            $table->string('password')->nullable();
            $table->string('code', 6)->nullable(); // Code à 6 caractères
            $table->timestamp('code_verified_at')->nullable(); // Date de vérification du code
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->softDeletes(); // Soft delete activé
            $table->timestamps();

            // Index pour les performances
            $table->index(['email', 'telephone']);
            $table->index('nci');
            $table->index('titulaire');
            $table->index('code');
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
