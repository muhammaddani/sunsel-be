<?php
namespace App\Http\Controllers;

use App\Models\PostDocument;
use Illuminate\Support\Facades\Storage;

class PostDocumentController extends Controller
{
    /**
     * Menangani permintaan download untuk satu dokumen.
     *
     * @param  \App\Models\PostDocument  $document
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(PostDocument $document)
    {
        // Cek apakah file benar-benar ada di storage untuk keamanan
        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File tidak ditemukan di storage.');
        }

        // Kembalikan file sebagai response download dengan nama aslinya
        $filePath = Storage::disk('public')->path($document->file_path);
        return response()->download($filePath, $document->original_name);
    }
}