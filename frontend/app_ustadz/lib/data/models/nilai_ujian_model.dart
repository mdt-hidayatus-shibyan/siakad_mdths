class UjianItem {
  final int id;
  final String namaUjian;
  final String tipeUjian; // IMDA 1, IMDA 2, IMNI
  final String semester;
  final String tanggalMulai;
  final String tanggalSelesai;

  UjianItem({
    required this.id,
    required this.namaUjian,
    required this.tipeUjian,
    required this.semester,
    required this.tanggalMulai,
    required this.tanggalSelesai,
  });

  factory UjianItem.fromJson(Map<String, dynamic> json) {
    return UjianItem(
      id: json['id'] ?? 0,
      namaUjian: json['nama_ujian'] ?? '',
      tipeUjian: json['tipe_ujian'] ?? '',
      semester: json['semester'] ?? '',
      tanggalMulai: json['tanggal_mulai'] ?? '',
      tanggalSelesai: json['tanggal_selesai'] ?? '',
    );
  }
}

class RuanganNilaiItem {
  final int id;
  final String namaRuangan;
  final int levelId;
  final String namaLevel;

  RuanganNilaiItem({
    required this.id,
    required this.namaRuangan,
    required this.levelId,
    required this.namaLevel,
  });

  factory RuanganNilaiItem.fromJson(Map<String, dynamic> json) {
    return RuanganNilaiItem(
      id: json['id'] ?? 0,
      namaRuangan: json['nama_ruangan'] ?? '',
      levelId: json['level_id'] ?? 0,
      namaLevel: json['nama_level'] ?? '',
    );
  }
}

class JadwalMapelNilaiItem {
  final int id;
  final int mataPelajaranId;
  final String namaMapel;
  final String tanggalUjian;
  final String? hariTanggal;
  final String? hariTanggalSingkat;
  final String waktuMulai;
  final String waktuSelesai;
  final String pengawasNama;
  final int totalMurid;
  final int jumlahDinilai;
  final String statusInput;
  final bool isPublished;

  JadwalMapelNilaiItem({
    required this.id,
    required this.mataPelajaranId,
    required this.namaMapel,
    required this.tanggalUjian,
    this.hariTanggal,
    this.hariTanggalSingkat,
    required this.waktuMulai,
    required this.waktuSelesai,
    required this.pengawasNama,
    required this.totalMurid,
    required this.jumlahDinilai,
    required this.statusInput,
    required this.isPublished,
  });

  factory JadwalMapelNilaiItem.fromJson(Map<String, dynamic> json) {
    return JadwalMapelNilaiItem(
      id: json['id'] ?? 0,
      mataPelajaranId: json['mata_pelajaran_id'] ?? 0,
      namaMapel: json['nama_mapel'] ?? '',
      tanggalUjian: json['tanggal_ujian'] ?? json['hari_tanggal_singkat'] ?? '',
      hariTanggal: json['hari_tanggal'],
      hariTanggalSingkat: json['hari_tanggal_singkat'] ?? json['tanggal_ujian'],
      waktuMulai: json['waktu_mulai'] ?? '07:30',
      waktuSelesai: json['waktu_selesai'] ?? '09:00',
      pengawasNama: json['pengawas_nama'] ?? 'Belum Ditentukan',
      totalMurid: json['total_murid'] ?? 0,
      jumlahDinilai: json['jumlah_dinilai'] ?? 0,
      statusInput: json['status_input'] ?? 'Belum Diisi',
      isPublished: json['is_published'] ?? false,
    );
  }
}

class MapelJadwalNilaiResponse {
  final List<RuanganNilaiItem> daftarRuangan;
  final int? selectedRuanganId;
  final String selectedRuanganNama;
  final String namaLevel;
  final bool isWaliRuangan;
  final List<UjianItem> daftarUjian;
  final int? selectedUjianId;
  final List<JadwalMapelNilaiItem> jadwalList;

  MapelJadwalNilaiResponse({
    required this.daftarRuangan,
    this.selectedRuanganId,
    required this.selectedRuanganNama,
    required this.namaLevel,
    required this.isWaliRuangan,
    required this.daftarUjian,
    this.selectedUjianId,
    required this.jadwalList,
  });

  factory MapelJadwalNilaiResponse.fromJson(Map<String, dynamic> json) {
    final rawRuangan = (json['daftar_ruangan'] as List? ?? []);
    final rawUjian = (json['daftar_ujian'] as List? ?? []);
    final rawJadwal = (json['jadwal_list'] as List? ?? []);

    return MapelJadwalNilaiResponse(
      daftarRuangan: rawRuangan
          .map((e) => RuanganNilaiItem.fromJson(e))
          .toList(),
      selectedRuanganId: json['selected_ruangan_id'],
      selectedRuanganNama: json['selected_ruangan_nama'] ?? '',
      namaLevel: json['nama_level'] ?? '',
      isWaliRuangan: json['is_wali_ruangan'] ?? false,
      daftarUjian: rawUjian.map((e) => UjianItem.fromJson(e)).toList(),
      selectedUjianId: json['selected_ujian_id'],
      jadwalList: rawJadwal
          .map((e) => JadwalMapelNilaiItem.fromJson(e))
          .toList(),
    );
  }
}

class NilaiInputDataResponse {
  final bool canEdit;
  final bool isReadOnly;
  final String? readOnlyReason;
  final bool isWaliRuangan;
  final List<MuridNilaiItem> murids;

  NilaiInputDataResponse({
    this.canEdit = true,
    this.isReadOnly = false,
    this.readOnlyReason,
    this.isWaliRuangan = false,
    required this.murids,
  });

