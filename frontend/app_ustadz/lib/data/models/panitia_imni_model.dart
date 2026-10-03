class PembayaranImniRingkasanModel {
  final int totalPeserta;
  final double totalTagihan;
  final double totalTerbayar;
  final double sisaPiutang;
  final double persentaseTerkumpul;
  final int totalLunas;
  final int totalBelumLunas;
  final int totalDispensasi;
  final List<BreakdownTingkatItem> breakdownTingkat;
  final List<RuanganOptionItem> daftarRuangan;

  PembayaranImniRingkasanModel({
    required this.totalPeserta,
    required this.totalTagihan,
    required this.totalTerbayar,
    required this.sisaPiutang,
    required this.persentaseTerkumpul,
    required this.totalLunas,
    required this.totalBelumLunas,
    required this.totalDispensasi,
    required this.breakdownTingkat,
    this.daftarRuangan = const [],
  });

  factory PembayaranImniRingkasanModel.fromJson(Map<String, dynamic> json) {
    final rawBreakdown = json['breakdown_tingkat'] as List? ?? [];
    final rawRuangan = json['daftar_ruangan'] as List? ?? [];

    return PembayaranImniRingkasanModel(
      totalPeserta: json['total_peserta'] ?? 0,
      totalTagihan: (json['total_tagihan'] as num?)?.toDouble() ?? 0.0,
      totalTerbayar: (json['total_terbayar'] as num?)?.toDouble() ?? 0.0,
      sisaPiutang: (json['sisa_piutang'] as num?)?.toDouble() ?? 0.0,
      persentaseTerkumpul: (json['persentase_terkumpul'] as num?)?.toDouble() ?? 0.0,
      totalLunas: json['total_lunas'] ?? 0,
      totalBelumLunas: json['total_belum_lunas'] ?? 0,
      totalDispensasi: json['total_dispensasi'] ?? 0,
      breakdownTingkat: rawBreakdown.map((e) => BreakdownTingkatItem.fromJson(e)).toList(),
      daftarRuangan: rawRuangan.map((e) => RuanganOptionItem.fromJson(e)).toList(),
    );
  }
}

class BreakdownTingkatItem {
  final int tingkatId;
  final String kodeTingkat;
  final String namaTingkat;
  final int totalPeserta;
  final int totalLunas;
  final double totalTagihan;
  final double totalTerbayar;
  final double sisaPiutang;

  BreakdownTingkatItem({
    required this.tingkatId,
    required this.kodeTingkat,
    required this.namaTingkat,
    required this.totalPeserta,
    required this.totalLunas,
    required this.totalTagihan,
    required this.totalTerbayar,
    required this.sisaPiutang,
  });

  factory BreakdownTingkatItem.fromJson(Map<String, dynamic> json) {
    return BreakdownTingkatItem(
      tingkatId: json['tingkat_id'] ?? 0,
      kodeTingkat: json['kode_tingkat'] ?? '',
      namaTingkat: json['nama_tingkat'] ?? '',
      totalPeserta: json['total_peserta'] ?? 0,
      totalLunas: json['total_lunas'] ?? 0,
      totalTagihan: (json['total_tagihan'] as num?)?.toDouble() ?? 0.0,
      totalTerbayar: (json['total_terbayar'] as num?)?.toDouble() ?? 0.0,
      sisaPiutang: (json['sisa_piutang'] as num?)?.toDouble() ?? 0.0,
    );
  }
}

class PesertaImniItem {
  final int id;
  final String nomorPeserta;
  final int? nomorMeja;
  final int muridId;
  final String namaLengkap;
  final String nism;
  final String jenisKelamin;
  final String? foto;
  final int tingkatId;
  final String namaTingkat;
  final String kodeTingkat;
  final String namaLevel;
  final String ruanganAsal;
  final String ruanganUjian;
  final String statusKelayakan;
  final String? catatanDispensasi;
  final PembayaranDetailItem pembayaran;

  PesertaImniItem({
    required this.id,
    required this.nomorPeserta,
    this.nomorMeja,
    required this.muridId,
    required this.namaLengkap,
    required this.nism,
    required this.jenisKelamin,
    this.foto,
    required this.tingkatId,
    required this.namaTingkat,
    required this.kodeTingkat,
    required this.namaLevel,
    required this.ruanganAsal,
    required this.ruanganUjian,
    required this.statusKelayakan,
    this.catatanDispensasi,
    required this.pembayaran,
  });

