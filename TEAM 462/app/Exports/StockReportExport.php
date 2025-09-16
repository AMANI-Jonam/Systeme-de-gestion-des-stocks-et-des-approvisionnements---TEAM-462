<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StockReportExport implements FromView, WithTitle, WithStyles, WithColumnWidths
{
    protected $productId;
    protected $warehouseId;
    protected $startDate;
    protected $endDate;

    public function __construct()
    {
        $this->productId = request()->get('product_id');
        $this->warehouseId = request()->get('warehouse_id');
        $this->startDate = request()->get('start_date');
        $this->endDate = request()->get('end_date');
    }

    public function view(): \Illuminate\Contracts\View\View
    {
        // Récupérer les informations du produit
        $product = Product::with(['productCategory', 'brand'])->find($this->productId);
        $warehouse = Warehouse::find($this->warehouseId);

        // Récupérer les mouvements de stock pour la période
        $movements = StockMovement::where('product_id', $this->productId)
            ->where('warehouse_id', $this->warehouseId)
            ->whereBetween('movement_date', [$this->startDate, $this->endDate])
            ->orderBy('movement_date')
            ->get();

        // Calculer le stock initial
        $initialStock = StockMovement::getInitialStock($this->productId, $this->warehouseId, $this->startDate);
        
        // Calculer les totaux pour la période
        $incomingStock = StockMovement::getIncomingStock($this->productId, $this->warehouseId, $this->startDate, $this->endDate);
        $outgoingStock = StockMovement::getOutgoingStock($this->productId, $this->warehouseId, $this->startDate, $this->endDate);
        
        // Calculer le prix unitaire moyen
        $averageUnitPrice = StockMovement::getAverageUnitPrice($this->productId, $this->warehouseId, $this->startDate, $this->endDate);
        
        if ($averageUnitPrice == 0) {
            $averageUnitPrice = $product->product_price;
        }

        // Construire la fiche de stock
        $stockReport = [];
        $runningStock = $initialStock;

        foreach ($movements as $movement) {
            $isIncoming = in_array($movement->movement_type, ['purchase', 'return', 'adjustment', 'transfer_in']);
            $isOutgoing = in_array($movement->movement_type, ['sale', 'transfer_out']);
            
            // Calculer les valeurs
            $entree = $isIncoming ? $movement->quantity : 0;
            $sortie = $isOutgoing ? $movement->quantity : 0;
            
            // Total = Stock initial + Entrée (même pour les ventes où entrée = 0)
            $total = $runningStock + $entree;
            
            // Stock final = Total - Sortie
            $finalStock = $total - $sortie;
            
            $stockReport[] = [
                'date' => $movement->movement_date->format('d/m/Y'),
                'product_name' => $product->name,
                'stock_initial' => $runningStock,
                'entree' => $entree,
                'total' => $total,
                'sortie' => $sortie,
                'stock_final' => $finalStock,
                'prix_unitaire' => $movement->unit_price,
                'prix_total' => $finalStock * $movement->unit_price,
                'movement_type' => $this->getMovementTypeLabel($movement->movement_type),
                'notes' => $movement->notes,
            ];

            $runningStock = $finalStock;
        }

        // Si aucun mouvement dans la période
        if (empty($stockReport)) {
            $stockReport[] = [
                'date' => \Carbon\Carbon::parse($this->startDate)->format('d/m/Y'),
                'product_name' => $product->name,
                'stock_initial' => $initialStock,
                'entree' => 0,
                'total' => $initialStock,
                'sortie' => 0,
                'stock_final' => $initialStock,
                'prix_unitaire' => $averageUnitPrice,
                'prix_total' => $initialStock * $averageUnitPrice,
                'movement_type' => 'Aucun mouvement',
                'notes' => 'Aucun mouvement dans cette période',
            ];
        }

        return view('excel.stock-report-excel', [
            'product' => $product,
            'warehouse' => $warehouse,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'summary' => [
                'stock_initial' => $initialStock,
                'total_entree' => $incomingStock,
                'total_sortie' => $outgoingStock,
                'stock_final' => $initialStock + $incomingStock - $outgoingStock,
                'prix_unitaire_moyen' => $averageUnitPrice,
            ],
            'movements' => $stockReport,
        ]);
    }

    public function title(): string
    {
        return 'Fiche de Stock';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['bold' => true, 'size' => 14]],
            4 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12, // Date
            'B' => 25, // Nom du produit
            'C' => 12, // Stock initial
            'D' => 10, // Entrée
            'E' => 10, // Total
            'F' => 10, // Sortie
            'G' => 12, // Stock final
            'H' => 12, // Prix unitaire
            'I' => 12, // Prix total
            'J' => 15, // Type de mouvement
            'K' => 20, // Notes
        ];
    }

    private function getMovementTypeLabel($type)
    {
        $labels = [
            'initial' => 'Stock initial',
            'purchase' => 'Achat',
            'sale' => 'Vente',
            'return' => 'Retour',
            'adjustment' => 'Ajustement',
            'transfer_in' => 'Transfert entrant',
            'transfer_out' => 'Transfert sortant',
        ];

        return $labels[$type] ?? $type;
    }
}