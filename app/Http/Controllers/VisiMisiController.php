<?php
namespace App\Http\Controllers;

use App\Models\VisiMisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisiMisiController extends Controller
{
    // ... method index() Anda sudah benar ...
    public function index()
    {
        $visi = VisiMisi::where('type', 'visi')->first();
        $misi = VisiMisi::where('type', 'misi')->orderBy('id')->get();

        return response()->json([
            'visi' => $visi ? $visi->content : '',
            'misi' => $misi->pluck('content'),
        ]);
    }


    public function update(Request $request)
    {
        $request->validate([
            'visi' => 'required|string',
            'misi' => 'required|array|min:1',
            'misi.*' => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            VisiMisi::updateOrCreate(
                ['type' => 'visi'],
                ['content' => $request->visi]
            );

            VisiMisi::where('type', 'misi')->delete();

            foreach ($request->misi as $misiContent) {
                if (!empty($misiContent)) {
                    VisiMisi::create([
                        'type' => 'misi',
                        // PERBAIKAN: 'contenta' diubah menjadi 'content'
                        'content' => $misiContent 
                    ]);
                }
            }
        });

        return response()->json(['message' => 'Visi & Misi berhasil diperbarui.']);
    }
}