<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="icon" type="image/x-icon" href="{{ asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        Tanda_Terima_Agunan_{{ $pinjaman->kode_pinjaman }}_{{ Str::slug($pinjaman->nasabah->nama_lengkap ?? 'Nasabah') }}
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            @page {
                size: A4;
                margin: 5mm;
            }

            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background: #fff;
            }

            .no-print {
                display: none !important;
            }

            .sk-container {
                margin: 0 !important;
                padding: 0.6cm 0.8cm !important;
                box-shadow: none !important;
                min-height: auto !important;
                max-width: 100% !important;
            }
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            background: #e2e8f0;
            color: #000;
        }

        .sk-container {
            background: white;
            max-width: 21cm;
            min-height: 29.7cm;
            margin: 5mm auto;
            padding: 0.8cm 1cm;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            line-height: 1.35;
        }

        .header-wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #000;
            padding-bottom: 8px;
            margin-bottom: 2px;
            font-family: 'Lexend', sans-serif;
        }

        .kop-kiri {
            width: 100%;
            text-align: left;
        }

        .kop-kiri img {
            max-width: 70%;
            height: auto;
            max-height: 120px;
            display: inline-block;
            object-fit: contain;
        }

        .garis-ganda {
            border-bottom: 1px solid #000;
            margin-bottom: 12px;
        }

        .judul-sk {
            text-align: center;
            margin-bottom: 12px;
        }

        .tabel-identitas {
            width: 100%;
            margin: 4px 0 4px 20px;
        }

        .tabel-identitas td {
            padding-bottom: 2px;
            font-size: 10pt;
            vertical-align: top;
        }

        .tabel-identitas td:nth-child(1) {
            width: 150px;
            font-weight: bold;
        }

        .tabel-identitas td:nth-child(2) {
            width: 12px;
            text-align: center;
        }

        .tabel-agunan {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }

        .tabel-agunan th,
        .tabel-agunan td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 9pt;
        }

        .tabel-agunan th {
            background-color: #f1f5f9;
            text-align: left;
        }
    </style>
</head>

