class RuanganFilterItem {
  final int id;
  final String namaRuangan;
  final String levelNama;

  const RuanganFilterItem({
    required this.id,
    required this.namaRuangan,
    required this.levelNama,
  });

  factory RuanganFilterItem.fromJson(Map<String, dynamic> json) {
    return RuanganFilterItem(
      id: json['id'] ?? 0,
      namaRuangan: json['nama_ruangan'] ?? '',
      levelNama: json['level_nama'] ?? '-',
    );
  }
}

class SemesterFilterItem {
  final int id;
  final String namaSemester;
  final bool isActive;

  const SemesterFilterItem({
    required this.id,
    required this.namaSemester,
    required this.isActive,
  });

  factory SemesterFilterItem.fromJson(Map<String, dynamic> json) {
    return SemesterFilterItem(
      id: json['id'] ?? 0,
      namaSemester: json['nama_semester'] ?? '',
      isActive: json['is_active'] == true || json['is_active'] == 1,
    );
  }
}

class BulanHijriyahFilterItem {
  final int id;
  final String namaBulan;
  final String tahunHijriyah;
  final int? semesterId;
  final dynamic semester;
  final String? semesterNama;
  final int urutan;

  const BulanHijriyahFilterItem({
    required this.id,
    required this.namaBulan,
    required this.tahunHijriyah,
    this.semesterId,
    this.semester,
    this.semesterNama,
    this.urutan = 0,
  });

  factory BulanHijriyahFilterItem.fromJson(Map<String, dynamic> json) {
    return BulanHijriyahFilterItem(
      id: json['id'] ?? 0,
      namaBulan: json['nama_bulan'] ?? '',
      tahunHijriyah: json['tahun_hijriyah']?.toString() ?? '',
      semesterId: json['semester_id'] != null
          ? int.tryParse(json['semester_id'].toString())
          : null,
      semester: json['semester'],
      semesterNama: json['semester_nama']?.toString(),
      urutan: json['urutan'] ?? 0,
    );
  }
}

// =========================================================================
// 1. LAPORAN PRESENSI MURID MODELS
// =========================================================================

class RekapJadwalItem {
  final int id;
  final String hari;
  final String jamKe;
  final String? jamMulai;
  final String? jamSelesai;
  final String jamText;
  final int? mataPelajaranId;
  final String namaMapel;
  final String kodeMapel;
  final int? ustadzId;
  final String namaUstadz;
  final int totalPertemuan;
  final int totalHadir;
  final int totalSakit;
  final int totalIzin;
  final int totalAlpha;
  final int totalDispensasi;
  final int totalPresensi;
  final double persentaseKehadiran;

  const RekapJadwalItem({
    required this.id,
    required this.hari,
    required this.jamKe,
    this.jamMulai,
    this.jamSelesai,
    required this.jamText,
    this.mataPelajaranId,
    required this.namaMapel,
    required this.kodeMapel,
    this.ustadzId,
    required this.namaUstadz,
    required this.totalPertemuan,
    required this.totalHadir,
    required this.totalSakit,
    required this.totalIzin,
    required this.totalAlpha,
    required this.totalDispensasi,
    required this.totalPresensi,
    required this.persentaseKehadiran,
  });

  factory RekapJadwalItem.fromJson(Map<String, dynamic> json) {
    return RekapJadwalItem(
      id: json['id'] ?? 0,
      hari: json['hari'] ?? '',
      jamKe: json['jam_ke']?.toString() ?? '',
      jamMulai: json['jam_mulai'],
      jamSelesai: json['jam_selesai'],
      jamText: json['jam_text'] ?? '',
      mataPelajaranId: json['mata_pelajaran_id'],
      namaMapel: json['nama_mapel'] ?? '',
      kodeMapel: json['kode_mapel'] ?? '-',
      ustadzId: json['ustadz_id'],
      namaUstadz: json['nama_ustadz'] ?? '-',
      totalPertemuan: json['total_pertemuan'] ?? 0,
      totalHadir: json['total_hadir'] ?? 0,
      totalSakit: json['total_sakit'] ?? 0,
      totalIzin: json['total_izin'] ?? 0,
      totalAlpha: json['total_alpha'] ?? 0,
      totalDispensasi: json['total_dispensasi'] ?? 0,
      totalPresensi: json['total_presensi'] ?? 0,
      persentaseKehadiran: (json['persentase_kehadiran'] is num)
          ? (json['persentase_kehadiran'] as num).toDouble()
          : 0.0,
    );
  }
}