  factory PesertaImniItem.fromJson(Map<String, dynamic> json) {
    return PesertaImniItem(
      id: json['id'] ?? 0,
      nomorPeserta: json['nomor_peserta'] ?? '-',
      nomorMeja: json['nomor_meja'],
      muridId: json['murid_id'] ?? 0,
      namaLengkap: json['nama_lengkap'] ?? '-',
      nism: json['nism'] ?? '-',
      jenisKelamin: json['jenis_kelamin'] ?? 'L',
      foto: json['foto'],
      tingkatId: json['tingkat_id'] ?? 0,
      namaTingkat: json['nama_tingkat'] ?? '-',
      kodeTingkat: json['kode_tingkat'] ?? '-',
      namaLevel: json['nama_level'] ?? '-',
      ruanganAsal: json['ruangan_asal'] ?? '-',
      ruanganUjian: json['ruangan_ujian'] ?? 'Belum Diplot',
      statusKelayakan: json['status_kelayakan'] ?? 'Layak',
      catatanDispensasi: json['catatan_dispensasi'],
      pembayaran: PembayaranDetailItem.fromJson(json['pembayaran'] ?? {}),
    );
  }
}

class PembayaranDetailItem {
  final int? id;
  final String noKwitansi;
  final double nominalTagihan;
  final double nominalBayar;
  final double sisaTagihan;
  final String statusPembayaran;
  final String? tanggalBayar;
  final String? tanggalBayarFormat;
  final String metodePembayaran;
  final String? namaPenyetor;
  final String penerimaNama;

  PembayaranDetailItem({
    this.id,
    required this.noKwitansi,
    required this.nominalTagihan,
    required this.nominalBayar,
    required this.sisaTagihan,
    required this.statusPembayaran,
    this.tanggalBayar,
    this.tanggalBayarFormat,
    required this.metodePembayaran,
    this.namaPenyetor,
    required this.penerimaNama,
  });

  factory PembayaranDetailItem.fromJson(Map<String, dynamic> json) {
    return PembayaranDetailItem(
      id: json['id'],
      noKwitansi: json['no_kwitansi'] ?? '-',
      nominalTagihan: (json['nominal_tagihan'] as num?)?.toDouble() ?? 0.0,
      nominalBayar: (json['nominal_bayar'] as num?)?.toDouble() ?? 0.0,
      sisaTagihan: (json['sisa_tagihan'] as num?)?.toDouble() ?? 0.0,
      statusPembayaran: json['status_pembayaran'] ?? 'Belum Lunas',
      tanggalBayar: json['tanggal_bayar'],
      tanggalBayarFormat: json['tanggal_bayar_format'],
      metodePembayaran: json['metode_pembayaran'] ?? 'Tunai',
      namaPenyetor: json['nama_penyetor'],
      penerimaNama: json['penerima_nama'] ?? 'Bendahara',
    );
  }
}

class PengeluaranImniRingkasanModel {
  final double totalPemasukan;
  final double totalPengeluaran;
  final double sisaSaldo;
  final List<BreakdownKategoriItem> breakdownKategori;

  PengeluaranImniRingkasanModel({
    required this.totalPemasukan,
    required this.totalPengeluaran,
    required this.sisaSaldo,
    required this.breakdownKategori,
  });

  factory PengeluaranImniRingkasanModel.fromJson(Map<String, dynamic> json) {
    final rawBreakdown = json['breakdown_kategori'] as List? ?? [];
    return PengeluaranImniRingkasanModel(
      totalPemasukan: (json['total_pemasukan'] as num?)?.toDouble() ?? 0.0,
      totalPengeluaran: (json['total_pengeluaran'] as num?)?.toDouble() ?? 0.0,
      sisaSaldo: (json['sisa_saldo'] as num?)?.toDouble() ?? 0.0,
      breakdownKategori: rawBreakdown.map((e) => BreakdownKategoriItem.fromJson(e)).toList(),
    );
  }
}

class BreakdownKategoriItem {
  final String kategori;
  final double total;
  final double persentase;

  BreakdownKategoriItem({
    required this.kategori,
    required this.total,
    required this.persentase,
  });

