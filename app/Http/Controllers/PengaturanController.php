<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth; // Panggil "Satpam"
use Illuminate\Support\Facades\Hash; // Panggil "Pengecek Sandi"
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule; // Panggil "Aturan Validasi"
use Illuminate\Http\JsonResponse;

class PengaturanController extends Controller
{
    /**
     * LOGIKA 1: Menampilkan halaman Pengaturan.
     */
    public function index(): View
    {
        // Pastikan nama file ini benar (Page/pengaturanindex.blade.php)
        return view('Page.pengaturanindex');
    }

    /**
     * LOGIKA 2: Update Info Akun (dari Modal 1)
     * (Kita akan isi ini nanti, fokus di sandi dulu)
     */
    public function updateInfo(Request $request): RedirectResponse
    {
        // 1. Dapatkan user yang sedang login
        $user = Auth::user();

        // 2. Validasi data
        $validatedData = $request->validate([
            'username'   => [
                'required','string','max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'email'      => [
                'required','string','email','max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
        ]);

        // 3. Simpan data baru ke database
        $user->update($validatedData);

        return back()->with('success', 'Informasi akun berhasil diperbarui!');
    }

    /**
     * LOGIKA 3: Update Password (dari Modal 2 & 3)
     */
    public function updatePassword(Request $request): RedirectResponse
    {

        // 1. Dapatkan user yang sedang login
        $user = Auth::user();

        // 2. Validasi (Ini adalah gabungan data dari Modal 2 (via JS) dan Modal 3)
        $validatedData = $request->validate([
            'sandi_lama' => 'required|string',
            'sandi_baru' => 'required|string|min:8|confirmed',
        ], [
            'sandi_lama.required' => 'Kata sandi lama wajib diisi.',
            'sandi_baru.required' => 'Kata sandi baru wajib diisi.',
            'sandi_baru.min' => 'Kata sandi baru minimal harus 8 karakter.',
            'sandi_baru.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        // 3. LOGIKA KEAMANAN: Cek apakah sandi lama yang dimasukkan benar
        if (!Hash::check($request->sandi_lama, $user->password)) {
            // Jika salah, kembalikan dengan pesan error
            return back()->with('error', 'Kata sandi lama yang Anda masukkan salah.');
        }

        // 4. Jika sandi lama benar, simpan sandi baru
        $user->update([
            'password' => $request->sandi_baru
        ]);

        // 5. Kembalikan dengan pesan sukses
        return back()->with('success', 'Kata sandi berhasil diubah!');
    }

    
    // LOGIKA 4: (Fitur Foto Dimatikan Sesuai Permintaan)
    // public function updatePhoto(Request $request)
    // {
    //     // ...
    // }

    public function cekSandiLama(Request $request): JsonResponse
    {
        // Validasi input
        $request->validate([
            'sandi_lama' => 'required|string',
        ]);

        $user = Auth::user();

        // Cek hash
        if (Hash::check($request->sandi_lama, $user->password)) {
            // Jika BENAR, kirim balasan sukses
            return response()->json(['success' => true]);
        } else {
            // Jika SALAH, kirim balasan error
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi lama yang Anda masukkan salah.'
            ], 422); // 422 adalah kode error validasi
        }
    }


}