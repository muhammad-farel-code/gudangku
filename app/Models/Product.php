<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'supplier_id',
        'sku',
        'name',
        'description',
        'unit',
        'purchase_price',
        'selling_price',
        'stock',
        'min_stock',
        'rack_location',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'stock' => 'integer',
            'min_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relasi ke kategori produk.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relasi ke pemasok produk.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relasi ke riwayat transaksi stok produk ini.
     */
    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }

    /**
     * Scope untuk produk dengan stok menipis atau habis (stok <= min_stock).
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }

    /**
     * Scope untuk produk yang aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Accessor status stok: 'Habis', 'Menipis', atau 'Aman'.
     */
    protected function stockStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->stock <= 0) {
                    return 'Habis';
                }
                if ($this->stock <= $this->min_stock) {
                    return 'Menipis';
                }
                return 'Aman';
            }
        );
    }
}