  factory BreakdownKategoriItem.fromJson(Map<String, dynamic> json) {
    return BreakdownKategoriItem(
      kategori: json['kategori'] ?? '',
      total: (json['total'] as num?)?.toDouble() ?? 0.0,
      persentase: (json['persentase'] as num?)?.toDouble() ?? 0.0,
    );
  }
}

class PengeluaranImniItem {
  final int id;
  final String kodeTransaksi;
  final String kategori;
  final String judulPengeluaran;
  final double nominal;
  final String? tanggalPengeluaran;
  final String? tanggalFormat;
  final String? penerimaDana;
  final String metodePembayaran;
  final String? buktiNotaUrl;
  final String? keterangan;
  final String pencatatNama;

  PengeluaranImniItem({
    required this.id,
    required this.kodeTransaksi,
    required this.kategori,
    required this.judulPengeluaran,
    required this.nominal,
    this.tanggalPengeluaran,
    this.tanggalFormat,
    this.penerimaDana,
    required this.metodePembayaran,
    this.buktiNotaUrl,
    this.keterangan,
    required this.pencatatNama,
  });

  factory PengeluaranImniItem.fromJson(Map<String, dynamic> json) {
    return PengeluaranImniItem(
      id: json['id'] ?? 0,
      kodeTransaksi: json['kode_transaksi'] ?? '',
      kategori: json['kategori'] ?? 'Lain-lain',
      judulPengeluaran: json['judul_pengeluaran'] ?? '',
      nominal: (json['nominal'] as num?)?.toDouble() ?? 0.0,
      tanggalPengeluaran: json['tanggal_pengeluaran'],
      tanggalFormat: json['tanggal_format'],
      penerimaDana: json['penerima_dana'],
      metodePembayaran: json['metode_pembayaran'] ?? 'Tunai',
      buktiNotaUrl: json['bukti_nota_url'],
      keterangan: json['keterangan'],
      pencatatNama: json['pencatat_nama'] ?? 'Panitia',
    );
  }
}

class PresensiImniMuridItem {
  final int pesertaId;
  final int muridId;
  final String nomorPeserta;
  final int? nomorMeja;
  final String nama;
  final String nism;
  final String? namaLevel;
  final String? ruanganAsal;
  final String? kodeTingkat;
  final int? tingkatId;
  final String jenisKelamin;
  final bool isLocked;
  final String? lockReason;
  String? status;
  String? catatan;

  PresensiImniMuridItem({
    required this.pesertaId,
    required this.muridId,
    required this.nomorPeserta,
    this.nomorMeja,
    required this.nama,
    required this.nism,
    this.namaLevel,
    this.ruanganAsal,
    this.kodeTingkat,
    this.tingkatId,
    required this.jenisKelamin,
    required this.isLocked,
    this.lockReason,
    this.status,
    this.catatan,
  });

  factory PresensiImniMuridItem.fromJson(Map<String, dynamic> json) {
    return PresensiImniMuridItem(
      pesertaId: json['peserta_id'] ?? 0,
      muridId: json['murid_id'] ?? 0,
      nomorPeserta: json['nomor_peserta'] ?? '-',
      nomorMeja: json['nomor_meja'],
      nama: json['nama'] ?? '-',
      nism: json['nism'] ?? '-',
      namaLevel: json['nama_level'],
      ruanganAsal: json['ruangan_asal'],
      kodeTingkat: json['kode_tingkat'],
      tingkatId: json['tingkat_id'],
      jenisKelamin: json['jenis_kelamin'] ?? 'L',
      isLocked: json['is_locked'] ?? false,
      lockReason: json['lock_reason'],
      status: json['status'],
      catatan: json['catatan'],
    );
  }
}

class NilaiImniMuridItem {
  final int pesertaId;
  final int muridId;
  final String nomorPeserta;
  final int? nomorMeja;
  final String nama;
  final String nism;
  final String jenisKelamin;
  final bool isLocked;
  final String? lockReason;
  double? nilai;
  bool isPublished;

  NilaiImniMuridItem({
    required this.pesertaId,
    required this.muridId,
    required this.nomorPeserta,
    this.nomorMeja,
    required this.nama,
    required this.nism,
    required this.jenisKelamin,
    required this.isLocked,
    this.lockReason,
    this.nilai,
    this.isPublished = false,
  });

