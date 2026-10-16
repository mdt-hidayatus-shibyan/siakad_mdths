class KasirIkhtibarRingkasanModel {
  final int targetNominal;
  final int totalMasuk;
  final int totalSisa;
  final int totalKk;
  final int totalLunas;
  final int totalBelumLunas;
  final int totalCicilan;
  final double persentaseKelunasan;
  final List<KampungOptionItem> daftarKampung;
  final String ikhtibarKe;
  final List<RekapBphItem> rekapBph;
  final RekapTabunganModel? rekapTabungan;

  KasirIkhtibarRingkasanModel({
    required this.targetNominal,
    required this.totalMasuk,
    required this.totalSisa,
    required this.totalKk,
    required this.totalLunas,
    required this.totalBelumLunas,
    required this.totalCicilan,
    required this.persentaseKelunasan,
    required this.daftarKampung,
    required this.ikhtibarKe,
    this.rekapBph = const [],
    this.rekapTabungan,
  });

  factory KasirIkhtibarRingkasanModel.fromJson(Map<String, dynamic> json) {
    final list = json['daftar_kampung'] as List? ?? [];
    final bphList = json['rekap_bph'] as List? ?? [];
    return KasirIkhtibarRingkasanModel(
      targetNominal: json['target_nominal'] ?? 0,
      totalMasuk: json['total_masuk'] ?? 0,
      totalSisa: json['total_sisa'] ?? 0,
      totalKk: json['total_kk'] ?? 0,
      totalLunas: json['total_lunas'] ?? 0,
      totalBelumLunas: json['total_belum_lunas'] ?? 0,
      totalCicilan: json['total_cicilan'] ?? 0,
      persentaseKelunasan: (json['persentase_kelunasan'] is num)
          ? (json['persentase_kelunasan'] as num).toDouble()
          : 0.0,
      daftarKampung: list.map((e) => KampungOptionItem.fromJson(e)).toList(),
      ikhtibarKe: json['ikhtibar_ke'] ?? '52 Th',
      rekapBph: bphList.map((e) => RekapBphItem.fromJson(e)).toList(),
      rekapTabungan: json['rekap_tabungan'] != null
          ? RekapTabunganModel.fromJson(json['rekap_tabungan'])
          : null,
    );
  }
}

class KampungOptionItem {
  final int id;
  final String kode;
  final String namaKampung;

  KampungOptionItem({
    required this.id,
    required this.kode,
    required this.namaKampung,
  });

  factory KampungOptionItem.fromJson(Map<String, dynamic> json) {
    return KampungOptionItem(
      id: json['id'] ?? 0,
      kode: json['kode'] ?? '',
      namaKampung: json['nama_kampung'] ?? '',
    );
  }
}

class TagihanKkIkhtibarItem {
  final int tagihanId;
  final int waliMuridId;
  final String noRegistrasi;
  final String noKk;
  final String namaKepalaKeluarga;
  final String kampung;
  final int nominalTagihan;
  final int nominalBayar;
  final int sisaTagihan;
  final String statusBayar;
  final bool hasPendingTabungan;
  final List<AnakKkItem> daftarAnak;
  final List<TabunganKkItem> daftarTabungan;
  final PembayaranTerakhirItem? pembayaranTerakhir;
  final String? kwitansiUrl;

  TagihanKkIkhtibarItem({
    required this.tagihanId,
    required this.waliMuridId,
    required this.noRegistrasi,
    required this.noKk,
    required this.namaKepalaKeluarga,
    required this.kampung,
    required this.nominalTagihan,
    required this.nominalBayar,
    required this.sisaTagihan,
    required this.statusBayar,
    this.hasPendingTabungan = false,
    required this.daftarAnak,
    required this.daftarTabungan,
    this.pembayaranTerakhir,
    this.kwitansiUrl,
  });

