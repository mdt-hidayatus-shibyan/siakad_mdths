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
