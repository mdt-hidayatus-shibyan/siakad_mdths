import '../../core/network/api_client.dart';

class TabunganKasModel {
  final int id;
  final String nomorRekening;
  final String namaRekening;
  final num saldo;
  final num totalSetor;
  final num totalTarik;
  final String status;

  TabunganKasModel({
    required this.id,
    required this.nomorRekening,
    required this.namaRekening,
    required this.saldo,
    required this.totalSetor,
    this.totalTarik = 0,
    this.status = 'Aktif',
  });

  factory TabunganKasModel.fromJson(Map<String, dynamic> json) {
    return TabunganKasModel(
      id: json['id'] ?? 0,
      nomorRekening: json['nomor_rekening'] ?? '',
      namaRekening: json['nama_rekening'] ?? 'Tabungan Kas Ruangan',
      saldo: json['saldo'] ?? 0,
      totalSetor: json['total_setor'] ?? 0,
      totalTarik: json['total_tarik'] ?? 0,
      status: json['status'] ?? 'Aktif',
    );
  }
}

class RuanganKasItem {
  final int id;
  final String namaRuangan;
  final String levelNama;

  RuanganKasItem({
    required this.id,
    required this.namaRuangan,
    this.levelNama = '-',
  });

  factory RuanganKasItem.fromJson(Map<String, dynamic> json) {
    return RuanganKasItem(
      id: json['id'] ?? 0,
      namaRuangan: json['nama_ruangan'] ?? '',
      levelNama: json['level_nama'] ?? '-',
    );
  }
}

class KasRingkasanModel {
  final int ruanganId;
  final String namaRuangan;
  final String levelNama;
  final int totalMurid;
  final num totalTargetKas;
  final num totalTerkumpul;
  final num totalSudahDisetor;
  final num totalMenungguVerifikasi;
  final num totalPengeluaran;
  final num totalDitarik;
  final num sisaDiTanganWali;
  final List<RuanganKasItem> ruanganList;
  final TabunganKasModel? tabungan;

  KasRingkasanModel({
    required this.ruanganId,
    required this.namaRuangan,
    this.levelNama = '-',
    required this.totalMurid,
    this.totalTargetKas = 0,
    required this.totalTerkumpul,
    required this.totalSudahDisetor,
    this.totalMenungguVerifikasi = 0,
    this.totalPengeluaran = 0,
    this.totalDitarik = 0,
    required this.sisaDiTanganWali,
    this.ruanganList = const [],
    this.tabungan,
  });

  factory KasRingkasanModel.fromJson(Map<String, dynamic> json) {
    return KasRingkasanModel(
      ruanganId: json['ruangan_id'] ?? 0,
      namaRuangan: json['nama_ruangan'] ?? '',
      levelNama: json['level_nama'] ?? '-',
      totalMurid: json['total_murid'] ?? json['total_Murid'] ?? 0,
      totalTargetKas: json['total_target_kas'] ?? 0,
      totalTerkumpul: json['total_terkumpul'] ?? 0,
      totalSudahDisetor: json['total_sudah_disetor'] ?? 0,
      totalMenungguVerifikasi: json['total_menunggu_verifikasi'] ?? 0,
      totalPengeluaran: json['total_pengeluaran'] ?? 0,
      totalDitarik: json['total_ditarik'] ?? 0,
      sisaDiTanganWali: json['sisa_di_tangan_wali'] ?? 0,
      ruanganList: (json['ruangan_list'] as List? ?? [])
          .map((e) => RuanganKasItem.fromJson(e))
          .toList(),
      tabungan: json['tabungan'] != null
          ? TabunganKasModel.fromJson(json['tabungan'])
          : null,
    );
  }
}

class MuridKasItem {
  final int muridId;
  final String nama;
  final String nism;
  final String jenisKelamin;
  final String? foto;
  final num targetKas;
  final num totalDibayar;
  final num sisaTunggakan;
  final String status; // Lunas, Belum Lunas

