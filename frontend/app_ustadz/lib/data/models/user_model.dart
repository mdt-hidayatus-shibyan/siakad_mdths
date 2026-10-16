import '../../core/network/api_client.dart';

class UserModel {
  final int id;
  final int? ustadzId;
  final String name;
  final String? email;
  final String? username;
  final String role;
  final String? photo;
  final String tahunPelajaran;
  final bool isWaliRuangan;
  final String? ruanganWali;
  final int? ruanganWaliId;
  final bool isPanitiaImni;
  final String? jabatanPanitiaImni;
  final String? noSkPanitiaImni;
  final bool isPanitiaIkhtibar;
  final String? ikhtibarKe;
  final String? namaKegiatanIkhtibar;
  final String? kelompokJabatanIkhtibar;
  final String? jabatanIkhtibar;
  final String? namaDivisiIkhtibar;
  final int? divisiIkhtibarId;
  final String? kodeUstadz;
  final String? nigm;
  final String? nik;
  final String? jenisKelamin;
  final String? tempatLahir;
  final String? tanggalLahir;
  final String? alamat;
  final String? noHp;
  final int? tahunMulaiMengajar;
  final String? tandaTangan;

  bool get isBphIkhtibar =>
      isPanitiaIkhtibar && (kelompokJabatanIkhtibar?.toUpperCase() == 'BPH');
  bool get isDivisiIkhtibar =>
      isPanitiaIkhtibar && (kelompokJabatanIkhtibar?.toUpperCase() == 'DIVISI');
  String get ikhtibarTitle => 'Kepanitiaan Ikhtibar ke-${ikhtibarKe ?? ""}';

  UserModel({
    required this.id,
    this.ustadzId,
    required this.name,
    this.email,
    this.username,
    required this.role,
    this.photo,
    required this.tahunPelajaran,
    required this.isWaliRuangan,
    this.ruanganWali,
    this.ruanganWaliId,
    this.isPanitiaImni = false,
    this.jabatanPanitiaImni,
    this.noSkPanitiaImni,
    this.isPanitiaIkhtibar = false,
    this.ikhtibarKe,
    this.namaKegiatanIkhtibar,
    this.kelompokJabatanIkhtibar,
    this.jabatanIkhtibar,
    this.namaDivisiIkhtibar,
    this.divisiIkhtibarId,
    this.kodeUstadz,
    this.nigm,
    this.nik,
    this.jenisKelamin,
    this.tempatLahir,
    this.tanggalLahir,
    this.alamat,
    this.noHp,
    this.tahunMulaiMengajar,
    this.tandaTangan,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] ?? json['user_id'] ?? 0,
      ustadzId: json['ustadz_id'],
      name: json['name'] ?? json['nama_lengkap'] ?? 'Ustadz',
      email: json['email'],
      username: json['username'],
      role: json['role'] ?? 'ustadz',
      photo: ApiClient.resolveImageUrl(json['photo'] ?? json['foto']),
      tahunPelajaran: json['tahun_pelajaran'] ?? '1447/1448 H | 2026/2027 M',
      isWaliRuangan: json['is_wali_ruangan'] ?? json['is_wali'] ?? false,
      ruanganWali: json['ruangan_wali'],
      ruanganWaliId: json['ruangan_wali_id'],
      isPanitiaImni: json['is_panitia_imni'] ?? (json['panitia_imni']?['is_panitia'] ?? false),
      jabatanPanitiaImni: json['jabatan_panitia_imni'] ?? json['panitia_imni']?['jabatan'],
      noSkPanitiaImni: json['no_sk_panitia_imni'] ?? json['panitia_imni']?['no_sk'],
      isPanitiaIkhtibar: json['is_panitia_ikhtibar'] ?? (json['panitia_ikhtibar'] != null),
      ikhtibarKe: json['ikhtibar_ke'] ?? json['panitia_ikhtibar']?['ikhtibar_ke'],
      namaKegiatanIkhtibar: json['nama_kegiatan_ikhtibar'] ?? json['panitia_ikhtibar']?['nama_kegiatan'],
      kelompokJabatanIkhtibar: json['kelompok_jabatan_ikhtibar'] ?? json['panitia_ikhtibar']?['kelompok_jabatan'],
      jabatanIkhtibar: json['jabatan_ikhtibar'] ?? json['panitia_ikhtibar']?['jabatan'],
      namaDivisiIkhtibar: json['nama_divisi_ikhtibar'] ?? json['panitia_ikhtibar']?['nama_divisi'],
      divisiIkhtibarId: json['divisi_ikhtibar_id'] ?? json['panitia_ikhtibar']?['divisi_id'],
      kodeUstadz: json['kode_ustadz'] ?? json['kode'],
      nigm: json['nigm'],
      nik: json['nik'],
      jenisKelamin: json['jenis_kelamin'] ?? 'L',
      tempatLahir: json['tempat_lahir'],
      tanggalLahir: json['tanggal_lahir'],
      alamat: json['alamat'],
      noHp: json['no_hp'],
      tahunMulaiMengajar: json['tahun_mulai_mengajar'] != null
          ? int.tryParse(json['tahun_mulai_mengajar'].toString())
          : null,
      tandaTangan: ApiClient.resolveImageUrl(json['tanda_tangan']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'ustadz_id': ustadzId,
      'name': name,
      'email': email,
      'username': username,
      'role': role,
      'photo': photo,
      'tahun_pelajaran': tahunPelajaran,
      'is_wali_ruangan': isWaliRuangan,
      'ruangan_wali': ruanganWali,
      'ruangan_wali_id': ruanganWaliId,
      'is_panitia_imni': isPanitiaImni,
      'jabatan_panitia_imni': jabatanPanitiaImni,
      'no_sk_panitia_imni': noSkPanitiaImni,
      'is_panitia_ikhtibar': isPanitiaIkhtibar,
      'ikhtibar_ke': ikhtibarKe,
      'nama_kegiatan_ikhtibar': namaKegiatanIkhtibar,
      'kelompok_jabatan_ikhtibar': kelompokJabatanIkhtibar,
      'jabatan_ikhtibar': jabatanIkhtibar,
      'nama_divisi_ikhtibar': namaDivisiIkhtibar,
      'divisi_ikhtibar_id': divisiIkhtibarId,
      'kode_ustadz': kodeUstadz,
      'nigm': nigm,
      'nik': nik,
      'jenis_kelamin': jenisKelamin,
      'tempat_lahir': tempatLahir,
      'tanggal_lahir': tanggalLahir,
      'alamat': alamat,
      'no_hp': noHp,
      'tahun_mulai_mengajar': tahunMulaiMengajar,
      'tanda_tangan': tandaTangan,
    };
  }

