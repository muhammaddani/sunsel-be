<?php

namespace App\Http\Controllers;

use App\Models\LocationSetting;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    // Method untuk mengambil data lokasi (Akses Publik)
    public function getLocation()
    {
        // Ambil data lokasi pertama, atau buat data default jika belum ada
        $location = LocationSetting::firstOrCreate(
            [], // Kondisi pencarian (kosong berarti ambil yang pertama)
            [   // Data default jika tidak ditemukan
                'name' => 'Kantor Wali Nagari SUNSEL',
                'address' => 'Alamat default, silakan perbarui.',
                'latitude' => '-0.811284', // Ganti dengan default yang lebih sesuai
                'longitude' => '101.378969'
            ]
        );

        return response()->json($location);
    }

    // Method untuk memperbarui data lokasi (Akses Admin)
    public function updateLocation(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Cari data pertama, atau buat baru jika tabel kosong
        $location = LocationSetting::firstOrNew();

        $location->fill($request->all());
        $location->save();

        return response()->json([
            'message' => 'Data lokasi berhasil diperbarui.',
            'data' => $location
        ]);
    }
}