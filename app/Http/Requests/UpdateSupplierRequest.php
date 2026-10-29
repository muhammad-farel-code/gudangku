<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
{
    /**
     * Otorisasi hanya untuk manager dan admin.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('admin', 'manager');
    }

    /**
     * Sanitasi input teks bebas untuk menangkal serangan XSS.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->name !== null ? trim(strip_tags((string) $this->name)) : null,
            'contact_person' => $this->contact_person !== null ? trim(strip_tags((string) $this->contact_person)) : null,
            'phone' => $this->phone !== null ? trim(strip_tags((string) $this->phone)) : null,
            'email' => $this->email !== null ? trim(strip_tags((string) $this->email)) : null,
            'address' => $this->address !== null ? trim(strip_tags((string) $this->address)) : null,
        ]);
    }

    /**
     * Aturan validasi pembaruan data pemasok.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'regex:/^[0-9+\-\s()]{6,30}$/'],
            'email' => ['nullable', 'string', 'email:rfc', 'max:150'],
            'address' => ['nullable', 'string', 'max:500'],
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
            'name.required' => 'Nama pemasok wajib diisi.',
            'name.max' => 'Nama pemasok maksimal 150 karakter.',
            'contact_person.max' => 'Nama kontak person maksimal 100 karakter.',
            'phone.regex' => 'Format nomor telepon tidak valid (hanya angka, +, -, spasi, tanda kurung min 6 karakter).',
            'email.email' => 'Format alamat email tidak valid.',
            'email.max' => 'Alamat email maksimal 150 karakter.',
            'address.max' => 'Alamat maksimal 500 karakter.',
        ];
    }
}
