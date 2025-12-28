<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Exception;

class ConnectionTestController extends Controller
{
    public function index()
    {
        $status = null;
        $message = null;
        $userCount = 0;

        try {
            // 1. Cek Koneksi Raw (PDO)
            DB::connection()->getPdo();
            
            // 2. Cek Query Menggunakan Model (Opsional, memastikan tabel bisa dibaca)
            // Kita pakai try-catch lagi di sini in case tabel users belum ada
            try {
                $userCount = User::count();
                $message = "Koneksi Berhasil! Tabel 'users' ditemukan.";
            } catch (Exception $e) {
                $message = "Koneksi Database Berhasil, tapi tabel 'users' bermasalah/tidak ada.";
            }

            $status = 'success';

        } catch (Exception $e) {
            $status = 'error';
            $message = "Gagal Terkoneksi: " . $e->getMessage();
        }

        // Mengirim data ke View
        return view('test-connection', [
            'status' => $status,
            'message' => $message,
            'userCount' => $userCount,
            'config' => [
                'host' => config('database.connections.mysql.host'),
                'database' => config('database.connections.mysql.database'),
            ]
        ]);
    }
}