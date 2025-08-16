<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageImage extends Model
{
    protected $fillable = ['page_id', 'image_path', 'caption'];
}
