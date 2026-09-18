import '../../core/constants/api_constants.dart';

int? _parseInt(dynamic val) {
  if (val == null) return null;
  if (val is int) return val;
  if (val is num) return val.toInt();
  if (val is String) return int.tryParse(val);
  return null;
}

int _parseIntRequired(dynamic val, [int defaultValue = 0]) {
  if (val == null) return defaultValue;
  if (val is int) return val;
  if (val is num) return val.toInt();
  if (val is String) return int.tryParse(val) ?? defaultValue;
  return defaultValue;
}

class CatatanUstadzItem {
  final int id;
  final int ustadzId;
  final int? userId;
  final int? tahunPelajaranId;
  final String kategori;
  final String targetTipe; // murid, madrasah, umum
  final int? muridId;
  final int? ruanganId;
  final String judul;
  final String isiCatatan;
  final String tingkatUrgensi; // Rendah, Sedang, Tinggi, Penting / Mendesak
  final String? lampiranFoto;
  final String? dibacaAdminPada;
  final String createdAt;
  final String? updatedAt;

  // Relations
  final String? muridNama;
  final String? muridNism;
  final String? ruanganNama;

  CatatanUstadzItem({
    required this.id,
    required this.ustadzId,
    this.userId,
    this.tahunPelajaranId,
    required this.kategori,
    required this.targetTipe,
    this.muridId,
    this.ruanganId,
    required this.judul,
    required this.isiCatatan,
    required this.tingkatUrgensi,
    this.lampiranFoto,
    this.dibacaAdminPada,
    required this.createdAt,
    this.updatedAt,
    this.muridNama,
    this.muridNism,
    this.ruanganNama,
  });

  String? get lampiranFotoUrl => ApiConstants.formatImageUrl(lampiranFoto);

  bool get isSudahDibacaAdmin =>
      dibacaAdminPada != null && dibacaAdminPada!.isNotEmpty;

  factory CatatanUstadzItem.fromJson(Map<String, dynamic> json) {
    final muridObj = json['murid'] as Map<String, dynamic>?;
    final ruanganObj = json['ruangan'] as Map<String, dynamic>?;

    return CatatanUstadzItem(
      id: _parseIntRequired(json['id']),
      ustadzId: _parseIntRequired(json['ustadz_id']),
      userId: _parseInt(json['user_id']),
      tahunPelajaranId: _parseInt(json['tahun_pelajaran_id']),
      kategori: json['kategori']?.toString() ?? 'Keluhan Murid',
      targetTipe: json['target_tipe']?.toString() ?? 'murid',
      muridId: _parseInt(json['murid_id']),
      ruanganId: _parseInt(json['ruangan_id']),
      judul: json['judul']?.toString() ?? '',
      isiCatatan: json['isi_catatan']?.toString() ?? '',
      tingkatUrgensi: json['tingkat_urgensi']?.toString() ?? 'Sedang',
      lampiranFoto: json['lampiran_foto']?.toString(),
      dibacaAdminPada: json['dibaca_admin_pada']?.toString(),
      createdAt: json['created_at']?.toString() ?? '',
      updatedAt: json['updated_at']?.toString(),
      muridNama: muridObj?['nama_lengkap']?.toString(),
      muridNism: muridObj?['nism']?.toString(),
      ruanganNama: ruanganObj?['nama_ruangan']?.toString(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'ustadz_id': ustadzId,
      'user_id': userId,
      'tahun_pelajaran_id': tahunPelajaranId,
      'kategori': kategori,
      'target_tipe': targetTipe,
      'murid_id': muridId,
      'ruangan_id': ruanganId,
      'judul': judul,
      'isi_catatan': isiCatatan,
      'tingkat_urgensi': tingkatUrgensi,
      'lampiran_foto': lampiranFoto,
      'dibaca_admin_pada': dibacaAdminPada,
      'created_at': createdAt,
    };
  }
}

class MuridOptionItem {
  final int id;
  final String nism;
  final String namaLengkap;
  final String ruangan;
  final int? ruanganId;

  MuridOptionItem({
    required this.id,
    required this.nism,
    required this.namaLengkap,
    required this.ruangan,
    this.ruanganId,
  });

  factory MuridOptionItem.fromJson(Map<String, dynamic> json) {
    return MuridOptionItem(
      id: _parseIntRequired(json['id']),
      nism: json['nism']?.toString() ?? '',
      namaLengkap: json['nama_lengkap']?.toString() ?? '',
      ruangan: json['ruangan']?.toString() ?? '-',
      ruanganId: _parseInt(json['ruangan_id']),
    );
  }
}

class RuanganOptionItem {
  final int id;
  final String namaRuangan;

  RuanganOptionItem({required this.id, required this.namaRuangan});

  factory RuanganOptionItem.fromJson(Map<String, dynamic> json) {
    return RuanganOptionItem(
      id: _parseIntRequired(json['id']),
      namaRuangan: json['nama_ruangan']?.toString() ?? '',
    );
  }
}

class CatatanOptionsData {
  final List<MuridOptionItem> murids;
  final List<RuanganOptionItem> ruangans;
  final List<String> kategoriList;
  final List<String> urgensiList;

  CatatanOptionsData({
    required this.murids,
    required this.ruangans,
    required this.kategoriList,
    required this.urgensiList,
  });

  factory CatatanOptionsData.fromJson(Map<String, dynamic> json) {
    return CatatanOptionsData(
      murids: (json['murids'] as List? ?? [])
          .map((m) => MuridOptionItem.fromJson(m as Map<String, dynamic>))
          .toList(),
      ruangans: (json['ruangans'] as List? ?? [])
          .map((r) => RuanganOptionItem.fromJson(r as Map<String, dynamic>))
          .toList(),
      kategoriList: (json['kategori_list'] as List? ?? [])
          .map((e) => e.toString())
          .toList(),
      urgensiList: (json['urgensi_list'] as List? ?? [])
          .map((e) => e.toString())
          .toList(),
    );
  }
}
