class DashboardModel {
  final bool isLiburHariIni;
  final String? keteranganLiburHariIni;
  final int totalJadwalMingguan;
  final int jadwalHariIni;
  final int presensiSelesaiHariIni;
  final int totalMuridWali;
  final List<JadwalHariIniItem> jadwalHariIniList;
  final List<PengumumanItem> pengumumanList;

  DashboardModel({
    this.isLiburHariIni = false,
    this.keteranganLiburHariIni,
    required this.totalJadwalMingguan,
    required this.jadwalHariIni,
    required this.presensiSelesaiHariIni,
    required this.totalMuridWali,
    required this.jadwalHariIniList,
    required this.pengumumanList,
  });

  factory DashboardModel.fromJson(Map<String, dynamic> json) {
    final stats = json['statistik'] ?? {};
    final rawJadwal = json['jadwal_hari_ini'] as List? ?? [];
    final rawPengumuman = json['pengumuman'] as List? ?? [];

    return DashboardModel(
      isLiburHariIni: json['is_libur_hari_ini'] ?? false,
      keteranganLiburHariIni: json['keterangan_libur_hari_ini'],
      totalJadwalMingguan: stats['total_jadwal_mingguan'] ?? 0,
      jadwalHariIni: stats['jadwal_hari_ini'] ?? 0,
      presensiSelesaiHariIni: stats['presensi_selesai_hari_ini'] ?? 0,
      totalMuridWali: stats['total_murid_wali'] ?? 0,
      jadwalHariIniList: rawJadwal
          .map((e) => JadwalHariIniItem.fromJson(e))
          .toList(),
      pengumumanList: rawPengumuman
          .map((e) => PengumumanItem.fromJson(e))
          .toList(),
    );
  }
}

class JadwalHariIniItem {
  final int id;
  final String jamKe;
  final String jam;
  final String mapel;
  final String kelas;
  final String? guru;
  final bool isUtama;
  final String peran;
  final bool isTeamTeaching;
  final bool sudahAbsen;

  JadwalHariIniItem({
    required this.id,
    required this.jamKe,
    required this.jam,
    required this.mapel,
    required this.kelas,
    this.guru,
    this.isUtama = true,
    this.peran = 'Guru Utama',
    this.isTeamTeaching = false,
    required this.sudahAbsen,
  });

  factory JadwalHariIniItem.fromJson(Map<String, dynamic> json) {
    return JadwalHariIniItem(
      id: json['id'] ?? 0,
      jamKe: json['jam_ke']?.toString() ?? '',
      jam: json['jam'] ?? '',
      mapel: json['mapel'] ?? '',
      kelas: json['kelas'] ?? '',
      guru: json['guru'],
      isUtama: json['is_utama'] ?? true,
      peran:
          json['peran'] ??
          (json['is_utama'] == false ? 'Guru Pendamping' : 'Guru Utama'),
      isTeamTeaching: json['is_team_teaching'] ?? false,
      sudahAbsen: json['sudah_absen'] ?? false,
    );
  }
}

class PengumumanItem {
  final int id;
  final String judul;
  final String konten;
  final String? kontenHtml;
  final String tipe;
  final String status;
  final String targetAudience;
  final String penulis;
  final String? periodeBerlaku;
  final String tanggalMulai;
  final String? createdAtFormat;
  final String? lampiranPdfUrl;
  final String? namaFilePdf;

  PengumumanItem({
    required this.id,
    required this.judul,
    required this.konten,
    this.kontenHtml,
    required this.tipe,
    this.status = 'Terbit',
    this.targetAudience = 'Semua',
    this.penulis = 'Administrator',
    this.periodeBerlaku,
    required this.tanggalMulai,
    this.createdAtFormat,
    this.lampiranPdfUrl,
    this.namaFilePdf,
  });

  factory PengumumanItem.fromJson(Map<String, dynamic> json) {
    return PengumumanItem(
      id: json['id'] ?? 0,
      judul: json['judul'] ?? json['nama_pengumuman'] ?? '',
      konten: json['konten'] ?? json['isi'] ?? '',
      kontenHtml: json['konten_html'],
      tipe: json['tipe'] ?? json['kategori'] ?? 'Informasi',
      status: json['status'] ?? 'Terbit',
      targetAudience: json['target_audience'] ?? 'Semua',
      penulis: json['penulis'] ?? 'Administrator',
      periodeBerlaku: json['periode_berlaku'],
      tanggalMulai: json['tanggal_mulai'] ?? json['created_at'] ?? '',
      createdAtFormat: json['created_at_format'],
      lampiranPdfUrl: json['lampiran_pdf_url'],
      namaFilePdf: json['nama_file_pdf'],
    );
  }
}

class KalendarPendidikanItem {
  final int id;
  final String namaKegiatan;
  final String tanggalMulai;
  final String? tanggalSelesai;
  final String? keterangan;
  final String? tipe;

  KalendarPendidikanItem({
    required this.id,
    required this.namaKegiatan,
    required this.tanggalMulai,
    this.tanggalSelesai,
    this.keterangan,
    this.tipe,
  });

  factory KalendarPendidikanItem.fromJson(Map<String, dynamic> json) {
    return KalendarPendidikanItem(
      id: json['id'] ?? 0,
      namaKegiatan:
          json['nama_kegiatan'] ?? json['kegiatan'] ?? json['judul'] ?? '',
      tanggalMulai: json['tanggal_mulai'] ?? '',
      tanggalSelesai: json['tanggal_selesai'],
      keterangan: json['keterangan'],
      tipe: json['tipe'] ?? json['kategori'] ?? 'Akademik',
    );
  }
}
