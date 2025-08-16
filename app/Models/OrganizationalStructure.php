<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationalStructure extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model.
     *
     * @var string
     */
    protected $table = 'organizational_structures';

    /**
     * Kolom yang diizinkan untuk diisi secara massal.
     *
     * @var array
     */
    protected $fillable = [
        'image_path',
    ];
}