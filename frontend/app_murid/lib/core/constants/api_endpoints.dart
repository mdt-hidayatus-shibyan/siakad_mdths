class ApiEndpoints {
  ApiEndpoints._();

  // Auth & Session
  static const String loginWali = '/wali/login';
  static const String logout = '/logout';
  static const String profile = '/profile';

  // Wali Dashboard & Monitoring
  static const String dashboard = '/wali/dashboard';
  static const String detailAnak = '/wali/anak'; // + /{id}
  static const String tagihanAnak = '/wali/tagihan'; // + /{id}
  static const String tagihanWali = '/wali/tagihan-wali';
  static const String presensiAnak = '/wali/presensi'; // + /{id}
  static const String pelanggaranAnak = '/wali/pelanggaran'; // + /{id}
  static const String nilaiAnak = '/wali/nilai'; // + /{id}
  static const String jadwalAnak = '/wali/jadwal'; // + /{id}
  static const String kenaikanAnak = '/wali/kenaikan'; // + /{id}
  static const String dokumenAnak = '/wali/dokumen'; // + /{id}
  static const String tabunganAnak = '/wali/tabungan'; // + /{id}
  static const String tabunganKomplain = '/wali/tabungan/komplain';
  static const String koperasiAnak = '/wali/koperasi'; // + /{id}
  static const String kasRuanganAnak = '/wali/kas-ruangan'; // + /{id}

  // Umum / Bantuan
  static const String pengumuman = '/wali/pengumuman';
  static const String bantuanKontak = '/bantuan/kontak';
  static const String kalendar = '/kalendar-pendidikan';
}