  factory TagihanKkIkhtibarItem.fromJson(Map<String, dynamic> json) {
    final anakList = json['daftar_anak'] as List? ?? [];
    final tabList = json['daftar_tabungan'] as List? ?? [];
    return TagihanKkIkhtibarItem(
      tagihanId: json['tagihan_id'] ?? 0,
      waliMuridId: json['wali_murid_id'] ?? 0,
      noRegistrasi: json['no_registrasi'] ?? '-',
      noKk: json['no_kk'] ?? '-',
      namaKepalaKeluarga: json['nama_kepala_keluarga'] ?? '-',
      kampung: json['kampung'] ?? '-',
      nominalTagihan: json['nominal_tagihan'] ?? 0,
      nominalBayar: json['nominal_bayar'] ?? 0,
      sisaTagihan: json['sisa_tagihan'] ?? 0,
      statusBayar: json['status_bayar'] ?? 'Belum Lunas',
      hasPendingTabungan: json['has_pending_tabungan'] ?? false,
      daftarAnak: anakList.map((e) => AnakKkItem.fromJson(e)).toList(),
      daftarTabungan: tabList.map((e) => TabunganKkItem.fromJson(e)).toList(),
      pembayaranTerakhir: json['pembayaran_terakhir'] != null
          ? PembayaranTerakhirItem.fromJson(json['pembayaran_terakhir'])
          : null,
      kwitansiUrl: json['kwitansi_url'],
    );
  }
}

class AnakKkItem {
  final int id;
  final String nama;
  final String ruangan;

  AnakKkItem({
    required this.id,
    required this.nama,
    required this.ruangan,
  });

  factory AnakKkItem.fromJson(Map<String, dynamic> json) {
    return AnakKkItem(
      id: json['id'] ?? 0,
      nama: json['nama'] ?? '-',
      ruangan: json['ruangan'] ?? '-',
    );
  }
}

class TabunganKkItem {
  final int id;
  final String nomorRekening;
  final String namaNasabah;
  final String ruangan;
  final int saldo;

  TabunganKkItem({
    required this.id,
    required this.nomorRekening,
    required this.namaNasabah,
    required this.ruangan,
    required this.saldo,
  });

  factory TabunganKkItem.fromJson(Map<String, dynamic> json) {
    return TabunganKkItem(
      id: json['id'] ?? 0,
      nomorRekening: json['nomor_rekening'] ?? '',
      namaNasabah: json['nama_nasabah'] ?? '',
      ruangan: json['ruangan'] ?? '',
      saldo: json['saldo'] ?? 0,
    );
  }
}

class PembayaranTerakhirItem {
  final int id;
  final int nominal;
  final String metode;
  final String? tanggal;
  final bool isVerified;
  final String? nomorTabungan;
  final String? namaNasabah;
  final String? ruanganNasabah;
  final String? kwitansiUrl;

  PembayaranTerakhirItem({
    this.id = 0,
    required this.nominal,
    required this.metode,
    this.tanggal,
    required this.isVerified,
    this.nomorTabungan,
    this.namaNasabah,
    this.ruanganNasabah,
    this.kwitansiUrl,
  });

  factory PembayaranTerakhirItem.fromJson(Map<String, dynamic> json) {
    return PembayaranTerakhirItem(
      id: json['id'] ?? 0,
      nominal: json['nominal'] ?? 0,
      metode: json['metode'] ?? 'Tunai',
      tanggal: json['tanggal'],
      isVerified: json['is_verified'] ?? true,
      nomorTabungan: json['nomor_tabungan'],
      namaNasabah: json['nama_nasabah'],
      ruanganNasabah: json['ruangan_nasabah'],
      kwitansiUrl: json['kwitansi_url'],
    );
  }
}

class LaporanIkhtibarRingkasanModel {
  final int targetTotal;
  final int masukTotal;
  final int sisaTotal;
  final double persentase;
  final List<RekapKampungItem> rekapKampung;
  final List<RekapMetodeItem> rekapMetode;
  final List<RekapBphItem> rekapBph;
  final RekapTabunganModel? rekapTabungan;

  LaporanIkhtibarRingkasanModel({
    required this.targetTotal,
    required this.masukTotal,
    required this.sisaTotal,
    required this.persentase,
    required this.rekapKampung,
    required this.rekapMetode,
    this.rekapBph = const [],
    this.rekapTabungan,
  });

