import 'dart:io';
import 'package:flutter/foundation.dart';
import '../data/models/panitia_ikhtibar_model.dart';
import '../data/repositories/panitia_ikhtibar_repository.dart';

class PanitiaIkhtibarProvider extends ChangeNotifier {
  final PanitiaIkhtibarRepository _repo = PanitiaIkhtibarRepository();

  // =========================================================================
  // 1. STATE: KASIR PEMBAYARAN TAGIHAN KK
  // =========================================================================
  KasirIkhtibarRingkasanModel? _kasirRingkasan;
  List<TagihanKkIkhtibarItem> _tagihanList = [];
  bool _isLoadingKasir = false;
  String? _kasirError;
  int? _filterKampungId;
  String _filterStatusBayar = 'Semua';
  String _searchQuery = '';

  KasirIkhtibarRingkasanModel? get kasirRingkasan => _kasirRingkasan;
  List<TagihanKkIkhtibarItem> get tagihanList => _tagihanList;
  bool get isLoadingKasir => _isLoadingKasir;
  String? get kasirError => _kasirError;
  int? get filterKampungId => _filterKampungId;
  String get filterStatusBayar => _filterStatusBayar;
  String get searchQuery => _searchQuery;

  void setFilterKampung(int? kampungId) {
    _filterKampungId = kampungId;
    notifyListeners();
    fetchKasirList();
  }

  void setFilterStatusBayar(String status) {
    _filterStatusBayar = status;
    notifyListeners();
    fetchKasirList();
  }

  void setSearchQuery(String q) {
    _searchQuery = q;
    notifyListeners();
    fetchKasirList();
  }

  Future<void> fetchKasirRingkasan({int? tahunId}) async {
    try {
      _isLoadingKasir = true;
      _kasirError = null;
      notifyListeners();

      _kasirRingkasan = await _repo.getKasirRingkasan(tahunId: tahunId);
    } catch (e) {
      _kasirError = e.toString().replaceFirst('Exception: ', '');
    } finally {
      _isLoadingKasir = false;
      notifyListeners();
    }
  }

  Future<void> fetchKasirList({int? tahunId}) async {
    try {
      _isLoadingKasir = true;
      _kasirError = null;
      notifyListeners();

      _tagihanList = await _repo.getKasirList(
        tahunId: tahunId,
        kampungId: _filterKampungId,
        statusBayar: _filterStatusBayar,
        search: _searchQuery,
      );
    } catch (e) {
      _kasirError = e.toString().replaceFirst('Exception: ', '');
    } finally {
      _isLoadingKasir = false;
      notifyListeners();
    }
  }

  Future<Map<String, dynamic>?> simpanPembayaran({
    required int tagihanId,
    required double nominalBayar,
    required String metodePembayaran,
    String? tanggalBayar,
    String? namaPenyetor,
    String? catatan,
    String? nomorTabungan,
    String? namaNasabah,
    String? ruanganNasabah,
    bool? isVerified,
    int? tahunId,
  }) async {
    try {
      final res = await _repo.simpanPembayaran(
        tagihanId: tagihanId,
        nominalBayar: nominalBayar,
        metodePembayaran: metodePembayaran,
        tanggalBayar: tanggalBayar,
        namaPenyetor: namaPenyetor,
        catatan: catatan,
        nomorTabungan: nomorTabungan,
        namaNasabah: namaNasabah,
        ruanganNasabah: ruanganNasabah,
        isVerified: isVerified,
      );

      // Refresh list & ringkasan
      await Future.wait([
        fetchKasirRingkasan(tahunId: tahunId),
        fetchKasirList(tahunId: tahunId),
      ]);
      return res['data'] as Map<String, dynamic>? ?? res;
    } catch (e) {
      _kasirError = e.toString().replaceFirst('Exception: ', '');
      notifyListeners();
      return null;
    }
  }

  Future<bool> toggleVerifikasiTabungan(int pembayaranId, {int? tahunId}) async {
    try {
      await _repo.toggleVerifikasiTabungan(pembayaranId);
      await Future.wait([
        fetchKasirRingkasan(tahunId: tahunId),
        fetchKasirList(tahunId: tahunId),
        fetchLaporanRiwayat(tahunId: tahunId),
        fetchLaporanRingkasan(tahunId: tahunId),
      ]);
      return true;
    } catch (e) {
      _kasirError = e.toString().replaceFirst('Exception: ', '');
      notifyListeners();
      return false;
    }
  }

  // =========================================================================
  // 2. STATE: LAPORAN PEMBAYARAN TAGIHAN IKHTIBAR
  // =========================================================================
  LaporanIkhtibarRingkasanModel? _laporanRingkasan;
  List<RiwayatPembayaranIkhtibarItem> _riwayatList = [];
  bool _isLoadingLaporan = false;
  String? _laporanError;
  String _filterLaporanMetode = 'Semua';
  int? _filterLaporanKampungId;
  DateTime? _filterLaporanTanggal;

  LaporanIkhtibarRingkasanModel? get laporanRingkasan => _laporanRingkasan;
  List<RiwayatPembayaranIkhtibarItem> get riwayatList => _riwayatList;
  bool get isLoadingLaporan => _isLoadingLaporan;
  String? get laporanError => _laporanError;
  String get filterLaporanMetode => _filterLaporanMetode;
  int? get filterLaporanKampungId => _filterLaporanKampungId;
  DateTime? get filterLaporanTanggal => _filterLaporanTanggal;

