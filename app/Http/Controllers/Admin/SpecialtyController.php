<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SpecialtyController extends Controller
{
    /**
     * Display a listing of the resource.
     * Menampilkan daftar semua spesialisasi.
     */
    public function index()
    {
        $specialties = Specialty::paginate(10); // Ambil semua spesialisasi dengan pagination
        return view('admin.specialties.index', compact('specialties'));
    }

    /**
     * Show the form for creating a new resource.
     * Menampilkan formulir untuk menambah spesialisasi baru.
     */
    public function create()
    {
        return view('admin.specialties.create');
    }

    /**
     * Store a newly created resource in storage.
     * Menyimpan spesialisasi baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:specialties,name', // Nama spesialisasi harus unik
            'description' => 'nullable|string',
        ]);

        Specialty::create($request->all());

        return redirect()->route('admin.specialties.index')->with('success', 'Spesialisasi berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     * Menampilkan detail spesialisasi tertentu (opsional, bisa diabaikan jika tidak ada halaman detail).
     */
    public function show(Specialty $specialty)
    {
        // Untuk saat ini, kita bisa melewati atau mengimplementasikan ini
        // jika ada kebutuhan untuk halaman detail spesialisasi.
        // Umumnya untuk CRUD sederhana, show() tidak selalu diperlukan jika semua info ada di index.
        return redirect()->route('admin.specialties.index'); // Redirect kembali ke index
    }

    /**
     * Show the form for editing the specified resource.
     * Menampilkan formulir untuk mengedit spesialisasi tertentu.
     */
    public function edit($id) // Hapus (Specialty $specialty) dan ganti dengan ($id)
    {
        $specialty = Specialty::findOrFail($id); // Cari spesialisasi berdasarkan ID
        return view('admin.specialties.edit', compact('specialty'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) // Hapus (Specialty $specialty) dan ganti dengan ($id)
    {
        $specialty = Specialty::findOrFail($id); // Cari spesialisasi berdasarkan ID

        $request->validate([
            'name'        => [
                'required',
                'string',
                'max:255',
                Rule::unique('specialties', 'name')->ignore($specialty->id),
            ],
            'description' => 'nullable|string',
        ]);

        $specialty->update($request->all());

        return redirect()->route('admin.specialties.index')->with('success', 'Spesialisasi berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) // Hapus (Specialty $specialty) dan ganti dengan ($id)
    {
        $specialty = Specialty::findOrFail($id); // Cari spesialisasi berdasarkan ID

        try {
            // Jika ada transaksi, pakai DB::transaction
            $specialty->delete();
            return redirect()->route('admin.specialties.index')->with('success', 'Spesialisasi berhasil dihapus!');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.specialties.index')->with('error', 'Tidak dapat menghapus spesialisasi ini karena masih ada dokter yang terkait.');
        }
    }
}