class MuridAbsenItem {
  final int muridId;
  final String nama;
  final String nism;
  final String status;

  const MuridAbsenItem({
    required this.muridId,
    required this.nama,
    required this.nism,
    required this.status,
  });

  factory MuridAbsenItem.fromJson(Map<String, dynamic> json) {
    return MuridAbsenItem(
      muridId: json['murid_id'] ?? 0,
      nama: json['nama'] ?? '',
      nism: json['nism'] ?? '',
      status: json['status'] ?? '',
    );
  }
}

class RiwayatPertemuanItem {
  final String tanggal;
  final String? hariTanggal;
  final int totalHadir;
  final int totalSakit;
  final int totalIzin;
  final int totalAlpha;
  final int totalDispensasi;
  final int totalAbsen;
  final List<MuridAbsenItem> muridAbsen;

  const RiwayatPertemuanItem({
    required this.tanggal,
    this.hariTanggal,
    required this.totalHadir,
    required this.totalSakit,
    required this.totalIzin,
    required this.totalAlpha,
    required this.totalDispensasi,
    required this.totalAbsen,
    required this.muridAbsen,
  });

  factory RiwayatPertemuanItem.fromJson(Map<String, dynamic> json) {
    final list = json['murid_absen'] as List? ?? [];
    return RiwayatPertemuanItem(
      tanggal: json['tanggal'] ?? '',
      hariTanggal: json['hari_tanggal'],
      totalHadir: json['total_hadir'] ?? 0,
      totalSakit: json['total_sakit'] ?? 0,
      totalIzin: json['total_izin'] ?? 0,
      totalAlpha: json['total_alpha'] ?? 0,
      totalDispensasi: json['total_dispensasi'] ?? 0,
      totalAbsen: json['total_absen'] ?? 0,
      muridAbsen: list.map((e) => MuridAbsenItem.fromJson(e)).toList(),
    );
  }
}

class RekapMuridPresensiItem {
  final int muridId;
  final String nama;
  final String nism;
  final String jenisKelamin;
  final String? foto;
  final String wali;
  final int hadirCount;
  final int sakitCount;
  final int izinCount;
  final int alphaCount;
  final int dispensasiCount;
  final int totalPresensi;
  final double persentaseKehadiran;
  final double akumulasiPoin;
  final String predikat;

  const RekapMuridPresensiItem({
    required this.muridId,
    required this.nama,
    required this.nism,
    required this.jenisKelamin,
    this.foto,
    required this.wali,
    required this.hadirCount,
    required this.sakitCount,
    required this.izinCount,
    required this.alphaCount,
    required this.dispensasiCount,
    required this.totalPresensi,
    required this.persentaseKehadiran,
    required this.akumulasiPoin,
    required this.predikat,
  });

  factory RekapMuridPresensiItem.fromJson(Map<String, dynamic> json) {
    return RekapMuridPresensiItem(
      muridId: json['murid_id'] ?? 0,
      nama: json['nama'] ?? '',
      nism: json['nism'] ?? '',
      jenisKelamin: json['jenis_kelamin'] ?? 'L',
      foto: json['foto'],
      wali: json['wali'] ?? '-',
      hadirCount: json['hadir_count'] ?? 0,
      sakitCount: json['sakit_count'] ?? 0,
      izinCount: json['izin_count'] ?? 0,
      alphaCount: json['alpha_count'] ?? 0,
      dispensasiCount: json['dispensasi_count'] ?? 0,
      totalPresensi: json['total_presensi'] ?? 0,
      persentaseKehadiran: (json['persentase_kehadiran'] is num)
          ? (json['persentase_kehadiran'] as num).toDouble()
          : 0.0,
      akumulasiPoin: (json['akumulasi_poin'] is num)
          ? (json['akumulasi_poin'] as num).toDouble()
          : 0.0,
      predikat: json['predikat'] ?? 'Baik',
    );
  }
}

