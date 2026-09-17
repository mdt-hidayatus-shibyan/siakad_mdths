class UjianOptionItem {
  final int id;
  final String namaUjian;
  final String tipeUjian;
  final String semester;
  final String tanggalMulai;
  final String tanggalSelesai;

  UjianOptionItem({
    required this.id,
    required this.namaUjian,
    required this.tipeUjian,
    required this.semester,
    required this.tanggalMulai,
    required this.tanggalSelesai,
  });

  factory UjianOptionItem.fromJson(Map<String, dynamic> json) {
    return UjianOptionItem(
      id: json['id'] ?? 0,
      namaUjian: json['nama_ujian'] ?? '',
      tipeUjian: json['tipe_ujian'] ?? '',
      semester: json['semester'] ?? '',
      tanggalMulai: json['tanggal_mulai'] ?? '',
      tanggalSelesai: json['tanggal_selesai'] ?? '',
    );
  }
}

class RuanganOptionItem {
  final int id;
  final String namaRuangan;
  final int levelId;
  final String namaLevel;

  RuanganOptionItem({
    required this.id,
    required this.namaRuangan,
    required this.levelId,
    required this.namaLevel,
  });

  factory RuanganOptionItem.fromJson(Map<String, dynamic> json) {
    return RuanganOptionItem(
      id: json['id'] ?? 0,
      namaRuangan: json['nama_ruangan'] ?? '',
      levelId: json['level_id'] ?? 0,
      namaLevel: json['nama_level'] ?? '',
    );
  }
}

class JadwalUjianItem {
  final int id;
  final int? mataPelajaranId;
  final String namaMapel;
  final String kodeMapel;
  final String? tanggalUjian;
  final String? hariTanggal;
  final String? hariTanggalSingkat;
  final String waktuMulai;
  final String waktuSelesai;
  final int? pengawasId;
  final String? pengawasNama;

  JadwalUjianItem({
    required this.id,
    this.mataPelajaranId,
    required this.namaMapel,
    required this.kodeMapel,
    this.tanggalUjian,
    this.hariTanggal,
    this.hariTanggalSingkat,
    required this.waktuMulai,
    required this.waktuSelesai,
    this.pengawasId,
    this.pengawasNama,
  });

  factory JadwalUjianItem.fromJson(Map<String, dynamic> json) {
    return JadwalUjianItem(
      id: json['id'] ?? 0,
      mataPelajaranId: json['mata_pelajaran_id'],
      namaMapel: json['nama_mapel'] ?? '-',
      kodeMapel: json['kode_mapel'] ?? '-',
      tanggalUjian: json['tanggal_ujian'] ?? json['hari_tanggal_singkat'],
      hariTanggal: json['hari_tanggal'],
      hariTanggalSingkat: json['hari_tanggal_singkat'] ?? json['tanggal_ujian'],
      waktuMulai: json['waktu_mulai'] ?? '07:30',
      waktuSelesai: json['waktu_selesai'] ?? '09:00',
      pengawasId: json['pengawas_id'],
      pengawasNama: json['pengawas_nama'],
    );
  }
}

class PengawasUjianData {
  final int? ustadzId;
  final String ustadzNama;
  int? ustadzPenggantiId;
  String? ustadzPenggantiNama;
  String status; // Hadir, Izin, Sakit, Badal
  String? catatanBeritaAcara;

  PengawasUjianData({
    this.ustadzId,
    required this.ustadzNama,
    this.ustadzPenggantiId,
    this.ustadzPenggantiNama,
    required this.status,
    this.catatanBeritaAcara,
  });

  factory PengawasUjianData.fromJson(Map<String, dynamic> json) {
    return PengawasUjianData(
      ustadzId: json['ustadz_id'],
      ustadzNama: json['ustadz_nama'] ?? 'Ustadz Pengawas',
      ustadzPenggantiId: json['ustadz_pengganti_id'],
      ustadzPenggantiNama: json['ustadz_pengganti_nama'],
      status: json['status'] ?? 'Hadir',
      catatanBeritaAcara: json['catatan_berita_acara'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'ustadz_id': ustadzId,
      'ustadz_pengganti_id': ustadzPenggantiId,
      'status': status,
      'catatan_berita_acara': catatanBeritaAcara,
    };
  }
}

class BadalUstadzOption {
  final int id;
  final String nama;
  final String? kode;

  BadalUstadzOption({required this.id, required this.nama, this.kode});

  factory BadalUstadzOption.fromJson(Map<String, dynamic> json) {
    return BadalUstadzOption(
      id: json['id'] ?? 0,
      nama: json['nama'] ?? '',
      kode: json['kode'],
    );
  }
}

class MuridPresensiUjianItem {
  final int muridId;
  final String nama;
  final String nism;
  final String jenisKelamin;
  final bool isLocked;
  final String? lockReason;
  String?
  status; // Hadir, Izin, Sakit, Alpha, Dispensasi, or null if belum diisi
  String? catatan;

