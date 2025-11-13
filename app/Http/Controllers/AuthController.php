<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth; 

class AuthController extends Controller
{
    // =====================================================
    // LOGIKA REGISTRASI
    // =====================================================

    /**
     * LOGIKA 1: Menampilkan halaman form registrasi.
     */
    public function registerCreate(): View
    {
        return view('Page.daftar'); 
    }

    /**
     * LOGIKA 2: Memproses data registrasi.
     */
    public function registerStore(Request $request): RedirectResponse
    {
        // 1. VALIDASI: (Kode ini dari kamu, sudah 100% benar)
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'username'   => 'required|string|max:255|unique:users',
            'email'      => 'required|string|email|max:255|unique:users',
            'password'   => 'required|string|min:8|confirmed',
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

        
        User::create($validatedData);

       
        return redirect()->route('login.form')->with('success', 'Akun berhasil dibuat! Silakan login.');
    }

    // =====================================================
    // LOGIKA LOGIN
    // =====================================================

   
    public function loginCreate(): View
    {
        return view('Page.Login');
    }

    /**
     * LOGIKA 4: Memproses percobaan login.
     */
    public function loginStore(Request $request): RedirectResponse
    {
        // 1. Validasi (Email & Password)
        $credentials = $request->validate([
            'credential' => 'required|email',
            'password'   => 'required|string',
        ], [
            'credential.required' => 'Email wajib diisi.',
            'credential.email'    => 'Format email tidak valid.'
        ]);

        $credentials['email'] = $credentials['credential'];
        unset($credentials['credential']);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            // Arahkan ke Halaman Pengaturan
            return redirect()->route('pengaturan');
        }

        // 4. Jika GAGAL
        return back()->with('error', 'Email atau Password yang Anda masukkan salah.');
    }

    // =====================================================
    // LOGIKA LOGOUT
    // =====================================================

    /**
     * LOGIKA 5: Logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Kembalikan ke halaman login
        return redirect()->route('login.form');
    }
}