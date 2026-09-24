class ApiConstants {
  ApiConstants._();

  // Backend API URL
  // static const String defaultBaseUrl = 'http://10.32.158.1:8000/api';
  // static const String defaultBaseUrl =
  //     'http://127.0.0.1:8000/api'; // Untuk browser / localhost
  static const String defaultBaseUrl =
      'https://mdt-hidayatus-shibyan.sch.id/api'; // Untuk produksi / VPS

  static const String login = '/login';
  static const String logout = '/logout';
  static const String profile = '/profile';
  static const String updatePassword = '/profile/update-password';
  static const String updateAccount = '/profile/update-account';
  static const String ustadzBiodata = '/ustadz/biodata';
  static const String ustadzUpdateBiodata = '/ustadz/update-biodata';
  static const String ustadzUpdateFoto = '/ustadz/update-foto';
  static const String ustadzUpdateTandaTangan = '/ustadz/update-tanda-tangan';
  static const String forgotPassword = '/forgot-password';
  static const String verifyOtp = '/verify-otp';
  static const String resetPassword = '/reset-password';

  static const String dashboard = '/dashboard';
  static const String pengumuman = '/pengumuman';
  static const String kalender = '/kalendar-pendidikan';

  static const String presensiSesi = '/presensi-murid/sesi';
  static const String presensiMurid = '/presensi-murid/murid';
  static const String presensiSimpan = '/presensi-murid/simpan';
  static const String presensiUstadzSesi = '/presensi-ustadz/sesi';
  static const String presensiUstadzCheckin = '/presensi-ustadz/checkin';
  static const String presensiUstadzDaftar = '/presensi-ustadz/daftar-ustadz';
  static const String presensiUstadzRiwayat = '/presensi-ustadz/riwayat';

  static const String pelanggaranReferensi = '/pelanggaran/referensi';
  static const String pelanggaranRuanganList = '/pelanggaran/ruangan-list';
  static const String pelanggaranMuridByRuangan =
      '/pelanggaran/murid-by-ruangan';
  static const String pelanggaranHarian = '/pelanggaran/harian';
  static const String pelanggaranRiwayat = '/pelanggaran/riwayat';
  static const String pelanggaranSimpan = '/pelanggaran/simpan';
  static const String pelanggaranSimpanMassal = '/pelanggaran/simpan-massal';

  static const String ujianList = '/ujian/list';
  static const String ujianMapelJadwal = '/ujian/mapel-jadwal';
  static const String ujianInputData = '/ujian/input-data';
  static const String ujianSimpanNilai = '/ujian/simpan-nilai';
  static const String ujianBeriDispensasi = '/ujian/beri-dispensasi';
  static const String ujianBatalDispensasi = '/ujian/batal-dispensasi';
  static const String ujianLeger = '/ujian/leger';
  static const String ujianSyaratUjian = '/ujian/syarat-ujian';
  static const String presensiUjianData = '/presensi-ujian/data';
  static const String presensiUjianSimpan = '/presensi-ujian/simpan';

  static const String kasRingkasan = '/kas-ruangan/ringkasan';
  static const String kasMuridList = '/kas-ruangan/murid-list';
  static const String kasSimpanBayar = '/kas-ruangan/simpan-bayar';
  static const String kasUpdateBayar = '/kas-ruangan/update-bayar';
  static const String kasHapusBayar = '/kas-ruangan/hapus-bayar';
  static const String kasRiwayatMurid = '/kas-ruangan/riwayat-murid';
  static const String kasPengaturan = '/kas-ruangan/pengaturan';
  static const String kasSetoranRiwayat = '/kas-ruangan/setoran/riwayat';
  static const String kasSetoranPenerima = '/kas-ruangan/setoran/penerima-list';
  static const String kasSetoranSimpan = '/kas-ruangan/setoran/simpan';
  static const String kasSetoranUpdate = '/kas-ruangan/setoran/update';
  static const String kasSetoranHapus = '/kas-ruangan/setoran/hapus';

  static const String tagihanSppRingkasan = '/tagihan/spp/ringkasan';
  static const String tagihanSppMuridList = '/tagihan/spp/murid-list';
  static const String tagihanSppKartu = '/tagihan/spp/kartu';

  static const String tagihanNonSppMasterList = '/tagihan/non-spp/master-list';
  static const String tagihanNonSppRingkasan = '/tagihan/non-spp/ringkasan';
  static const String tagihanNonSppMuridList = '/tagihan/non-spp/murid-list';
  static const String tagihanNonSppBayar = '/tagihan/non-spp/bayar';
  static const String tagihanNonSppBatalBayar = '/tagihan/non-spp/batal-bayar';

  static const String muridRuangan = '/murid/ruangan';

  static const String mataPelajaran = '/mata-pelajaran';
  static const String jadwalPelajaran = '/jadwal-pelajaran';
  static const String jadwalUjian = '/jadwal-ujian';
  static const String referensiPelanggaranMaster = '/referensi-pelanggaran';

  // Laporan Terpadu
  static const String laporanPresensiMurid = '/laporan/presensi-murid';
  static const String laporanPresensiUstadz = '/laporan/presensi-ustadz';
  static const String laporanPelanggaranMurid = '/laporan/pelanggaran-murid';
  static const String laporanUjian = '/laporan/ujian';
  static const String laporanKenaikanKelas = '/laporan/kenaikan-kelas';

  // Bantuan & Hubungi Admin
  static const String bantuanKontak = '/bantuan/kontak';
  static const String bantuanLaporan = '/bantuan/laporan';
  static const String bantuanRiwayat = '/bantuan/riwayat';
  static const String appVersion = '/app-version';

  // Tabungan Madrasah
  static const String tabunganUstadzRekening = '/tabungan/ustadz/rekening';
  static const String tabunganRuangan = '/tabungan/ruangan';
  static const String tabunganDetail = '/tabungan/detail';
  static const String tabunganSetor = '/tabungan/setor';
  static const String tabunganCari = '/tabungan/cari';

  // Catatan & Keluhan Ustadz
  static const String catatanUstadz = '/catatan-ustadz';
  static const String catatanUstadzOptions = '/catatan-ustadz/options';

  /// Format URL foto/gambar dari backend agar cocok dengan host yang aktif.
  /// Menghindari kegagalan koneksi ketika backend mengembalikan host 'localhost' / '127.0.0.1'
  /// saat diakses dari HP fisik atau emulator Android.
  static String? formatImageUrl(String? rawUrl, [String? customBaseUrl]) {
    if (rawUrl == null || rawUrl.trim().isEmpty) return null;

    final trimmed = rawUrl.trim();
    final activeBase = (customBaseUrl != null && customBaseUrl.isNotEmpty)
        ? customBaseUrl
        : defaultBaseUrl;
    final baseUri = Uri.tryParse(activeBase);
    if (baseUri == null || !baseUri.hasScheme || !baseUri.hasAuthority) {
      return trimmed;
    }

    final targetHostAuthority = '${baseUri.scheme}://${baseUri.authority}';

    // Jika URL adalah URL penuh (misalnya http://localhost:8000/storage/... atau https://domain/storage/...)
    final rawUri = Uri.tryParse(trimmed);
    if (rawUri != null && rawUri.hasScheme && rawUri.hasAuthority) {
      if (rawUri.host == 'localhost' ||
          rawUri.host == '127.0.0.1' ||
          rawUri.host != baseUri.host) {
        final pathAndQuery =
            '${rawUri.path}${rawUri.hasQuery ? '?${rawUri.query}' : ''}';
        return '$targetHostAuthority$pathAndQuery';
      }
      return trimmed;
    }

    // Jika path relatif (misalnya 'uploads/murid/...' atau '/storage/uploads/...')
    if (!trimmed.startsWith('http://') && !trimmed.startsWith('https://')) {
      final cleanPath = trimmed.startsWith('/') ? trimmed : '/$trimmed';
      if (!cleanPath.startsWith('/storage/')) {
        return '$targetHostAuthority/storage$cleanPath';
      }
      return '$targetHostAuthority$cleanPath';
    }

    return trimmed;
  }
}
