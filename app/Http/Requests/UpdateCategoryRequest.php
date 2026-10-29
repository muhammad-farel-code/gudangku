<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Otorisasi hanya untuk manager dan admin.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('admin', 'manager');
    }

    /**
     * Sanitasi input teks bebas untuk menangkal XSS.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->name !== null ? trim(strip_tags((string) $this->name)) : null,
            'description' => $this->description !== null ? trim(strip_tags((string) $this->description)) : null,
        ]);
    }

    /**
     * Aturan validasi pembaruan kategori (mengabaikan ID kategori saat ini).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $category = $this->route('category');
        $categoryId = $category instanceof \App\Models\Category ? $category->id : $category;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')->ignore($categoryId),
            ],
            'description' => ['nullable', 'string', 'max:500'],
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
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'name.unique' => 'Nama kategori ini sudah terdaftar di sistem.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
        ];
    }
}