  factory NilaiImniMuridItem.fromJson(Map<String, dynamic> json) {
    return NilaiImniMuridItem(
      pesertaId: json['peserta_id'] ?? 0,
      muridId: json['murid_id'] ?? 0,
      nomorPeserta: json['nomor_peserta'] ?? '-',
      nomorMeja: json['nomor_meja'],
      nama: json['nama'] ?? '-',
      nism: json['nism'] ?? '-',
      jenisKelamin: json['jenis_kelamin'] ?? 'L',
      isLocked: json['is_locked'] ?? false,
      lockReason: json['lock_reason'],
      nilai: (json['nilai'] as num?)?.toDouble(),
      isPublished: json['is_published'] ?? false,
    );
  }
}

class LegerImniRowItem {
  final int pesertaId;
  final int muridId;
  final String nomorPeserta;
  final String nism;
  final String nama;
  final String jenisKelamin;
  final Map<String, double?> nilaiMapel;
  final double total;
  final double rataRata;
  final String predikat;
  final int jumlahTerisi;
  final int totalMapel;
  final int ranking;

  LegerImniRowItem({
    required this.pesertaId,
    required this.muridId,
    required this.nomorPeserta,
    required this.nism,
    required this.nama,
    required this.jenisKelamin,
    required this.nilaiMapel,
    required this.total,
    required this.rataRata,
    required this.predikat,
    required this.jumlahTerisi,
    required this.totalMapel,
    required this.ranking,
  });

  factory LegerImniRowItem.fromJson(Map<String, dynamic> json) {
    final rawScores = json['nilai_mapel'];
    final Map<String, double?> mapelMap = {};
    if (rawScores is Map) {
      rawScores.forEach((k, v) {
        mapelMap[k.toString()] = (v as num?)?.toDouble();
      });
    }

    return LegerImniRowItem(
      pesertaId: json['peserta_id'] ?? 0,
      muridId: json['murid_id'] ?? 0,
      nomorPeserta: json['nomor_peserta'] ?? '-',
      nism: json['nism'] ?? '-',
      nama: json['nama'] ?? '-',
      jenisKelamin: json['jenis_kelamin'] ?? 'L',
      nilaiMapel: mapelMap,
      total: (json['total'] as num?)?.toDouble() ?? 0.0,
      rataRata: (json['rata_rata'] as num?)?.toDouble() ?? 0.0,
      predikat: json['predikat'] ?? 'E',
      jumlahTerisi: json['jumlah_terisi'] ?? 0,
      totalMapel: json['total_mapel'] ?? 0,
      ranking: json['ranking'] ?? 0,
    );
  }
}

class RuanganOptionItem {
  final int id;
  final String namaRuangan;
  final int? levelId;
  final String namaLevel;
  final int? tingkatId;
  final String kodeTingkat;

  RuanganOptionItem({
    required this.id,
    required this.namaRuangan,
    this.levelId,
    required this.namaLevel,
    this.tingkatId,
    required this.kodeTingkat,
  });

  factory RuanganOptionItem.fromJson(Map<String, dynamic> json) {
    return RuanganOptionItem(
      id: json['id'] ?? 0,
      namaRuangan: json['nama_ruangan'] ?? '',
      levelId: json['level_id'],
      namaLevel: json['nama_level'] ?? '-',
      tingkatId: json['tingkat_id'],
      kodeTingkat: json['kode_tingkat'] ?? '-',
    );
  }
}

class JadwalOptionItem {
  final int id;
  final int? mataPelajaranId;
  final String namaMapel;
  final String? kodeMapel;
  final String? tanggalUjian;
  final String? hariTanggal;
  final String? hariTanggalSingkat;
  final String? waktuMulai;
  final String? waktuSelesai;
  final int? pengawasId;
  final String? pengawasNama;
  final int? jumlahDinilai;
  final bool isPublished;

  JadwalOptionItem({
    required this.id,
    this.mataPelajaranId,
    required this.namaMapel,
    this.kodeMapel,
    this.tanggalUjian,
    this.hariTanggal,
    this.hariTanggalSingkat,
    this.waktuMulai,
    this.waktuSelesai,
    this.pengawasId,
    this.pengawasNama,
    this.jumlahDinilai,
    this.isPublished = false,
  });

