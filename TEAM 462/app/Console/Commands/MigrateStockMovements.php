<?php

namespace App\Console\Commands;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateStockMovements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:migrate-movements {--force : Force la migration même si des mouvements existent déjà}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migre les données existantes des achats et ventes vers les mouvements de stock';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Début de la migration des mouvements de stock...');

        // Vérifier si des mouvements existent déjà
        $existingMovements = StockMovement::count();
        if ($existingMovements > 0 && !$this->option('force')) {
            $this->error("Des mouvements de stock existent déjà ({$existingMovements} enregistrements).");
            $this->error("Utilisez l'option --force pour forcer la migration.");
            return 1;
        }

        if ($this->option('force') && $existingMovements > 0) {
            $this->warn("Suppression des mouvements existants...");
            StockMovement::truncate();
        }

        DB::beginTransaction();

        try {
            // Migrer les achats
            $this->migratePurchases();
            
            // Migrer les ventes
            $this->migrateSales();
            
            DB::commit();
            
            $totalMovements = StockMovement::count();
            $this->info("Migration terminée avec succès ! {$totalMovements} mouvements créés.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Erreur lors de la migration : " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Migre les achats vers les mouvements de stock
     */
    private function migratePurchases()
    {
        $this->info('Migration des achats...');
        
        $purchases = Purchase::with('purchaseItems')->get();
        $bar = $this->output->createProgressBar($purchases->count());
        $bar->start();

        foreach ($purchases as $purchase) {
            foreach ($purchase->purchaseItems as $item) {
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $purchase->warehouse_id,
                    'movement_type' => 'purchase',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->net_unit_cost ?? $item->product_cost ?? 0,
                    'total_price' => $item->quantity * ($item->net_unit_cost ?? $item->product_cost ?? 0),
                    'reference_type' => 'Purchase',
                    'reference_id' => $purchase->id,
                    'notes' => 'Migration automatique - Achat',
                    'movement_date' => $purchase->date,
                ]);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }

    /**
     * Migre les ventes vers les mouvements de stock
     */
    private function migrateSales()
    {
        $this->info('Migration des ventes...');
        
        $sales = Sale::with('saleItems')->get();
        $bar = $this->output->createProgressBar($sales->count());
        $bar->start();

        foreach ($sales as $sale) {
            foreach ($sale->saleItems as $item) {
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $sale->warehouse_id,
                    'movement_type' => 'sale',
                    'quantity' => $item->quantity,
                    'unit_price' => $item->product_price ?? $item->net_unit_price ?? 0,
                    'total_price' => $item->quantity * ($item->product_price ?? $item->net_unit_price ?? 0),
                    'reference_type' => 'Sale',
                    'reference_id' => $sale->id,
                    'notes' => 'Migration automatique - Vente',
                    'movement_date' => $sale->date,
                ]);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }
}


