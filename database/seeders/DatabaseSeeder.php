<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Package;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\StudioSlot;
use App\Models\Gallery;
use App\Models\GalleryPhoto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin User
        User::updateOrCreate(
            ['email' => 'admin@narocapture.com'],
            [
                'name' => 'naro capture Admin',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Packages
        $pkgBirthday = Package::updateOrCreate(
            ['code' => 'PKG-BIRTHDAY'],
            [
                'slug' => 'birthday',
                'name' => 'Birthday',
                'image' => 'paket-birthday.jpg',
                'tagline' => 'Rayakan pertambahan usia dengan potret ceria dan penuh warna.',
                'description' => 'Sesi personal atau pesta ulang tahun dengan dekorasi balon/kue, mengabadikan momen spesial penuh kebahagiaan.',
                'price' => 1250000,
                'duration' => '60 menit',
                'duration_minutes' => 60,
                'photo_count' => 15,
                'photo_count_label' => '15 foto retouched',
                'features' => [
                    'Properti kue & balon studio',
                    '15 foto edited resolusi tinggi',
                    'Akses private digital gallery 60 hari',
                    'Bebas bawa properti ultah sendiri'
                ],
                'badge' => 'Ceria',
                'is_active' => true,
                'is_featured' => false,
            ]
        );

        $pkgFamily = Package::updateOrCreate(
            ['code' => 'PKG-FAMILY'],
            [
                'slug' => 'family-session',
                'name' => 'Family Session',
                'image' => 'paket-family.jpg',
                'tagline' => 'Abadikan kehangatan bersama orang tercinta dalam sesi santai dan natural.',
                'description' => 'Momen hangat keluarga tanpa kesan kaku dengan arahan pose luwes dan interaksi alami antar anggota keluarga.',
                'price' => 2450000,
                'duration' => '90 menit',
                'duration_minutes' => 90,
                'photo_count' => 25,
                'photo_count_label' => '25 foto retouched + cetak 10R',
                'features' => [
                    'Maksimal 6-8 anggota keluarga',
                    '2 konsep latar / ganti kostum',
                    '25 foto master edited & cetak 10R framed',
                    'Semua file original via private cloud'
                ],
                'badge' => 'Terpopuler',
                'is_active' => true,
                'is_featured' => true,
            ]
        );

        $pkgGraduation = Package::updateOrCreate(
            ['code' => 'PKG-GRADUATION'],
            [
                'slug' => 'graduation',
                'name' => 'Graduation',
                'image' => 'paket-graduation.jpg',
                'tagline' => 'Momen perayaan kelulusan dan keberhasilan yang membanggakan.',
                'description' => 'Sesi toga dan wisuda formal maupun santai bersama orang tua dan sahabat tercinta.',
                'price' => 1500000,
                'duration' => '60 menit',
                'duration_minutes' => 60,
                'photo_count' => 15,
                'photo_count_label' => '15 foto retouched + cetak toga',
                'features' => [
                    'Sesi toga wisudawan + keluarga inti',
                    '15 foto edited resolusi tinggi',
                    '1 cetak pembesar 10RS + frame kayu',
                    'Free peminjaman properti toga/ijazah mockup'
                ],
                'badge' => 'Wisuda',
                'is_active' => true,
                'is_featured' => false,
            ]
        );

        $pkgPrewedding = Package::updateOrCreate(
            ['code' => 'PKG-PREWEDDING'],
            [
                'slug' => 'prewedding',
                'name' => 'Prewedding',
                'image' => 'paket-prewedding.jpg',
                'tagline' => 'Cerita cinta Anda dalam rangkaian visual sinematik dan puitis.',
                'description' => 'Sesi pra-nikah terkonsep dengan arahan moodboard terkurasi, tata cahaya sinematik, dan hasil berstandar galeri.',
                'price' => 4800000,
                'duration' => '180 menit',
                'duration_minutes' => 180,
                'photo_count' => 40,
                'photo_count_label' => '40 foto master retouched',
                'features' => [
                    'Sesi studio & semi-outdoor',
                    '3 kali ganti busana/wardrobe',
                    '40 foto retouched detail warna filmic',
                    '1 album kolase eksklusif 20x30 cm'
                ],
                'badge' => 'Eksklusif',
                'is_active' => true,
                'is_featured' => false,
            ]
        );

        // 3. Customers
        $customerAyu = Customer::updateOrCreate(
            ['email' => 'ayu.pramesti@example.com'],
            [
                'customer_code' => 'CUST-2026-001',
                'name' => 'Ayu Pramesti',
                'whatsapp' => '081234567890',
                'notes' => 'Pelanggan tetap sesi personal, prefer tone hangat & filmic.',
                'status' => 'aktif',
            ]
        );

        $customerBudi = Customer::updateOrCreate(
            ['email' => 'budi.santoso@example.com'],
            [
                'customer_code' => 'CUST-2026-002',
                'name' => 'Budi Santoso & Keluarga',
                'whatsapp' => '081398765432',
                'notes' => 'Sesi tahunan bersama anak kembar usia 5 tahun.',
                'status' => 'aktif',
            ]
        );

        // 4. Orders
        $order1 = Order::updateOrCreate(
            ['order_code' => 'CH-CONTOH-001'],
            [
                'invoice_number' => 'INV-CH-2026-001',
                'customer_id' => $customerAyu->id,
                'package_id' => $pkgBirthday->id,
                'customer_name' => $customerAyu->name,
                'customer_email' => $customerAyu->email,
                'customer_whatsapp' => $customerAyu->whatsapp,
                'package_name' => $pkgBirthday->name,
                'total_price' => $pkgBirthday->price,
                'session_date' => '2026-10-18',
                'session_time_slot' => '10.00–11.00 WIB',
                'location' => 'Studio Utama (Studio A)',
                'customer_notes' => 'Meminta nuansa warm neutral dengan wardrobe krem/putih.',
                'internal_notes' => 'Konfirmasi via WA 9 Okt. Klien request soft background.',
                'session_status' => 'Permintaan',
                'payment_status' => 'Bukti terkirim',
            ]
        );

        $order2 = Order::updateOrCreate(
            ['order_code' => 'CH-CONTOH-002'],
            [
                'invoice_number' => 'INV-CH-2026-002',
                'customer_id' => $customerBudi->id,
                'package_id' => $pkgFamily->id,
                'customer_name' => $customerBudi->name,
                'customer_email' => $customerBudi->email,
                'customer_whatsapp' => $customerBudi->whatsapp,
                'package_name' => $pkgFamily->name,
                'total_price' => $pkgFamily->price,
                'session_date' => '2026-10-24',
                'session_time_slot' => '14.00–15.30 WIB',
                'location' => 'Studio Keluarga (Studio B)',
                'customer_notes' => 'Bawa 2 set pakaian, butuh bangku anak tambahan.',
                'internal_notes' => 'Sudah DP lunas, siapkan properti mainan kayu.',
                'session_status' => 'Dikonfirmasi',
                'payment_status' => 'Terverifikasi',
            ]
        );

        // 5. Payments
        Payment::updateOrCreate(
            ['payment_code' => 'BYR-CONTOH-001'],
            [
                'order_id' => $order1->id,
                'sender_name' => 'Ayu Pramesti',
                'transfer_date' => '2026-10-09',
                'bank_source' => 'BCA',
                'nominal' => $pkgBirthday->price,
                'sender_notes' => 'Transfer via m-BCA jam 10:15 WIB. Mohon verifikasi.',
                'proof_file' => 'struk-bca-ch001.jpg',
                'file_original_name' => 'bukti_transfer_ayu_bca.jpg',
                'file_extension' => 'JPG',
                'status' => 'Bukti terkirim',
                'internal_review_notes' => 'Bukti cocok dengan nominal paket, menunggu pengecekan mutasi BCA.',
                'verified_at' => null,
            ]
        );

        Payment::updateOrCreate(
            ['payment_code' => 'BYR-CONTOH-002'],
            [
                'order_id' => $order2->id,
                'sender_name' => 'Budi Santoso',
                'transfer_date' => '2026-10-08',
                'bank_source' => 'Mandiri',
                'nominal' => $pkgFamily->price,
                'sender_notes' => 'Transfer lunas penuh paket keluarga.',
                'proof_file' => 'struk-mandiri-ch002.jpg',
                'file_original_name' => 'mandiri_transfer_budi.pdf',
                'file_extension' => 'PDF',
                'status' => 'Terverifikasi',
                'internal_review_notes' => 'Sudah diverifikasi via mutasi rekening Mandiri Studio.',
                'verified_at' => '2026-10-08 14:20:00',
            ]
        );

        // 6. Studio Slots
        StudioSlot::updateOrCreate(
            ['date' => '2026-10-18', 'start_time' => '10:00:00'],
            [
                'end_time' => '11:00:00',
                'type' => 'booked',
                'order_id' => $order1->id,
                'note' => 'Potret Pribadi - Ayu Pramesti',
            ]
        );

        StudioSlot::updateOrCreate(
            ['date' => '2026-10-24', 'start_time' => '14:00:00'],
            [
                'end_time' => '15:30:00',
                'type' => 'booked',
                'order_id' => $order2->id,
                'note' => 'Cerita Keluarga - Budi Santoso',
            ]
        );

        StudioSlot::updateOrCreate(
            ['date' => '2026-10-20', 'start_time' => '09:00:00'],
            [
                'end_time' => '13:00:00',
                'type' => 'blocked',
                'order_id' => null,
                'note' => 'Kalibrasi Lampu Godox & Pengecatan Dinding Studio A',
            ]
        );

        // 7. Gallery
        $gallery = Gallery::updateOrCreate(
            ['slug' => 'galeri-ayu-pramesti'],
            [
                'title' => 'Galeri Sesi Potret Ayu Pramesti',
                'order_id' => $order1->id,
                'client_name' => 'Ayu Pramesti',
                'cover_image' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=800&q=80',
                'target_photos' => 20,
                'status' => 'Diterbitkan',
                'access_type' => 'Privat',
                'pin_code' => '1234',
            ]
        );

        GalleryPhoto::updateOrCreate(
            ['gallery_id' => $gallery->id, 'sort_order' => 1],
            ['photo_path' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=800&q=80', 'caption' => 'Warm tone portrait']
        );
        GalleryPhoto::updateOrCreate(
            ['gallery_id' => $gallery->id, 'sort_order' => 2],
            ['photo_path' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=800&q=80', 'caption' => 'Black & white classic']
        );
    }
}