class LaporanPresensiMuridData {
  final int ruanganId;
  final String namaRuangan;
  final String levelNama;
  final bool isWaliRuangan;
  final String waliRuanganNama;
  final String tahunPelajaran;
  final int? selectedSemesterId;
  final int? selectedJadwalId;
  final RekapJadwalItem? selectedJadwal;
  final double poinAlphaRate;
  final double poinIzinRate;
  final int totalMurid;
  final int totalHariEfektif;
  final int totalHadir;
  final int totalSakit;
  final int totalIzin;
  final int totalAlpha;
  final int totalDispensasi;
  final double totalPoinKelas;
  final double persentaseKehadiranKelas;
  final List<SemesterFilterItem> semesterList;
  final List<RuanganFilterItem> ruanganList;
  final List<BulanHijriyahFilterItem> bulanHijriyahList;
  final List<RekapJadwalItem> jadwalPelajaranList;
  final List<RiwayatPertemuanItem> riwayatPertemuan;
  final List<RekapMuridPresensiItem> rekapMurid;

  const LaporanPresensiMuridData({
    required this.ruanganId,
    required this.namaRuangan,
    required this.levelNama,
    this.isWaliRuangan = true,
    required this.waliRuanganNama,
    required this.tahunPelajaran,
    this.selectedSemesterId,
    this.selectedJadwalId,
    this.selectedJadwal,
    required this.poinAlphaRate,
    required this.poinIzinRate,
    required this.totalMurid,
    required this.totalHariEfektif,
    required this.totalHadir,
    required this.totalSakit,
    required this.totalIzin,
    required this.totalAlpha,
    required this.totalDispensasi,
    required this.totalPoinKelas,
    required this.persentaseKehadiranKelas,
    required this.semesterList,
    required this.ruanganList,
    required this.bulanHijriyahList,
    required this.jadwalPelajaranList,
    required this.riwayatPertemuan,
    required this.rekapMurid,
  });

  factory LaporanPresensiMuridData.fromJson(Map<String, dynamic> json) {
    final sList = json['semester_list'] as List? ?? [];
    final rList = json['ruangan_list'] as List? ?? [];
    final bList = json['bulan_hijriyah_list'] as List? ?? [];
    final jList = json['jadwal_pelajaran_list'] as List? ?? [];
    final pList = json['riwayat_pertemuan'] as List? ?? [];
    final mList = json['rekap_murid'] as List? ?? [];

    return LaporanPresensiMuridData(
      ruanganId: json['ruangan_id'] ?? 0,
      namaRuangan: json['nama_ruangan'] ?? '',
      levelNama: json['level_nama'] ?? '-',
      isWaliRuangan: json['is_wali_ruangan'] ?? true,
      waliRuanganNama: json['wali_ruangan_nama'] ?? '-',
      tahunPelajaran: json['tahun_pelajaran'] ?? '',
      selectedSemesterId: json['selected_semester_id'],
      selectedJadwalId: json['selected_jadwal_id'],
      selectedJadwal: json['selected_jadwal'] != null
          ? RekapJadwalItem.fromJson(json['selected_jadwal'])
          : null,
      poinAlphaRate: (json['poin_alpha_rate'] is num)
          ? (json['poin_alpha_rate'] as num).toDouble()
          : 1.0,
      poinIzinRate: (json['poin_izin_rate'] is num)
          ? (json['poin_izin_rate'] as num).toDouble()
          : 0.16,
      totalMurid: json['total_murid'] ?? 0,
      totalHariEfektif: json['total_hari_efektif'] ?? 0,
      totalHadir: json['total_hadir'] ?? 0,
      totalSakit: json['total_sakit'] ?? 0,
      totalIzin: json['total_izin'] ?? 0,
      totalAlpha: json['total_alpha'] ?? 0,
      totalDispensasi: json['total_dispensasi'] ?? 0,
      totalPoinKelas: (json['total_poin_kelas'] is num)
          ? (json['total_poin_kelas'] as num).toDouble()
          : 0.0,
      persentaseKehadiranKelas: (json['persentase_kehadiran_kelas'] is num)
          ? (json['persentase_kehadiran_kelas'] as num).toDouble()
          : 0.0,
      semesterList: sList.map((e) => SemesterFilterItem.fromJson(e)).toList(),
      ruanganList: rList.map((e) => RuanganFilterItem.fromJson(e)).toList(),
      bulanHijriyahList: bList
          .map((e) => BulanHijriyahFilterItem.fromJson(e))
          .toList(),
      jadwalPelajaranList: jList
          .map((e) => RekapJadwalItem.fromJson(e))
          .toList(),
      riwayatPertemuan: pList
          .map((e) => RiwayatPertemuanItem.fromJson(e))
          .toList(),
      rekapMurid: mList.map((e) => RekapMuridPresensiItem.fromJson(e)).toList(),
    );
  }
}