  factory JadwalOptionItem.fromJson(Map<String, dynamic> json) {
    return JadwalOptionItem(
      id: json['id'] ?? 0,
      mataPelajaranId: json['mata_pelajaran_id'],
      namaMapel: json['nama_mapel'] ?? '',
      kodeMapel: json['kode_mapel'],
      tanggalUjian: json['tanggal_ujian'],
      hariTanggal: json['hari_tanggal'],
      hariTanggalSingkat: json['hari_tanggal_singkat'],
      waktuMulai: json['waktu_mulai'],
      waktuSelesai: json['waktu_selesai'],
      pengawasId: json['pengawas_id'],
      pengawasNama: json['pengawas_nama'],
      jumlahDinilai: json['jumlah_dinilai'],
      isPublished: json['is_published'] ?? false,
    );
  }
}

class PengawasPresensiData {
  int? ustadzId;
  String? ustadzNama;
  int? ustadzPenggantiId;
  String? ustadzPenggantiNama;
  String status;
  String? catatanBeritaAcara;
  bool isPlotted;

  PengawasPresensiData({
    this.ustadzId,
    this.ustadzNama,
    this.ustadzPenggantiId,
    this.ustadzPenggantiNama,
    this.status = 'Hadir',
    this.catatanBeritaAcara,
    this.isPlotted = false,
  });

