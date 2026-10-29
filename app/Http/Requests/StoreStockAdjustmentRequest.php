<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockAdjustmentRequest extends FormRequest
{
    /**
     * Hanya admin dan manager yang berwenang melakukan penyesuaian stok opname.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('admin', 'manager');
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
     * Aturan validasi penyesuaian stok opname.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'actual_stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['required', 'string', 'min:5', 'max:500'],
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
            'product_id.exists' => 'Produk yang dipilih tidak valid.',
            'actual_stock.required' => 'Jumlah stok fisik aktual hasil opname wajib diisi.',
            'actual_stock.integer' => 'Stok fisik harus berupa bilangan bulat.',
            'actual_stock.min' => 'Stok fisik tidak boleh bernilai negatif.',
            'transaction_date.required' => 'Tanggal penyesuaian wajib diisi.',
            'transaction_date.date' => 'Format tanggal tidak valid.',
            'transaction_date.before_or_equal' => 'Tanggal transaksi tidak boleh melebihi hari ini.',
            'notes.required' => 'Alasan penyesuaian stok wajib diisi.',
            'notes.min' => 'Alasan penyesuaian minimal 5 karakter.',
            'notes.max' => 'Alasan penyesuaian maksimal 500 karakter.',
        ];
    }
}
