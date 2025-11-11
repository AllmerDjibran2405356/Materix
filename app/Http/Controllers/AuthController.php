<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth; // <-- Panggil "Satpam" Keamanan

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
        // Ganti 'Page.daftar' sesuai nama file Blade registrasi kamu
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

        // 2. SIMPAN KE DATABASE: (Sudah 100% benar)
        User::create($validatedData);

        // 3. KEMBALIKAN:
        //    (Kita arahkan ke halaman Login)
        return redirect()->route('login.form')->with('success', 'Akun berhasil dibuat! Silakan login.');
    }

    // =====================================================
    // LOGIKA LOGIN
    // =====================================================

    /**
     * LOGIKA 3: Menampilkan halaman form login.
     */
    public function loginCreate(): View
    {
        // Ganti 'Page.Login' sesuai nama file Blade login kamu
        return view('Page.Login');
    }

    /**
     * LOGIKA 4: Memproses percobaan login.
     */
    public function loginStore(Request $request): RedirectResponse
    {
        // 1. Validasi (Email & Password)
        // Kita pakai nama 'credential' dari form login simple kita
        $credentials = $request->validate([
            'credential' => 'required|email',
            'password'   => 'required|string',
        ], [
            'credential.required' => 'Email wajib diisi.',
            'credential.email'    => 'Format email tidak valid.'
        ]);

        // Ganti nama 'credential' menjadi 'email' agar "Sihir" Auth::attempt() paham
        $credentials['email'] = $credentials['credential'];
        unset($credentials['credential']);

        // 2. "Sihir" Auth::attempt()
        if (Auth::attempt($credentials)) {
            // 3. Jika BERHASIL
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