<body>

    @php
        $pengasuh =
            \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Pengasuh') ??
            \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Kepala');
        $bendahara = \App\Models\Kepengurusan\Pengurus::getAktifByJabatan('Bendahara');
        $penerima = $pinjaman->jaminans->first()?->penerimaJaminan ?? auth()->user();
    @endphp

    <!-- TOMBOL AKSI & INDIKATOR -->
    <div
        class="no-print sticky top-0 z-50 flex flex-wrap justify-center items-center py-2.5 px-4 bg-slate-900 text-white gap-3 border-b mb-4 shadow-md font-sans">
        <div
            class="px-3 py-1 bg-amber-500/20 text-amber-300 border border-amber-400/40 rounded-lg text-xs font-bold flex items-center shadow-2xs">
            📄 TANDA TERIMA TITIPAN AGUNAN / JAMINAN
        </div>
        <button onclick="window.print()"
            class="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-black flex items-center gap-1.5 transition-all shadow-md active:scale-95">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <div class="sk-container text-[10pt] text-justify">

        <!-- KOP SURAT ALA SK -->
        <div class="header-wrapper">
            <div class="kop-kiri">
                <img src="{{ asset(getSetting('kop_logo')) }}" alt="Kop Surat Madrasah">
            </div>
        </div>
        <div class="garis-ganda"></div>

        <!-- JUDUL SURAT -->
        <div class="judul-sk">
            <h2 class="text-[11.5pt] font-bold underline uppercase mb-0.5 tracking-wide">SURAT TANDA TERIMA TITIPAN
                AGUNAN / JAMINAN</h2>
            <p class="font-bold text-[10pt]">Nomor:
                {{ $pinjaman->kode_pinjaman }}/TT-JMN/MDT/{{ date('Y', strtotime($pinjaman->tanggal_pengajuan)) }}</p>
            <p class="font-bold uppercase text-[9.5pt] text-slate-700 mt-0.5">BADAN PENGELOLA KEUANGAN & PERBENDAHARAAN
            </p>
        </div>

        <p class="mb-2">
            Pada hari ini,
            <strong>{{ \Carbon\Carbon::parse($pinjaman->tanggal_pengajuan)->translatedFormat('l') }}</strong>,
            tanggal
            <strong>{{ \Carbon\Carbon::parse($pinjaman->tanggal_pengajuan)->translatedFormat('d F Y') }}</strong>,
            telah diserahterimakan dokumen / barang berharga sebagai agunan jaminan fasilitas pinjaman dengan rincian
            data sebagai berikut:
        </p>

        <!-- IDENTITAS NASABAH -->
        <div class="font-bold text-[10pt]">I. DATA NASABAH (Yang Menyerahkan):</div>
        <table class="tabel-identitas">
            <tr>
                <td>Nama Nasabah</td>
                <td>:</td>
                <td class="font-bold uppercase">{{ $pinjaman->nasabah->nama_lengkap }}</td>
            </tr>
            <tr>
                <td>Kode Nasabah / NIK</td>
                <td>:</td>
                <td>{{ $pinjaman->nasabah->kode_nasabah }} / {{ $pinjaman->nasabah->nik_ktp ?? '-' }}</td>
            </tr>
            <tr>
                <td>Kategori Nasabah</td>
                <td>:</td>
                <td>{{ ucwords(str_replace('_', ' ', $pinjaman->nasabah->tipe_nasabah)) }}</td>
            </tr>
            <tr>
                <td>Nomor Kontak / HP</td>
                <td>:</td>
                <td>{{ $pinjaman->nasabah->no_hp }}</td>
            </tr>
            <tr>
                <td>Alamat Domisili</td>
                <td>:</td>
                <td>{{ $pinjaman->nasabah->alamat }}</td>
            </tr>
        </table>

        <!-- REFERENSI PINJAMAN -->
        <div class="font-bold text-[10pt] mt-2">II. REFERENSI FASILITAS PINJAMAN:</div>
        <table class="tabel-identitas">
            <tr>
                <td>Kode Pinjaman</td>
                <td>:</td>
                <td class="font-bold">{{ $pinjaman->kode_pinjaman }}</td>
            </tr>
            <tr>
                <td>Nominal Pinjaman</td>
                <td>:</td>
                <td class="font-bold">Rp {{ number_format($pinjaman->nominal_pinjaman, 0, ',', '.') }},- (Tenor:
                    {{ $pinjaman->tenor_bulan }} Bulan)</td>
            </tr>
            <tr>
                <td>Keperluan Pinjaman</td>
                <td>:</td>
                <td>{{ $pinjaman->keperluan_pinjaman }}</td>
            </tr>
        </table>

        <!-- DAFTAR AGUNAN -->
        <div class="font-bold text-[10pt] mt-2">III. RINCIAN BARANG / DOKUMEN AGUNAN YANG DITITIPKAN:</div>
        <table class="tabel-agunan mb-2">
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">No</th>
                    <th>Jenis Agunan</th>
                    <th>Nama / Rincian Barang</th>
                    <th>No. Dokumen / Seri</th>
                    <th>Atas Nama Dokumen</th>
                    <th style="text-align: right;">Taksiran Nilai</th>
                    <th>Lokasi Simpan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pinjaman->jaminans as $idx => $jmn)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $jmn->jenis_jaminan)) }}</td>
                        <td><strong>{{ $jmn->nama_barang_jaminan }}</strong></td>
                        <td>{{ $jmn->nomor_dokumen_jaminan ?? '-' }}</td>
                        <td>{{ $jmn->atas_nama_dokumen ?? '-' }}</td>
                        <td style="text-align: right;">Rp {{ number_format($jmn->taksiran_nilai, 0, ',', '.') }}</td>
                        <td>{{ $jmn->lokasi_penyimpanan ?? 'Brankas MDT' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; font-style: italic;">Tidak ada rincian agunan
                            fisik terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <p class="text-[9pt] italic text-slate-700 mb-3 leading-relaxed">
            <strong>Ketentuan Penitipan:</strong> Dokumen / barang berharga tersebut di atas disimpan secara aman di
            bawah pengawasan Badan Pengelola Keuangan MDT Hidayatus Shibyan dan akan diserahterimakan kembali secara
            utuh kepada nasabah setelah seluruh kewajiban pinjaman dilunasi secara sah.
        </p>

        <!-- TANDA TANGAN ALA SK RESMI -->
        <table class="w-full text-center mt-6 text-[9.5pt]" style="table-layout: fixed;">
            <tr>
                <td class="w-1/3 align-top" style="height: 50px;">
                    <p class="mb-0.5">Mengetahui,</p>
                    <p class="font-bold">Pengasuh Madrasah</p>
                </td>
                <td class="w-1/3 align-top" style="height: 50px;">
                    <p class="mb-0.5">Yang Menerima Titipan,</p>
                    <p class="font-bold">Petugas / Bendahara MDT</p>
                </td>
                <td class="w-1/3 align-top" style="height: 50px;">
                    <p class="mb-0">Ditetapkan di : Bangkalan</p>
                    <p class="mb-0.5">Pada Tanggal :
                        {{ \Carbon\Carbon::parse($pinjaman->tanggal_pengajuan)->translatedFormat('d F Y') }}</p>
                    <p class="font-bold">Yang Menyerahkan Agunan</p>
                </td>
            </tr>
            <tr>
                <!-- TTD 1: Pengasuh -->
                <td class="align-bottom pb-1 pt-1">
                    <div class="flex justify-center">
                        @if (!empty($pengasuh?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $pengasuh->id]),
                            ) !!}
                        @else
                            <div style="height: 65px;"
                                class="flex items-center justify-center text-slate-400 text-xs italic">
                                (Tanda Tangan)
                            </div>
                        @endif
                    </div>
                </td>

                <!-- TTD 2: Penerima Titipan -->
                <td class="align-bottom pb-1 pt-1">
                    <div class="flex justify-center">
                        @if (!empty($bendahara?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'pengurus', 'id' => $bendahara->id]),
                            ) !!}
                        @elseif(!empty($penerima?->id))
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(65)->generate(
                                URL::signedRoute('profil.publik', ['tipe' => 'administrator', 'id' => $penerima->id]),
                            ) !!}
                        @else
                            <div style="height: 65px;"
                                class="flex items-center justify-center text-slate-400 text-xs italic">
                                (Tanda Tangan)
                            </div>
                        @endif
                    </div>
                </td>

                <!-- TTD 3: Peminjam / Yang Menyerahkan -->
                <td class="align-bottom pb-1 pt-1">
                    <div class="flex justify-center items-center flex-col">
                        <div style="height: 60px;" class="flex items-end justify-center text-slate-400 text-xs italic">
                            (Tanda Tangan & Nama Terang)
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td class="align-bottom">
                    <p class="font-bold underline mb-0">
                        {{ $pengasuh?->anggota?->ustadz?->nama_lengkap ?? ($pengasuh?->anggota?->nama ?? 'Pengasuh MDT Hidayatus Shibyan') }}
                    </p>
                </td>
                <td class="align-bottom">
                    <p class="font-bold underline mb-0">
                        {{ $bendahara?->anggota?->ustadz?->nama_lengkap ?? ($penerima->name ?? 'Petugas MDT') }}
                    </p>
                </td>
                <td class="align-bottom">
                    <p class="font-bold underline mb-0">{{ $pinjaman->nasabah->nama_lengkap }}</p>
                </td>
            </tr>
        </table>
    </div>

    @if (request()->has('print') || request()->has('auto_print') || request()->has('download'))
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    window.print();
                }, 600);
            });
        </script>
    @endif

</body>

</html>