  factory LaporanIkhtibarRingkasanModel.fromJson(Map<String, dynamic> json) {
    final kList = json['rekap_kampung'] as List? ?? [];
    final mList = json['rekap_metode'] as List? ?? [];
    final bphList = json['rekap_bph'] as List? ?? [];
    return LaporanIkhtibarRingkasanModel(
      targetTotal: json['target_total'] ?? 0,
      masukTotal: json['masuk_total'] ?? 0,
      sisaTotal: json['sisa_total'] ?? 0,
      persentase: (json['persentase'] is num)
          ? (json['persentase'] as num).toDouble()
          : 0.0,
      rekapKampung: kList.map((e) => RekapKampungItem.fromJson(e)).toList(),
      rekapMetode: mList.map((e) => RekapMetodeItem.fromJson(e)).toList(),
      rekapBph: bphList.map((e) => RekapBphItem.fromJson(e)).toList(),
      rekapTabungan: json['rekap_tabungan'] != null
          ? RekapTabunganModel.fromJson(json['rekap_tabungan'])
          : null,
    );
  }
}

class RekapKampungItem {
  final int kampungId;
  final String kode;
  final String namaKampung;
  final int totalKk;
  final int lunasCount;
  final int belumLunas;
  final int target;
  final int masuk;
  final int sisa;
  final double persentase;

  RekapKampungItem({
    required this.kampungId,
    required this.kode,
    required this.namaKampung,
    required this.totalKk,
    required this.lunasCount,
    required this.belumLunas,
    required this.target,
    required this.masuk,
    required this.sisa,
    required this.persentase,
  });

  factory RekapKampungItem.fromJson(Map<String, dynamic> json) {
    return RekapKampungItem(
      kampungId: json['kampung_id'] ?? 0,
      kode: json['kode'] ?? '',
      namaKampung: json['nama_kampung'] ?? '',
      totalKk: json['total_kk'] ?? 0,
      lunasCount: json['lunas_count'] ?? 0,
      belumLunas: json['belum_lunas'] ?? 0,
      target: json['target'] ?? 0,
      masuk: json['masuk'] ?? 0,
      sisa: json['sisa'] ?? 0,
      persentase: (json['persentase'] is num)
          ? (json['persentase'] as num).toDouble()
          : 0.0,
    );
  }
}

class RekapMetodeItem {
  final String metodePembayaran;
  final int totalNominal;
  final int totalTransaksi;

  RekapMetodeItem({
    required this.metodePembayaran,
    required this.totalNominal,
    required this.totalTransaksi,
  });

  factory RekapMetodeItem.fromJson(Map<String, dynamic> json) {
    return RekapMetodeItem(
      metodePembayaran: json['metode_pembayaran'] ?? '-',
      totalNominal: (json['total_nominal'] is num)
          ? (json['total_nominal'] as num).toInt()
          : 0,
      totalTransaksi: json['total_transaksi'] ?? 0,
    );
  }
}

class RekapBphItem {
  final int userId;
  final String nama;
  final String jabatan;
  final int totalTransaksi;
  final int totalNominal;
  final int nominalTunai;
  final int nominalTabungan;

  RekapBphItem({
    required this.userId,
    required this.nama,
    required this.jabatan,
    required this.totalTransaksi,
    required this.totalNominal,
    required this.nominalTunai,
    required this.nominalTabungan,
  });

  factory RekapBphItem.fromJson(Map<String, dynamic> json) {
    return RekapBphItem(
      userId: json['user_id'] ?? 0,
      nama: json['nama'] ?? '-',
      jabatan: json['jabatan'] ?? 'BPH',
      totalTransaksi: json['total_transaksi'] ?? 0,
      totalNominal: (json['total_nominal'] is num)
          ? (json['total_nominal'] as num).toInt()
          : 0,
      nominalTunai: (json['nominal_tunai'] is num)
          ? (json['nominal_tunai'] as num).toInt()
          : 0,
      nominalTabungan: (json['nominal_tabungan'] is num)
          ? (json['nominal_tabungan'] as num).toInt()
          : 0,
    );
  }
}

