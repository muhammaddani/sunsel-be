<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WisataDetail extends Model
{
    protected $fillable = ['post_id', 'nomor_telepon', 'email', 'alamat', 'website'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
