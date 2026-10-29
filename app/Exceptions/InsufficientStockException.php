<?php

namespace App\Exceptions;

use App\Models\Product;
use Exception;

class InsufficientStockException extends Exception
{
    protected Product $product;
    protected int $requested;
    protected int $available;

    public function __construct(Product $product, int $requested, int $available)
    {
        $this->product = $product;
        $this->requested = $requested;
        $this->available = $available;

        $message = "Stok untuk produk '{$product->name}' (SKU: {$product->sku}) tidak mencukupi. Tersedia: {$available} {$product->unit}, diminta: {$requested} {$product->unit}.";

        parent::__construct($message);
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getRequested(): int
    {
        return $this->requested;
    }

    public function getAvailable(): int
    {
        return $this->available;
    }
}
