<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockInRequest extends FormRequest
{
    /**
     * Semua pengguna terotentikasi (admin, manager, staff) dapat mencatat stok masuk.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Sanitasi input sebelum validasi.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'notes' => $this->notes !== null ? trim(strip_tags((string) $this->notes)) : null,
            'transaction_date' => $this->transaction_date ?: now()->toDateString(),
        ]);
    }

    /**
     * Aturan validasi stok masuk.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where('is_active', true),
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:500'],
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
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists' => 'Produk yang dipilih tidak valid atau sedang dinonaktifkan.',
            'quantity.required' => 'Jumlah barang masuk wajib diisi.',
            'quantity.integer' => 'Jumlah barang harus berupa bilangan bulat.',
            'quantity.min' => 'Jumlah barang masuk minimal 1 unit.',
            'supplier_id.exists' => 'Pemasok yang dipilih tidak valid.',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'transaction_date.date' => 'Format tanggal transaksi tidak valid.',
            'transaction_date.before_or_equal' => 'Tanggal transaksi tidak boleh melebihi hari ini.',
            'notes.max' => 'Catatan maksimal 500 karakter.',
        ];
    }
}