  factory PengawasPresensiData.fromJson(Map<String, dynamic> json) {
    return PengawasPresensiData(
      ustadzId: json['ustadz_id'],
      ustadzNama: json['ustadz_nama'],
      ustadzPenggantiId: json['ustadz_pengganti_id'],
      ustadzPenggantiNama: json['ustadz_pengganti_nama'],
      status: json['status'] ?? 'Hadir',
      catatanBeritaAcara: json['catatan_berita_acara'],
      isPlotted: json['is_plotted'] ?? (json['ustadz_id'] != null || (json['ustadz_nama'] != null && json['ustadz_nama'] != 'Belum Ditentukan')),
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

class PresensiImniSummary {
  final int total;
  final int hadir;
  final int izin;
  final int sakit;
  final int alpha;
  final int dispensasi;
  final int belum;

  PresensiImniSummary({
    required this.total,
    required this.hadir,
    required this.izin,
    required this.sakit,
    required this.alpha,
    required this.dispensasi,
    required this.belum,
  });

  factory PresensiImniSummary.fromJson(Map<String, dynamic> json) {
    return PresensiImniSummary(
      total: json['total'] ?? 0,
      hadir: json['hadir'] ?? 0,
      izin: json['izin'] ?? 0,
      sakit: json['sakit'] ?? 0,
      alpha: json['alpha'] ?? 0,
      dispensasi: json['dispensasi'] ?? 0,
      belum: json['belum'] ?? 0,
    );
  }
}

class HariUjianImniItem {
  final int hariKe;
  final String tanggal;
  final String namaHari;
  final String tanggalFormat;
  final String tanggalLengkap;
  final List<dynamic> jadwalIbt;
  final List<dynamic> jadwalTsa;

  HariUjianImniItem({
    required this.hariKe,
    required this.tanggal,
    required this.namaHari,
    required this.tanggalFormat,
    required this.tanggalLengkap,
    required this.jadwalIbt,
    required this.jadwalTsa,
  });

  factory HariUjianImniItem.fromJson(Map<String, dynamic> json) {
    return HariUjianImniItem(
      hariKe: json['hari_ke'] ?? 1,
      tanggal: json['tanggal'] ?? '',
      namaHari: json['nama_hari'] ?? '',
      tanggalFormat: json['tanggal_format'] ?? '',
      tanggalLengkap: json['tanggal_lengkap'] ?? '',
      jadwalIbt: json['jadwal_ibt'] as List? ?? [],
      jadwalTsa: json['jadwal_tsa'] as List? ?? [],
    );
  }
}

class RuanganImniOptionItem {
  final int id;
  final String namaRuanganImni;
  final String ruanganFisikNama;
  final int kapasitas;
  final int urutan;
  final String penanggungJawab;

  RuanganImniOptionItem({
    required this.id,
    required this.namaRuanganImni,
    required this.ruanganFisikNama,
    required this.kapasitas,
    required this.urutan,
    required this.penanggungJawab,
  });

  factory RuanganImniOptionItem.fromJson(Map<String, dynamic> json) {
    return RuanganImniOptionItem(
      id: json['id'] ?? 0,
      namaRuanganImni: json['nama_ruangan_imni'] ?? '',
      ruanganFisikNama: json['ruangan_fisik_nama'] ?? '-',
      kapasitas: json['kapasitas'] ?? 0,
      urutan: json['urutan'] ?? 1,
      penanggungJawab: json['penanggung_jawab'] ?? '-',
    );
  }
}

class PresensiImniDataResponse {
  final String kategori; // 'tpq' or 'imni'
  final dynamic ujian;
  // Mode TPQ
  final List<RuanganOptionItem> daftarRuangan;
  final int? selectedRuanganId;
  final String selectedRuanganNama;
  final String namaLevel;
  final List<JadwalOptionItem> jadwalList;
  final int? selectedJadwalId;
  // Mode IMNI (6 IBT & 3 TSA)
  final List<HariUjianImniItem> daftarHariUjian;
  final String? selectedTanggal;
  final HariUjianImniItem? selectedHariInfo;
  final List<RuanganImniOptionItem> daftarRuanganImni;
  final int? selectedRuanganImniId;
  final RuanganImniOptionItem? selectedRuanganImni;
  // Common
  final PengawasPresensiData? pengawas;
  final List<Map<String, dynamic>> daftarBadal;
  final List<PresensiImniMuridItem> muridList;
  final PresensiImniSummary summary;

  PresensiImniDataResponse({
    this.kategori = 'imni',
    this.ujian,
    required this.daftarRuangan,
    this.selectedRuanganId,
    required this.selectedRuanganNama,
    required this.namaLevel,
    required this.jadwalList,
    this.selectedJadwalId,
    required this.daftarHariUjian,
    this.selectedTanggal,
    this.selectedHariInfo,
    required this.daftarRuanganImni,
    this.selectedRuanganImniId,
    this.selectedRuanganImni,
    this.pengawas,
    required this.daftarBadal,
    required this.muridList,
    required this.summary,
  });

  factory PresensiImniDataResponse.fromJson(Map<String, dynamic> json) {
    final rawRuangan = json['daftar_ruangan'] as List? ?? [];
    final rawJadwal = json['jadwal_list'] as List? ?? [];
    final rawMurid = json['murid_list'] as List? ?? [];
    final rawBadal = json['daftar_badal'] as List? ?? [];
    final rawHari = json['daftar_hari_ujian'] as List? ?? [];
    final rawRuanganImni = json['daftar_ruangan_imni'] as List? ?? [];

    return PresensiImniDataResponse(
      kategori: json['kategori'] ?? 'imni',
      ujian: json['ujian'],
      daftarRuangan: rawRuangan.map((e) => RuanganOptionItem.fromJson(e)).toList(),
      selectedRuanganId: json['selected_ruangan_id'],
      selectedRuanganNama: json['selected_ruangan_nama'] ?? '',
      namaLevel: json['nama_level'] ?? '',
      jadwalList: rawJadwal.map((e) => JadwalOptionItem.fromJson(e)).toList(),
      selectedJadwalId: json['selected_jadwal_id'],
      daftarHariUjian: rawHari.map((e) => HariUjianImniItem.fromJson(e)).toList(),
      selectedTanggal: json['selected_tanggal'],
      selectedHariInfo: json['selected_hari_info'] != null
          ? HariUjianImniItem.fromJson(json['selected_hari_info'])
          : null,
      daftarRuanganImni: rawRuanganImni.map((e) => RuanganImniOptionItem.fromJson(e)).toList(),
      selectedRuanganImniId: json['selected_ruangan_imni_id'],
      selectedRuanganImni: json['selected_ruangan_imni'] != null
          ? RuanganImniOptionItem.fromJson(json['selected_ruangan_imni'])
          : null,
      pengawas: json['pengawas'] != null ? PengawasPresensiData.fromJson(json['pengawas']) : null,
      daftarBadal: rawBadal.map((e) => Map<String, dynamic>.from(e as Map)).toList(),
      muridList: rawMurid.map((e) => PresensiImniMuridItem.fromJson(e)).toList(),
      summary: PresensiImniSummary.fromJson(json['summary'] ?? {}),
    );
  }
}

class NilaiImniDataResponse {
  final dynamic ujian;
  final List<RuanganOptionItem> daftarRuangan;
  final int? selectedRuanganId;
  final String selectedRuanganNama;
  final String namaLevel;
  final List<JadwalOptionItem> jadwalList;
  final int? selectedJadwalId;
  final List<NilaiImniMuridItem> murids;

  NilaiImniDataResponse({
    this.ujian,
    required this.daftarRuangan,
    this.selectedRuanganId,
    required this.selectedRuanganNama,
    required this.namaLevel,
    required this.jadwalList,
    this.selectedJadwalId,
    required this.murids,
  });

  factory NilaiImniDataResponse.fromJson(Map<String, dynamic> json) {
    final rawRuangan = json['daftar_ruangan'] as List? ?? [];
    final rawJadwal = json['jadwal_list'] as List? ?? [];
    final rawMurid = json['murids'] as List? ?? [];

    return NilaiImniDataResponse(
      ujian: json['ujian'],
      daftarRuangan: rawRuangan.map((e) => RuanganOptionItem.fromJson(e)).toList(),
      selectedRuanganId: json['selected_ruangan_id'],
      selectedRuanganNama: json['selected_ruangan_nama'] ?? '',
      namaLevel: json['nama_level'] ?? '',
      jadwalList: rawJadwal.map((e) => JadwalOptionItem.fromJson(e)).toList(),
      selectedJadwalId: json['selected_jadwal_id'],
      murids: rawMurid.map((e) => NilaiImniMuridItem.fromJson(e)).toList(),
    );
  }
}

class StatistikLegerImni {
  final int totalMurid;
  final double rataRataKelas;
  final double nilaiTertinggi;
  final double nilaiTerendah;

  StatistikLegerImni({
    required this.totalMurid,
    required this.rataRataKelas,
    required this.nilaiTertinggi,
    required this.nilaiTerendah,
  });

  factory StatistikLegerImni.fromJson(Map<String, dynamic> json) {
    return StatistikLegerImni(
      totalMurid: json['total_murid'] ?? 0,
      rataRataKelas: (json['rata_rata_kelas'] as num?)?.toDouble() ?? 0.0,
      nilaiTertinggi: (json['nilai_tertinggi'] as num?)?.toDouble() ?? 0.0,
      nilaiTerendah: (json['nilai_terendah'] as num?)?.toDouble() ?? 0.0,
    );
  }
}

class KolomMapelItem {
  final int id;
  final String namaMapel;
  final String? kodeMapel;

  KolomMapelItem({
    required this.id,
    required this.namaMapel,
    this.kodeMapel,
  });

  factory KolomMapelItem.fromJson(Map<String, dynamic> json) {
    return KolomMapelItem(
      id: json['id'] ?? 0,
      namaMapel: json['nama_mapel'] ?? '',
      kodeMapel: json['kode_mapel'],
    );
  }
}

class LegerImniDataResponse {
  final dynamic ujian;
  final List<RuanganOptionItem> daftarRuangan;
  final int? selectedRuanganId;
  final String selectedRuanganNama;
  final List<KolomMapelItem> kolomMapel;
  final StatistikLegerImni statistik;
  final List<LegerImniRowItem> leger;

  LegerImniDataResponse({
    this.ujian,
    this.daftarRuangan = const [],
    this.selectedRuanganId,
    this.selectedRuanganNama = '',
    required this.kolomMapel,
    required this.statistik,
    required this.leger,
  });

  factory LegerImniDataResponse.fromJson(Map<String, dynamic> json) {
    final rawRuangan = json['daftar_ruangan'] as List? ?? [];
    final rawKolom = json['kolom_mapel'] as List? ?? [];
    final rawLeger = json['leger'] as List? ?? [];

    return LegerImniDataResponse(
      ujian: json['ujian'],
      daftarRuangan: rawRuangan.map((e) => RuanganOptionItem.fromJson(e)).toList(),
      selectedRuanganId: json['selected_ruangan_id'],
      selectedRuanganNama: json['selected_ruangan_nama'] ?? '',
      kolomMapel: rawKolom.map((e) => KolomMapelItem.fromJson(e)).toList(),
      statistik: StatistikLegerImni.fromJson(json['statistik'] ?? {}),
      leger: rawLeger.map((e) => LegerImniRowItem.fromJson(e)).toList(),
    );
  }
}
