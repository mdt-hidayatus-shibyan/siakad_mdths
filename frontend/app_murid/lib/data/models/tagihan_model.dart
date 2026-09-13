class ItemSppModel {
  final int id;
  final String bulan;
  final int nominal;
  final String statusBayar;
  final String? tanggalBayar;
  final String? noTransaksi;

  ItemSppModel({
    required this.id,
    required this.bulan,
    required this.nominal,
    required this.statusBayar,
    this.tanggalBayar,
    this.noTransaksi,
  });

  bool get isLunas => statusBayar == 'Lunas';
  bool get isBebas => statusBayar == 'Bebas';

  factory ItemSppModel.fromJson(Map<String, dynamic> json) {
    return ItemSppModel(
      id: json['id'] is int ? json['id'] : int.tryParse('${json['id']}') ?? 0,
      bulan: json['bulan']?.toString() ?? '-',
      nominal: json['nominal'] is int
          ? json['nominal']
          : int.tryParse('${json['nominal']}') ?? 0,
      statusBayar: json['status_bayar']?.toString() ?? 'Belum Lunas',
      tanggalBayar: json['tanggal_bayar']?.toString(),
      noTransaksi: json['no_transaksi']?.toString(),
    );
  }
}

class ItemNonSppModel {
  final int id;
  final String namaTagihan;
  final String tipe;
  final int nominal;
  final String statusBayar;
  final String? tanggalBayar;
  final String? noTransaksi;

  ItemNonSppModel({
    required this.id,
    required this.namaTagihan,
    required this.tipe,
    required this.nominal,
    required this.statusBayar,
    this.tanggalBayar,
    this.noTransaksi,
  });

  bool get isLunas => statusBayar == 'Lunas';

  factory ItemNonSppModel.fromJson(Map<String, dynamic> json) {
    return ItemNonSppModel(
      id: json['id'] is int ? json['id'] : int.tryParse('${json['id']}') ?? 0,
      namaTagihan: json['nama_tagihan']?.toString() ?? 'Tagihan',
      tipe: json['tipe']?.toString() ?? 'insidental',
      nominal: json['nominal'] is int
          ? json['nominal']
          : int.tryParse('${json['nominal']}') ?? 0,
      statusBayar: json['status_bayar']?.toString() ?? 'Belum Lunas',
      tanggalBayar: json['tanggal_bayar']?.toString(),
      noTransaksi: json['no_transaksi']?.toString(),
    );
  }
}

class RekapTagihanAnakModel {
  final int totalTagihan;
  final int totalLunas;
  final int totalTunggakan;
  final int totalSpp;
  final int totalSppLunas;
  final int totalSppTunggakan;
  final int totalNonSpp;
  final int totalNonSppLunas;
  final int totalNonSppTunggakan;
  final List<ItemSppModel> sppList;
  final List<ItemNonSppModel> nonSppList;

  RekapTagihanAnakModel({
    required this.totalTagihan,
    required this.totalLunas,
    required this.totalTunggakan,
    required this.totalSpp,
    required this.totalSppLunas,
    required this.totalSppTunggakan,
    required this.totalNonSpp,
    required this.totalNonSppLunas,
    required this.totalNonSppTunggakan,
    required this.sppList,
    required this.nonSppList,
  });

  // Getters with direct computation fallback
  int get calculatedTotalSpp =>
      sppList.fold<int>(0, (sum, item) => sum + item.nominal);
  int get calculatedTotalSppLunas => sppList
      .where((item) => item.isLunas)
      .fold<int>(0, (sum, item) => sum + item.nominal);
  int get calculatedTotalSppTunggakan =>
      (calculatedTotalSpp - calculatedTotalSppLunas).clamp(
        0,
        calculatedTotalSpp,
      );

  int get calculatedTotalNonSpp =>
      nonSppList.fold<int>(0, (sum, item) => sum + item.nominal);
  int get calculatedTotalNonSppLunas => nonSppList
      .where((item) => item.isLunas)
      .fold<int>(0, (sum, item) => sum + item.nominal);
  int get calculatedTotalNonSppTunggakan =>
      (calculatedTotalNonSpp - calculatedTotalNonSppLunas).clamp(
        0,
        calculatedTotalNonSpp,
      );