class RekapTabunganModel {
  final int totalNominal;
  final int totalTransaksi;
  final int verifiedNominal;
  final int verifiedCount;
  final int pendingNominal;
  final int pendingCount;

  RekapTabunganModel({
    required this.totalNominal,
    required this.totalTransaksi,
    required this.verifiedNominal,
    required this.verifiedCount,
    required this.pendingNominal,
    required this.pendingCount,
  });

  factory RekapTabunganModel.fromJson(Map<String, dynamic> json) {
    return RekapTabunganModel(
      totalNominal: (json['total_nominal'] is num)
          ? (json['total_nominal'] as num).toInt()
          : 0,
      totalTransaksi: json['total_transaksi'] ?? 0,
      verifiedNominal: (json['verified_nominal'] is num)
          ? (json['verified_nominal'] as num).toInt()
          : 0,
      verifiedCount: json['verified_count'] ?? 0,
      pendingNominal: (json['pending_nominal'] is num)
          ? (json['pending_nominal'] as num).toInt()
          : 0,
      pendingCount: json['pending_count'] ?? 0,
    );
  }
}

class RiwayatPembayaranIkhtibarItem {
  final int id;
  final String noTransaksi;
  final String namaPenyetor;
  final String kampung;
  final int nominal;
  final String metode;
  final String tipePembayaran;
  final int terminKe;
  final String? tanggal;
  final bool isVerified;
  final String? nomorTabungan;
  final String? namaNasabah;
  final String? ruanganNasabah;
  final String diterimaOleh;
  final String? kwitansiUrl;
  final String? kwitansiTagihanUrl;

  RiwayatPembayaranIkhtibarItem({
    required this.id,
    required this.noTransaksi,
    required this.namaPenyetor,
    required this.kampung,
    required this.nominal,
    required this.metode,
    this.tipePembayaran = 'Lunas',
    required this.terminKe,
    this.tanggal,
    required this.isVerified,
    this.nomorTabungan,
    this.namaNasabah,
    this.ruanganNasabah,
    required this.diterimaOleh,
    this.kwitansiUrl,
    this.kwitansiTagihanUrl,
  });

  factory RiwayatPembayaranIkhtibarItem.fromJson(Map<String, dynamic> json) {
    return RiwayatPembayaranIkhtibarItem(
      id: json['id'] ?? 0,
      noTransaksi: json['no_transaksi'] ?? '-',
      namaPenyetor: json['nama_penyetor'] ?? '-',
      kampung: json['kampung'] ?? '-',
      nominal: json['nominal'] ?? 0,
      metode: json['metode'] ?? 'Tunai',
      tipePembayaran: json['tipe_pembayaran'] ?? 'Lunas',
      terminKe: json['termin_ke'] ?? 1,
      tanggal: json['tanggal'],
      isVerified: json['is_verified'] ?? true,
      nomorTabungan: json['nomor_tabungan'],
      namaNasabah: json['nama_nasabah'],
      ruanganNasabah: json['ruangan_nasabah'],
      diterimaOleh: json['diterima_oleh'] ?? 'Admin',
      kwitansiUrl: json['kwitansi_url'],
      kwitansiTagihanUrl: json['kwitansi_tagihan_url'],
    );
  }
}

class PaguSpjRingkasanModel {
  final String mode; // 'BPH' or 'DIVISI'
  final int? totalPagu;
  final int? totalTersalurkan;
  final int? totalSpj;
  final int? sisaKasUtama;
  final int? totalPenarikan;
  final List<DivisiPaguItem> daftarDivisi;
  final DivisiPaguItem? divisi; // if mode == 'DIVISI'

  PaguSpjRingkasanModel({
    required this.mode,
    this.totalPagu,
    this.totalTersalurkan,
    this.totalSpj,
    this.sisaKasUtama,
    this.totalPenarikan,
    this.daftarDivisi = const [],
    this.divisi,
  });

