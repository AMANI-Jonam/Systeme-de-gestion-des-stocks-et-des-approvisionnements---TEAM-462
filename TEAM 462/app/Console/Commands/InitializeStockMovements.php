<?php

namespace App\Console\Commands;

use App\Models\ManageStock;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InitializeStockMovements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:initialize {--date= : Date d\'initialisation (format: Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialise les mouvements de stock avec les stocks actuels';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Initialisation des mouvements de stock...');

        $initDate = $this->option('date') ?: '2024-01-01';
        
        DB::beginTransaction();

        try {
            $stocks = ManageStock::with(['product', 'warehouse'])->get();
            $bar = $this->output->createProgressBar($stocks->count());
            $bar->start();

            foreach ($stocks as $stock) {
                if ($stock->quantity > 0) {
                    // Vérifier si un mouvement initial existe déjà
                    $existingInitial = StockMovement::where('product_id', $stock->product_id)
                        ->where('warehouse_id', $stock->warehouse_id)
                        ->where('movement_type', 'initial')
                        ->first();

                    if (!$existingInitial) {
                        StockMovement::create([
                            'product_id' => $stock->product_id,
                            'warehouse_id' => $stock->warehouse_id,
                            'movement_type' => 'initial',
                            'quantity' => $stock->quantity,
                            'unit_price' => $stock->product->product_price ?? 0,
                            'total_price' => $stock->quantity * ($stock->product->product_price ?? 0),
                            'reference_type' => null,
                            'reference_id' => null,
                            'notes' => 'Stock initial - Migration automatique',
                            'movement_date' => $initDate,
                        ]);
                    }
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            
            DB::commit();
            
            $totalInitials = StockMovement::where('movement_type', 'initial')->count();
            $this->info("Initialisation terminée ! {$totalInitials} mouvements initiaux créés.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Erreur lors de l'initialisation : " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}