  MuridPresensiUjianItem({
    required this.muridId,
    required this.nama,
    required this.nism,
    required this.jenisKelamin,
    required this.isLocked,
    this.lockReason,
    this.status,
    this.catatan,
  });

  factory MuridPresensiUjianItem.fromJson(Map<String, dynamic> json) {
    return MuridPresensiUjianItem(
      muridId: json['murid_id'] ?? 0,
      nama: json['nama'] ?? '',
      nism: json['nism'] ?? '-',
      jenisKelamin: json['jenis_kelamin'] ?? 'L',
      isLocked: json['is_locked'] ?? false,
      lockReason: json['lock_reason'],
      status: json['status'],
      catatan: json['catatan'],
    );
  }

  MuridPresensiUjianItem copyWith({
    String? status,
    bool clearStatus = false,
    String? catatan,
  }) {
    return MuridPresensiUjianItem(
      muridId: muridId,
      nama: nama,
      nism: nism,
      jenisKelamin: jenisKelamin,
      isLocked: isLocked,
      lockReason: lockReason,
      status: clearStatus ? null : (status ?? this.status),
      catatan: catatan ?? this.catatan,
    );
  }
}

class PresensiUjianSummary {
  final int total;
  final int belum;
  final int hadir;
  final int izin;
  final int sakit;
  final int alpha;
  final int dispensasi;

  PresensiUjianSummary({
    required this.total,
    this.belum = 0,
    required this.hadir,
    required this.izin,
    required this.sakit,
    required this.alpha,
    required this.dispensasi,
  });

  factory PresensiUjianSummary.fromJson(Map<String, dynamic> json) {
    return PresensiUjianSummary(
      total: json['total'] ?? 0,
      belum: json['belum'] ?? 0,
      hadir: json['hadir'] ?? 0,
      izin: json['izin'] ?? 0,
      sakit: json['sakit'] ?? 0,
      alpha: json['alpha'] ?? 0,
      dispensasi: json['dispensasi'] ?? 0,
    );
  }
}

class PresensiUjianDataResponse {
  final List<UjianOptionItem> daftarUjian;
  final int? selectedUjianId;
  final List<RuanganOptionItem> daftarRuangan;
  final int? selectedRuanganId;
  final String selectedRuanganNama;
  final String namaLevel;
  final bool isWaliRuangan;
  final String waliRuanganNama;
  final List<JadwalUjianItem> jadwalList;
  final int? selectedJadwalId;
  final PengawasUjianData? pengawas;
  final List<BadalUstadzOption> daftarBadal;
  final List<MuridPresensiUjianItem> muridList;
  final PresensiUjianSummary summary;

  PresensiUjianDataResponse({
    required this.daftarUjian,
    this.selectedUjianId,
    required this.daftarRuangan,
    this.selectedRuanganId,
    this.selectedRuanganNama = '',
    this.namaLevel = '',
    this.isWaliRuangan = false,
    this.waliRuanganNama = '-',
    required this.jadwalList,
    this.selectedJadwalId,
    this.pengawas,
    required this.daftarBadal,
    required this.muridList,
    required this.summary,
  });

  factory PresensiUjianDataResponse.fromJson(Map<String, dynamic> json) {
    return PresensiUjianDataResponse(
      daftarUjian:
          (json['daftar_ujian'] as List<dynamic>?)
              ?.map((e) => UjianOptionItem.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      selectedUjianId: json['selected_ujian_id'],
      daftarRuangan:
          (json['daftar_ruangan'] as List<dynamic>?)
              ?.map(
                (e) => RuanganOptionItem.fromJson(e as Map<String, dynamic>),
              )
              .toList() ??
          [],
      selectedRuanganId: json['selected_ruangan_id'],
      selectedRuanganNama: json['selected_ruangan_nama'] ?? '',
      namaLevel: json['nama_level'] ?? '',
      isWaliRuangan: json['is_wali_ruangan'] ?? false,
      waliRuanganNama: json['wali_ruangan_nama'] ?? '-',
      jadwalList:
          (json['jadwal_list'] as List<dynamic>?)
              ?.map((e) => JadwalUjianItem.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      selectedJadwalId: json['selected_jadwal_id'],
      pengawas: json['pengawas'] != null
          ? PengawasUjianData.fromJson(json['pengawas'] as Map<String, dynamic>)
          : null,
      daftarBadal:
          (json['daftar_badal'] as List<dynamic>?)
              ?.map(
                (e) => BadalUstadzOption.fromJson(e as Map<String, dynamic>),
              )
              .toList() ??
          [],
      muridList:
          (json['murid_list'] as List<dynamic>?)
              ?.map(
                (e) =>
                    MuridPresensiUjianItem.fromJson(e as Map<String, dynamic>),
              )
              .toList() ??
          [],
      summary: json['summary'] != null
          ? PresensiUjianSummary.fromJson(
              json['summary'] as Map<String, dynamic>,
            )
          : PresensiUjianSummary(
              total: 0,
              hadir: 0,
              izin: 0,
              sakit: 0,
              alpha: 0,
              dispensasi: 0,
            ),
    );
  }
}
