<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminPackageController extends Controller
{
    /**
     * Tampilkan daftar paket layanan
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = Package::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        $packages = $query->get();

        return view('admin.paket', compact('packages'));
    }

    /**
     * Simpan paket layanan baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|string',
            'duration_minutes' => 'required|numeric',
            'photo_count' => 'required|numeric',
            'description' => 'required|string',
            'features' => 'nullable|string',
        ]);

        $cleanPrice = (int) preg_replace('/[^0-9]/', '', $validated['price']);
        $featuresList = array_values(array_filter(array_map('trim', explode("\n", $validated['features'] ?? ''))));

        $slug = Str::slug($validated['name']);
        $code = 'PKG-'.strtoupper(str_replace('-', '_', $slug));

        Package::create([
            'slug' => $slug,
            'code' => $code,
            'name' => $validated['name'],
            'tagline' => $validated['description'],
            'description' => $validated['description'],
            'price' => $cleanPrice,
            'duration' => $validated['duration_minutes'].' menit',
            'duration_minutes' => (int) $validated['duration_minutes'],
            'photo_count' => (int) $validated['photo_count'],
            'photo_count_label' => $validated['photo_count'].' foto retouched',
            'features' => $featuresList,
            'image' => 'paket-birthday.jpg',
            'is_active' => true,
            'is_featured' => false,
        ]);

        return redirect()->route('admin.paket')
            ->with('success', 'Paket layanan "'.$validated['name'].'" berhasil ditambahkan.');
    }

    /**
     * Update paket layanan yang ada
     */
    public function update(Request $request, $id)
    {
        $package = Package::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required',
            'duration_minutes' => 'required|numeric',
            'photo_count' => 'required|numeric',
            'description' => 'required|string',
            'features' => 'nullable|string',
        ]);

        $cleanPrice = (int) preg_replace('/[^0-9]/', '', (string) $validated['price']);
        $featuresList = array_values(array_filter(array_map('trim', explode("\n", $validated['features'] ?? ''))));

        $package->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $cleanPrice,
            'duration' => $validated['duration_minutes'].' menit',
            'duration_minutes' => (int) $validated['duration_minutes'],
            'photo_count' => (int) $validated['photo_count'],
            'photo_count_label' => $validated['photo_count'].' foto retouched',
            'features' => $featuresList,
        ]);

        return redirect()->route('admin.paket')
            ->with('success', 'Paket layanan "'.$package->name.'" berhasil diperbarui.');
    }

    /**
     * Toggle status aktif / nonaktif
     */
    public function toggleStatus($id)
    {
        $package = Package::findOrFail($id);
        $package->is_active = ! $package->is_active;
        $package->save();

        $statusText = $package->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.paket')
            ->with('success', 'Paket "'.$package->name.'" telah '.$statusText.'.');
    }

    /**
     * Hapus paket layanan
     */
    public function destroy($id)
    {
        $package = Package::findOrFail($id);
        $name = $package->name;
        $package->delete();

        return redirect()->route('admin.paket')
            ->with('success', 'Paket "'.$name.'" berhasil dihapus.');
    }
}
