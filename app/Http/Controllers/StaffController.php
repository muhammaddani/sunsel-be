<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaffController extends Controller
{
    public function index()
    {
        $staff = Staff::orderBy('id', 'asc')->get();
        return response()->json($staff);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('staff-photos', 'public');
        }

        $staff = Staff::create([
            'name' => $request->input('name'),
            'position' => $request->input('position'),
            'photo' => $path,
        ]);

        return response()->json($staff, 201);
    }

    // Menggunakan POST untuk update karena form HTML tidak bisa mengirim PUT dengan multipart/form-data
    public function update(Request $request, Staff $staff)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name', 'position']);

        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($staff->photo) {
                Storage::disk('public')->delete($staff->photo);
            }
            // Simpan foto baru
            $data['photo'] = $request->file('photo')->store('staff-photos', 'public');
        }

        $staff->update($data);

        return response()->json($staff);
    }

    public function destroy(Staff $staff)
    {
        if ($staff->photo) {
            Storage::disk('public')->delete($staff->photo);
        }
        $staff->delete();
        return response()->json(null, 204);
    }
}