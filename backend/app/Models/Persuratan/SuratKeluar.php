<?php

namespace App\Models\Persuratan;

use App\Models\Murid;
use App\Models\TahunPelajaran;
use App\Models\User;
use App\Models\Ustadz;
use App\Models\WaliMurid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SuratKeluar extends Model
{
    use HasFactory;

    protected $table = 'surat_keluars';

    protected $guarded = ['id'];

    protected $casts = [
        'murid_ids'           => 'array',
        'isi_spesifik'        => 'array',
        'penandatangan_list'  => 'array',
        'tanggal_surat'       => 'date',
    ];

    /**
     * Daftar 7 Jenis & Template Surat Resmi
     */
    public const DAFTAR_JENIS_SURAT = [
        'surat_panggilan' => [
            'nama'        => 'Surat Panggilan',
            'singkatan'   => 'Surat Panggilan Murid/Wali',
            'kode'        => 'SPG',
            'target'      => 'Murid / Wali Murid',
            'deskripsi'   => 'Pemanggilan murid atau orang tua/wali ke madrasah terkait disiplin, KBM, atau pembinaan.',
            'icon'        => 'bi-envelope-exclamation-fill',
            'bg_color'    => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
            'badge_color' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200 dark:border-amber-800/60',
        ],
        'surat_peringatan' => [
            'nama'        => 'Surat Peringatan (SP)',
            'singkatan'   => 'Surat Peringatan Murid',
            'kode'        => 'SP',
            'target'      => 'Murid',
            'deskripsi'   => 'Peringatan resmi (SP 1, SP 2, SP 3 / Keras) atas pelanggaran tata tertib madrasah.',
            'icon'        => 'bi-exclamation-octagon-fill',
            'bg_color'    => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
            'badge_color' => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-200 dark:border-rose-800/60',
        ],
        'surat_pemberitahuan' => [
            'nama'        => 'Surat Pemberitahuan',
            'singkatan'   => 'Pemberitahuan Wali Murid',
            'kode'        => 'SPB',
            'target'      => 'Wali Murid',
            'deskripsi'   => 'Penyampaian informasi resmi, jadwal akademik, iuran/syahriah, atau kegiatan kepada orang tua.',
            'icon'        => 'bi-info-circle-fill',
            'bg_color'    => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
            'badge_color' => 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border-blue-200 dark:border-blue-800/60',
        ],
        'surat_edaran' => [
            'nama'        => 'Surat Edaran',
            'singkatan'   => 'Edaran Resmi Madrasah',
            'kode'        => 'SE',
            'target'      => 'Warga Madrasah / Umum',
            'deskripsi'   => 'Maklumat umum bagi seluruh asatidz, murid, dan masyarakat terkait kebijakan atau libur madrasah.',
            'icon'        => 'bi-megaphone-fill',
            'bg_color'    => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20',
            'badge_color' => 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200 dark:border-purple-800/60',
        ],
        'surat_permohonan_izin' => [
            'nama'        => 'Surat Permohonan Izin',
            'singkatan'   => 'Permohonan Izin / Tempat',
            'kode'        => 'SI',
            'target'      => 'Instansi / Lembaga Luar',
            'deskripsi'   => 'Permohonan izin peminjaman tempat, fasilitas, rute kegiatan luar, atau audiensi resmi.',
            'icon'        => 'bi-file-earmark-check-fill',
            'bg_color'    => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
            'badge_color' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800/60',
        ],
        'surat_dispensasi' => [
            'nama'        => 'Surat Dispensasi Antar Lembaga',
            'singkatan'   => 'Dispensasi Sekolah / Luar',
            'kode'        => 'DSP',
            'target'      => 'Sekolah Formal / Lembaga',
            'deskripsi'   => 'Permohonan dispensasi kehadiran bagi murid/asatidz yang mengikuti kegiatan resmi madrasah.',
            'icon'        => 'bi-shield-check',
            'bg_color'    => 'bg-teal-500/10 text-teal-600 dark:text-teal-400 border-teal-500/20',
            'badge_color' => 'bg-teal-100 text-teal-700 dark:bg-teal-950/60 dark:text-teal-300 border-teal-200 dark:border-teal-800/60',
        ],
        'surat_undangan' => [
            'nama'        => 'Surat Undangan',
            'singkatan'   => 'Undangan Acara / Rapat',
            'kode'        => 'UND',
            'target'      => 'Wali / Ustadz / Tokoh / Mitra',
            'deskripsi'   => 'Undangan rapat dinas guru, musyawarah wali murid, wisuda/imtihan, dan PHBI madrasah.',
            'icon'        => 'bi-envelope-paper-heart-fill',
            'bg_color'    => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20',
            'badge_color' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800/60',
        ],
    ];

    /**
     * Konversi angka bulan ke format Romawi
     */
    public static function getBulanRomawi(int $month): string
    {
        $romawi = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];
        return $romawi[$month] ?? 'I';
    }

    /**
     * Generate Nomor Surat Resmi Otomatis Berurutan
     * Format: {Urut 3 Digit}/{Kode}/MDT-HS/{Bulan Romawi}/{Tahun}
     * Contoh: 004/SPG/MDT-HS/IX/2026
     */
    public static function generateNomorSurat(string $jenisSurat, ?string $tanggal = null): array
    {
        $date = $tanggal ? Carbon::parse($tanggal) : Carbon::now();
        $year = $date->year;
        $monthRomawi = self::getBulanRomawi($date->month);

        $kodeJenis = self::DAFTAR_JENIS_SURAT[$jenisSurat]['kode'] ?? 'SRT';

        // Hitung nomor agenda urut dalam tahun yang sama
        $lastSurat = self::whereYear('tanggal_surat', $year)
            ->orderBy('nomor_agenda', 'desc')
            ->first();

        $nextAgenda = ($lastSurat && $lastSurat->nomor_agenda) ? ((int) $lastSurat->nomor_agenda + 1) : 1;
        $nomorUrutFormatted = str_pad($nextAgenda, 3, '0', STR_PAD_LEFT);

        $nomorSurat = "{$nomorUrutFormatted}/{$kodeJenis}/MDT-HS/{$monthRomawi}/{$year}";

        return [
            'nomor_surat'  => $nomorSurat,
            'nomor_agenda' => $nextAgenda,
        ];
    }

    /**
     * Generate token verifikasi QR unik
     */
    public static function generateQrToken(): string
    {
        return 'MDTHS-' . strtoupper(Str::random(12)) . '-' . date('Ymd');
    }

    // ==========================================
    // RELASI ELOQUENT
    // ==========================================

    public function waliMurid()
    {
        return $this->belongsTo(WaliMurid::class, 'wali_murid_id');
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==========================================
    // ACCESSOR & HELPER
    // ==========================================

    /**
     * Dapatkan model Murid pertama (untuk kompatibilitas tampilan tunggal)
     */
    public function getMuridAttribute(): ?Murid
    {
        if (empty($this->murid_ids) || !is_array($this->murid_ids)) {
            return null;
        }
        $firstId = $this->murid_ids[0] ?? null;
        if (!$firstId) {
            return null;
        }
        return Murid::with(['waliMurid.kampung', 'ruangans', 'ruanganMasuk'])->find($firstId);
    }

    /**
     * Dapatkan Collection seluruh murid terkait
     */
    public function getMuridsAttribute()
    {
        if (empty($this->murid_ids) || !is_array($this->murid_ids)) {
            return collect();
        }
        return Murid::with(['waliMurid.kampung', 'ruangans', 'ruanganMasuk'])
            ->whereIn('id', $this->murid_ids)
            ->get();
    }

    public function getMetaJenisAttribute(): array
    {
        return self::DAFTAR_JENIS_SURAT[$this->jenis_surat] ?? [
            'nama'        => 'Surat Resmi',
            'singkatan'   => 'Surat',
            'kode'        => 'SRT',
            'target'      => 'Umum',
            'deskripsi'   => 'Surat keluar resmi madrasah.',
            'icon'        => 'bi-file-earmark-text-fill',
            'bg_color'    => 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20',
            'badge_color' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700',
        ];
    }

    public function getNamaJenisAttribute(): string
    {
        return $this->meta_jenis['nama'] ?? 'Surat Keluar';
    }

    public function getBadgeStatusAttribute(): array
    {
        return match ($this->status) {
            'draft' => [
                'label' => 'Konsep (Draft)',
                'class' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border-zinc-300/80 dark:border-zinc-700',
                'icon'  => 'bi-pencil-square',
            ],
            'arsip' => [
                'label' => 'Diarsipkan',
                'class' => 'bg-slate-100 text-slate-700 dark:bg-slate-900/60 dark:text-slate-300 border-slate-300/80 dark:border-slate-800',
                'icon'  => 'bi-archive-fill',
            ],
            default => [
                'label' => 'Telah Terbit',
                'class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-300/80 dark:border-emerald-800',
                'icon'  => 'bi-check2-circle',
            ],
        };
    }

    public function getPenandatanganAktifAttribute(): array
    {
        if (is_array($this->penandatangan_list) && count($this->penandatangan_list) > 0) {
            $filtered = array_values(array_filter($this->penandatangan_list, function ($item) {
                return !isset($item['is_active']) || $item['is_active'] === true || $item['is_active'] === '1' || $item['is_active'] === 1 || $item['is_active'] === 'true';
            }));

            if (count($filtered) > 0) {
                return $filtered;
            }
        }

        // Fallback untuk surat lama / data tunggal
        return [
            [
                'key'         => 'pejabat_utama',
                'is_active'   => true,
                'label_atas'  => null,
                'jabatan'     => $this->penandatangan_jabatan ?: 'Pengasuh',
                'nama'        => $this->penandatangan_nama ?: '-',
                'nip'         => $this->penandatangan_nip ?: '-',
                'tipe_relasi' => 'administrator',
                'id_relasi'   => null,
            ]
        ];
    }

    /**
     * Dapatkan daftar murid dispensasi kolektif / massal
     */
    public function getMuridDispensasiListAttribute(): array
    {
        $isi = $this->isi_spesifik ?? [];
        if (is_array($isi['murid_dispensasi_list'] ?? null) && count($isi['murid_dispensasi_list']) > 0) {
            return $isi['murid_dispensasi_list'];
        }

        // Fallback jika hanya murid_ids yang tersimpan
        if (!empty($this->murid_ids) && is_array($this->murid_ids)) {
            $murids = Murid::with(['ruangans', 'ruanganMasuk'])->whereIn('id', $this->murid_ids)->get();
            return $murids->map(function ($m) {
                return [
                    'murid_id'      => $m->id,
                    'nama_lengkap'  => $m->nama_lengkap,
                    'nism'          => $m->nism,
                    'ruangan'       => $m->nama_ruangan_aktif ?? '-',
                    'kelas_lembaga' => '-',
                ];
            })->toArray();
        }

        return [];
    }

    /**
     * Cek apakah ini surat dispensasi massal (banyak murid)
     */
    public function getIsDispensasiMassalAttribute(): bool
    {
        return $this->jenis_surat === 'surat_dispensasi' && (count($this->murid_dispensasi_list) > 1 || (is_array($this->murid_ids) && count($this->murid_ids) > 1));
    }

    /**
     * Cek apakah surat memiliki lembar lampiran resmi
     */
    public function getHasLampiranAttribute(): bool
    {
        if ($this->is_dispensasi_massal) {
            return true;
        }
        $isi = $this->isi_spesifik ?? [];
        if (!empty($isi['has_lampiran']) && ($isi['has_lampiran'] === true || $isi['has_lampiran'] === '1' || $isi['has_lampiran'] === 1 || $isi['has_lampiran'] === 'true')) {
            return true;
        }
        if (!empty($isi['lampiran_konten']) && trim((string)$isi['lampiran_konten']) !== '') {
            return true;
        }
        return false;
    }

    /**
     * Dapatkan judul lembar lampiran
     */
    public function getLampiranJudulAttribute(): string
    {
        $isi = $this->isi_spesifik ?? [];
        if ($this->is_dispensasi_massal || $this->jenis_surat === 'surat_dispensasi') {
            $namaKeg = !empty($isi['nama_kegiatan_dispensasi']) ? strtoupper(trim((string)$isi['nama_kegiatan_dispensasi'])) : 'KEGIATAN';
            $namaKeg = html_entity_decode($namaKeg, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $judul = $isi['lampiran_judul'] ?? '';
            if (empty($judul) || in_array($judul, ['SUSUNAN ACARA & JADWAL KEGIATAN', 'RINCIAN LAMPIRAN SURAT', 'Surat Keluar', 'KETENTUAN & TATA TERTIB RESMI'])) {
                return "NAMA-NAMA MURID YANG MENGIKUTI {$namaKeg}";
            }
            return html_entity_decode((string)$judul, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        $judul = !empty($isi['lampiran_judul']) ? (string)$isi['lampiran_judul'] : 'RINCIAN LAMPIRAN SURAT';
        return html_entity_decode($judul, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Dapatkan teks isi lembar lampiran
     */
    public function getLampiranKontenAttribute(): ?string
    {
        $isi = $this->isi_spesifik ?? [];
        return $isi['lampiran_konten'] ?? null;
    }

    /**
     * Total halaman dokumen (1 jika tanpa lampiran, 2 jika ada lampiran)
     */
    public function getTotalHalamanAttribute(): int
    {
        return $this->has_lampiran ? 2 : 1;
    }
}
