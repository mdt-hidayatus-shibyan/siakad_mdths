// Model untuk Kalender Pendidikan, Referensi Pelanggaran, Mata Pelajaran, dan Jadwal Pelajaran
import 'presensi_ujian_model.dart';

class KalendarEvent {
  final String id;
  final String title;
  final String start;
  final String end;
  final String kategori;
  final String tipe;
  final String hexColor;

  KalendarEvent({
    required this.id,
    required this.title,
    required this.start,
    required this.end,
    required this.kategori,
    required this.tipe,
    required this.hexColor,
  });

  factory KalendarEvent.fromJson(Map<String, dynamic> json) {
    return KalendarEvent(
      id: json['id']?.toString() ?? '',
      title: json['title'] ?? '',
      start: json['start'] ?? '',
      end: json['end'] ?? json['start'] ?? '',
      kategori: json['kategori'] ?? 'Kegiatan',
      tipe: json['tipe'] ?? 'kegiatan',
      hexColor: json['hex_color'] ?? '#10b981',
    );
  }
}

class BulanHijriyahItem {
  final int id;
  final String namaBulan;
  final String tahunHijriyah;
  final int urutan;
  final String tanggalMulai;
  final String tanggalSelesai;
  final bool isActive;

  BulanHijriyahItem({
    required this.id,
    required this.namaBulan,
    required this.tahunHijriyah,
    required this.urutan,
    required this.tanggalMulai,
    required this.tanggalSelesai,
    required this.isActive,
  });

  factory BulanHijriyahItem.fromJson(Map<String, dynamic> json) {
    return BulanHijriyahItem(
      id: json['id'] ?? 0,
      namaBulan: json['nama_bulan'] ?? '',
      tahunHijriyah: json['tahun_hijriyah']?.toString() ?? '',
      urutan: json['urutan'] ?? 0,
      tanggalMulai: json['tanggal_mulai'] ?? '',
      tanggalSelesai: json['tanggal_selesai'] ?? '',
      isActive: json['is_active'] ?? false,
    );
  }
}

class TahunPelajaranItem {
  final int id;
  final String namaHijriyah;
  final String namaMasehi;
  final bool isActive;

  TahunPelajaranItem({
    required this.id,
    required this.namaHijriyah,
    required this.namaMasehi,
    required this.isActive,
  });

  String get label => '$namaHijriyah H / $namaMasehi M';

  factory TahunPelajaranItem.fromJson(Map<String, dynamic> json) {
    return TahunPelajaranItem(
      id: json['id'] ?? 0,
      namaHijriyah: json['nama_hijriyah'] ?? '',
      namaMasehi: json['nama_masehi'] ?? '',
      isActive: (json['is_active'] == 1 || json['is_active'] == true),
    );
  }
}

class KalendarPendidikanResponse {
  final int tahunAktifId;
  final List<TahunPelajaranItem> daftarTahun;
  final List<BulanHijriyahItem> bulanHijriyah;
  final List<KalendarEvent> events;

  KalendarPendidikanResponse({
    required this.tahunAktifId,
    required this.daftarTahun,
    required this.bulanHijriyah,
    required this.events,
  });

  factory KalendarPendidikanResponse.fromJson(Map<String, dynamic> json) {
    final rawTahun = json['daftar_tahun'] as List? ?? [];
    final rawHijri = json['bulan_hijriyah'] as List? ?? [];
    final rawEvents = json['events'] as List? ?? [];

    return KalendarPendidikanResponse(
      tahunAktifId: json['tahun_aktif_id'] ?? 0,
      daftarTahun: rawTahun.map((e) => TahunPelajaranItem.fromJson(e)).toList(),
      bulanHijriyah: rawHijri
          .map((e) => BulanHijriyahItem.fromJson(e))
          .toList(),
      events: rawEvents.map((e) => KalendarEvent.fromJson(e)).toList(),
    );
  }
}

class ReferensiPelanggaranItem {
  final int id;
  final String namaPelanggaran;
  final String kategori;
  final double poin;

