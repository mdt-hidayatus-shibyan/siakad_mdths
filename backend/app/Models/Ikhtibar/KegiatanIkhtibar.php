<?php

namespace App\Models\Ikhtibar;

use App\Models\PengaturanTagihan;
use App\Models\TahunPelajaran;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class KegiatanIkhtibar extends Model
{
    use HasFactory;

    protected $table = 'kegiatan_ikhtibars';

    protected $fillable = [
        'tahun_pelajaran_id',
        'pengaturan_tagihan_id',
        'kalendar_pendidikan_id',
        'logo_kegiatan',
        'kop_logo_ikhtibar',
        'nama_kegiatan',
        'ikhtibar_ke',
        'tema_kegiatan',
        'tanggal_mulai',
        'tanggal_selesai',
        'tanggal_puncak_acara',
        'tanggal_hijriyah',
        'lokasi_kegiatan',
        'stempel_kegiatan',
        'no_sk_panitia',
        'tanggal_penetapan_sk',
        'status_kegiatan',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_mulai'        => 'date',
        'tanggal_selesai'      => 'date',
        'tanggal_puncak_acara' => 'date',
        'tanggal_penetapan_sk' => 'date',
    ];

    /**
     * Relasi ke Tahun Pelajaran
     */
    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    /**
     * Relasi ke Pengaturan Tagihan (Sasaran: wali_murid / per KK)
     */
    public function pengaturanTagihan()
    {
        return $this->belongsTo(PengaturanTagihan::class, 'pengaturan_tagihan_id');
    }

    /**
     * Relasi ke Kalendar Pendidikan
     */
    public function kalendarPendidikan()
    {
        return $this->belongsTo(\App\Models\KalendarPendidikan::class, 'kalendar_pendidikan_id');
    }

    /**
     * Relasi ke Divisi-Divisi Ikhtibar
     */
    public function divisis()
    {
        return $this->hasMany(DivisiIkhtibar::class, 'kegiatan_ikhtibar_id')->orderBy('urutan');
    }

    /**
     * Relasi ke Seluruh Personel Panitia Ikhtibar
     */
    public function panitias()
    {
        return $this->hasMany(PanitiaIkhtibar::class, 'kegiatan_ikhtibar_id');
    }

    /**
     * Relasi ke Seluruh Transaksi Penyaluran / Penarikan Sisa Pagu
     */
    public function penyaluranDanas()
    {
        return $this->hasMany(PenyaluranDanaIkhtibar::class, 'kegiatan_ikhtibar_id');
    }

    /**
     * Relasi ke Seluruh Pengeluaran SPJ
     */
    public function pengeluarans()
    {
        return $this->hasMany(PengeluaranIkhtibar::class, 'kegiatan_ikhtibar_id');
    }

    /**
     * Scope Kegiatan Aktif (mengikuti tahun pelajaran aktif)
     */
    public function scopeAktif($query)
    {
        return $query->whereHas('tahunPelajaran', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Accessor: Judul Lengkap Ikhtibar beserta Edisi (Contoh: "Ikhtibar & Haflah Akhirussanah Ke-52 Th")
     */
    public function getJudulLengkapAttribute(): string
    {
        return $this->ikhtibar_ke
            ? "{$this->nama_kegiatan} Ke-{$this->ikhtibar_ke}"
            : ($this->nama_kegiatan ?? 'Ikhtibar & Haflah Akhirussanah');
    }

    /**
     * Accessor: URL Logo Ikhtibar Pintar (Fallback ke logo madrasah jika belum diunggah)
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo_kegiatan && Storage::disk('public')->exists($this->logo_kegiatan)) {
            return asset('storage/' . $this->logo_kegiatan);
        }
        return asset('assets/images/logo.png');
    }

    /**
     * Accessor: URL Kop Logo Ikhtibar untuk Kop Surat & Kwitansi Resmi
     */
    public function getKopLogoUrlAttribute(): string
    {
        if ($this->kop_logo_ikhtibar && Storage::disk('public')->exists($this->kop_logo_ikhtibar)) {
            return asset('storage/' . $this->kop_logo_ikhtibar);
        }
        if ($this->logo_kegiatan && Storage::disk('public')->exists($this->logo_kegiatan)) {
            return asset('storage/' . $this->logo_kegiatan);
        }
        return asset(getSetting('kop_logo', getSetting('app_logo', 'assets/LOGO MDT.png')));
    }

    /**
     * Accessor: URL Stempel Panitia
     */
    public function getStempelUrlAttribute(): ?string
    {
        if ($this->stempel_kegiatan && Storage::disk('public')->exists($this->stempel_kegiatan)) {
            return asset('storage/' . $this->stempel_kegiatan);
        }
        return null;
    }

    /**
     * Sinkronkan tanggal mulai dan tanggal selesai ikhtibar ke Kalendar Pendidikan
     */
    public function syncToKalendarPendidikan()
    {
        if (!$this->tanggal_mulai || !$this->tanggal_selesai || !$this->tahun_pelajaran_id) {
            if ($this->kalendar_pendidikan_id) {
                \App\Models\KalendarPendidikan::where('id', $this->kalendar_pendidikan_id)->delete();
                $this->updateQuietly(['kalendar_pendidikan_id' => null]);
            }
            return null;
        }

        $kategori = \App\Models\KategoriKegiatan::firstOrCreate(
            ['nama_kategori' => 'Ikhtibar'],
            ['kode_warna' => '#0284c7']
        );

        $namaKegiatan = $this->nama_kegiatan;
        if ($this->ikhtibar_ke && !str_contains($namaKegiatan, $this->ikhtibar_ke)) {
            $namaKegiatan .= ' (' . $this->ikhtibar_ke . ')';
        }

        $kalendar = null;
        if ($this->kalendar_pendidikan_id) {
            $kalendar = \App\Models\KalendarPendidikan::find($this->kalendar_pendidikan_id);
        }

        if (!$kalendar) {
            $kalendar = \App\Models\KalendarPendidikan::where('tahun_pelajaran_id', $this->tahun_pelajaran_id)
                ->where(function ($q) {
                    $q->where('nama_kegiatan', 'like', '%ikhtibar%')
                        ->orWhere('keterangan', 'like', '%ikhtibar%');
                })
                ->first();
        }

        $data = [
            'tahun_pelajaran_id'   => $this->tahun_pelajaran_id,
            'kategori_kegiatan_id' => $kategori->id,
            'nama_kegiatan'        => $namaKegiatan,
            'tipe_presensi'        => 'tidak_ada',
            'sesi_kegiatan'        => null,
            'keterangan'           => 'Kegiatan Ikhtibar & Haflah Akhirussanah' . ($this->ikhtibar_ke ? ' (' . $this->ikhtibar_ke . ')' : ''),
            'tanggal_mulai'        => $this->tanggal_mulai,
            'tanggal_selesai'      => $this->tanggal_selesai,
        ];

        if ($kalendar) {
            $kalendar->update($data);
        } else {
            $kalendar = \App\Models\KalendarPendidikan::create($data);
        }

        if ($this->kalendar_pendidikan_id !== $kalendar->id) {
            $this->updateQuietly(['kalendar_pendidikan_id' => $kalendar->id]);
        }

        return $kalendar;
    }

    /**
     * Model Booted Hook untuk otomatisasi sinkronisasi ke Kalendar Pendidikan
     */
    protected static function booted()
    {
        static::saved(function ($model) {
            if ($model->wasChanged(['tanggal_mulai', 'tanggal_selesai', 'nama_kegiatan', 'ikhtibar_ke', 'tahun_pelajaran_id'])) {
                $model->syncToKalendarPendidikan();
            }
        });

        static::deleted(function ($model) {
            if ($model->kalendar_pendidikan_id) {
                \App\Models\KalendarPendidikan::where('id', $model->kalendar_pendidikan_id)->delete();
            }
        });
    }
}