  MuridKasItem({
    required this.muridId,
    required this.nama,
    required this.nism,
    required this.jenisKelamin,
    this.foto,
    required this.targetKas,
    required this.totalDibayar,
    this.sisaTunggakan = 0,
    required this.status,
  });

  factory MuridKasItem.fromJson(Map<String, dynamic> json) {
    return MuridKasItem(
      muridId: json['murid_id'] ?? 0,
      nama: json['nama'] ?? '',
      nism: json['nism'] ?? '',
      jenisKelamin: json['jenis_kelamin'] ?? 'L',
      foto: ApiClient.resolveImageUrl(json['foto']),
      targetKas: json['target_kas'] ?? 0,
      totalDibayar: json['total_dibayar'] ?? 0,
      sisaTunggakan: json['sisa_tunggakan'] ?? 0,
      status: json['status'] ?? 'Belum Lunas',
    );
  }
}

class RiwayatBayarKasItem {
  final int id;
  final String tanggalBayar;
  final String? hariTanggal;
  final num jumlahBayar;
  final bool isDisetor;
  final String keteranganStatus;

  RiwayatBayarKasItem({
    required this.id,
    required this.tanggalBayar,
    this.hariTanggal,
    required this.jumlahBayar,
    required this.isDisetor,
    required this.keteranganStatus,
  });

  factory RiwayatBayarKasItem.fromJson(Map<String, dynamic> json) {
    return RiwayatBayarKasItem(
      id: json['id'] ?? 0,
      tanggalBayar: json['tanggal_bayar'] ?? '',
      hariTanggal: json['hari_tanggal'],
      jumlahBayar: json['jumlah_bayar'] ?? 0,
      isDisetor: json['is_disetor'] ?? false,
      keteranganStatus: json['keterangan_status'] ?? '',
    );
  }
}

class PengaturanKasItem {
  final int ruanganId;
  final String namaRuangan;
  final int nominalLaki;
  final int nominalPerempuan;

  PengaturanKasItem({
    required this.ruanganId,
    required this.namaRuangan,
    required this.nominalLaki,
    required this.nominalPerempuan,
  });

  factory PengaturanKasItem.fromJson(Map<String, dynamic> json) {
    return PengaturanKasItem(
      ruanganId: json['ruangan_id'] ?? 0,
      namaRuangan: json['nama_ruangan'] ?? 'Ruangan Binaan',
      nominalLaki: json['nominal_laki'] ?? 0,
      nominalPerempuan: json['nominal_perempuan'] ?? 0,
    );
  }
}

class PenerimaKasItem {
  final int id;
  final String name;
  final String username;
  final String role;

  PenerimaKasItem({
    required this.id,
    required this.name,
    required this.username,
    required this.role,
  });

  factory PenerimaKasItem.fromJson(Map<String, dynamic> json) {
    return PenerimaKasItem(
      id: json['id'] ?? 0,
      name: json['name'] ?? '',
      username: json['username'] ?? '',
      role: json['role'] ?? 'Staff',
    );
  }
}

class SetoranKasItem {
  final int id;
  final int ruanganId;
  final String tanggalSetor;
  final String? hariTanggal;
  final num jumlahSetor;
  final String status; // Menunggu Verifikasi, Diterima, Ditolak
  final String? catatanVerifikasi;
  final String? diverifikasiOlehNama;
  final String? diverifikasiPada;
  final String keterangan;
  final int? penerimaId;
  final String penerimaNama;
  final String disetorOlehNama;
  final bool canEdit;
  final bool canDelete;

  SetoranKasItem({
    required this.id,
    required this.ruanganId,
    required this.tanggalSetor,
    this.hariTanggal,
    required this.jumlahSetor,
    this.status = 'Menunggu Verifikasi',
    this.catatanVerifikasi,
    this.diverifikasiOlehNama,
    this.diverifikasiPada,
    required this.keterangan,
    this.penerimaId,
    required this.penerimaNama,
    required this.disetorOlehNama,
    this.canEdit = true,
    this.canDelete = true,
  });

