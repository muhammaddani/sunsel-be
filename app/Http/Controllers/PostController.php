<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with(['wisataDetail', 'documents', 'photos']);
        if ($request->has('type')) {
             $query->where('type', $request->query('type'));
        }
        $posts = $query->latest()->get();
        return response()->json($posts);
    }

    public function show(Post $post)
    {
        return response()->json($post->load(['wisataDetail', 'documents', 'photos']));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:berita,pengumuman,wisata',
            'photo' => 'nullable|image|max:2048',
            'photos.*' => 'nullable|image|max:2048',
            'documents.*' => 'nullable|file|max:5120',
            'contact_phone' => 'nullable|string',
            'contact_address' => 'nullable|string',
            'contact_website' => 'nullable|url',
        ]);

        DB::beginTransaction();
        try {
            $post = Post::create([
                'title' => $validatedData['title'],
                'content' => $validatedData['content'],
                'type' => $validatedData['type'],
                'slug' => Str::slug($validatedData['title']) . '-' . time(),
            ]);

            if ($request->type === 'wisata') {
                // Jika Wisata, simpan multiple foto ke tabel post_photos
                if ($request->hasFile('photos')) {
                    foreach ($request->file('photos') as $file) {
                        $path = $file->store('post-photos', 'public');
                        $post->photos()->create(['photo_path' => $path]);
                    }
                }
            } else {
                // Jika Berita/Pengumuman, simpan satu foto ke tabel posts
                if ($request->hasFile('photo')) {
                    $path = $request->file('photo')->store('post-photos', 'public');
                    $post->update(['image' => $path]);
                }
            }
            
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store('documents', 'public');
                    $post->documents()->create([
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                    ]);
                }
            }

            if ($request->type === 'wisata') {
                $post->wisataDetail()->create([
                    'nomor_telepon' => $request->contact_phone,
                    'alamat' => $request->contact_address,
                    'website' => $request->contact_website,
                ]);
            }

            DB::commit();
            return response()->json($post->load(['wisataDetail', 'documents', 'photos']), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan data.', 'error' => $e->getMessage()], 500);
        }
    }

    // --- METHOD UPDATE YANG SEBENARNYA ---
    public function update(Request $request, Post $post)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:berita,pengumuman,wisata',
            'photo' => 'nullable|image|max:2048',
            'photos.*' => 'nullable|image|max:2048',
            'documents.*' => 'nullable|file|max:5120',
            'existing_photos' => 'nullable|array',
            'existing_documents' => 'nullable|array',
            'existing_documents.*' => 'sometimes|integer|exists:post_documents,id',
            'contact_phone' => 'nullable|string',
            'contact_address' => 'nullable|string',
            'contact_website' => 'nullable|url',
        ]);
        
        DB::beginTransaction();
        try {
            // 1. Update data teks utama
            $post->update([
                'title' => $validatedData['title'],
                'content' => $validatedData['content'],
                'type' => $validatedData['type'],
                'slug' => Str::slug($validatedData['title']) . '-' . time(),
            ]);

            // 2. Logika update foto 
            if ($request->type === 'wisata') {
                // Hapus foto tunggal jika post diubah menjadi wisata
                if ($post->image) {
                    Storage::disk('public')->delete($post->image);
                    $post->update(['image' => null]);
                }

                // Proses multi-foto untuk wisata
                $existingPhotoIds = $request->input('existing_photos', []);
                $photosToDelete = $post->photos()->whereNotIn('id', $existingPhotoIds)->get();
                foreach ($photosToDelete as $photo) {
                    Storage::disk('public')->delete($photo->photo_path);
                    $photo->delete();
                }
                if ($request->hasFile('photos')) {
                    foreach ($request->file('photos') as $file) {
                        $path = $file->store('post-photos', 'public');
                        $post->photos()->create(['photo_path' => $path]);
                    }
                }
            } else {
                // Hapus multi-foto jika post diubah dari wisata
                foreach ($post->photos as $photo) {
                    Storage::disk('public')->delete($photo->photo_path);
                    $photo->delete();
                }

                // Proses foto tunggal untuk berita/pengumuman
                if ($request->hasFile('photo')) {
                    if ($post->image) Storage::disk('public')->delete($post->image);
                    $path = $request->file('photo')->store('post-photos', 'public');
                    $post->update(['image' => $path]);
                }
            }

            // Logika multi-dokumen
            $existingDocIds = $request->input('existing_documents', []);
            $docsToDelete = $post->documents()->whereNotIn('id', $existingDocIds)->get();
            foreach ($docsToDelete as $doc) {
                Storage::disk('public')->delete($doc->file_path);
                $doc->delete();
            }
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store('documents', 'public');
                    $post->documents()->create([
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                    ]);
                }
            }

            // 4. Logika update detail wisata
            if ($request->type === 'wisata') {
                $post->wisataDetail()->updateOrCreate(
                    ['post_id' => $post->id], 
                    [
                        'nomor_telepon' => $request->contact_phone,
                        'alamat' => $request->contact_address,
                        'website' => $request->contact_website,
                    ]
                );
            } else {
                // Hapus detail wisata jika tipe diubah dari wisata ke tipe lain
                $post->wisataDetail()->delete();
            }

            DB::commit();
            return response()->json($post->load(['wisataDetail', 'documents', 'photos']));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal memperbarui data.', 'error' => $e->getMessage()], 500);
        }
    }


    public function destroy(Post $post)
    {
        // Hapus foto tunggal
        if ($post->image) Storage::disk('public')->delete($post->image);
        
        // Hapus multi-foto
        foreach ($post->photos as $photo) Storage::disk('public')->delete($photo->photo_path);
        
        // Hapus multi-dokumen
        foreach ($post->documents as $doc) Storage::disk('public')->delete($doc->file_path);

        $post->delete(); // Relasi lain akan terhapus otomatis karena cascade
        return response()->json(null, 204);
    }
    
    public function download(Post $post)
    {
        if (!$post->document_path) {
            abort(404, 'Dokumen tidak ditemukan.');
        }
        // Perbaikan kecil untuk path download
        return response()->download(Storage::disk('public')->path($post->document_path));
    }
}