  ReferensiPelanggaranItem({
    required this.id,
    required this.namaPelanggaran,
    required this.kategori,
    required this.poin,
  });

  String get poinFormatted {
    final rounded = double.parse(poin.toStringAsFixed(2));
    if (rounded % 1 == 0) return rounded.toInt().toString();
    String str = rounded.toStringAsFixed(2);
    while (str.endsWith('0')) {
      str = str.substring(0, str.length - 1);
    }
    if (str.endsWith('.')) {
      str = str.substring(0, str.length - 1);
    }
    return str;
  }

  factory ReferensiPelanggaranItem.fromJson(Map<String, dynamic> json) {
    return ReferensiPelanggaranItem(
      id: json['id'] ?? 0,
      namaPelanggaran: json['nama_pelanggaran'] ?? '',
      kategori: json['kategori'] ?? 'Ringan',
      poin: (json['poin'] is num)
          ? (json['poin'] as num).toDouble()
          : double.tryParse(json['poin']?.toString() ?? '0') ?? 0.0,
    );
  }
}

class PelanggaranSummary {
  final int total;
  final int ringan;
  final int sedang;
  final int berat;

  PelanggaranSummary({
    required this.total,
    required this.ringan,
    required this.sedang,
    required this.berat,
  });

  factory PelanggaranSummary.fromJson(Map<String, dynamic> json) {
    return PelanggaranSummary(
      total: json['total'] ?? 0,
      ringan: json['ringan'] ?? 0,
      sedang: json['sedang'] ?? 0,
      berat: json['berat'] ?? 0,
    );
  }
}

class ReferensiPelanggaranResponse {
  final PelanggaranSummary summary;
  final List<ReferensiPelanggaranItem> list;

  ReferensiPelanggaranResponse({required this.summary, required this.list});

  factory ReferensiPelanggaranResponse.fromJson(Map<String, dynamic> json) {
    final rawList = json['list'] as List? ?? [];
    return ReferensiPelanggaranResponse(
      summary: PelanggaranSummary.fromJson(json['summary'] ?? {}),
      list: rawList.map((e) => ReferensiPelanggaranItem.fromJson(e)).toList(),
    );
  }
}

class LevelItem {
  final int id;
  final String namaLevel;

  LevelItem({required this.id, required this.namaLevel});

  factory LevelItem.fromJson(Map<String, dynamic> json) {
    return LevelItem(id: json['id'] ?? 0, namaLevel: json['nama_level'] ?? '');
  }
}

class MataPelajaranItem {
  final int id;
  final int levelId;
  final String levelNama;
  final String kodeMapel;
  final String namaMapel;
  final String kelompok;
  final String? referensi;
  final String? pengarang;
  final String? penerbit;

  MataPelajaranItem({
    required this.id,
    required this.levelId,
    required this.levelNama,
    required this.kodeMapel,
    required this.namaMapel,
    required this.kelompok,
    this.referensi,
    this.pengarang,
    this.penerbit,
  });

  factory MataPelajaranItem.fromJson(Map<String, dynamic> json) {
    return MataPelajaranItem(
      id: json['id'] ?? 0,
      levelId: json['level_id'] ?? 0,
      levelNama: json['level_nama'] ?? '-',
      kodeMapel: json['kode_mapel'] ?? '',
      namaMapel: json['nama_mapel'] ?? '',
      kelompok: json['kelompok'] ?? 'Wajib',
      referensi: json['referensi'],
      pengarang: json['pengarang'],
      penerbit: json['penerbit'],
    );
  }
}

class MataPelajaranResponse {
  final List<LevelItem> levels;
  final List<MataPelajaranItem> mataPelajaran;

  MataPelajaranResponse({required this.levels, required this.mataPelajaran});