// =========================================================================
// 2. LAPORAN PELANGGARAN MURID MODELS
// =========================================================================

class RekapPelanggaranMuridItem {
  final int muridId;
  final String nama;
  final String nism;
  final String jenisKelamin;
  final String? foto;
  final String wali;
  final int totalKasus;
  final double totalPoin;
  final String statusKedisiplinan;

  const RekapPelanggaranMuridItem({
    required this.muridId,
    required this.nama,
    required this.nism,
    required this.jenisKelamin,
    this.foto,
    required this.wali,
    required this.totalKasus,
    required this.totalPoin,
    required this.statusKedisiplinan,
  });

  factory RekapPelanggaranMuridItem.fromJson(Map<String, dynamic> json) {
    return RekapPelanggaranMuridItem(
      muridId: json['murid_id'] ?? 0,
      nama: json['nama'] ?? '',
      nism: json['nism'] ?? '',
      jenisKelamin: json['jenis_kelamin'] ?? 'L',
      foto: json['foto'],
      wali: json['wali'] ?? '-',
      totalKasus: json['total_kasus'] ?? 0,
      totalPoin: (json['total_poin'] is num)
          ? (json['total_poin'] as num).toDouble()
          : 0.0,
      statusKedisiplinan: json['status_kedisiplinan'] ?? 'Disiplin',
    );
  }
}

class LogPelanggaranItem {
  final int id;
  final int muridId;
  final String namaMurid;
  final String nism;
  final String tanggal;
  final String? hariTanggal;
  final String namaPelanggaran;
  final String kategori;
  final double poin;
  final String keterangan;
  final String pencatat;

  const LogPelanggaranItem({
    required this.id,
    required this.muridId,
    required this.namaMurid,
    required this.nism,
    required this.tanggal,
    this.hariTanggal,
    required this.namaPelanggaran,
    required this.kategori,
    required this.poin,
    required this.keterangan,
    required this.pencatat,
  });

  factory LogPelanggaranItem.fromJson(Map<String, dynamic> json) {
    return LogPelanggaranItem(
      id: json['id'] ?? 0,
      muridId: json['murid_id'] ?? 0,
      namaMurid: json['nama_murid'] ?? '',
      nism: json['nism'] ?? '',
      tanggal: json['tanggal'] ?? '',
      hariTanggal: json['hari_tanggal'],
      namaPelanggaran: json['nama_pelanggaran'] ?? '',
      kategori: json['kategori'] ?? 'Ringan',
      poin: (json['poin'] is num) ? (json['poin'] as num).toDouble() : 0.0,
      keterangan: json['keterangan'] ?? '-',
      pencatat: json['pencatat'] ?? 'Ustadz',
    );
  }
}

class LaporanPelanggaranData {
  final int ruanganId;
  final String namaRuangan;
  final String levelNama;
  final String tahunPelajaran;
  final int totalMurid;
  final int totalKasus;
  final double totalPoin;
  final int kasusSelesai;
  final int kasusDiproses;
  final int kasusRingan;
  final int kasusSedang;
  final int kasusBerat;
  final List<RuanganFilterItem> ruanganList;
  final List<RekapPelanggaranMuridItem> rekapMurid;
  final List<LogPelanggaranItem> riwayatLog;

  const LaporanPelanggaranData({
    required this.ruanganId,
    required this.namaRuangan,
    required this.levelNama,
    required this.tahunPelajaran,
    required this.totalMurid,
    required this.totalKasus,
    required this.totalPoin,
    required this.kasusSelesai,
    required this.kasusDiproses,
    required this.kasusRingan,
    required this.kasusSedang,
    required this.kasusBerat,
    required this.ruanganList,
    required this.rekapMurid,
    required this.riwayatLog,
  });

