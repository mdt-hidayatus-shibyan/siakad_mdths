<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID CARD PANITIA - {{ strtoupper($kegiatan->judul_lengkap) }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f0f0f0;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            padding: 10px;
            box-sizing: border-box;
        }
        .card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #d1d5db;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            overflow: hidden;
            width: 100%;
            height: 330px;
            display: flex;
            flex-direction: column;
            position: relative;
            box-sizing: border-box;
        }
        .card-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: #fff;
            text-align: center;
            padding: 10px 8px;
            position: relative;
        }
        .card-header .logo {
            width: 32px;
            height: 32px;
            object-fit: contain;
            margin-bottom: 2px;
        }
        .card-header h4 {
            font-size: 8pt;
            font-weight: 800;
            margin: 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .card-header p {
            font-size: 6.5pt;
            margin: 1px 0 0 0;
            opacity: 0.9;
        }
        .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px;
            text-align: center;
        }
        .photo-box {
            width: 75px;
            height: 95px;
            border-radius: 8px;
            border: 2px solid #3b82f6;
            overflow: hidden;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }
        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .person-name {
            font-size: 9pt;
            font-weight: bold;
            color: #111827;
            margin: 0;
            max-width: 170px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .person-role {
            font-size: 8pt;
            font-weight: 800;
            color: #2563eb;
            text-transform: uppercase;
            margin: 2px 0 0 0;
        }
        .person-divisi {
            font-size: 7.5pt;
            color: #6b7280;
            margin: 1px 0 0 0;
        }
        .card-footer {
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 6px 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 6pt;
            color: #64748b;
        }
        .card-footer .badge-edisi {
            background: #e0e7ff;
            color: #3730a3;
            padding: 2px 5px;
            border-radius: 4px;
            font-weight: bold;
        }
        .badge-bph {
            background: #fef3c7;
            color: #92400e;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 6.5pt;
            font-weight: bold;
            margin-top: 3px;
            display: inline-block;
        }
        @media print {
            body {
                background: #fff;
            }
            .no-print {
                display: none;
            }
            .card {
                box-shadow: none;
                border: 1px dashed #cbd5e1;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="position: fixed; top: 15px; right: 15px; background: #333; padding: 10px 15px; border-radius: 8px; z-index: 9999;">
        <button onclick="window.print()" style="background: #4f46e5; color: #fff; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; font-weight: bold;">
            Cetak Semua ID Card
        </button>
    </div>

    <div class="container">
        @forelse ($panitias as $panitia)
            <div class="card">
                <!-- Header Kartu -->
                <div class="card-header">
                    <img src="{{ $kegiatan->logo_kegiatan ? $kegiatan->logo_url : asset(getSetting('app_logo', 'assets/LOGO MDT.png')) }}" alt="Logo" class="logo">
                    <h4>{{ $kegiatan->judul_lengkap }}</h4>
                    <p>{{ $kegiatan->tahunPelajaran?->nama_hijriyah }} H / {{ $kegiatan->tahunPelajaran?->nama_masehi }} M</p>
                </div>

                <!-- Foto & Identitas -->
                <div class="card-body">
                    <div class="photo-box">
                        @if ($panitia->foto_utama)
                            <img src="{{ $panitia->foto_utama }}" alt="Foto">
                        @else
                            <span style="font-size: 24pt; color: #9ca3af;">👤</span>
                        @endif
                    </div>
                    <h5 class="person-name" title="{{ $panitia->nama_tampil }}">{{ $panitia->nama_tampil }}</h5>
                    <div class="person-role">{{ $panitia->jabatan }}</div>
                    <div class="person-divisi">
                        {{ $panitia->divisi?->nama_divisi ?? 'Pengarah BPH' }}
                    </div>

                    @if ($panitia->isBphInti())
                        <span class="badge-bph">BPH INTI</span>
                    @else
                        <span style="font-size: 6.5pt; color: #9ca3af; margin-top: 3px;">
                            {{ $panitia->tipe_personel === 'ustadz' ? 'Dewan Ustadz' : 'Eksternal' }}
                        </span>
                    @endif
                </div>

                <!-- Footer Kartu -->
                <div class="card-footer">
                    <span>PANITIA RESMI</span>
                    <span class="badge-edisi">{{ $kegiatan->ikhtibar_ke ?? 'IKHTIBAR' }}</span>
                </div>
            </div>
        @empty
            <div style="grid-column: span 3; text-align: center; padding: 40px; color: #9ca3af;">
                Belum ada panitia yang terdaftar untuk dicetak ID Card.
            </div>
        @endforelse
    </div>
</body>
</html>

