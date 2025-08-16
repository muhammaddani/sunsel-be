<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistikController extends Controller
{
    /**
     * Mengambil semua data statistik kependudukan dalam satu response.
     */
    public function getAllStatistik()
    {
        // 1. Statistik Wilayah (Jorong)
        $wilayah = DB::table('penduduks')
            ->join('jorongs', 'penduduks.jorong_id', '=', 'jorongs.id')
            ->select(
                'jorongs.nama_jorong',
                DB::raw('COUNT(DISTINCT penduduks.no_kk) as jumlah_kk'),
                DB::raw("SUM(CASE WHEN penduduks.jenis_kelamin = 'Laki-laki' THEN 1 ELSE 0 END) as jumlah_laki"),
                DB::raw("SUM(CASE WHEN penduduks.jenis_kelamin = 'Perempuan' THEN 1 ELSE 0 END) as jumlah_perempuan")
            )
            ->groupBy('jorongs.nama_jorong')
            ->orderBy('jorongs.nama_jorong')
            ->get();

        // 2. Statistik Agama
        $agama = DB::table('penduduks')
            ->join('agamas', 'penduduks.agama_id', '=', 'agamas.id')
            ->select('agamas.nama_agama as name', DB::raw('COUNT(*) as value'))
            ->groupBy('agamas.nama_agama')
            ->get();
            
        // 3. Statistik Pekerjaan
        $pekerjaan = DB::table('penduduks')
            ->join('pekerjaans', 'penduduks.pekerjaan_id', '=', 'pekerjaans.id')
            ->select('pekerjaans.nama_pekerjaan as name', DB::raw('COUNT(*) as value'))
            ->groupBy('pekerjaans.nama_pekerjaan')
            ->get();

        // 4. Statistik Pendidikan Terakhir
        $pendidikan = DB::table('penduduks')
            ->join('pendidikan_terakhirs', 'penduduks.pendidikan_terakhir_id', '=', 'pendidikan_terakhirs.id')
            ->select('pendidikan_terakhirs.nama_jenjang as name', DB::raw('COUNT(*) as value'))
            ->groupBy('pendidikan_terakhirs.nama_jenjang')
            ->get();
            
        // 5. Statistik Rentang Umur
        $usia = DB::table('penduduks')
            ->select(
                DB::raw("CASE 
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 0 AND 4 THEN '0-4 (Balita)'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 5 AND 11 THEN '5-11 (Anak-anak)'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 12 AND 16 THEN '12-16 (Remaja Awal)'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 17 AND 25 THEN '17-25 (Remaja Akhir)'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 26 AND 45 THEN '26-45 (Dewasa)'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 46 AND 65 THEN '46-65 (Lansia)'
                    ELSE '> 65 (Manula)'
                END as name"),
                DB::raw('COUNT(*) as value')
            )
            ->groupBy('name')
            ->orderBy(DB::raw('MIN(TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()))'))
            ->get();
            
        // Kembalikan semua data dalam satu response JSON
        return response()->json([
            'wilayah' => $wilayah,
            'agama' => $agama,
            'pekerjaan' => $pekerjaan,
            'pendidikan' => $pendidikan,
            'usia' => $usia,
        ]);
    }
    public function statistikPerJorong()
    {
        $statistik = DB::table('penduduks')
            ->join('jorongs', 'penduduks.jorong_id', '=', 'jorongs.id')
            ->select(
                'jorongs.nama_jorong as jorong',
                DB::raw('COUNT(DISTINCT penduduks.no_kk) as kk'),
                DB::raw("SUM(CASE WHEN penduduks.jenis_kelamin = 'Laki-laki' THEN 1 ELSE 0 END) as lakiLaki"),
                DB::raw("SUM(CASE WHEN penduduks.jenis_kelamin = 'Perempuan' THEN 1 ELSE 0 END) as perempuan"),
                DB::raw('COUNT(penduduks.id) as total')
            )
            ->groupBy('jorongs.nama_jorong')
            ->orderBy('jorongs.nama_jorong')
            ->get();

        return response()->json($statistik);
    }

    public function statistikPendidikan()
    {
        // Ambil semua jenjang pendidikan sebagai dasar
        $allPendidikan = DB::table('pendidikan_terakhirs')->pluck('nama_jenjang');

        // Ambil data penduduk yang ada
        $statistik = DB::table('penduduks')
            ->join('pendidikan_terakhirs', 'penduduks.pendidikan_terakhir_id', '=', 'pendidikan_terakhirs.id')
            ->select(
                'pendidikan_terakhirs.nama_jenjang as kelompok',
                DB::raw('COUNT(penduduks.id) as jumlah'),
                DB::raw("SUM(CASE WHEN penduduks.jenis_kelamin = 'Laki-laki' THEN 1 ELSE 0 END) as lakiLaki"),
                DB::raw("SUM(CASE WHEN penduduks.jenis_kelamin = 'Perempuan' THEN 1 ELSE 0 END) as perempuan")
            )
            ->groupBy('pendidikan_terakhirs.nama_jenjang')
            ->get()
            ->keyBy('kelompok'); // Jadikan nama jenjang sebagai key untuk mudah diakses

        // Gabungkan hasil agar semua jenjang pendidikan muncul, meskipun jumlahnya 0
        $result = $allPendidikan->map(function ($jenjang) use ($statistik) {
            if (isset($statistik[$jenjang])) {
                return $statistik[$jenjang];
            }
            return (object) [
                'kelompok' => $jenjang,
                'jumlah' => 0,
                'lakiLaki' => 0,
                'perempuan' => 0,
            ];
        });

        return response()->json($result);
    }

    public function statistikUsia()
    {
        $usia = DB::table('penduduks')
            ->select(
                DB::raw("CASE 
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 0 AND 5 THEN '0-5 Tahun (Balita)'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 6 AND 17 THEN '6-17 Tahun (Usia Sekolah)'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 18 AND 25 THEN '18-25 Tahun (Dewasa Awal)'
                    WHEN TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 26 AND 59 THEN '26-59 Tahun (Dewasa Produktif)'
                    ELSE '60+ Tahun (Lansia)'
                END as kelompok"),
                DB::raw('COUNT(*) as jumlah'),
                DB::raw("SUM(CASE WHEN jenis_kelamin = 'Laki-laki' THEN 1 ELSE 0 END) as lakiLaki"),
                DB::raw("SUM(CASE WHEN jenis_kelamin = 'Perempuan' THEN 1 ELSE 0 END) as perempuan")
            )
            ->groupBy('kelompok')
            ->orderBy(DB::raw('MIN(TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()))'))
            ->get();
            
        return response()->json($usia);
    }
}
