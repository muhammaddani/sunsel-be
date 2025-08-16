<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penduduk extends Model
{
    use HasFactory;

    protected $fillable = [
        'nik', 
        'no_kk', 
        'nama_lengkap', 
        'jenis_kelamin', 
        'tanggal_lahir',
        'jorong_id', 
        'agama_id', 
        'pekerjaan_id', 
        'pendidikan_terakhir_id', 
        'pendidikan_ditempuh_id',
    ];

    protected $dates = [
        'tanggal_lahir'
    ];

    // Relasi ke tabel lain
    public function jorong() { 
        return $this->belongsTo(Jorong::class); 
    }
    
    public function agama() { 
        return $this->belongsTo(Agama::class); 
    }
    
    public function pekerjaan() { 
        return $this->belongsTo(Pekerjaan::class); 
    }
    
    public function pendidikanTerakhir() { 
        return $this->belongsTo(PendidikanTerakhir::class); 
    }
    
    public function pendidikanDitempuh() { 
        return $this->belongsTo(PendidikanDitempuh::class); 
    }

    // Helper method untuk menghitung umur
    public function getUmurAttribute()
    {
        if (!$this->tanggal_lahir) return null;
        
        $today = new \DateTime();
        $birth = new \DateTime($this->tanggal_lahir);
        return $today->diff($birth)->y;
    }

    // Helper method untuk kelompok umur
    public function getKelompokUmurAttribute()
    {
        $umur = $this->umur;
        if ($umur === null) return null;
        
        if ($umur >= 0 && $umur <= 1) return "0 s/d 1 Tahun";
        if ($umur >= 2 && $umur <= 4) return "2 s/d 4 Tahun";
        if ($umur >= 5 && $umur <= 9) return "5 s/d 9 Tahun";
        if ($umur >= 10 && $umur <= 14) return "10 s/d 14 Tahun";
        if ($umur >= 15 && $umur <= 19) return "15 s/d 19 Tahun";
        if ($umur >= 20 && $umur <= 24) return "20 s/d 24 Tahun";
        if ($umur >= 25 && $umur <= 29) return "25 s/d 29 Tahun";
        if ($umur >= 30 && $umur <= 34) return "30 s/d 34 Tahun";
        if ($umur >= 35 && $umur <= 39) return "35 s/d 39 Tahun";
        if ($umur >= 40 && $umur <= 44) return "40 s/d 44 Tahun";
        if ($umur >= 45 && $umur <= 49) return "45 s/d 49 Tahun";
        if ($umur >= 50 && $umur <= 54) return "50 s/d 54 Tahun";
        if ($umur >= 55 && $umur <= 59) return "55 s/d 59 Tahun";
        if ($umur >= 60 && $umur <= 64) return "60 s/d 64 Tahun";
        return "65+ Tahun";
    }
}