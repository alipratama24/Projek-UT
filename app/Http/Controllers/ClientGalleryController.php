<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class ClientGalleryController extends Controller
{
    /**
     * Tampilkan galeri foto klien
     */
    public function index(Request $request)
    {
        $slug = $request->query('slug');
        $gallery = null;

        if ($slug) {
            $gallery = Gallery::with(['photos', 'order'])->where('slug', $slug)->first();
        }

        if (! $gallery) {
            $gallery = Gallery::with(['photos', 'order'])->where('status', 'Diterbitkan')->latest()->first()
                ?? Gallery::with(['photos', 'order'])->latest()->first();
        }

        // Pengecekan Akses Privat dengan Proteksi PIN
        $isLocked = false;
        if ($gallery && $gallery->access_type === 'Privat' && ! empty($gallery->pin_code)) {
            $isAdmin = false;
            $adminToken = $request->cookie('admin_auth_token');
            if ($adminToken) {
                $isAdmin = \App\Models\User::where('remember_token', $adminToken)->exists();
            }

            $isUnlocked = session("gallery_unlocked_{$gallery->id}") === true;

            // Jika bukan admin dan belum unlock PIN
            if (! $isAdmin && ! $isUnlocked) {
                $isLocked = true;
            }
        }

        $fallbackImages = [
            ['file' => 'work-1.jpg', 'title' => 'Keluarga — 01'],
            ['file' => 'work-2.jpg', 'title' => 'Keluarga — 02'],
            ['file' => 'work-3.jpg', 'title' => 'Keluarga — 03'],
            ['file' => 'work-4.jpg', 'title' => 'Keluarga — 04'],
            ['file' => 'work-5.jpg', 'title' => 'Keluarga — 05'],
            ['file' => 'work-6.jpg', 'title' => 'Keluarga — 06'],
        ];

        if ($gallery) {
            $order = $gallery->order;
            $data = [
                'order_code' => $order ? $order->order_code : 'GLR-'.strtoupper(substr($gallery->slug, 0, 8)),
                'invoice_number' => $order ? $order->invoice_number : 'INV-STUDIO',
                'package_name' => $gallery->title,
                'nama_lengkap' => $gallery->client_name,
                'session_date' => $order && $order->session_date ? $order->session_date->translatedFormat('l, d F Y') : 'Sesi Studio',
                'session_time' => $order ? $order->session_time_slot : 'Terselesaikan',
                'lokasi' => $order ? $order->location : 'Studio Utama naro capture',
            ];

            $maskedEmail = $order ? substr($order->customer_email, 0, 1).'•••@'.explode('@', $order->customer_email)[1] : 'k•••@gmail.com';
            $maskedPhone = $order ? '+62 8•• •••• '.substr($order->customer_whatsapp, -4) : '+62 8•• •••• 7890';

            // Resolusi Cover Image yang dijamin selalu muncul
            $coverFile = $gallery->cover_image ?: 'work-1.jpg';
            if (! Str::startsWith($coverFile, ['http://', 'https://'])) {
                if (file_exists(public_path('images/'.$coverFile))) {
                    $coverImage = asset('images/'.$coverFile);
                } elseif (Storage::disk('public')->exists($coverFile) || file_exists(public_path('storage/'.$coverFile))) {
                    $coverImage = asset('storage/'.$coverFile);
                } else {
                    $coverImage = asset('images/work-1.jpg');
                }
            } else {
                $coverImage = $coverFile;
            }

            // Jika galeri terkunci PIN, jangan paparkan daftar foto
            if ($isLocked) {
                $photos = [];
            } elseif ($gallery->photos->isNotEmpty()) {
                $photos = $gallery->photos->map(function ($p, $idx) use ($gallery) {
                    $src = $p->photo_path;
                    if (! Str::startsWith($src, ['http://', 'https://'])) {
                        if (file_exists(public_path('images/'.$src))) {
                            $src = asset('images/'.$src);
                        } elseif (Storage::disk('public')->exists($src) || file_exists(public_path('storage/'.$src))) {
                            $src = asset('storage/'.$src);
                        } else {
                            $src = asset('images/work-'.(($idx % 6) + 1).'.jpg');
                        }
                    }

                    $title = $p->caption ?: $gallery->title.' — '.str_pad($idx + 1, 2, '0', STR_PAD_LEFT);

                    return [
                        'id' => $p->id,
                        'img' => $src,
                        'file' => $p->photo_path,
                        'title' => $title,
                        'download_url' => route('galeri-klien.download-photo', [
                            'file' => $p->photo_path,
                            'name' => $title,
                        ]),
                    ];
                })->toArray();
            } else {
                // Fallback foto studio agar tidak pernah kosong / blank
                $photos = array_map(function ($item, $idx) use ($gallery) {
                    return [
                        'id' => $idx + 1,
                        'img' => asset('images/'.$item['file']),
                        'file' => $item['file'],
                        'title' => $gallery->title.' — '.str_pad($idx + 1, 2, '0', STR_PAD_LEFT),
                        'download_url' => route('galeri-klien.download-photo', [
                            'file' => $item['file'],
                            'name' => $gallery->title.' — '.($idx + 1),
                        ]),
                    ];
                }, $fallbackImages, array_keys($fallbackImages));
            }
        } else {
            $data = session('last_booking', [
                'order_code' => 'CH-CONTOH-001',
                'invoice_number' => 'INV-CH-2026-001',
                'package_name' => 'Cerita Keluarga',
                'nama_lengkap' => 'Ayu Pramesti',
                'session_date' => 'Sabtu, 17 Oktober 2026',
                'session_time' => '15.30–17.00 WIB · 90 menit',
                'lokasi' => 'Rumah keluarga di Jakarta Selatan & taman terdekat',
            ]);

            $maskedEmail = 'a•••@gmail.com';
            $maskedPhone = '+62 8•• •••• 7890';
            $coverImage = asset('images/work-1.jpg');

            $photos = array_map(function ($item, $idx) {
                return [
                    'id' => $idx + 1,
                    'img' => asset('images/'.$item['file']),
                    'file' => $item['file'],
                    'title' => $item['title'],
                    'download_url' => route('galeri-klien.download-photo', [
                        'file' => $item['file'],
                        'name' => $item['title'],
                    ]),
                ];
            }, $fallbackImages, array_keys($fallbackImages));
        }

        return view('galeri-klien', compact('data', 'maskedEmail', 'maskedPhone', 'gallery', 'photos', 'isLocked', 'coverImage'));
    }

    /**
     * Verifikasi PIN akses galeri privat
     */
    public function verifyPin(Request $request)
    {
        $galleryId = $request->input('gallery_id');
        $pinInput = trim((string) $request->input('pin_code'));

        $gallery = Gallery::findOrFail($galleryId);

        if ($gallery->access_type !== 'Privat' || empty($gallery->pin_code) || $pinInput === (string) $gallery->pin_code) {
            session(["gallery_unlocked_{$gallery->id}" => true]);

            return redirect()->route('galeri-klien', ['slug' => $gallery->slug])
                ->with('success', 'PIN berhasil diverifikasi. Selamat menikmati galeri foto pribadi Anda.');
        }

        return redirect()->route('galeri-klien', ['slug' => $gallery->slug])
            ->with('error', 'Kode PIN yang Anda masukkan salah. Silakan periksa kembali atau hubungi studio naro capture.')
            ->withInput();
    }

    /**
     * Unduh satu foto resolusi penuh
     */
    public function downloadPhoto(Request $request)
    {
        $file = $request->query('file');
        $title = $request->query('name', 'foto-naro-capture');

        if (! $file) {
            abort(404, 'File foto tidak ditentukan.');
        }

        $cleanTitle = Str::slug($title) ?: 'foto-studio';
        $filePath = null;
        $extension = 'jpg';

        // 1. Cek di storage/app/public/
        if (Storage::disk('public')->exists($file)) {
            $filePath = Storage::disk('public')->path($file);
            $extension = pathinfo($filePath, PATHINFO_EXTENSION) ?: 'jpg';
        }
        // 2. Cek di public/images/
        elseif (file_exists(public_path('images/'.basename($file)))) {
            $filePath = public_path('images/'.basename($file));
            $extension = pathinfo($filePath, PATHINFO_EXTENSION) ?: 'jpg';
        }
        // 3. Cek di storage/app/public/galleries/ jika hanya dikirim nama file
        elseif (Storage::disk('public')->exists('galleries/'.basename($file))) {
            $filePath = Storage::disk('public')->path('galleries/'.basename($file));
            $extension = pathinfo($filePath, PATHINFO_EXTENSION) ?: 'jpg';
        }

        if (! $filePath || ! file_exists($filePath)) {
            abort(404, 'Berkas foto tidak ditemukan di server.');
        }

        $downloadFilename = $cleanTitle.'.'.$extension;

        return response()->download($filePath, $downloadFilename, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    /**
     * Unduh seluruh foto galeri dalam arsip ZIP
     */
    public function downloadAll(Request $request)
    {
        $slug = $request->query('slug');
        $gallery = null;

        if ($slug) {
            $gallery = Gallery::with(['photos', 'order'])->where('slug', $slug)->first();
        }

        if (! $gallery) {
            $gallery = Gallery::with(['photos', 'order'])->where('status', 'Diterbitkan')->latest()->first()
                ?? Gallery::with(['photos', 'order'])->latest()->first();
        }

        $filesToAdd = [];
        $orderCode = $gallery && $gallery->order ? $gallery->order->order_code : ($gallery ? $gallery->slug : 'CH-2026-001');

        if ($gallery && $gallery->photos->isNotEmpty()) {
            foreach ($gallery->photos as $idx => $photo) {
                $path = $photo->photo_path;
                $fullPath = null;

                if (Storage::disk('public')->exists($path)) {
                    $fullPath = Storage::disk('public')->path($path);
                } elseif (file_exists(public_path('images/'.basename($path)))) {
                    $fullPath = public_path('images/'.basename($path));
                }

                if ($fullPath && file_exists($fullPath)) {
                    $ext = pathinfo($fullPath, PATHINFO_EXTENSION) ?: 'jpg';
                    $caption = $photo->caption ? Str::slug($photo->caption) : 'foto-'.str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
                    $filesToAdd[] = [
                        'source' => $fullPath,
                        'name' => $caption.'.'.$ext,
                    ];
                }
            }
        }

        // Jika belum ada foto di database, sediakan foto studio bawaan
        if (empty($filesToAdd)) {
            for ($i = 1; $i <= 6; $i++) {
                $samplePath = public_path("images/work-{$i}.jpg");
                if (file_exists($samplePath)) {
                    $filesToAdd[] = [
                        'source' => $samplePath,
                        'name' => 'Keluarga-'.str_pad($i, 2, '0', STR_PAD_LEFT).'.jpg',
                    ];
                }
            }
        }

        if (empty($filesToAdd)) {
            return back()->with('error', 'Tidak ada foto yang tersedia untuk diunduh.');
        }

        $zipFileName = 'naro-capture-'.Str::slug($orderCode).'-semua-foto.zip';
        $tempZipPath = tempnam(sys_get_temp_dir(), 'naro_zip_');

        $zip = new ZipArchive;
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Gagal membuat arsip ZIP.');
        }

        foreach ($filesToAdd as $item) {
            $zip->addFile($item['source'], $item['name']);
        }

        $zip->close();

        return response()->download($tempZipPath, $zipFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }
}
