<?php

namespace App\Models;

use App\Models\Contracts\JsonResourceful;
use App\Traits\HasJsonResourcefulData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\StockMovement
 *
 * @property int $id
 * @property int $product_id
 * @property int $warehouse_id
 * @property string $movement_type
 * @property float $quantity
 * @property float $unit_price
 * @property float $total_price
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon $movement_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product $product
 * @property-read \App\Models\Warehouse $warehouse
 *
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement query()
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereMovementDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereMovementType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereReferenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StockMovement whereWarehouseId($value)
 *
 * @mixin \Eloquent
 */
class StockMovement extends BaseModel implements JsonResourceful
{
    use HasFactory, HasJsonResourcefulData;

    protected $table = 'stock_movements';

    const JSON_API_TYPE = 'stock_movements';

    // Types de mouvements
    const TYPE_INITIAL = 'initial';
    const TYPE_PURCHASE = 'purchase';
    const TYPE_SALE = 'sale';
    const TYPE_RETURN = 'return';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_TRANSFER_IN = 'transfer_in';
    const TYPE_TRANSFER_OUT = 'transfer_out';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'movement_type',
        'quantity',
        'unit_price',
        'total_price',
        'reference_type',
        'reference_id',
        'notes',
        'movement_date',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'warehouse_id' => 'integer',
        'quantity' => 'float',
        'unit_price' => 'float',
        'total_price' => 'float',
        'reference_id' => 'integer',
        'movement_date' => 'date',
    ];

    public static $rules = [
        'product_id' => 'required|exists:products,id',
        'warehouse_id' => 'required|exists:warehouses,id',
        'movement_type' => 'required|string|in:initial,purchase,sale,return,adjustment,transfer_in,transfer_out',
        'quantity' => 'required|numeric',
        'unit_price' => 'required|numeric|min:0',
        'total_price' => 'required|numeric|min:0',
        'reference_type' => 'nullable|string',
        'reference_id' => 'nullable|integer',
        'notes' => 'nullable|string',
        'movement_date' => 'required|date',
    ];

    public function prepareLinks(): array
    {
        return [
            'self' => route('stock-movements.show', $this->id),
        ];
    }

    public function prepareAttributes(): array
    {
        return [
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'movement_type' => $this->movement_type,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'notes' => $this->notes,
            'movement_date' => $this->movement_date->format('Y-m-d'),
            'product' => $this->product,
            'warehouse' => $this->warehouse,
            'created_at' => $this->created_at,
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    /**
     * Obtenir le stock initial d'un produit à une date donnée
     */
    public static function getInitialStock($productId, $warehouseId, $date)
    {
        return self::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('movement_type', self::TYPE_INITIAL)
            ->where('movement_date', '<=', $date)
            ->sum('quantity');
    }

    /**
     * Obtenir les entrées d'un produit dans une période
     */
    public static function getIncomingStock($productId, $warehouseId, $startDate, $endDate)
    {
        return self::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->whereIn('movement_type', [self::TYPE_PURCHASE, self::TYPE_RETURN, self::TYPE_ADJUSTMENT, self::TYPE_TRANSFER_IN])
            ->whereBetween('movement_date', [$startDate, $endDate])
            ->sum('quantity');
    }

    /**
     * Obtenir les sorties d'un produit dans une période
     */
    public static function getOutgoingStock($productId, $warehouseId, $startDate, $endDate)
    {
        return self::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->whereIn('movement_type', [self::TYPE_SALE, self::TYPE_TRANSFER_OUT])
            ->whereBetween('movement_date', [$startDate, $endDate])
            ->sum('quantity');
    }

    /**
     * Obtenir le prix unitaire moyen d'un produit
     */
    public static function getAverageUnitPrice($productId, $warehouseId, $startDate, $endDate)
    {
        $movements = self::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->whereIn('movement_type', [self::TYPE_PURCHASE, self::TYPE_RETURN, self::TYPE_ADJUSTMENT])
            ->whereBetween('movement_date', [$startDate, $endDate])
            ->get();

        if ($movements->isEmpty()) {
            return 0;
        }

        $totalQuantity = $movements->sum('quantity');
        $totalPrice = $movements->sum('total_price');

        return $totalQuantity > 0 ? $totalPrice / $totalQuantity : 0;
    }
}

