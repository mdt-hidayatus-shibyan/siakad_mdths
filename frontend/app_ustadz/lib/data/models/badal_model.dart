class BadalRuanganItem {
  final int id;
  final String namaRuangan;
  final String level;
  final int urutanLevel;
  final String waliKelas;
  final int? waliKelasId;
  final int totalMurid;

  BadalRuanganItem({
    required this.id,
    required this.namaRuangan,
    required this.level,
    this.urutanLevel = 999,
    required this.waliKelas,
    this.waliKelasId,
    required this.totalMurid,
  });

  factory BadalRuanganItem.fromJson(Map<String, dynamic> json) {
    return BadalRuanganItem(
      id: json['id'] ?? 0,
      namaRuangan: json['nama_ruangan'] ?? '',
      level: json['level'] ?? '-',
      urutanLevel: json['urutan_level'] ?? 999,
      waliKelas: json['wali_kelas'] ?? 'Belum Diatur',
      waliKelasId: json['wali_kelas_id'],
      totalMurid: json['total_murid'] ?? 0,
    );
  }
}

class BadalJadwalItem {
  final int jadwalId;
  final String jamKe;
  final String jam;
  final String mapel;
  final String guruPengampu;
  final int? guruUtamaId;
  final bool isPengampuAsli;

  // Status Presensi Ustadz
  final String ustadzStatus;
  final String? ustadzKeterangan;
  final int? ustadzPenggantiId;
  final String? ustadzPenggantiNama;
  final bool isSayaPengganti;

  // Status Presensi Murid
  final bool sudahAbsenMurid;
  final int totalMurid;
  final int totalTerisi;
  final int totalHadir;
  final int totalIzin;
  final int totalSakit;
  final int totalAlpha;
  final int totalDispensasi;

  BadalJadwalItem({
    required this.jadwalId,
    required this.jamKe,
    required this.jam,
    required this.mapel,
    required this.guruPengampu,
    this.guruUtamaId,
    required this.isPengampuAsli,
    required this.ustadzStatus,
    this.ustadzKeterangan,
    this.ustadzPenggantiId,
    this.ustadzPenggantiNama,
    required this.isSayaPengganti,
    required this.sudahAbsenMurid,
    required this.totalMurid,
    required this.totalTerisi,
    required this.totalHadir,
    required this.totalIzin,
    required this.totalSakit,
    required this.totalAlpha,
    required this.totalDispensasi,
  });

  factory BadalJadwalItem.fromJson(Map<String, dynamic> json) {
    return BadalJadwalItem(
      jadwalId: json['jadwal_id'] ?? 0,
      jamKe: json['jam_ke'] ?? '',
      jam: json['jam'] ?? '',
      mapel: json['mapel'] ?? '',
      guruPengampu: json['guru_pengampu'] ?? 'Belum Diatur',
      guruUtamaId: json['guru_utama_id'],
      isPengampuAsli: json['is_pengampu_asli'] ?? false,
      ustadzStatus: json['ustadz_status'] ?? 'Belum Absen',
      ustadzKeterangan: json['ustadz_keterangan'],
      ustadzPenggantiId: json['ustadz_pengganti_id'],
      ustadzPenggantiNama: json['ustadz_pengganti_nama'],
      isSayaPengganti: json['is_saya_pengganti'] ?? false,
      sudahAbsenMurid: json['sudah_absen_murid'] ?? false,
      totalMurid: json['total_murid'] ?? 0,
      totalTerisi: json['total_terisi'] ?? 0,
      totalHadir: json['total_hadir'] ?? 0,
      totalIzin: json['total_izin'] ?? 0,
      totalSakit: json['total_sakit'] ?? 0,
      totalAlpha: json['total_alpha'] ?? 0,
      totalDispensasi: json['total_dispensasi'] ?? 0,
    );
  }
}

class BadalJadwalResponse {
  final BadalRuanganItem? ruangan;
  final String tanggal;
  final String hari;
  final bool isLibur;
  final String? keteranganLibur;
  final bool isUjian;
  final String? namaUjian;
  final int? ujianId;
  final List<BadalJadwalItem> data;

  BadalJadwalResponse({
    this.ruangan,
    required this.tanggal,
    required this.hari,
    required this.isLibur,
    this.keteranganLibur,
    required this.isUjian,
    this.namaUjian,
    this.ujianId,
    required this.data,
  });

  factory BadalJadwalResponse.fromJson(Map<String, dynamic> json) {
    final rawList = json['data'] as List? ?? [];
    return BadalJadwalResponse(
      ruangan: json['ruangan'] != null
          ? BadalRuanganItem.fromJson(json['ruangan'])
          : null,
      tanggal: json['tanggal'] ?? '',
      hari: json['hari'] ?? '',
      isLibur: json['is_libur'] ?? false,
      keteranganLibur: json['keterangan_libur'],
      isUjian: json['is_ujian'] ?? false,
      namaUjian: json['nama_ujian'],
      ujianId: json['ujian_id'],
      data: rawList.map((e) => BadalJadwalItem.fromJson(e)).toList(),
    );
  }
}
