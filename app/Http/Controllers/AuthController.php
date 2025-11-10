<?php

namespace App\Http\Controllers;

use App\Models\User; // <-- 1. Kita panggil "Satpam" (Model User)
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash; // Kita tidak perlu ini jika pakai 'casts'

class AuthController extends Controller
{
    /**
     * LOGIKA UNTUK MENAMPILKAN HALAMAN FORM REGISTRASI
     * Ini dipanggil oleh Route::get()
     */
    public function create(): View
    {
        // Tugasnya hanya 1: Tampilkan file Blade-nya
        return view('Page.daftar');
    }

    /**
     * LOGIKA UNTUK MEMPROSES DATA SAAT FORM DI-SUBMIT
     * Ini dipanggil oleh Route::post()
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. VALIDASI: Cek semua data yang masuk
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'username'   => 'required|string|max:255|unique:users',
            'email'      => 'required|string|email|max:255|unique:users',
            'password'   => 'required|string|min:8|confirmed', // 'confirmed' akan otomatis cek 'password_confirmation'
        ], [
            // Pesan Error kustom Bahasa Indonesia
            'first_name.required' => 'Nama depan wajib diisi.',
            'last_name.required'  => 'Nama belakang wajib diisi.',
            'username.required'   => 'Nama pengguna wajib diisi.',
            'username.unique'     => 'Nama pengguna ini sudah terpakai.',
            'email.required'      => 'Email wajib diisi.',
            'email.email'         => 'Format email tidak valid.',
            'email.unique'        => 'Email ini sudah terdaftar.',
            'password.required'   => 'Kata sandi wajib diisi.',
            'password.min'        => 'Kata sandi minimal harus 8 karakter.',
            'password.confirmed'  => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        // 2. SIMPAN KE DATABASE:
        //    Berikan data yang sudah lolos validasi ke "Satpam" (User Model)
        User::create($validatedData);


        // 3. KEMBALIKAN:
        //    Beri tahu user bahwa registrasi sukses.
        return redirect()->back()->with('success', 'Akun berhasil dibuat');
    }
}