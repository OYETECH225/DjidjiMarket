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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('courier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', [
                'en_attente_paiement', 'paiement_sequestre', 'confirmee', 'en_preparation',
                'cherche_livreur', 'livreur_assigne', 'recuperee', 'en_livraison',
                'livree', 'paiement_libere', 'litige_ouvert', 'annulee',
            ])->default('en_attente_paiement');
            $table->decimal('delivery_latitude', 10, 7)->nullable();
            $table->decimal('delivery_longitude', 10, 7)->nullable();
            $table->string('delivery_address_text')->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->enum('source', ['app', 'web', 'tiktok_live', 'lien_vendeur'])->default('app');
            $table->string('promo_code_used')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
