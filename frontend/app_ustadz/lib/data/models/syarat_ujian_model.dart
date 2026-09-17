import 'presensi_ujian_model.dart';

class MuridSyaratUjianItem {
  final int muridId;
  final String nism;
  final String nama;
  final String jenisKelamin;
  final bool isLocked;
  final String? lockReason;
  final bool hasDispensasi;
  final String? alasanDispensasi;
  final String? dispensasiOleh;
  final String statusSyarat; // 'Lunas', 'Dispensasi', 'Terkunci'

  MuridSyaratUjianItem({
    required this.muridId,
    required this.nism,
    required this.nama,
    required this.jenisKelamin,
    required this.isLocked,
    this.lockReason,
    required this.hasDispensasi,
    this.alasanDispensasi,
    this.dispensasiOleh,
    required this.statusSyarat,
  });

  factory MuridSyaratUjianItem.fromJson(Map<String, dynamic> json) {
    return MuridSyaratUjianItem(
      muridId: json['murid_id'] ?? 0,
      nism: json['nism'] ?? '-',
      nama: json['nama'] ?? '',
      jenisKelamin: json['jenis_kelamin'] ?? 'L',
      isLocked: json['is_locked'] ?? false,
      lockReason: json['lock_reason'],
      hasDispensasi: json['has_dispensasi'] ?? false,
      alasanDispensasi: json['alasan_dispensasi'],
      dispensasiOleh: json['dispensasi_oleh'],
      statusSyarat:
          json['status_syarat'] ??
          (json['is_locked'] == true ? 'Terkunci' : 'Lunas'),
    );
  }
}

class SyaratUjianSummary {
  final int total;
  final int lunas;
  final int dispensasi;
  final int terkunci;

  SyaratUjianSummary({
    this.total = 0,
    this.lunas = 0,
    this.dispensasi = 0,
    this.terkunci = 0,
  });

  factory SyaratUjianSummary.fromJson(Map<String, dynamic> json) {
    return SyaratUjianSummary(
      total: json['total'] ?? 0,
      lunas: json['lunas'] ?? 0,
      dispensasi: json['dispensasi'] ?? 0,
      terkunci: json['terkunci'] ?? 0,
    );
  }
}

class SyaratUjianDataResponse {
  final List<RuanganOptionItem> daftarRuangan;
  final int? selectedRuanganId;
  final String selectedRuanganNama;
  final String namaLevel;
  final bool isWaliRuangan;
  final String waliRuanganNama;
  final List<UjianOptionItem> daftarUjian;
  final int? selectedUjianId;
  final SyaratUjianSummary summary;
  final List<MuridSyaratUjianItem> muridList;

  SyaratUjianDataResponse({
    required this.daftarRuangan,
    this.selectedRuanganId,
    this.selectedRuanganNama = '',
    this.namaLevel = '',
    this.isWaliRuangan = false,
    this.waliRuanganNama = '-',
    required this.daftarUjian,
    this.selectedUjianId,
    required this.summary,
    required this.muridList,
  });

  factory SyaratUjianDataResponse.fromJson(Map<String, dynamic> json) {
    return SyaratUjianDataResponse(
      daftarRuangan:
          (json['daftar_ruangan'] as List<dynamic>?)
              ?.map(
                (e) => RuanganOptionItem.fromJson(e as Map<String, dynamic>),
              )
              .toList() ??
          [],
      selectedRuanganId: json['selected_ruangan_id'],
      selectedRuanganNama: json['selected_ruangan_nama'] ?? '',
      namaLevel: json['nama_level'] ?? '',
      isWaliRuangan: json['is_wali_ruangan'] ?? false,
      waliRuanganNama: json['wali_ruangan_nama'] ?? '-',
      daftarUjian:
          (json['daftar_ujian'] as List<dynamic>?)
              ?.map((e) => UjianOptionItem.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      selectedUjianId: json['selected_ujian_id'],
      summary: json['summary'] != null
          ? SyaratUjianSummary.fromJson(json['summary'] as Map<String, dynamic>)
          : SyaratUjianSummary(),
      muridList:
          (json['murid_list'] as List<dynamic>?)
              ?.map(
                (e) => MuridSyaratUjianItem.fromJson(e as Map<String, dynamic>),
              )
              .toList() ??
          [],
    );
  }
}
