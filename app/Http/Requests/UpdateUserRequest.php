<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Hanya admin yang berwenang memperbarui pengguna.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Sanitasi input sebelum validasi untuk mencegah XSS.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->name !== null ? trim(strip_tags((string) $this->name)) : null,
            'email' => $this->email !== null ? strtolower(trim((string) $this->email)) : null,
            'is_active' => $this->boolean('is_active', true),
        ]);
    }

    /**
     * Aturan validasi pembaruan pengguna.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $targetUser = $this->route('user');
        $userId = $targetUser instanceof User ? $targetUser->id : $targetUser;

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'email:rfc',
                'max:150',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in(['admin', 'manager', 'staff'])],
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
            'name.required' => 'Nama pengguna wajib diisi.',
            'name.max' => 'Nama pengguna maksimal 100 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'role.required' => 'Peran (role) akun wajib dipilih.',
            'role.in' => 'Peran akun yang dipilih tidak valid.',
        ];
    }
}