  factory RekapTagihanAnakModel.fromJson(Map<String, dynamic> json) {
    final summary = json['summary'] as Map<String, dynamic>? ?? {};
    final rawSpp = json['spp'] as List<dynamic>? ?? [];
    final rawNonSpp = json['non_spp'] as List<dynamic>? ?? [];

    final sppList = rawSpp
        .map((e) => ItemSppModel.fromJson(e as Map<String, dynamic>))
        .toList();
    final nonSppList = rawNonSpp
        .map((e) => ItemNonSppModel.fromJson(e as Map<String, dynamic>))
        .toList();

    final defaultSpp = sppList.fold<int>(0, (sum, item) => sum + item.nominal);
    final defaultSppLunas = sppList
        .where((item) => item.isLunas)
        .fold<int>(0, (sum, item) => sum + item.nominal);
    final defaultNonSpp = nonSppList.fold<int>(
      0,
      (sum, item) => sum + item.nominal,
    );
    final defaultNonSppLunas = nonSppList
        .where((item) => item.isLunas)
        .fold<int>(0, (sum, item) => sum + item.nominal);

    return RekapTagihanAnakModel(
      totalTagihan: summary['total_tagihan'] is int
          ? summary['total_tagihan']
          : int.tryParse('${summary['total_tagihan']}') ??
                (defaultSpp + defaultNonSpp),
      totalLunas: summary['total_lunas'] is int
          ? summary['total_lunas']
          : int.tryParse('${summary['total_lunas']}') ??
                (defaultSppLunas + defaultNonSppLunas),
      totalTunggakan: summary['total_tunggakan'] is int
          ? summary['total_tunggakan']
          : int.tryParse('${summary['total_tunggakan']}') ?? 0,
      totalSpp: summary['total_spp'] is int
          ? summary['total_spp']
          : int.tryParse('${summary['total_spp']}') ?? defaultSpp,
      totalSppLunas: summary['total_spp_lunas'] is int
          ? summary['total_spp_lunas']
          : int.tryParse('${summary['total_spp_lunas']}') ?? defaultSppLunas,
      totalSppTunggakan: summary['total_spp_tunggakan'] is int
          ? summary['total_spp_tunggakan']
          : int.tryParse('${summary['total_spp_tunggakan']}') ??
                (defaultSpp - defaultSppLunas).clamp(0, defaultSpp),
      totalNonSpp: summary['total_non_spp'] is int
          ? summary['total_non_spp']
          : int.tryParse('${summary['total_non_spp']}') ?? defaultNonSpp,
      totalNonSppLunas: summary['total_non_spp_lunas'] is int
          ? summary['total_non_spp_lunas']
          : int.tryParse('${summary['total_non_spp_lunas']}') ??
                defaultNonSppLunas,
      totalNonSppTunggakan: summary['total_non_spp_tunggakan'] is int
          ? summary['total_non_spp_tunggakan']
          : int.tryParse('${summary['total_non_spp_tunggakan']}') ??
                (defaultNonSpp - defaultNonSppLunas).clamp(0, defaultNonSpp),
      sppList: sppList,
      nonSppList: nonSppList,
    );
  }
}

class ItemTagihanWaliModel {
  final int id;
  final String namaTagihan;
  final String kategori;
  final int nominal;
  final String statusBayar;
  final String? tanggalBayar;
  final String? noTransaksi;
  final String? metodePembayaran;
  final String? keterangan;
  final String? tahunPelajaran;

  ItemTagihanWaliModel({
    required this.id,
    required this.namaTagihan,
    required this.kategori,
    required this.nominal,
    required this.statusBayar,
    this.tanggalBayar,
    this.noTransaksi,
    this.metodePembayaran,
    this.keterangan,
    this.tahunPelajaran,
  });

  bool get isLunas => statusBayar == 'Lunas';

  factory ItemTagihanWaliModel.fromJson(Map<String, dynamic> json) {
    return ItemTagihanWaliModel(
      id: json['id'] is int ? json['id'] : int.tryParse('${json['id']}') ?? 0,
      namaTagihan: json['nama_tagihan']?.toString() ?? 'Tagihan Keluarga',
      kategori: json['kategori']?.toString() ?? 'Wali Murid',
      nominal: json['nominal'] is int
          ? json['nominal']
          : int.tryParse('${json['nominal']}') ?? 0,
      statusBayar: json['status_bayar']?.toString() ?? 'Belum Lunas',
      tanggalBayar: json['tanggal_bayar']?.toString(),
      noTransaksi: json['no_transaksi']?.toString(),
      metodePembayaran: json['metode_pembayaran']?.toString(),
      keterangan: json['keterangan']?.toString(),
      tahunPelajaran: json['tahun_pelajaran']?.toString(),
    );
  }
}

class RekapTagihanWaliModel {
  final int totalTagihan;
  final int totalLunas;
  final int totalTunggakan;
  final List<ItemTagihanWaliModel> items;

  RekapTagihanWaliModel({
    required this.totalTagihan,
    required this.totalLunas,
    required this.totalTunggakan,
    required this.items,
  });

  int get calculatedTotal =>
      items.fold<int>(0, (sum, item) => sum + item.nominal);
  int get calculatedTotalLunas => items
      .where((item) => item.isLunas)
      .fold<int>(0, (sum, item) => sum + item.nominal);
  int get calculatedTotalTunggakan =>
      (calculatedTotal - calculatedTotalLunas).clamp(0, calculatedTotal);

  factory RekapTagihanWaliModel.fromJson(Map<String, dynamic> json) {
    final summary = json['summary'] as Map<String, dynamic>? ?? {};
    final rawItems = json['items'] as List<dynamic>? ?? [];

    final items = rawItems
        .map((e) => ItemTagihanWaliModel.fromJson(e as Map<String, dynamic>))
        .toList();

    final defaultTotal = items.fold<int>(0, (sum, item) => sum + item.nominal);
    final defaultLunas = items
        .where((item) => item.isLunas)
        .fold<int>(0, (sum, item) => sum + item.nominal);

    return RekapTagihanWaliModel(
      totalTagihan: summary['total_tagihan'] is int
          ? summary['total_tagihan']
          : int.tryParse('${summary['total_tagihan']}') ?? defaultTotal,
      totalLunas: summary['total_lunas'] is int
          ? summary['total_lunas']
          : int.tryParse('${summary['total_lunas']}') ?? defaultLunas,
      totalTunggakan: summary['total_tunggakan'] is int
          ? summary['total_tunggakan']
          : int.tryParse('${summary['total_tunggakan']}') ??
                (defaultTotal - defaultLunas).clamp(0, defaultTotal),
      items: items,
    );
  }
}
