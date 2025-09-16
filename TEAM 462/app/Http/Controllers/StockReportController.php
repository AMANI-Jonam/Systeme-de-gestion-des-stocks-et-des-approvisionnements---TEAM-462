<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Exports\StockReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class StockReportController extends Controller
{
    /**
     * Obtenir la liste des produits pour la sélection
     */
    public function getProducts(Request $request): JsonResponse
    {
        $warehouseId = $request->get('warehouse_id');
        
        if ($warehouseId) {
            // Récupérer seulement les produits qui ont des mouvements dans cet entrepôt
            $productIds = StockMovement::where('warehouse_id', $warehouseId)
                ->distinct()
                ->pluck('product_id');
                
            $products = Product::with(['productCategory', 'brand'])
                ->whereIn('id', $productIds)
                ->select('id', 'name', 'code', 'product_price', 'product_unit')
                ->get();
        } else {
            // Récupérer tous les produits si aucun entrepôt n'est sélectionné
            $products = Product::with(['productCategory', 'brand'])
                ->select('id', 'name', 'code', 'product_price', 'product_unit')
                ->get();
        }

        $products = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'price' => $product->product_price,
                'unit' => $product->product_unit,
                'category' => $product->productCategory->name ?? '',
                'brand' => $product->brand->name ?? '',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    /**
     * Obtenir la liste des entrepôts
     */
    public function getWarehouses(): JsonResponse
    {
        $warehouses = Warehouse::select('id', 'name')->get();

        return response()->json([
            'success' => true,
            'data' => $warehouses
        ]);
    }

    /**
     * Générer la fiche de stock pour un produit et une période
     */
    public function generateStockReport(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $productId = $request->product_id;
        $warehouseId = $request->warehouse_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // Récupérer les informations du produit
        $product = Product::with(['productCategory', 'brand'])->find($productId);
        $warehouse = Warehouse::find($warehouseId);

        // Récupérer les mouvements de stock pour la période
        $movements = StockMovement::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->whereBetween('movement_date', [$startDate, $endDate])
            ->orderBy('movement_date')
            ->get();

        // Calculer le stock initial (avant la période)
        $initialStock = StockMovement::getInitialStock($productId, $warehouseId, $startDate);
        
        // Calculer les totaux pour la période
        $incomingStock = StockMovement::getIncomingStock($productId, $warehouseId, $startDate, $endDate);
        $outgoingStock = StockMovement::getOutgoingStock($productId, $warehouseId, $startDate, $endDate);
        
        // Calculer le prix unitaire moyen
        $averageUnitPrice = StockMovement::getAverageUnitPrice($productId, $warehouseId, $startDate, $endDate);
        
        // Si aucun mouvement dans la période, utiliser le prix du produit
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
                'date' => $movement->movement_date->format('Y-m-d'),
                'product_name' => $product->name,
                'stock_initial' => $runningStock,
                'entree' => $entree,
                'total' => $total,
                'sortie' => $sortie,
                'stock_final' => $finalStock,
                'prix_unitaire' => $movement->unit_price,
                'prix_total' => $finalStock * $movement->unit_price,
                'movement_type' => $movement->movement_type,
                'notes' => $movement->notes,
            ];

            $runningStock = $finalStock;
        }

        // Si aucun mouvement dans la période, créer une entrée avec le stock initial
        if (empty($stockReport)) {
            $stockReport[] = [
                'date' => $startDate,
                'product_name' => $product->name,
                'stock_initial' => $initialStock,
                'entree' => 0,
                'total' => $initialStock,
                'sortie' => 0,
                'stock_final' => $initialStock,
                'prix_unitaire' => $averageUnitPrice,
                'prix_total' => $initialStock * $averageUnitPrice,
                'movement_type' => 'no_movement',
                'notes' => 'Aucun mouvement dans cette période',
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'category' => $product->productCategory->name ?? '',
                    'brand' => $product->brand->name ?? '',
                ],
                'warehouse' => [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                ],
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'summary' => [
                    'stock_initial' => $initialStock,
                    'total_entree' => $incomingStock,
                    'total_sortie' => $outgoingStock,
                    'stock_final' => $initialStock + $incomingStock - $outgoingStock,
                    'prix_unitaire_moyen' => $averageUnitPrice,
                ],
                'movements' => $stockReport,
            ]
        ]);
    }

    /**
     * Exporter la fiche de stock en PDF
     */
    public function exportPdf(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $productId = $request->product_id;
        $warehouseId = $request->warehouse_id;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // Récupérer les informations du produit
        $product = Product::with(['productCategory', 'brand'])->find($productId);
        $warehouse = Warehouse::find($warehouseId);

        // Récupérer les mouvements de stock pour la période
        $movements = StockMovement::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->whereBetween('movement_date', [$startDate, $endDate])
            ->orderBy('movement_date')
            ->get();

        // Calculer le stock initial
        $initialStock = StockMovement::getInitialStock($productId, $warehouseId, $startDate);
        
        // Calculer les totaux pour la période
        $incomingStock = StockMovement::getIncomingStock($productId, $warehouseId, $startDate, $endDate);
        $outgoingStock = StockMovement::getOutgoingStock($productId, $warehouseId, $startDate, $endDate);
        
        // Calculer le prix unitaire moyen
        $averageUnitPrice = StockMovement::getAverageUnitPrice($productId, $warehouseId, $startDate, $endDate);
        
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
                'date' => $movement->movement_date->format('Y-m-d'),
                'product_name' => $product->name,
                'stock_initial' => $runningStock,
                'entree' => $entree,
                'total' => $total,
                'sortie' => $sortie,
                'stock_final' => $finalStock,
                'prix_unitaire' => $movement->unit_price,
                'prix_total' => $finalStock * $movement->unit_price,
                'movement_type' => $movement->movement_type,
                'notes' => $movement->notes,
            ];

            $runningStock = $finalStock;
        }

        // Si aucun mouvement dans la période
        if (empty($stockReport)) {
            $stockReport[] = [
                'date' => $startDate,
                'product_name' => $product->name,
                'stock_initial' => $initialStock,
                'entree' => 0,
                'total' => $initialStock,
                'sortie' => 0,
                'stock_final' => $initialStock,
                'prix_unitaire' => $averageUnitPrice,
                'prix_total' => $initialStock * $averageUnitPrice,
                'movement_type' => 'no_movement',
                'notes' => 'Aucun mouvement dans cette période',
            ];
        }

        // Préparer le logo
        $companyLogo = getLogoUrl();
        $companyLogo = (string) \Image::make($companyLogo)->encode('data-url');

        // Préparer les données pour la vue
        $data = [
            'product' => [
                'name' => $product->name,
                'code' => $product->code,
                'category' => $product->productCategory->name ?? '',
                'brand' => $product->brand->name ?? '',
            ],
            'warehouse' => [
                'name' => $warehouse->name,
            ],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => [
                'stock_initial' => $initialStock,
                'total_entree' => $incomingStock,
                'total_sortie' => $outgoingStock,
                'stock_final' => $initialStock + $incomingStock - $outgoingStock,
                'prix_unitaire_moyen' => $averageUnitPrice,
            ],
            'movements' => $stockReport,
            'companyLogo' => $companyLogo,
        ];

        // Générer le PDF
        $pdf = Pdf::loadView('reports.stock-report-pdf', $data)->setOptions([
            'tempDir' => public_path(),
            'chroot' => public_path(),
        ]);
        
        return $pdf->download('fiche-stock-' . $product->code . '-' . $startDate . '.pdf');
    }

    /**
     * Exporter la fiche de stock en Excel
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Ajouter les paramètres à la requête pour l'export
        $request->merge([
            'export_type' => 'stock_report',
            'product_id' => $request->product_id,
            'warehouse_id' => $request->warehouse_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return Excel::download(new StockReportExport(), 'fiche-stock-' . $request->product_id . '.xlsx');
    }
}