  factory MataPelajaranResponse.fromJson(Map<String, dynamic> json) {
    final rawLevels = json['levels'] as List? ?? [];
    final rawMapels = json['mata_pelajaran'] as List? ?? [];

    return MataPelajaranResponse(
      levels: rawLevels.map((e) => LevelItem.fromJson(e)).toList(),
      mataPelajaran: rawMapels
          .map((e) => MataPelajaranItem.fromJson(e))
          .toList(),
    );
  }
}

class UstadzPengampuItem {
  final int id;
  final String nama;
  final String? kode;
  final String? foto;
  final bool isUtama;
  final int urutan;

  UstadzPengampuItem({
    required this.id,
    required this.nama,
    this.kode,
    this.foto,
    this.isUtama = false,
    this.urutan = 1,
  });

  factory UstadzPengampuItem.fromJson(Map<String, dynamic> json) {
    return UstadzPengampuItem(
      id: json['id'] ?? 0,
      nama: json['nama'] ?? '',
      kode: json['kode'],
      foto: json['foto'],
      isUtama: json['is_utama'] == true || json['is_utama'] == 1,
      urutan: json['urutan'] ?? 1,
    );
  }
}

class SesiJadwalItem {
  final int id;
  final String jamKe;
  final String jam;
  final String mapel;
  final String? ustadz;
  final String? kodeUstadz;
  final String? ustadzFoto;
  final List<UstadzPengampuItem> daftarUstadz;
  final String ruangan;
  final String? namaGedung;
  final String? namaKamar;
  final String level;

  SesiJadwalItem({
    required this.id,
    required this.jamKe,
    required this.jam,
    required this.mapel,
    this.ustadz,
    this.kodeUstadz,
    this.ustadzFoto,
    this.daftarUstadz = const [],
    required this.ruangan,
    this.namaGedung,
    this.namaKamar,
    required this.level,
  });

  bool get isTeamTeaching => daftarUstadz.length > 1;

  String get lokasiGedungKamar {
    final list = <String>[];
    if (namaGedung != null && namaGedung!.trim().isNotEmpty) {
      list.add(namaGedung!.trim());
    }
    if (namaKamar != null && namaKamar!.trim().isNotEmpty) {
      list.add(namaKamar!.trim());
    }
    if (list.isEmpty) return 'Gedung / Kamar: -';
    return list.join(' • ');
  }

  factory SesiJadwalItem.fromJson(Map<String, dynamic> json) {
    final rawPengampu = json['daftar_ustadz'] as List? ?? [];
    return SesiJadwalItem(
      id: json['id'] ?? 0,
      jamKe: json['jam_ke']?.toString() ?? '',
      jam: json['jam'] ?? '',
      mapel: json['mapel'] ?? '',
      ustadz: json['ustadz'],
      kodeUstadz: json['kode_ustadz'],
      ustadzFoto: json['ustadz_foto'],
      daftarUstadz: rawPengampu
          .map((e) => UstadzPengampuItem.fromJson(e))
          .toList(),
      ruangan: json['ruangan'] ?? '',
      namaGedung: json['nama_gedung'],
      namaKamar: json['nama_kamar'],
      level: json['level'] ?? '',
    );
  }
}

class HariJadwalItem {
  final String hari;
  final int totalSesi;
  final List<SesiJadwalItem> sesi;

  HariJadwalItem({
    required this.hari,
    required this.totalSesi,
    required this.sesi,
  });

  factory HariJadwalItem.fromJson(Map<String, dynamic> json) {
    final rawSesi = json['sesi'] as List? ?? [];
    return HariJadwalItem(
      hari: json['hari'] ?? '',
      totalSesi: json['total_sesi'] ?? 0,
      sesi: rawSesi.map((e) => SesiJadwalItem.fromJson(e)).toList(),
    );
  }
}

class JadwalPelajaranResponse {
  final String ustadzNama;
  final int totalJadwalMingguan;
  final List<HariJadwalItem> jadwalPerHari;
  final bool isWaliRuangan;
  final int? ruanganWaliId;
  final String? ruanganWaliNama;
  final String? levelWaliNama;
  final int totalJadwalRuanganMingguan;
  final List<HariJadwalItem> jadwalRuanganPerHari;

