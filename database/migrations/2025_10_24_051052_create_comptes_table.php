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
        Schema::create('comptes', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID comme clé primaire
            $table->string('numero_compte', 9)->unique(); // CXXXXXXXX format
            $table->foreignUuid('client_id')->constrained('clients')->onDelete('cascade');
            $table->enum('type', ['epargne', 'cheque']);
            $table->decimal('solde', 15, 2)->default(0);
            $table->string('devise', 10)->default('FCFA');
            $table->enum('statut', ['actif', 'bloque', 'ferme'])->default('actif');
            $table->text('motif_blocage')->nullable();
            $table->timestamp('date_debut_blocage')->nullable();
            $table->timestamp('date_fin_blocage')->nullable();
            $table->integer('version')->default(1); // Version du compte
            $table->timestamp('derniere_modification')->nullable(); // Dernière modification
            $table->softDeletes(); // Soft delete activé
            $table->timestamps();

            // Index pour les performances
            $table->index(['client_id', 'type']);
            $table->index(['statut', 'type']);
            $table->index('numero_compte');
            $table->index('solde');
            $table->index('date_debut_blocage');
            $table->index('date_fin_blocage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
