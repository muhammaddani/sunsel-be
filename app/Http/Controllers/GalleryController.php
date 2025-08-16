<?php
namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index()
    {
        return Gallery::latest()->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $path = $request->file('image')->store('gallery', 'public'); 

        Gallery::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . time(),
            'description' => $request->description,
            'image_path' => $path,
        ]);

        return response()->json(['message' => 'Item galeri berhasil ditambahkan.'], 201);
    }

    public function destroy(Gallery $gallery)
    {
        // Hapus file gambar dari storage
        Storage::delete($gallery->image_path);
        
        // Hapus record dari database
        $gallery->delete();

        return response()->json(['message' => 'Item galeri berhasil dihapus.']);
    }
    
}