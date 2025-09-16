<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Warehouse;
use Carbon\Carbon;

class StockMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer les premiers produits et entrepôts
        $products = Product::take(5)->get();
        $warehouses = Warehouse::take(2)->get();

        if ($products->isEmpty() || $warehouses->isEmpty()) {
            $this->command->info('Aucun produit ou entrepôt trouvé. Veuillez d\'abord créer des produits et des entrepôts.');
            return;
        }

        $product = $products->first();
        $warehouse = $warehouses->first();

        // Créer des mouvements de stock de test
        $movements = [
            [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'movement_type' => StockMovement::TYPE_INITIAL,
                'quantity' => 100,
                'unit_price' => $product->product_price,
                'total_price' => 100 * $product->product_price,
                'reference_type' => null,
                'reference_id' => null,
                'notes' => 'Stock initial',
                'movement_date' => Carbon::now()->subDays(30),
            ],
            [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'movement_type' => StockMovement::TYPE_PURCHASE,
                'quantity' => 50,
                'unit_price' => $product->product_price,
                'total_price' => 50 * $product->product_price,
                'reference_type' => 'Purchase',
                'reference_id' => 1,
                'notes' => 'Achat de stock',
                'movement_date' => Carbon::now()->subDays(25),
            ],
            [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'movement_type' => StockMovement::TYPE_SALE,
                'quantity' => 20,
                'unit_price' => $product->product_price,
                'total_price' => 20 * $product->product_price,
                'reference_type' => 'Sale',
                'reference_id' => 1,
                'notes' => 'Vente de stock',
                'movement_date' => Carbon::now()->subDays(20),
            ],
            [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'movement_type' => StockMovement::TYPE_PURCHASE,
                'quantity' => 30,
                'unit_price' => $product->product_price,
                'total_price' => 30 * $product->product_price,
                'reference_type' => 'Purchase',
                'reference_id' => 2,
                'notes' => 'Achat de stock',
                'movement_date' => Carbon::now()->subDays(15),
            ],
            [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'movement_type' => StockMovement::TYPE_SALE,
                'quantity' => 25,
                'unit_price' => $product->product_price,
                'total_price' => 25 * $product->product_price,
                'reference_type' => 'Sale',
                'reference_id' => 2,
                'notes' => 'Vente de stock',
                'movement_date' => Carbon::now()->subDays(10),
            ],
            [
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'movement_type' => StockMovement::TYPE_ADJUSTMENT,
                'quantity' => 5,
                'unit_price' => $product->product_price,
                'total_price' => 5 * $product->product_price,
                'reference_type' => 'Adjustment',
                'reference_id' => 1,
                'notes' => 'Ajustement de stock',
                'movement_date' => Carbon::now()->subDays(5),
            ],
        ];

        foreach ($movements as $movement) {
            StockMovement::create($movement);
        }

        $this->command->info('Mouvements de stock de test créés avec succès!');
    }
}