  bool get isMenungguVerifikasi => status == 'Menunggu Verifikasi';
  bool get isDiterima => status == 'Diterima';
  bool get isDitolak => status == 'Ditolak';

  factory SetoranKasItem.fromJson(Map<String, dynamic> json) {
    return SetoranKasItem(
      id: json['id'] ?? 0,
      ruanganId: json['ruangan_id'] ?? 0,
      tanggalSetor: json['tanggal_setor'] ?? '',
      hariTanggal: json['hari_tanggal'],
      jumlahSetor: json['jumlah_setor'] ?? 0,
      status: json['status'] ?? 'Menunggu Verifikasi',
      catatanVerifikasi: json['catatan_verifikasi'],
      diverifikasiOlehNama: json['diverifikasi_oleh_nama'],
      diverifikasiPada: json['diverifikasi_pada'],
      keterangan: json['keterangan'] ?? '-',
      penerimaId: json['penerima_id'],
      penerimaNama: json['penerima_nama'] ?? 'Petugas Tabungan',
      disetorOlehNama: json['disetor_oleh_nama'] ?? 'Wali Ruangan',
      canEdit: json['can_edit'] ?? (json['status'] == 'Menunggu Verifikasi'),
      canDelete:
          json['can_delete'] ?? (json['status'] == 'Menunggu Verifikasi'),
    );
  }
}

class PenarikanKasItem {
  final int id;
  final String kodeTransaksi;
  final String tanggal;
  final String? hariTanggal;
  final num nominal;
  final String keterangan;
  final String kategori;
  final String petugasNama;

  PenarikanKasItem({
    required this.id,
    required this.kodeTransaksi,
    required this.tanggal,
    this.hariTanggal,
    required this.nominal,
    required this.keterangan,
    required this.kategori,
    required this.petugasNama,
  });

  factory PenarikanKasItem.fromJson(Map<String, dynamic> json) {
    return PenarikanKasItem(
      id: json['id'] ?? 0,
      kodeTransaksi: json['kode_transaksi'] ?? '-',
      tanggal: json['tanggal'] ?? '',
      hariTanggal: json['hari_tanggal'],
      nominal: json['nominal'] ?? 0,
      keterangan: json['keterangan'] ?? '-',
      kategori: json['kategori'] ?? 'Penarikan Kas',
      petugasNama: json['petugas_nama'] ?? 'Petugas Tabungan',
    );
  }
}

class RiwayatSetoranModel {
  final int ruanganId;
  final String namaRuangan;
  final String levelNama;
  final num totalTerkumpul;
  final num totalDisetor;
  final num totalMenungguVerifikasi;
  final num totalPengeluaran;
  final num sisaDiTanganWali;
  final TabunganKasModel? tabungan;
  final List<SetoranKasItem> list;
  final List<PenarikanKasItem> penarikanList;

  RiwayatSetoranModel({
    required this.ruanganId,
    required this.namaRuangan,
    required this.levelNama,
    required this.totalTerkumpul,
    required this.totalDisetor,
    this.totalMenungguVerifikasi = 0,
    this.totalPengeluaran = 0,
    required this.sisaDiTanganWali,
    this.tabungan,
    required this.list,
    this.penarikanList = const [],
  });