  UserModel copyWith({
    String? name,
    String? email,
    String? username,
    String? photo,
    bool setPhotoNull = false,
    bool? isPanitiaImni,
    String? jabatanPanitiaImni,
    String? noSkPanitiaImni,
    bool? isPanitiaIkhtibar,
    String? ikhtibarKe,
    String? namaKegiatanIkhtibar,
    String? kelompokJabatanIkhtibar,
    String? jabatanIkhtibar,
    String? namaDivisiIkhtibar,
    int? divisiIkhtibarId,
    String? nigm,
    String? nik,
    String? jenisKelamin,
    String? tempatLahir,
    String? tanggalLahir,
    String? alamat,
    String? noHp,
    int? tahunMulaiMengajar,
    String? tandaTangan,
  }) {
    return UserModel(
      id: id,
      ustadzId: ustadzId,
      name: name ?? this.name,
      email: email ?? this.email,
      username: username ?? this.username,
      role: role,
      photo: setPhotoNull ? null : (photo ?? this.photo),
      tahunPelajaran: tahunPelajaran,
      isWaliRuangan: isWaliRuangan,
      ruanganWali: ruanganWali,
      ruanganWaliId: ruanganWaliId,
      isPanitiaImni: isPanitiaImni ?? this.isPanitiaImni,
      jabatanPanitiaImni: jabatanPanitiaImni ?? this.jabatanPanitiaImni,
      noSkPanitiaImni: noSkPanitiaImni ?? this.noSkPanitiaImni,
      isPanitiaIkhtibar: isPanitiaIkhtibar ?? this.isPanitiaIkhtibar,
      ikhtibarKe: ikhtibarKe ?? this.ikhtibarKe,
      namaKegiatanIkhtibar:
          namaKegiatanIkhtibar ?? this.namaKegiatanIkhtibar,
      kelompokJabatanIkhtibar:
          kelompokJabatanIkhtibar ?? this.kelompokJabatanIkhtibar,
      jabatanIkhtibar: jabatanIkhtibar ?? this.jabatanIkhtibar,
      namaDivisiIkhtibar: namaDivisiIkhtibar ?? this.namaDivisiIkhtibar,
      divisiIkhtibarId: divisiIkhtibarId ?? this.divisiIkhtibarId,
      kodeUstadz: kodeUstadz,
      nigm: nigm ?? this.nigm,
      nik: nik ?? this.nik,
      jenisKelamin: jenisKelamin ?? this.jenisKelamin,
      tempatLahir: tempatLahir ?? this.tempatLahir,
      tanggalLahir: tanggalLahir ?? this.tanggalLahir,
      alamat: alamat ?? this.alamat,
      noHp: noHp ?? this.noHp,
      tahunMulaiMengajar: tahunMulaiMengajar ?? this.tahunMulaiMengajar,
      tandaTangan: tandaTangan ?? this.tandaTangan,
    );
  }
}

class PengaturanKasModel {
  final int ruanganId;
  final String namaRuangan;
  final int nominalLaki;
  final int nominalPerempuan;

  PengaturanKasModel({
    required this.ruanganId,
    required this.namaRuangan,
    required this.nominalLaki,
    required this.nominalPerempuan,
  });

  factory PengaturanKasModel.fromJson(Map<String, dynamic> json) {
    return PengaturanKasModel(
      ruanganId: json['ruangan_id'] ?? 0,
      namaRuangan: json['nama_ruangan'] ?? 'Kelas Binaan',
      nominalLaki: json['nominal_laki'] ?? 0,
      nominalPerempuan: json['nominal_perempuan'] ?? 0,
    );
  }
}
