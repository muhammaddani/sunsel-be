<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PostDocument extends Model
{
    protected $fillable = ['post_id', 'file_path', 'original_name'];
}