  JadwalPelajaranResponse({
    required this.ustadzNama,
    required this.totalJadwalMingguan,
    required this.jadwalPerHari,
    this.isWaliRuangan = false,
    this.ruanganWaliId,
    this.ruanganWaliNama,
    this.levelWaliNama,
    this.totalJadwalRuanganMingguan = 0,
    this.jadwalRuanganPerHari = const [],
  });

  factory JadwalPelajaranResponse.fromJson(Map<String, dynamic> json) {
    final rawJadwal = json['jadwal_per_hari'] as List? ?? [];
    final rawJadwalRuangan = json['jadwal_ruangan_per_hari'] as List? ?? [];
    return JadwalPelajaranResponse(
      ustadzNama: json['ustadz_nama'] ?? '',
      totalJadwalMingguan: json['total_jadwal_mingguan'] ?? 0,
      jadwalPerHari: rawJadwal.map((e) => HariJadwalItem.fromJson(e)).toList(),
      isWaliRuangan:
          json['is_wali_ruangan'] == true || json['is_wali_ruangan'] == 1,
      ruanganWaliId: json['ruangan_wali_id'],
      ruanganWaliNama: json['ruangan_wali_nama'],
      levelWaliNama: json['level_wali_nama'],
      totalJadwalRuanganMingguan: json['total_jadwal_ruangan_mingguan'] ?? 0,
      jadwalRuanganPerHari: rawJadwalRuangan
          .map((e) => HariJadwalItem.fromJson(e))
          .toList(),
    );
  }
}

class UjianAgendaItem {
  final int id;
  final String namaUjian;
  final String tipeUjian;
  final String semester;
  final String? tanggalMulai;
  final String? tanggalSelesai;

  UjianAgendaItem({
    required this.id,
    required this.namaUjian,
    required this.tipeUjian,
    required this.semester,
    this.tanggalMulai,
    this.tanggalSelesai,
  });

  factory UjianAgendaItem.fromJson(Map<String, dynamic> json) {
    return UjianAgendaItem(
      id: json['id'] ?? 0,
      namaUjian: json['nama_ujian'] ?? '',
      tipeUjian: json['tipe_ujian'] ?? 'IMDA 1',
      semester: json['semester'] ?? '-',
      tanggalMulai: json['tanggal_mulai'],
      tanggalSelesai: json['tanggal_selesai'],
    );
  }
}

class SesiJadwalUjianItem {
  final int id;
  final int ujianId;
  final String namaUjian;
  final String tipeUjian;
  final String semester;
  final int? mataPelajaranId;
  final bool isCustomMapel;
  final String namaMapel;
  final int levelId;
  final String namaLevel;
  final String waktuMulai;
  final String waktuSelesai;
  final String jam;
  final int? ustadzId;
  final String namaPengawas;
  final String? kodePengawas;
  final String? pengawasFoto;
  final bool isMySchedule;

  SesiJadwalUjianItem({
    required this.id,
    required this.ujianId,
    required this.namaUjian,
    required this.tipeUjian,
    required this.semester,
    this.mataPelajaranId,
    this.isCustomMapel = false,
    required this.namaMapel,
    required this.levelId,
    required this.namaLevel,
    required this.waktuMulai,
    required this.waktuSelesai,
    required this.jam,
    this.ustadzId,
    required this.namaPengawas,
    this.kodePengawas,
    this.pengawasFoto,
    this.isMySchedule = false,
  });

