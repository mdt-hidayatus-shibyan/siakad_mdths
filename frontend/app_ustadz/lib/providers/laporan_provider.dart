import 'package:flutter/material.dart';
import '../data/models/laporan_model.dart';
import '../data/repositories/laporan_repository.dart';

class LaporanProvider extends ChangeNotifier {
  final LaporanRepository _repo = LaporanRepository();

  // State Presensi Murid
  LaporanPresensiMuridData? _presensiMuridData;
  bool _isLoadingPresensiMurid = false;
  String? _errorPresensiMurid;

  // State Pelanggaran Murid
  LaporanPelanggaranData? _pelanggaranData;
  bool _isLoadingPelanggaran = false;
  String? _errorPelanggaran;

  // State Presensi Ustadz
  LaporanPresensiUstadzData? _presensiUstadzData;
  bool _isLoadingPresensiUstadz = false;
  String? _errorPresensiUstadz;

  // State Kenaikan Kelas & Kelulusan
  LaporanKenaikanKelasData? _kenaikanKelasData;
  bool _isLoadingKenaikanKelas = false;
  String? _errorKenaikanKelas;

  // State Laporan Ujian
  LaporanUjianData? _laporanUjianData;
  bool _isLoadingLaporanUjian = false;
  String? _errorLaporanUjian;

  // Getters Presensi Murid
  LaporanPresensiMuridData? get presensiMuridData => _presensiMuridData;
  bool get isLoadingPresensiMurid => _isLoadingPresensiMurid;
  String? get errorPresensiMurid => _errorPresensiMurid;

  // Getters Pelanggaran Murid
  LaporanPelanggaranData? get pelanggaranData => _pelanggaranData;
  bool get isLoadingPelanggaran => _isLoadingPelanggaran;
  String? get errorPelanggaran => _errorPelanggaran;

  // Getters Presensi Ustadz
  LaporanPresensiUstadzData? get presensiUstadzData => _presensiUstadzData;
  bool get isLoadingPresensiUstadz => _isLoadingPresensiUstadz;
  String? get errorPresensiUstadz => _errorPresensiUstadz;

  // Getters Kenaikan Kelas & Kelulusan
  LaporanKenaikanKelasData? get kenaikanKelasData => _kenaikanKelasData;
  bool get isLoadingKenaikanKelas => _isLoadingKenaikanKelas;
  String? get errorKenaikanKelas => _errorKenaikanKelas;

  // Getters Laporan Ujian
  LaporanUjianData? get laporanUjianData => _laporanUjianData;
  bool get isLoadingLaporanUjian => _isLoadingLaporanUjian;
  String? get errorLaporanUjian => _errorLaporanUjian;

  /// Memuat Laporan Presensi Murid
  Future<void> fetchPresensiMurid({
    int? ruanganId,
    int? semesterId,
    int? bulanHijriyahId,
    String? semester,
    String? startDate,
    String? endDate,
    int? jadwalId,
  }) async {
    _isLoadingPresensiMurid = true;
    _errorPresensiMurid = null;
    notifyListeners();

    try {
      _presensiMuridData = await _repo.getLaporanPresensiMurid(
        ruanganId: ruanganId,
        semesterId: semesterId,
        bulanHijriyahId: bulanHijriyahId,
        semester: semester,
        startDate: startDate,
        endDate: endDate,
        jadwalId: jadwalId,
      );
    } catch (e) {
      _errorPresensiMurid = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoadingPresensiMurid = false;
      notifyListeners();
    }
  }

  /// Memuat Laporan Buku Kasus & Pelanggaran Murid
  Future<void> fetchPelanggaranMurid({
    int? ruanganId,
    int? bulanHijriyahId,
    String? startDate,
    String? endDate,
    String? kategori,
  }) async {
    _isLoadingPelanggaran = true;
    _errorPelanggaran = null;
    notifyListeners();

    try {
      _pelanggaranData = await _repo.getLaporanPelanggaranMurid(
        ruanganId: ruanganId,
        bulanHijriyahId: bulanHijriyahId,
        startDate: startDate,
        endDate: endDate,
        kategori: kategori,
      );
    } catch (e) {
      _errorPelanggaran = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoadingPelanggaran = false;
      notifyListeners();
    }
  }

  /// Memuat Laporan Presensi Ustadz
  Future<void> fetchPresensiUstadz({
    int? ruanganId,
    int? ustadzId,
    int? semesterId,
    int? bulanHijriyahId,
    String? status,
    String? startDate,
    String? endDate,
    bool? isPribadi,
  }) async {
    _isLoadingPresensiUstadz = true;
    _errorPresensiUstadz = null;
    notifyListeners();

    try {
      _presensiUstadzData = await _repo.getLaporanPresensiUstadz(
        ruanganId: ruanganId,
        ustadzId: ustadzId,
        semesterId: semesterId,
        bulanHijriyahId: bulanHijriyahId,
        status: status,
        startDate: startDate,
        endDate: endDate,
        isPribadi: isPribadi,
      );
    } catch (e) {
      _errorPresensiUstadz = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoadingPresensiUstadz = false;
      notifyListeners();
    }
  }

  /// Memuat Laporan Kenaikan Kelas & Kelulusan Murid
  Future<void> fetchKenaikanKelas({int? ruanganId, int? tahunId}) async {
    _isLoadingKenaikanKelas = true;
    _errorKenaikanKelas = null;
    notifyListeners();

    try {
      _kenaikanKelasData = await _repo.getLaporanKenaikanKelas(
        ruanganId: ruanganId,
        tahunId: tahunId,
      );
    } catch (e) {
      _errorKenaikanKelas = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoadingKenaikanKelas = false;
      notifyListeners();
    }
  }

  /// Memuat Laporan Ujian & Leger Kelas
  Future<void> fetchLaporanUjian({int? ruanganId, int? ujianId}) async {
    _isLoadingLaporanUjian = true;
    _errorLaporanUjian = null;
    notifyListeners();

    try {
      _laporanUjianData = await _repo.getLaporanUjian(
        ruanganId: ruanganId,
        ujianId: ujianId,
      );
    } catch (e) {
      _errorLaporanUjian = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoadingLaporanUjian = false;
      notifyListeners();
    }
  }
}
