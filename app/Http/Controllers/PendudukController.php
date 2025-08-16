<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Penduduk;
use App\Models\Jorong;
use App\Models\Agama;
use App\Models\Pekerjaan;
use App\Models\PendidikanTerakhir;
use App\Models\PendidikanDitempuh;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;

class PendudukController extends Controller
{
    public function index(Request $request)
    {
        // 1. Mulai query dengan JOIN ke tabel relasi untuk sorting & filtering
        $query = Penduduk::query()
            ->join('jorongs', 'penduduks.jorong_id', '=', 'jorongs.id')
            ->join('agamas', 'penduduks.agama_id', '=', 'agamas.id')
            ->join('pekerjaans', 'penduduks.pekerjaan_id', '=', 'pekerjaans.id')
            ->join('pendidikan_terakhirs', 'penduduks.pendidikan_terakhir_id', '=', 'pendidikan_terakhirs.id')
            ->join('pendidikan_ditempuhs', 'penduduks.pendidikan_ditempuh_id', '=', 'pendidikan_ditempuhs.id')
            ->select('penduduks.*'); // Penting untuk menghindari kolom ambigu dari tabel lain

        // --- FILTERING ---
        // Filter Pencarian (NIK & Nama)
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('penduduks.nik', 'like', '%' . $searchTerm . '%')
                ->orWhere('penduduks.nama_lengkap', 'like', '%' . $searchTerm . '%');
            });
        }

        // Filter berdasarkan Jorong
        if ($request->filled('jorong_id')) {
            $query->where('penduduks.jorong_id', $request->input('jorong_id'));
        }

        // Filter berdasarkan Pendidikan Terakhir
        if ($request->filled('pendidikan_terakhir_id')) {
            $query->where('penduduks.pendidikan_terakhir_id', $request->input('pendidikan_terakhir_id'));
        }

        // Filter berdasarkan Pendidikan yang Sedang Ditempuh
        if ($request->filled('pendidikan_ditempuh_id')) {
            $query->where('penduduks.pendidikan_ditempuh_id', $request->input('pendidikan_ditempuh_id'));
        }

        // Filter berdasarkan Jenis Kelamin
        if ($request->filled('jenis_kelamin')) {
            $query->where('penduduks.jenis_kelamin', $request->input('jenis_kelamin'));
        }

        // Filter berdasarkan Agama
        if ($request->filled('agama_id')) {
            $query->where('penduduks.agama_id', $request->input('agama_id'));
        }

        // Filter berdasarkan Rentang Umur
        if ($request->filled('umur')) {
            $ageRange = $request->input('umur');
            $this->applyAgeFilter($query, $ageRange);
        }

        // --- SORTING ---
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');

        // 2. Whitelist diperbarui dengan kolom dari tabel join
        $allowedSorts = [
            'nik', 
            'nama_lengkap', 
            'jenis_kelamin',
            'tanggal_lahir',
            'jorongs.nama_jorong', 
            'agamas.nama_agama',
            'pekerjaans.nama_pekerjaan',
            'pendidikan_terakhirs.nama_jenjang', 
            'pendidikan_ditempuhs.status_pendidikan',
            'created_at'
        ]; 
        
        if (in_array($sortBy, $allowedSorts)) {
            // Jika sorting berdasarkan kolom dari tabel lain, kita tidak bisa menggunakan alias
            // Jadi kita gunakan nama kolom yang sebenarnya dari whitelist
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('penduduks.created_at', 'desc');
        }

        // 3. Eager load relasi setelah semua query selesai
        $penduduk = $query->with(['jorong', 'agama', 'pekerjaan', 'pendidikanTerakhir', 'pendidikanDitempuh'])
                        ->paginate(10);

        return response()->json($penduduk);
    }

    /**
     * Apply age filter based on age range
     */
    private function applyAgeFilter($query, $ageRange)
    {
        $today = Carbon::now();
        
        switch ($ageRange) {
            case '0-1':
                $minDate = $today->copy()->subYear(1);
                $maxDate = $today->copy();
                break;
            case '2-4':
                $minDate = $today->copy()->subYears(4)->subDay(1);
                $maxDate = $today->copy()->subYears(2);
                break;
            case '5-9':
                $minDate = $today->copy()->subYears(9)->subDay(1);
                $maxDate = $today->copy()->subYears(5);
                break;
            case '10-14':
                $minDate = $today->copy()->subYears(14)->subDay(1);
                $maxDate = $today->copy()->subYears(10);
                break;
            case '15-19':
                $minDate = $today->copy()->subYears(19)->subDay(1);
                $maxDate = $today->copy()->subYears(15);
                break;
            case '20-24':
                $minDate = $today->copy()->subYears(24)->subDay(1);
                $maxDate = $today->copy()->subYears(20);
                break;
            case '25-29':
                $minDate = $today->copy()->subYears(29)->subDay(1);
                $maxDate = $today->copy()->subYears(25);
                break;
            case '30-34':
                $minDate = $today->copy()->subYears(34)->subDay(1);
                $maxDate = $today->copy()->subYears(30);
                break;
            case '35-39':
                $minDate = $today->copy()->subYears(39)->subDay(1);
                $maxDate = $today->copy()->subYears(35);
                break;
            case '40-44':
                $minDate = $today->copy()->subYears(44)->subDay(1);
                $maxDate = $today->copy()->subYears(40);
                break;
            case '45-49':
                $minDate = $today->copy()->subYears(49)->subDay(1);
                $maxDate = $today->copy()->subYears(45);
                break;
            case '50-54':
                $minDate = $today->copy()->subYears(54)->subDay(1);
                $maxDate = $today->copy()->subYears(50);
                break;
            case '55-59':
                $minDate = $today->copy()->subYears(59)->subDay(1);
                $maxDate = $today->copy()->subYears(55);
                break;
            case '60-64':
                $minDate = $today->copy()->subYears(64)->subDay(1);
                $maxDate = $today->copy()->subYears(60);
                break;
            case '65+':
                $maxDate = $today->copy()->subYears(65);
                $query->where('penduduks.tanggal_lahir', '<=', $maxDate->format('Y-m-d'));
                return;
            default:
                return;
        }
        
        $query->whereBetween('penduduks.tanggal_lahir', [
            $minDate->format('Y-m-d'),
            $maxDate->format('Y-m-d')
        ]);
    }

    public function getOptions()
    {
        return response()->json([
            'jorongs' => Jorong::orderBy('nama_jorong')->get(),
            'agamas' => Agama::orderBy('nama_agama')->get(),
            'pekerjaans' => Pekerjaan::orderBy('nama_pekerjaan')->get(),
            'pendidikan_terakhirs' => PendidikanTerakhir::all(),
            'pendidikan_ditempuhs' => PendidikanDitempuh::select('id', 'status_pendidikan as nama_jenjang')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nik' => 'required|string|size:16|unique:penduduks,nik',
            'no_kk' => 'required|string|size:16',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tanggal_lahir' => 'required|date',
            'jorong_id' => 'required|exists:jorongs,id',
            'agama_id' => 'required|exists:agamas,id',
            'pekerjaan_id' => 'required|exists:pekerjaans,id',
            'pendidikan_terakhir_id' => 'required|exists:pendidikan_terakhirs,id',
            'pendidikan_ditempuh_id' => 'required|exists:pendidikan_ditempuhs,id',
        ]);

        $penduduk = Penduduk::create($validatedData);
        return response()->json(['message' => 'Data penduduk berhasil ditambahkan.', 'data' => $penduduk], 201);
    }

    public function show(Penduduk $penduduk)
    {
        // Load relasi untuk ditampilkan di form edit
        $penduduk->load(['jorong', 'agama', 'pekerjaan', 'pendidikanTerakhir', 'pendidikanDitempuh']);
        return response()->json($penduduk);
    }

    public function update(Request $request, Penduduk $penduduk)
    {
        $validatedData = $request->validate([
            'nik' => ['required', 'string', 'size:16', Rule::unique('penduduks')->ignore($penduduk->id)],
            'no_kk' => 'required|string|size:16',
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tanggal_lahir' => 'required|date',
            'jorong_id' => 'required|exists:jorongs,id',
            'agama_id' => 'required|exists:agamas,id',
            'pekerjaan_id' => 'required|exists:pekerjaans,id',
            'pendidikan_terakhir_id' => 'required|exists:pendidikan_terakhirs,id',
            'pendidikan_ditempuh_id' => 'required|exists:pendidikan_ditempuhs,id',
        ]);

        $penduduk->update($validatedData);
        return response()->json(['message' => 'Data penduduk berhasil diperbarui.', 'data' => $penduduk]);
    }

    public function destroy(Penduduk $penduduk)
    {
        $penduduk->delete();
        return response()->json(['message' => 'Data penduduk berhasil dihapus.']);
    }

    /**
     * Export selected data to Excel format
     */
    public function exportExcel(Request $request)
    {
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return response()->json(['message' => 'Tidak ada data yang dipilih untuk diekspor.'], 400);
        }

        try {
            // 1. Gunakan cursor() untuk mengambil data satu per satu, sangat hemat memori
            $pendudukData = Penduduk::with(['jorong', 'agama', 'pekerjaan', 'pendidikanTerakhir', 'pendidikanDitempuh'])
                            ->whereIn('id', $ids)
                            ->cursor(); // <-- Perubahan kunci dari get() menjadi cursor()

            // 2. Buat fungsi generator untuk memproses setiap baris data
            $generator = function() use ($pendudukData) {
                foreach ($pendudukData as $p) {
                    // Yield akan mengirimkan data baris ini untuk ditulis ke Excel
                    yield [
                        'NIK'                   => $p->nik ?? '-',
                        'No KK'                 => $p->no_kk ?? '-',
                        'Nama Lengkap'          => $p->nama_lengkap ?? '-',
                        'Jenis Kelamin'         => $p->jenis_kelamin ?? '-',
                        'Tanggal Lahir'         => $p->tanggal_lahir ? \Carbon\Carbon::parse($p->tanggal_lahir)->format('Y-m-d') : '-',
                        'Jorong'                => $p->jorong?->nama_jorong ?? '-',
                        'Agama'                 => $p->agama?->nama_agama ?? '-',
                        'Pendidikan Terakhir'   => $p->pendidikanTerakhir?->nama_jenjang ?? '-',
                        'Pekerjaan'             => $p->pekerjaan?->nama_pekerjaan ?? '-',
                        'Pendidikan Ditempuh'   => $p->pendidikanDitempuh?->status_pendidikan ?? '-',
                    ];
                }
            };
            
            // 3. Panggil FastExcel dengan generator yang sudah efisien
            return (new FastExcel($generator()))->download('data-penduduk.xlsx');

        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan di server saat membuat file Excel.'], 500);
        }
    }
}