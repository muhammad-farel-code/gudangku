<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Otorisasi hanya untuk manager dan admin.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('admin', 'manager');
    }

    /**
     * Sanitasi input sebelum validasi untuk menangkal XSS.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'sku' => $this->sku !== null ? strtoupper(trim(strip_tags((string) $this->sku))) : null,
            'name' => $this->name !== null ? trim(strip_tags((string) $this->name)) : null,
            'description' => $this->description !== null ? trim(strip_tags((string) $this->description)) : null,
            'unit' => $this->unit !== null ? strtolower(trim(strip_tags((string) $this->unit))) : 'pcs',
            'rack_location' => $this->rack_location !== null ? strtoupper(trim(strip_tags((string) $this->rack_location))) : null,
            'is_active' => $this->boolean('is_active', true),
        ]);
    }

    /**
     * Aturan validasi penyimpanan produk baru (termasuk BR10: selling_price >= purchase_price).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'unit' => ['required', 'string', 'max:20'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'gte:purchase_price'],
            'initial_stock' => ['nullable', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'rack_location' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Pesan validasi bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori produk wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'supplier_id.exists' => 'Pemasok yang dipilih tidak valid.',
            'sku.required' => 'Kode SKU produk wajib diisi.',
            'sku.max' => 'Kode SKU maksimal 50 karakter.',
            'sku.unique' => 'Kode SKU ini sudah terdaftar. Gunakan SKU lain.',
            'name.required' => 'Nama produk wajib diisi.',
            'name.max' => 'Nama produk maksimal 150 karakter.',
            'unit.required' => 'Satuan produk wajib diisi (misal: pcs, box, kg).',
            'purchase_price.required' => 'Harga beli wajib diisi.',
            'purchase_price.min' => 'Harga beli tidak boleh bernilai negatif.',
            'selling_price.required' => 'Harga jual wajib diisi.',
            'selling_price.gte' => 'Harga jual harus lebih besar atau sama dengan harga beli (Aturan BR10).',
            'initial_stock.min' => 'Stok awal tidak boleh bernilai negatif.',
            'min_stock.required' => 'Batas minimum stok wajib diisi.',
            'min_stock.min' => 'Batas minimum stok tidak boleh bernilai negatif.',
        ];
    }
}