  factory PaguSpjRingkasanModel.fromJson(Map<String, dynamic> json) {
    final mode = json['mode'] ?? 'DIVISI';
    final list = json['daftar_divisi'] as List? ?? [];
    return PaguSpjRingkasanModel(
      mode: mode,
      totalPagu: json['total_pagu'],
      totalTersalurkan: json['total_tersalurkan'],
      totalSpj: json['total_spj'],
      sisaKasUtama: json['sisa_kas_utama'],
      totalPenarikan: json['total_penarikan'],
      daftarDivisi: list.map((e) => DivisiPaguItem.fromJson(e)).toList(),
      divisi: json['divisi'] != null
          ? DivisiPaguItem.fromJson(json['divisi'])
          : null,
    );
  }
}

class DivisiPaguItem {
  final int id;
  final String kode;
  final String namaDivisi;
  final bool? isBph;
  final int? urutan;
  final int nominalPagu;
  final int danaTersalurkan;
  final int realisasiSpj;
  final int sisaPagu;
  final int sisaKasDivisi;
  final String penanggungJawab;

  DivisiPaguItem({
    required this.id,
    required this.kode,
    required this.namaDivisi,
    this.isBph,
    this.urutan,
    required this.nominalPagu,
    required this.danaTersalurkan,
    required this.realisasiSpj,
    required this.sisaPagu,
    required this.sisaKasDivisi,
    required this.penanggungJawab,
  });

  factory DivisiPaguItem.fromJson(Map<String, dynamic> json) {
    return DivisiPaguItem(
      id: json['id'] ?? 0,
      kode: json['kode'] ?? '',
      namaDivisi: json['nama_divisi'] ?? '',
      isBph: json['is_bph'],
      urutan: json['urutan'],
      nominalPagu: json['nominal_pagu'] ?? 0,
      danaTersalurkan: json['dana_tersalurkan'] ?? 0,
      realisasiSpj: json['realisasi_spj'] ?? 0,
      sisaPagu: json['sisa_pagu'] ?? 0,
      sisaKasDivisi: json['sisa_kas_divisi'] ?? 0,
      penanggungJawab: json['penanggung_jawab'] ?? 'Belum Ditentukan',
    );
  }
}

class SpjIkhtibarItem {
  final int id;
  final String kodeSpj;
  final int? divisiId;
  final String namaDivisi;
  final String judulPengeluaran;
  final String? rincianItem;
  final int nominal;
  final String? tanggalNota;
  final String namaTokoPenerima;
  final String? buktiNotaUrl;
  final String statusVerifikasi;
  final String diinputOleh;
  final String diverifikasiOleh;
  final String? catatanVerifikasi;

  SpjIkhtibarItem({
    required this.id,
    required this.kodeSpj,
    this.divisiId,
    required this.namaDivisi,
    required this.judulPengeluaran,
    this.rincianItem,
    required this.nominal,
    this.tanggalNota,
    required this.namaTokoPenerima,
    this.buktiNotaUrl,
    required this.statusVerifikasi,
    required this.diinputOleh,
    required this.diverifikasiOleh,
    this.catatanVerifikasi,
  });

  factory SpjIkhtibarItem.fromJson(Map<String, dynamic> json) {
    return SpjIkhtibarItem(
      id: json['id'] ?? 0,
      kodeSpj: json['kode_spj'] ?? '-',
      divisiId: json['divisi_id'],
      namaDivisi: json['nama_divisi'] ?? 'Sekretariat BPH',
      judulPengeluaran: json['judul_pengeluaran'] ?? '',
      rincianItem: json['rincian_item'],
      nominal: json['nominal'] ?? 0,
      tanggalNota: json['tanggal_nota'],
      namaTokoPenerima: json['nama_toko_penerima'] ?? '-',
      buktiNotaUrl: json['bukti_nota_url'],
      statusVerifikasi: json['status_verifikasi'] ?? 'Pending',
      diinputOleh: json['diinput_oleh'] ?? '-',
      diverifikasiOleh: json['diverifikasi_oleh'] ?? '-',
      catatanVerifikasi: json['catatan_verifikasi'],
    );
  }
}

