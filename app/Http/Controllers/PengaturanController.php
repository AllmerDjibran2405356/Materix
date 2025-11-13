<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash; 
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule; 
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


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
            'password' => $request->sandi_baru,
        ]);

        // 5. Kembalikan dengan pesan sukses
        return back()->with('success', 'Kata sandi berhasil diubah!');
    }

    
    // LOGIKA 4: (Fitur Foto Dimatikan)
    // public function updatePhoto(Request $request)
    // {
    //     // ...
    // }

   public function cekSandiLama(Request $request): JsonResponse
    {
    // Debug logging
    Log::info('CekSandiLama called', ['input' => $request->all()]);
    
    $request->validate([
        'sandi_lama' => 'required|string',
    ]);

    $user = Auth::user();
    
    Log::info('User checking password', ['user_id' => $user->id]);

    if (Hash::check($request->sandi_lama, $user->password)) {
        Log::info('Password CORRECT for user', ['user_id' => $user->id]);
        return response()->json(['success' => true]);
    } else {
        Log::info('Password WRONG for user', ['user_id' => $user->id]);
        return response()->json([
            'success' => false,
            'message' => 'Kata sandi lama yang Anda masukkan salah.'
        ], 422);
    }
    }

 /**
     * LOGIKA 5: Update Profile Picture - PAKAI REDIRECT RESPONSE
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        try {
            Log::info('Update Avatar Called', ['has_file' => $request->hasFile('avatar')]);
            
            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);

            $user = Auth::user();
            
            Log::info('User attempting avatar update', ['user_id' => $user->id]);

            // Hapus avatar lama jika ada
            if ($user->avatar) {
                $oldAvatarPath = 'public/avatars/' . $user->avatar;
                if (Storage::exists($oldAvatarPath)) {
                    Storage::delete($oldAvatarPath);
                    Log::info('Old avatar deleted', ['file' => $user->avatar]);
                }
            }

            // Simpan avatar baru
            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $filename = 'avatar_' . $user->id . '_' . time() . '.' . $avatar->getClientOriginalExtension();
                
                Log::info('File details', [
                    'original_name' => $avatar->getClientOriginalName(),
                    'extension' => $avatar->getClientOriginalExtension(),
                    'size' => $avatar->getSize()
                ]);
                
                // Simpan ke storage
                $path = $avatar->storeAs('public/avatars', $filename);
                Log::info('File stored', ['path' => $path, 'filename' => $filename]);
                
                // Update database
                $user->avatar = $filename;
                $user->save();
                
                Log::info('Avatar updated in database', ['new_avatar' => $filename]);
                
                return redirect()->route('pengaturan')->with('success', 'Profile picture berhasil diupdate!');
            }

            Log::error('No file found in request');
            return redirect()->route('pengaturan')->with('error', 'Gagal mengupload avatar!');

        } catch (\Exception $e) {
            Log::error('Avatar update error: ' . $e->getMessage());
            return redirect()->route('pengaturan')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

}