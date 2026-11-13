<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Draf Invoice {{ $data['invoice_number'] }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #253247;
            margin: 0;
            padding: 36px;
            font-size: 13px;
            line-height: 1.5;
        }
        .header {
            border-bottom: 2px solid #E1E7F0;
            padding-bottom: 24px;
            margin-bottom: 28px;
        }
        .brand-title {
            font-size: 26px;
            font-weight: bold;
            color: #253247;
            letter-spacing: -0.5px;
            margin: 0;
        }
        .brand-sub {
            font-size: 10px;
            color: #6E7A8B;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 4px;
        }
        .meta-table {
            width: 100%;
            margin-top: 20px;
        }
        .meta-table td {
            vertical-align: top;
        }
        .badge-draft {
            display: inline-block;
            background: #EFF4FB;
            color: #3B5B9A;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .section-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #6E7A8B;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            font-weight: bold;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 28px 0;
        }
        .items-table th {
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6E7A8B;
            border-bottom: 1px solid #E1E7F0;
            padding: 10px 0;
        }
        .items-table td {
            padding: 14px 0;
            border-bottom: 1px solid #F1F4F8;
        }
        .price-col {
            text-align: right;
            font-weight: bold;
        }
        .total-box {
            width: 100%;
            margin-top: 10px;
        }
        .total-box td {
            padding: 6px 0;
        }
        .grand-total {
            font-size: 18px;
            font-weight: bold;
            color: #3B5B9A;
            border-top: 2px solid #253247;
            padding-top: 10px !important;
        }
        .payment-info {
            margin-top: 36px;
            padding: 18px;
            background: #F8FAFC;
            border-radius: 8px;
            border: 1px solid #E1E7F0;
        }
        .footer-note {
            margin-top: 40px;
            border-top: 1px dashed #CBD5E1;
            padding-top: 16px;
            font-size: 11px;
            color: #94A3B8;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <h1 class="brand-title">naro capture</h1>
                    <div class="brand-sub">STUDIO FOTOGRAFI · INVOICE TAGIHAN</div>
                    <div style="margin-top: 8px; font-size: 12px; color: #64748B;">
                        hello@narocapture.com · Jakarta, Indonesia
                    </div>
                </td>
                <td style="text-align: right;">
                    <span class="badge-draft">DRAF · BELUM DIBAYAR</span>
                    <h2 style="margin: 8px 0 0; font-size: 18px; color: #253247;">{{ $data['invoice_number'] }}</h2>
                    <div style="font-size: 11px; color: #64748B; margin-top: 4px;">
                        Tanggal terbit: {{ date('d F Y') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 50%;">
                <div class="section-label">DITUJUKAN KEPADA:</div>
                <strong style="font-size: 15px;">{{ $data['nama_lengkap'] }}</strong><br>
                <span>Email: {{ $data['email'] }}</span><br>
                <span>WhatsApp: {{ $data['whatsapp'] }}</span>
            </td>
            <td style="width: 50%; text-align: right;">
                <div class="section-label">DETAIL PESANAN:</div>
                <strong>Kode Pesanan: {{ $data['order_code'] }}</strong><br>
                <span>Jadwal: {{ $data['session_date'] }}</span><br>
                <span>Waktu: {{ $data['session_time'] }}</span>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 60%;">LAYANAN & CAKUPAN SESI</th>
                <th style="width: 15%; text-align: center;">DURASI</th>
                <th style="width: 25%; text-align: right;">JUMLAH</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong style="font-size: 14px; color: #253247;">{{ $data['package_name'] }}</strong>
                    <div style="font-size: 11px; color: #64748B; margin-top: 4px;">
                        Lokasi: {{ $data['lokasi'] ?? 'Studio Utama naro capture' }}
                    </div>
                </td>
                <td style="text-align: center; color: #64748B;">
                    {{ $data['package_duration'] ?? 60 }} Menit
                </td>
                <td class="price-col">
                    {{ $data['package_price'] }}
                </td>
            </tr>
        </tbody>
    </table>

    <table class="total-box">
        <tr>
            <td style="width: 65%;"></td>
            <td style="width: 20%; color: #64748B;">Subtotal:</td>
            <td style="width: 15%; text-align: right; font-weight: bold;">{{ $data['package_price'] }}</td>
        </tr>
        <tr>
            <td></td>
            <td class="grand-total">Total Tagihan:</td>
            <td class="grand-total" style="text-align: right;">{{ $data['package_price'] }}</td>
        </tr>
    </table>

    <div class="payment-info">
        <div class="section-label">REKENING PEMBAYARAN RESMI STUDIO:</div>
        <table style="width: 100%; margin-top: 8px;">
            <tr>
                <td style="width: 50%;">
                    <strong>Bank BCA</strong><br>
                    <span>No. Rek: <strong>8735 0912 344</strong></span><br>
                    <span style="font-size: 11px; color: #64748B;">a.n. naro capture Studio</span>
                </td>
                <td style="width: 50%;">
                    <strong>Bank Mandiri</strong><br>
                    <span>No. Rek: <strong>137 00 2841 9920</strong></span><br>
                    <span style="font-size: 11px; color: #64748B;">a.n. naro capture Studio</span>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer-note">
        Dokumen ini diterbitkan secara otomatis oleh sistem naro capture sebagai draf rincian tagihan.<br>
        Setelah melakukan transfer, silakan unggah bukti pembayaran melalui halaman konfirmasi untuk verifikasi jadwal.
    </div>
</body>
</html>