  void setFilterLaporanMetode(String metode) {
    _filterLaporanMetode = metode;
    notifyListeners();
    fetchLaporanRiwayat();
  }

  void setFilterLaporanKampung(int? kampungId) {
    _filterLaporanKampungId = kampungId;
    notifyListeners();
    fetchLaporanRiwayat();
  }

  void setFilterLaporanTanggal(DateTime? tanggal) {
    _filterLaporanTanggal = tanggal;
    notifyListeners();
    fetchLaporanRiwayat();
  }

  Future<void> fetchLaporanRingkasan({int? tahunId}) async {
    try {
      _isLoadingLaporan = true;
      _laporanError = null;
      notifyListeners();

      _laporanRingkasan = await _repo.getLaporanRingkasan(tahunId: tahunId);
    } catch (e) {
      _laporanError = e.toString().replaceFirst('Exception: ', '');
    } finally {
      _isLoadingLaporan = false;
      notifyListeners();
    }
  }

  Future<void> fetchLaporanRiwayat({int? tahunId}) async {
    try {
      _isLoadingLaporan = true;
      _laporanError = null;
      notifyListeners();

      final tglStr = _filterLaporanTanggal != null
          ? "${_filterLaporanTanggal!.year}-${_filterLaporanTanggal!.month.toString().padLeft(2, '0')}-${_filterLaporanTanggal!.day.toString().padLeft(2, '0')}"
          : null;

      _riwayatList = await _repo.getLaporanRiwayat(
        tahunId: tahunId,
        metodePembayaran: _filterLaporanMetode,
        tanggal: tglStr,
        kampungId: _filterLaporanKampungId,
      );
    } catch (e) {
      _laporanError = e.toString().replaceFirst('Exception: ', '');
    } finally {
      _isLoadingLaporan = false;
      notifyListeners();
    }
  }

  // =========================================================================
  // 3. STATE: PAGU & REALISASI SPJ (BPH & DIVISI)
  // =========================================================================
  PaguSpjRingkasanModel? _paguSpjRingkasan;
  List<SpjIkhtibarItem> _spjList = [];
  bool _isLoadingPaguSpj = false;
  String? _paguSpjError;
  int? _filterSpjDivisiId;
  String _filterSpjStatus = 'Semua';

  PaguSpjRingkasanModel? get paguSpjRingkasan => _paguSpjRingkasan;
  List<SpjIkhtibarItem> get spjList => _spjList;
  bool get isLoadingPaguSpj => _isLoadingPaguSpj;
  String? get paguSpjError => _paguSpjError;
  int? get filterSpjDivisiId => _filterSpjDivisiId;
  String get filterSpjStatus => _filterSpjStatus;

  void setFilterSpjDivisi(int? divisiId) {
    _filterSpjDivisiId = divisiId;
    notifyListeners();
    fetchSpjList();
  }

  void setFilterSpjStatus(String status) {
    _filterSpjStatus = status;
    notifyListeners();
    fetchSpjList();
  }

  Future<void> fetchPaguSpjRingkasan({int? tahunId}) async {
    try {
      _isLoadingPaguSpj = true;
      _paguSpjError = null;
      notifyListeners();

      _paguSpjRingkasan = await _repo.getPaguSpjRingkasan(tahunId: tahunId);
    } catch (e) {
      _paguSpjError = e.toString().replaceFirst('Exception: ', '');
    } finally {
      _isLoadingPaguSpj = false;
      notifyListeners();
    }
  }

  Future<void> fetchSpjList({int? tahunId}) async {
    try {
      _isLoadingPaguSpj = true;
      _paguSpjError = null;
      notifyListeners();

      _spjList = await _repo.getSpjList(
        tahunId: tahunId,
        divisiId: _filterSpjDivisiId,
        statusVerifikasi: _filterSpjStatus,
      );
    } catch (e) {
      _paguSpjError = e.toString().replaceFirst('Exception: ', '');
    } finally {
      _isLoadingPaguSpj = false;
      notifyListeners();
    }
  }

  Future<bool> simpanSpj({
    int? divisiId,
    required String judulPengeluaran,
    required double nominal,
    required String tanggalNota,
    String? namaTokoPenerima,
    String metodePembayaran = 'Tunai',
    String? rincianItem,
    File? buktiNotaFile,
    int? tahunId,
  }) async {
    try {
      await _repo.simpanSpj(
        divisiId: divisiId,
        judulPengeluaran: judulPengeluaran,
        nominal: nominal,
        tanggalNota: tanggalNota,
        namaTokoPenerima: namaTokoPenerima,
        metodePembayaran: metodePembayaran,
        rincianItem: rincianItem,
        buktiNotaFile: buktiNotaFile,
      );

      // Refresh data
      await Future.wait([
        fetchPaguSpjRingkasan(tahunId: tahunId),
        fetchSpjList(tahunId: tahunId),
      ]);
      return true;
    } catch (e) {
      _paguSpjError = e.toString().replaceFirst('Exception: ', '');
      notifyListeners();
      return false;
    }
  }
}