  factory LaporanPelanggaranData.fromJson(Map<String, dynamic> json) {
    final rList = json['ruangan_list'] as List? ?? [];
    final mList = json['rekap_murid'] as List? ?? [];
    final lList = json['riwayat_log'] as List? ?? [];

    return LaporanPelanggaranData(
      ruanganId: json['ruangan_id'] ?? 0,
      namaRuangan: json['nama_ruangan'] ?? '',
      levelNama: json['level_nama'] ?? '-',
      tahunPelajaran: json['tahun_pelajaran'] ?? '',
      totalMurid: json['total_murid'] ?? 0,
      totalKasus: json['total_kasus'] ?? 0,
      totalPoin: (json['total_poin'] is num)
          ? (json['total_poin'] as num).toDouble()
          : 0.0,
      kasusSelesai: json['kasus_selesai'] ?? 0,
      kasusDiproses: json['kasus_diproses'] ?? 0,
      kasusRingan: json['kasus_ringan'] ?? 0,
      kasusSedang: json['kasus_sedang'] ?? 0,
      kasusBerat: json['kasus_berat'] ?? 0,
      ruanganList: rList.map((e) => RuanganFilterItem.fromJson(e)).toList(),
      rekapMurid: mList
          .map((e) => RekapPelanggaranMuridItem.fromJson(e))
          .toList(),
      riwayatLog: lList.map((e) => LogPelanggaranItem.fromJson(e)).toList(),
    );
  }
}

// =========================================================================
// 3. LAPORAN PRESENSI USTADZ MODELS
// =========================================================================

class UstadzFilterItem {
  final int id;
  final String nama;
  final String niup;
  final String? foto;

  const UstadzFilterItem({
    required this.id,
    required this.nama,
    required this.niup,
    this.foto,
  });

  factory UstadzFilterItem.fromJson(Map<String, dynamic> json) {
    return UstadzFilterItem(
      id: json['id'] ?? 0,
      nama: json['nama'] ?? json['nama_lengkap'] ?? '',
      niup: json['niup'] ?? '-',
      foto: json['foto'],
    );
  }
}

class RekapUstadzPresensiItem {
  final int ustadzId;
  final String nama;
  final String niup;
  final String? foto;
  final List<String> mapelList;
  final int totalSesi;
  final int totalHadir;
  final int totalTugas;
  final int totalIzin;
  final int totalSakit;
  final int totalAlpha;
  final double persentaseKehadiran;

  const RekapUstadzPresensiItem({
    required this.ustadzId,
    required this.nama,
    required this.niup,
    this.foto,
    required this.mapelList,
    required this.totalSesi,
    required this.totalHadir,
    required this.totalTugas,
    required this.totalIzin,
    required this.totalSakit,
    required this.totalAlpha,
    required this.persentaseKehadiran,
  });

  factory RekapUstadzPresensiItem.fromJson(Map<String, dynamic> json) {
    final mList = json['mapel_list'] as List? ?? [];
    return RekapUstadzPresensiItem(
      ustadzId: json['ustadz_id'] ?? 0,
      nama: json['nama'] ?? '',
      niup: json['niup'] ?? '-',
      foto: json['foto'],
      mapelList: mList.map((e) => e.toString()).toList(),
      totalSesi: json['total_sesi'] ?? 0,
      totalHadir: json['total_hadir'] ?? 0,
      totalTugas: json['total_tugas'] ?? 0,
      totalIzin: json['total_izin'] ?? 0,
      totalSakit: json['total_sakit'] ?? 0,
      totalAlpha: json['total_alpha'] ?? 0,
      persentaseKehadiran: (json['persentase_kehadiran'] is num)
          ? (json['persentase_kehadiran'] as num).toDouble()
          : 0.0,
    );
  }
}

class RiwayatPresensiUstadzItem {
  final int id;
  final int? ustadzId;
  final String namaUstadz;
  final String niupUstadz;
  final String? fotoUstadz;
  final String tanggal;
  final String? hariTanggal;
  final String status;
  final String jamMasuk;
  final String? jamKeluar;
  final String mapel;
  final String namaRuangan;
  final String keterangan;
  final String? foto;

  const RiwayatPresensiUstadzItem({
    required this.id,
    this.ustadzId,
    required this.namaUstadz,
    required this.niupUstadz,
    this.fotoUstadz,
    required this.tanggal,
    this.hariTanggal,
    required this.status,
    required this.jamMasuk,
    this.jamKeluar,
    required this.mapel,
    required this.namaRuangan,
    required this.keterangan,
    this.foto,
  });

