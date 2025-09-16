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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->string('movement_type'); // initial, purchase, sale, return, adjustment, transfer_in, transfer_out
            $table->decimal('quantity', 15, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->string('reference_type')->nullable(); // Sale, Purchase, etc.
            $table->unsignedBigInteger('reference_id')->nullable(); // ID de la vente, achat, etc.
            $table->text('notes')->nullable();
            $table->date('movement_date');
            $table->timestamps();

            $table->index(['product_id', 'warehouse_id']);
            $table->index(['movement_type']);
            $table->index(['movement_date']);
            $table->index(['reference_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
