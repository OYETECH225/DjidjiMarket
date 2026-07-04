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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('provider', ['orange_money', 'mtn_money', 'moov_money', 'wave', 'cash_on_delivery']);
            $table->string('provider_transaction_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['initie', 'confirme', 'sequestre', 'libere', 'rembourse', 'echoue'])->default('initie');
            $table->timestamp('escrow_released_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
