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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('business_name');
            $table->enum('vendor_type', ['boutique', 'street_food', 'restaurant']);
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('cover_url')->nullable();
            $table->string('address_text')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->enum('verification_level', ['non_verifie', 'identite_confirmee', 'verifie'])->default('non_verifie');
            $table->string('rccm_number')->nullable();
            $table->string('dfe_number')->nullable();
            $table->string('rccm_document_url')->nullable();
            $table->string('cni_document_url')->nullable();
            $table->enum('rccm_assist_status', ['dossier_recu', 'depose_cepici', 'en_attente', 'obtenu'])->nullable();
            $table->decimal('commission_rate', 5, 2)->default(10.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
