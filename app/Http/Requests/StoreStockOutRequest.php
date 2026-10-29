<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockOutRequest extends FormRequest
{
    /**
     * Semua pengguna terotentikasi (admin, manager, staff) dapat mencatat stok keluar.
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
            'recipient' => $this->recipient !== null ? trim(strip_tags((string) $this->recipient)) : null,
            'notes' => $this->notes !== null ? trim(strip_tags((string) $this->notes)) : null,
            'transaction_date' => $this->transaction_date ?: now()->toDateString(),
        ]);
    }

    /**
     * Aturan validasi stok keluar.
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
            'recipient' => ['required', 'string', 'max:150'],
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
            'quantity.required' => 'Jumlah barang keluar wajib diisi.',
            'quantity.integer' => 'Jumlah barang harus berupa bilangan bulat.',
            'quantity.min' => 'Jumlah barang keluar minimal 1 unit.',
            'recipient.required' => 'Nama penerima atau divisi tujuan barang wajib diisi.',
            'recipient.max' => 'Nama penerima maksimal 150 karakter.',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'transaction_date.date' => 'Format tanggal transaksi tidak valid.',
            'transaction_date.before_or_equal' => 'Tanggal transaksi tidak boleh melebihi hari ini.',
            'notes.max' => 'Catatan maksimal 500 karakter.',
        ];
    }
}