  factory RiwayatPresensiUstadzItem.fromJson(Map<String, dynamic> json) {
    return RiwayatPresensiUstadzItem(
      id: json['id'] ?? 0,
      ustadzId: json['ustadz_id'],
      namaUstadz: json['nama_ustadz'] ?? 'Ustadz',
      niupUstadz: json['niup_ustadz'] ?? '-',
      fotoUstadz: json['foto_ustadz'],
      tanggal: json['tanggal'] ?? '',
      hariTanggal: json['hari_tanggal'],
      status: json['status'] ?? 'Hadir',
      jamMasuk: json['jam_masuk'] ?? '-',
      jamKeluar: json['jam_keluar'],
      mapel: json['mapel'] ?? '-',
      namaRuangan: json['nama_ruangan'] ?? '',
      keterangan: json['keterangan'] ?? '-',
      foto: json['foto'],
    );
  }
}

class LaporanPresensiUstadzData {
  final int ruanganId;
  final String namaRuangan;
  final String levelNama;
  final int? selectedSemesterId;
  final int? selectedUstadzId;
  final bool isWaliRuangan;
  final List<SemesterFilterItem> semesterList;
  final List<RuanganFilterItem> ruanganList;
  final List<BulanHijriyahFilterItem> bulanHijriyahList;
  final List<UstadzFilterItem> daftarUstadz;
  final String tahunPelajaran;
  final int totalSesi;
  final int totalHadir;
  final int totalTugas;
  final int totalIzin;
  final int totalSakit;
  final int totalAlpha;
  final double persentaseKehadiran;
  final List<RekapUstadzPresensiItem> rekapUstadz;
  final List<RiwayatPresensiUstadzItem> riwayat;

  const LaporanPresensiUstadzData({
    required this.ruanganId,
    required this.namaRuangan,
    required this.levelNama,
    this.isWaliRuangan = true,
    this.selectedSemesterId,
    this.selectedUstadzId,
    required this.semesterList,
    required this.ruanganList,
    required this.bulanHijriyahList,
    required this.daftarUstadz,
    required this.tahunPelajaran,
    required this.totalSesi,
    required this.totalHadir,
    required this.totalTugas,
    required this.totalIzin,
    required this.totalSakit,
    required this.totalAlpha,
    required this.persentaseKehadiran,
    required this.rekapUstadz,
    required this.riwayat,
  });

  factory LaporanPresensiUstadzData.fromJson(Map<String, dynamic> json) {
    final sList = json['semester_list'] as List? ?? [];
    final rList = json['ruangan_list'] as List? ?? [];
    final bList = json['bulan_hijriyah_list'] as List? ?? [];
    final uList = json['daftar_ustadz'] as List? ?? [];
    final rkList = json['rekap_ustadz'] as List? ?? [];
    final rwList = json['riwayat'] as List? ?? [];

    return LaporanPresensiUstadzData(
      ruanganId: json['ruangan_id'] ?? 0,
      namaRuangan: json['nama_ruangan'] ?? '',
      levelNama: json['level_nama'] ?? '-',
      isWaliRuangan: json['is_wali_ruangan'] ?? true,
      selectedSemesterId: json['selected_semester_id'],
      selectedUstadzId: json['selected_ustadz_id'],
      semesterList: sList.map((e) => SemesterFilterItem.fromJson(e)).toList(),
      ruanganList: rList.map((e) => RuanganFilterItem.fromJson(e)).toList(),
      bulanHijriyahList: bList
          .map((e) => BulanHijriyahFilterItem.fromJson(e))
          .toList(),
      daftarUstadz: uList.map((e) => UstadzFilterItem.fromJson(e)).toList(),
      tahunPelajaran: json['tahun_pelajaran'] ?? '',
      totalSesi: json['total_sesi'] ?? 0,
      totalHadir: json['total_hadir'] ?? 0,
      totalTugas: json['total_tugas'] ?? 0,
      totalIzin: json['total_izin'] ?? 0,
      totalSakit: json['total_sakit'] ?? 0,
      totalAlpha: json['total_alpha'] ?? 0,
      persentaseKehadiran: (json['persentase_kehadiran'] is num)
          ? (json['persentase_kehadiran'] as num).toDouble()
          : 0.0,
      rekapUstadz: rkList
          .map((e) => RekapUstadzPresensiItem.fromJson(e))
          .toList(),
      riwayat: rwList
          .map((e) => RiwayatPresensiUstadzItem.fromJson(e))
          .toList(),
    );
  }
}
