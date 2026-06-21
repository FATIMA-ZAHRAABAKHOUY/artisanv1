<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('fournisseurs') && ! Schema::hasColumn('fournisseurs', 'nom')) {
            Schema::table('fournisseurs', function (Blueprint $table) {
                $table->string('nom');
                $table->string('type')->default('local');
                $table->string('statut')->default('actif');
                $table->string('email')->nullable();
                $table->string('telephone')->nullable();
                $table->string('whatsapp')->nullable();
                $table->text('adresse')->nullable();
                $table->string('ville')->nullable();
                $table->string('region')->nullable();
                $table->string('code_postal')->nullable();
                $table->string('site_web')->nullable();
                $table->string('logo')->nullable();
                $table->text('description')->nullable();
                $table->decimal('remise_cooperative', 5, 2)->default(0);
                $table->unsignedSmallInteger('delai_livraison_min')->nullable();
                $table->unsignedSmallInteger('delai_livraison_max')->nullable();
                $table->decimal('note_moyenne', 3, 2)->nullable();
            });
        }

        if (Schema::hasTable('fournisseur_specialites') && ! Schema::hasColumn('fournisseur_specialites', 'fournisseur_id')) {
            Schema::table('fournisseur_specialites', function (Blueprint $table) {
                $table->unsignedBigInteger('fournisseur_id');
                $table->string('specialite');
                $table->foreign('fournisseur_id')->references('id')->on('fournisseurs')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('fournisseur_materiaus') && ! Schema::hasTable('fournisseur_materiaux')) {
            Schema::rename('fournisseur_materiaus', 'fournisseur_materiaux');
        }

        if (Schema::hasTable('fournisseur_materiaux') && ! Schema::hasColumn('fournisseur_materiaux', 'fournisseur_id')) {
            Schema::table('fournisseur_materiaux', function (Blueprint $table) {
                $table->unsignedBigInteger('materiau_id')->nullable();
                $table->unsignedBigInteger('fournisseur_id');
                $table->string('nom_produit_fournisseur')->nullable();
                $table->string('reference_produit')->nullable();
                $table->decimal('prix_unitaire', 10, 2)->nullable();
                $table->string('unite_prix')->nullable();
                $table->string('url_produit')->nullable();
                $table->unsignedSmallInteger('delai_livraison_min')->nullable();
                $table->unsignedSmallInteger('delai_livraison_max')->nullable();
                $table->boolean('est_recommande')->default(false);
                $table->boolean('stock_disponible')->default(true);
                $table->text('notes_apprenant')->nullable();
                $table->foreign('fournisseur_id')->references('id')->on('fournisseurs')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('fournisseur_outils') && ! Schema::hasColumn('fournisseur_outils', 'fournisseur_id')) {
            Schema::table('fournisseur_outils', function (Blueprint $table) {
                $table->unsignedBigInteger('outil_id')->nullable();
                $table->unsignedBigInteger('fournisseur_id');
                $table->string('nom_produit_fournisseur')->nullable();
                $table->decimal('prix_unitaire', 10, 2)->nullable();
                $table->boolean('est_recommande')->default(false);
                $table->string('url_produit')->nullable();
                $table->foreign('fournisseur_id')->references('id')->on('fournisseurs')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        //
    }
};