  factory NilaiInputDataResponse.fromJson(Map<String, dynamic> json) {
    final rawList = (json['murids'] as List? ?? []);
    return NilaiInputDataResponse(
      canEdit: json['can_edit'] ?? true,
      isReadOnly: json['is_read_only'] ?? false,
      readOnlyReason: json['read_only_reason'],
      isWaliRuangan: json['is_wali_ruangan'] ?? false,
      murids: rawList.map((e) => MuridNilaiItem.fromJson(e)).toList(),
    );
  }
}

class MuridNilaiItem {
  final int muridId;
  final String nism;
  final String nama;
  final String jenisKelamin;
  final bool isLocked;
  final String? lockReason;
  double? nilai;
  final bool isPublished;

  MuridNilaiItem({
    required this.muridId,
    required this.nism,
    required this.nama,
    this.jenisKelamin = 'L',
    required this.isLocked,
    this.lockReason,
    this.nilai,
    required this.isPublished,
  });

  factory MuridNilaiItem.fromJson(Map<String, dynamic> json) {
    return MuridNilaiItem(
      muridId: json['murid_id'] ?? json['id'] ?? 0,
      nism: json['nism'] ?? '',
      nama: json['nama'] ?? '',
      jenisKelamin: json['jenis_kelamin'] ?? 'L',
      isLocked: json['is_locked'] ?? false,
      lockReason: json['lock_reason'],
      nilai: json['nilai'] != null ? (json['nilai'] as num).toDouble() : null,
      isPublished: json['is_published'] ?? false,
    );
  }
}

class KolomMapelItem {
  final int id;
  final String namaMapel;
  final String? kodeMapel;

  KolomMapelItem({required this.id, required this.namaMapel, this.kodeMapel});

  factory KolomMapelItem.fromJson(Map<String, dynamic> json) {
    return KolomMapelItem(
      id: json['id'] ?? 0,
      namaMapel: json['nama_mapel'] ?? '',
      kodeMapel: json['kode_mapel'],
    );
  }
}

class LegerStatistik {
  final int totalMurid;
  final double rataRataKelas;
  final double nilaiTertinggi;
  final double nilaiTerendah;

  LegerStatistik({
    required this.totalMurid,
    required this.rataRataKelas,
    required this.nilaiTertinggi,
    required this.nilaiTerendah,
  });

  int get totalSantri => totalMurid;

  factory LegerStatistik.fromJson(Map<String, dynamic> json) {
    return LegerStatistik(
      totalMurid: json['total_murid'] ?? json['total_santri'] ?? 0,
      rataRataKelas: (json['rata_rata_kelas'] as num?)?.toDouble() ?? 0.0,
      nilaiTertinggi: (json['nilai_tertinggi'] as num?)?.toDouble() ?? 0.0,
      nilaiTerendah: (json['nilai_terendah'] as num?)?.toDouble() ?? 0.0,
    );
  }
}

class LegerRowItem {
  final int ranking;
  final int muridId;
  final String nism;
  final String nama;
  final String jenisKelamin;
  final Map<String, dynamic> nilaiMapel;
  final double total;
  final double rataRata;
  final String predikat;
  final int jumlahTerisi;
  final int totalMapel;

  LegerRowItem({
    required this.ranking,
    required this.muridId,
    required this.nism,
    required this.nama,
    this.jenisKelamin = 'L',
    required this.nilaiMapel,
    required this.total,
    required this.rataRata,
    this.predikat = 'E',
    this.jumlahTerisi = 0,
    this.totalMapel = 0,
  });

  factory LegerRowItem.fromJson(Map<String, dynamic> json) {
    return LegerRowItem(
      ranking: json['ranking'] ?? 0,
      muridId: json['murid_id'] ?? 0,
      nism: json['nism'] ?? '',
      nama: json['nama'] ?? '',
      jenisKelamin: json['jenis_kelamin'] ?? 'L',
      nilaiMapel: json['nilai_mapel'] != null
          ? Map<String, dynamic>.from(json['nilai_mapel'])
          : {},
      total: (json['total'] as num?)?.toDouble() ?? 0.0,
      rataRata: (json['rata_rata'] as num?)?.toDouble() ?? 0.0,
      predikat: json['predikat'] ?? 'E',
      jumlahTerisi: json['jumlah_terisi'] ?? 0,
      totalMapel: json['total_mapel'] ?? 0,
    );
  }
}

class LegerResponse {
  final String ujianNama;
  final String ruanganNama;
  final String namaLevel;
  final List<KolomMapelItem> kolomMapel;
  final LegerStatistik? statistik;
  final List<LegerRowItem> leger;

  LegerResponse({
    required this.ujianNama,
    required this.ruanganNama,
    required this.namaLevel,
    required this.kolomMapel,
    this.statistik,
    required this.leger,
  });

  factory LegerResponse.fromJson(Map<String, dynamic> json) {
    final rawKolom = (json['kolom_mapel'] as List? ?? []);
    final rawLeger = (json['leger'] as List? ?? []);

    return LegerResponse(
      ujianNama: json['ujian']?['nama_ujian'] ?? '',
      ruanganNama: json['ruangan']?['nama_ruangan'] ?? '',
      namaLevel: json['ruangan']?['nama_level'] ?? '',
      kolomMapel: rawKolom.map((e) => KolomMapelItem.fromJson(e)).toList(),
      statistik: json['statistik'] != null
          ? LegerStatistik.fromJson(json['statistik'])
          : null,
      leger: rawLeger.map((e) => LegerRowItem.fromJson(e)).toList(),
    );
  }
}