  factory RiwayatSetoranModel.fromJson(Map<String, dynamic> json) {
    return RiwayatSetoranModel(
      ruanganId: json['ruangan_id'] ?? 0,
      namaRuangan: json['nama_ruangan'] ?? '',
      levelNama: json['level_nama'] ?? '-',
      totalTerkumpul: json['total_terkumpul'] ?? 0,
      totalDisetor: json['total_disetor'] ?? 0,
      totalMenungguVerifikasi: json['total_menunggu_verifikasi'] ?? 0,
      totalPengeluaran: json['total_pengeluaran'] ?? 0,
      sisaDiTanganWali: json['sisa_di_tangan_wali'] ?? 0,
      tabungan: json['tabungan'] != null
          ? TabunganKasModel.fromJson(json['tabungan'])
          : null,
      list: (json['list'] as List? ?? [])
          .map((e) => SetoranKasItem.fromJson(e))
          .toList(),
      penarikanList: (json['penarikan_list'] as List? ?? [])
          .map((e) => PenarikanKasItem.fromJson(e))
          .toList(),
    );
  }
}

class PengeluaranKasItem {
  final int id;
  final int ruanganId;
  final String judul;
  final String kategori;
  final num nominal;
  final String tanggalPengeluaran;
  final String? hariTanggal;
  final String keterangan;
  final String? buktiNota;
  final String diinputOlehNama;
  final bool canEdit;
  final bool canDelete;

  PengeluaranKasItem({
    required this.id,
    required this.ruanganId,
    required this.judul,
    this.kategori = 'Operasional',
    required this.nominal,
    required this.tanggalPengeluaran,
    this.hariTanggal,
    this.keterangan = '-',
    this.buktiNota,
    this.diinputOlehNama = 'Wali Ruangan',
    this.canEdit = true,
    this.canDelete = true,
  });

  String? get buktiNotaUrl => buktiNota;
  String get pencatatNama => diinputOlehNama;

  factory PengeluaranKasItem.fromJson(Map<String, dynamic> json) {
    return PengeluaranKasItem(
      id: json['id'] ?? 0,
      ruanganId: json['ruangan_id'] ?? 0,
      judul: json['judul'] ?? '',
      kategori: json['kategori'] ?? 'Operasional',
      nominal: json['nominal'] ?? 0,
      tanggalPengeluaran: json['tanggal_pengeluaran'] ?? '',
      hariTanggal: json['hari_tanggal'],
      keterangan: json['keterangan'] ?? '-',
      buktiNota: ApiClient.resolveImageUrl(json['bukti_nota']),
      diinputOlehNama: json['diinput_oleh_nama'] ?? 'Wali Ruangan',
      canEdit: json['can_edit'] ?? true,
      canDelete: json['can_delete'] ?? true,
    );
  }
}

class RiwayatPengeluaranModel {
  final int ruanganId;
  final String namaRuangan;
  final String levelNama;
  final num totalTerkumpul;
  final num totalDisetor;
  final num totalMenungguVerifikasi;
  final num totalPengeluaran;
  final num totalDitarik;
  final num sisaDiTanganWali;
  final List<PengeluaranKasItem> list;

  RiwayatPengeluaranModel({
    required this.ruanganId,
    required this.namaRuangan,
    required this.levelNama,
    required this.totalTerkumpul,
    required this.totalDisetor,
    this.totalMenungguVerifikasi = 0,
    required this.totalPengeluaran,
    this.totalDitarik = 0,
    required this.sisaDiTanganWali,
    required this.list,
  });

  factory RiwayatPengeluaranModel.fromJson(Map<String, dynamic> json) {
    return RiwayatPengeluaranModel(
      ruanganId: json['ruangan_id'] ?? 0,
      namaRuangan: json['nama_ruangan'] ?? '',
      levelNama: json['level_nama'] ?? '-',
      totalTerkumpul: json['total_terkumpul'] ?? 0,
      totalDisetor: json['total_disetor'] ?? 0,
      totalMenungguVerifikasi: json['total_menunggu_verifikasi'] ?? 0,
      totalPengeluaran: json['total_pengeluaran'] ?? 0,
      totalDitarik: json['total_ditarik'] ?? 0,
      sisaDiTanganWali: json['sisa_di_tangan_wali'] ?? 0,
      list: (json['list'] as List? ?? [])
          .map((e) => PengeluaranKasItem.fromJson(e))
          .toList(),
    );
  }
}
