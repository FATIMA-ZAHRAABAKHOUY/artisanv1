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
        Schema::create('suggestion_achat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apprenant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('formation_id')->constrained('formations')->cascadeOnDelete();
            $table->foreignId('fournisseur_id')->constrained('fournisseurs')->cascadeOnDelete();
            $table->string('type_objet', 20);
            $table->unsignedBigInteger('objet_id');
            $table->boolean('est_clique')->default(false);
            $table->boolean('est_achete')->default(false);
            $table->timestamp('created_at')->nullable();
            $table->unique(['apprenant_id', 'formation_id', 'fournisseur_id', 'type_objet', 'objet_id'], 'suggestion_achat_unique_tracking');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suggestion_achat');
    }
};
