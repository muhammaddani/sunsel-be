<?php
namespace App\Http\Controllers;

use App\Models\OrganizationalStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizationalStructureController extends Controller
{
    // Mengambil gambar bagan untuk ditampilkan ke publik
    public function show()
    {
        $structure = OrganizationalStructure::first();
        
        if ($structure && $structure->image_path) {
            // Pastikan path gambar dikembalikan dengan format yang benar
            $structure->gambar = asset('storage/' . $structure->image_path);
        }
        
        return response()->json($structure);
    }

    // Menyimpan atau mengupdate gambar bagan dari halaman admin
    public function store(Request $request)
    {
        $request->validate([
            'gambar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        try {
            // 1. Cari dulu apakah sudah ada data
            $structure = OrganizationalStructure::first();

            // Hapus gambar lama jika ada
            if ($structure && $structure->image_path) {
                Storage::disk('public')->delete($structure->image_path);
            }

            // Simpan gambar baru
            $path = $request->file('gambar')->store('structure', 'public');

            // 2. Logika yang lebih eksplisit untuk update atau create
            if ($structure) {
                // Jika sudah ada, update
                $structure->image_path = $path;
                $structure->save();
            } else {
                // Jika belum ada, buat baru
                $structure = OrganizationalStructure::create(['image_path' => $path]);
            }

            // Tambahkan URL gambar untuk response
            $structure->gambar = asset('storage/' . $structure->image_path);

            return response()->json([
                'message' => 'Bagan struktur organisasi berhasil disimpan',
                'data' => $structure
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menyimpan bagan struktur organisasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Menghapus gambar bagan struktur organisasi
    public function destroy()
    {
        try {
            $structure = OrganizationalStructure::first();
            
            if (!$structure) {
                return response()->json([
                    'message' => 'Bagan struktur organisasi tidak ditemukan'
                ], 404);
            }

            // Hapus file gambar dari storage
            if ($structure->image_path) {
                Storage::disk('public')->delete($structure->image_path);
            }

            // Hapus record dari database
            $structure->delete();

            return response()->json([
                'message' => 'Bagan struktur organisasi berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus bagan struktur organisasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}