  factory SesiJadwalUjianItem.fromJson(Map<String, dynamic> json) {
    return SesiJadwalUjianItem(
      id: json['id'] ?? 0,
      ujianId: json['ujian_id'] ?? 0,
      namaUjian: json['nama_ujian'] ?? '-',
      tipeUjian: json['tipe_ujian'] ?? '-',
      semester: json['semester'] ?? '-',
      mataPelajaranId: json['mata_pelajaran_id'],
      isCustomMapel:
          json['is_custom_mapel'] == true || json['is_custom_mapel'] == 1,
      namaMapel: json['nama_mapel'] ?? 'Ujian',
      levelId: json['level_id'] ?? 0,
      namaLevel: json['nama_level'] ?? '-',
      waktuMulai: json['waktu_mulai'] ?? '',
      waktuSelesai: json['waktu_selesai'] ?? '',
      jam: json['jam'] ?? '',
      ustadzId: json['ustadz_id'],
      namaPengawas: json['nama_pengawas'] ?? 'Belum Ditentukan',
      kodePengawas: json['kode_pengawas'],
      pengawasFoto: json['pengawas_foto'],
      isMySchedule:
          json['is_my_schedule'] == true || json['is_my_schedule'] == 1,
    );
  }
}

class TanggalJadwalUjianItem {
  final String tanggal;
  final String hariTanggal;
  final String hariTanggalSingkat;
  final int totalSesi;
  final List<SesiJadwalUjianItem> sesi;

  TanggalJadwalUjianItem({
    required this.tanggal,
    required this.hariTanggal,
    required this.hariTanggalSingkat,
    required this.totalSesi,
    required this.sesi,
  });

  factory TanggalJadwalUjianItem.fromJson(Map<String, dynamic> json) {
    final rawSesi = json['sesi'] as List? ?? [];
    return TanggalJadwalUjianItem(
      tanggal: json['tanggal'] ?? '',
      hariTanggal: json['hari_tanggal'] ?? '',
      hariTanggalSingkat: json['hari_tanggal_singkat'] ?? '',
      totalSesi: json['total_sesi'] ?? 0,
      sesi: rawSesi.map((e) => SesiJadwalUjianItem.fromJson(e)).toList(),
    );
  }
}

class JadwalUjianResponse {
  final List<RuanganOptionItem> daftarRuangan;
  final int? selectedRuanganId;
  final String selectedRuanganNama;
  final String namaLevel;
  final bool isWaliRuangan;
  final String waliRuanganNama;
  final List<UjianOptionItem> daftarUjian;
  final int? selectedUjianId;
  final int totalJadwal;
  final int totalJadwalSaya;
  final List<TanggalJadwalUjianItem> jadwalPerTanggal;

  JadwalUjianResponse({
    this.daftarRuangan = const [],
    this.selectedRuanganId,
    this.selectedRuanganNama = '',
    this.namaLevel = '',
    this.isWaliRuangan = false,
    this.waliRuanganNama = '-',
    this.daftarUjian = const [],
    this.selectedUjianId,
    required this.totalJadwal,
    required this.totalJadwalSaya,
    required this.jadwalPerTanggal,
  });

  factory JadwalUjianResponse.fromJson(Map<String, dynamic> json) {
    final rawRuangan = json['daftar_ruangan'] as List? ?? [];
    final rawUjian = json['daftar_ujian'] as List? ?? [];
    final rawJadwal = json['jadwal_per_tanggal'] as List? ?? [];

    return JadwalUjianResponse(
      daftarRuangan: rawRuangan
          .map((e) => RuanganOptionItem.fromJson(e as Map<String, dynamic>))
          .toList(),
      selectedRuanganId: json['selected_ruangan_id'],
      selectedRuanganNama: json['selected_ruangan_nama'] ?? '',
      namaLevel: json['nama_level'] ?? '',
      isWaliRuangan:
          json['is_wali_ruangan'] == true || json['is_wali_ruangan'] == 1,
      waliRuanganNama: json['wali_ruangan_nama'] ?? '-',
      daftarUjian: rawUjian
          .map((e) => UjianOptionItem.fromJson(e as Map<String, dynamic>))
          .toList(),
      selectedUjianId: json['selected_ujian_id'],
      totalJadwal: json['total_jadwal'] ?? 0,
      totalJadwalSaya: json['total_jadwal_saya'] ?? 0,
      jadwalPerTanggal: rawJadwal
          .map((e) => TanggalJadwalUjianItem.fromJson(e))
          .toList(),
    );
  }
}
