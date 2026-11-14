<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\GalleryPhoto;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminGalleryController extends Controller
{
    /**
     * Tampilkan daftar galeri pelanggan
     */
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'semua');
        $search = $request->query('search');

        $query = Gallery::with(['order', 'photos'])->latest();

        if ($activeTab === 'draf') {
            $query->where('status', 'Draf');
        } elseif ($activeTab === 'penyuntingan') {
            $query->where('status', 'Penyuntingan');
        } elseif ($activeTab === 'siap') {
            $query->where('status', 'Siap');
        } elseif ($activeTab === 'diterbitkan') {
            $query->where('status', 'Diterbitkan');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('client_name', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_code', 'like', "%{$search}%");
                    });
            });
        }

        $galleries = $query->get();

        // Hitung statistik
        $penyuntinganCount = Gallery::where('status', 'Penyuntingan')->count();
        $drafCount = Gallery::where('status', 'Draf')->count();
        $siapCount = Gallery::where('status', 'Siap')->count();
        $diterbitkanCount = Gallery::where('status', 'Diterbitkan')->count();

        // Hanya pesanan yang sudah selesai sesi fotonya yang bisa dihubungkan ke galeri
        $orders = Order::where('session_status', 'Selesai')->latest()->get();

        return view('admin.galeri', compact(
            'galleries',
            'activeTab',
            'penyuntinganCount',
            'drafCount',
            'siapCount',
            'diterbitkanCount',
            'orders'
        ));
    }

    /**
     * Buat galeri baru dari form modal
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'order_id' => 'nullable|exists:orders,id',
            'target_photos' => 'required|numeric|min:1',
            'access_type' => 'required|in:Privat,Terbuka',
            'pin_code' => 'nullable|string|max:10',
            'photos.*' => 'nullable|image|max:10240',
        ]);

        $order = null;
        if (! empty($validated['order_id'])) {
            $order = Order::find($validated['order_id']);
        }

        $clientName = $order ? $order->customer_name : 'Klien Studio';
        $slug = Str::slug($validated['title']).'-'.Str::random(4);

        $pinCode = null;
        if ($validated['access_type'] === 'Privat') {
            $pinCode = ! empty($validated['pin_code']) ? $validated['pin_code'] : '1234';
        }

        $gallery = Gallery::create([
            'slug' => $slug,
            'title' => $validated['title'],
            'order_id' => $order ? $order->id : null,
            'client_name' => $clientName,
            'target_photos' => (int) $validated['target_photos'],
            'status' => 'Draf',
            'access_type' => $validated['access_type'],
            'pin_code' => $pinCode,
            'cover_image' => 'work-1.jpg',
        ]);

        // Simpan foto awal jika diunggah
        if ($request->hasFile('photos')) {
            $sort = 1;
            foreach ($request->file('photos') as $photoFile) {
                $path = $photoFile->store('galleries', 'public');
                GalleryPhoto::create([
                    'gallery_id' => $gallery->id,
                    'photo_path' => $path,
                    'caption' => 'Foto '.$sort,
                    'sort_order' => $sort,
                ]);
                $sort++;
            }
        }

        return redirect()->route('admin.galeri')
            ->with('success', 'Galeri "'.$gallery->title.'" berhasil dibuat sebagai Draf.');
    }

    /**
     * Perbarui data galeri dan unggah foto tambahan
     */
    public function update(Request $request, $id)
    {
        $gallery = Gallery::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'order_id' => 'nullable|exists:orders,id',
            'status' => 'required|in:Draf,Penyuntingan,Siap,Diterbitkan',
            'target_photos' => 'required|numeric|min:1',
            'access_type' => 'required|in:Privat,Terbuka',
            'pin_code' => 'nullable|string|max:10',
            'photos.*' => 'nullable|image|max:10240',
            'deleted_photo_ids' => 'nullable|array',
            'deleted_photo_ids.*' => 'integer',
        ]);

        $order = null;
        if (! empty($validated['order_id'])) {
            $order = Order::find($validated['order_id']);
        }

        $pinCode = null;
        if ($validated['access_type'] === 'Privat') {
            $pinCode = ! empty($validated['pin_code']) ? $validated['pin_code'] : ($gallery->pin_code ?: '1234');
        }

        $gallery->update([
            'title' => $validated['title'],
            'order_id' => $order ? $order->id : null,
            'client_name' => $order ? $order->customer_name : $gallery->client_name,
            'status' => $validated['status'],
            'target_photos' => (int) $validated['target_photos'],
            'access_type' => $validated['access_type'],
            'pin_code' => $pinCode,
        ]);

        // Hapus foto yang ditandai untuk dihapus oleh admin saat simpan
        if (! empty($validated['deleted_photo_ids'])) {
            $photosToDelete = GalleryPhoto::where('gallery_id', $gallery->id)
                ->whereIn('id', $validated['deleted_photo_ids'])
                ->get();

            foreach ($photosToDelete as $delPhoto) {
                if (Storage::disk('public')->exists($delPhoto->photo_path)) {
                    Storage::disk('public')->delete($delPhoto->photo_path);
                }
                $delPhoto->delete();
            }
        }

        // Unggah foto baru jika ada
        if ($request->hasFile('photos')) {
            $currentMaxSort = $gallery->photos()->max('sort_order') ?? 0;
            $sort = $currentMaxSort + 1;
            foreach ($request->file('photos') as $photoFile) {
                $path = $photoFile->store('galleries', 'public');
                GalleryPhoto::create([
                    'gallery_id' => $gallery->id,
                    'photo_path' => $path,
                    'caption' => 'Foto '.$sort,
                    'sort_order' => $sort,
                ]);
                $sort++;
            }
        }

        return redirect()->route('admin.galeri')
            ->with('success', 'Galeri "'.$gallery->title.'" berhasil diperbarui.');
    }

    /**
     * Hapus satu foto dari galeri
     */
    public function destroyPhoto($photo_id)
    {
        $photo = GalleryPhoto::findOrFail($photo_id);
        $galleryTitle = $photo->gallery ? $photo->gallery->title : 'Galeri';

        if (Storage::disk('public')->exists($photo->photo_path)) {
            Storage::disk('public')->delete($photo->photo_path);
        }

        $photo->delete();

        return redirect()->route('admin.galeri')
            ->with('success', 'Foto berhasil dihapus dari '.$galleryTitle.'.');
    }

    /**
     * Update status galeri
     */
    public function updateStatus(Request $request, $id)
    {
        $gallery = Gallery::findOrFail($id);
        $status = $request->input('status', 'Draf');

        $gallery->update(['status' => $status]);

        return redirect()->route('admin.galeri')
            ->with('success', 'Status galeri "'.$gallery->title.'" diubah menjadi '.$status.'.');
    }

    /**
     * Hapus galeri
     */
    public function destroy($id)
    {
        $gallery = Gallery::findOrFail($id);
        $title = $gallery->title;
        $gallery->delete();

        return redirect()->route('admin.galeri')
            ->with('success', 'Galeri "'.$title.'" berhasil dihapus.');
    }
}
