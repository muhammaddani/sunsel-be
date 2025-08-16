<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'content',
        'type',
        'image',
    ];

    public function wisataDetail()
    {
        return $this->hasOne(WisataDetail::class);
    }

    public function documents()
    {
        return $this->hasMany(PostDocument::class);
    }

    public function photos()
    {
        return $this->hasMany(PostPhoto::class);
    }
}