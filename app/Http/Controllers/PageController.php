<?php
namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\PageImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    // Mengambil data halaman beserta gambar-gambarnya
    public function show(Page $page)
    {
        return response()->json($page->load('images'));
    }

    // Mengupdate halaman (konten teks + multi-gambar)
    public function update(Request $request, Page $page)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255', // 'sometimes' agar tidak wajib saat update
            'content' => 'required|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'captions.*' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $page) {
            // 1. Update konten teks utama jika ada
            if ($request->has('title')) {
                $page->update($request->only(['title', 'content']));
            } else {
                $page->update($request->only(['content']));
            }

            // 2. Hapus gambar lama dari storage dan database
            foreach ($page->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }
            $page->images()->delete();

            // 3. Simpan gambar-gambar baru jika ada
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $file) {
                    $path = $file->store('page-images', 'public');
                    
                    PageImage::create([
                        'page_id' => $page->id,
                        'image_path' => $path,
                        'caption' => $request->input('captions')[$index] ?? null,
                    ]);
                }
            }
        });

        return response()->json($page->load('images'));
    }
}