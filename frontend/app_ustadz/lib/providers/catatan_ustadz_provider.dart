import 'package:flutter/material.dart';
import '../data/models/catatan_ustadz_model.dart';
import '../data/repositories/catatan_ustadz_repository.dart';

class CatatanUstadzProvider extends ChangeNotifier {
  final CatatanUstadzRepository _repo = CatatanUstadzRepository();

  List<CatatanUstadzItem> _catatanList = [];
  CatatanOptionsData? _optionsData;

  bool _isLoading = false;
  bool _isLoadingOptions = false;
  bool _isSaving = false;
  String? _errorMessage;

  String _selectedKategori = 'Semua';
  String _selectedTarget = 'Semua';
  String _searchQuery = '';

  List<CatatanUstadzItem> get catatanList => _catatanList;
  CatatanOptionsData? get optionsData => _optionsData;

  bool get isLoading => _isLoading;
  bool get isLoadingOptions => _isLoadingOptions;
  bool get isSaving => _isSaving;
  String? get errorMessage => _errorMessage;

  String get selectedKategori => _selectedKategori;
  String get selectedTarget => _selectedTarget;
  String get searchQuery => _searchQuery;

  void setKategori(String kategori) {
    if (_selectedKategori != kategori) {
      _selectedKategori = kategori;
      fetchCatatanList();
    }
  }

  void setTarget(String target) {
    if (_selectedTarget != target) {
      _selectedTarget = target;
      fetchCatatanList();
    }
  }

  void setSearch(String query) {
    _searchQuery = query;
    fetchCatatanList();
  }

  /// Ambil daftar catatan ustadz
  Future<void> fetchCatatanList({bool silent = false}) async {
    if (!silent) {
      _isLoading = true;
      _errorMessage = null;
      notifyListeners();
    }

    try {
      _catatanList = await _repo.getCatatanList(
        kategori: _selectedKategori == 'Semua' ? null : _selectedKategori,
        targetTipe: _selectedTarget == 'Semua'
            ? null
            : _selectedTarget.toLowerCase(),
        search: _searchQuery.isEmpty ? null : _searchQuery,
      );
      _errorMessage = null;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Ambil data opsi form (Murid, Ruangan, Kategori, Urgensi)
  Future<void> fetchOptions() async {
    if (_optionsData != null) return;

    _isLoadingOptions = true;
    notifyListeners();

    try {
      _optionsData = await _repo.getOptions();
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoadingOptions = false;
      notifyListeners();
    }
  }

  /// Simpan catatan ustadz baru
  Future<bool> simpanCatatan({
    required String judul,
    required String kategori,
    required String targetTipe,
    required String isiCatatan,
    required String tingkatUrgensi,
    int? muridId,
    int? ruanganId,
    String? fotoFilePath,
  }) async {
    _isSaving = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final newCatatan = await _repo.simpanCatatan(
        judul: judul,
        kategori: kategori,
        targetTipe: targetTipe,
        isiCatatan: isiCatatan,
        tingkatUrgensi: tingkatUrgensi,
        muridId: muridId,
        ruanganId: ruanganId,
        fotoFilePath: fotoFilePath,
      );

      _catatanList.insert(0, newCatatan);
      _isSaving = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isSaving = false;
      notifyListeners();
      return false;
    }
  }

  /// Update catatan ustadz
  Future<bool> updateCatatan(
    int id, {
    required String judul,
    required String kategori,
    required String targetTipe,
    required String isiCatatan,
    required String tingkatUrgensi,
    int? muridId,
    int? ruanganId,
    String? fotoFilePath,
  }) async {
    _isSaving = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final updated = await _repo.updateCatatan(
        id,
        judul: judul,
        kategori: kategori,
        targetTipe: targetTipe,
        isiCatatan: isiCatatan,
        tingkatUrgensi: tingkatUrgensi,
        muridId: muridId,
        ruanganId: ruanganId,
        fotoFilePath: fotoFilePath,
      );

      final index = _catatanList.indexWhere((c) => c.id == id);
      if (index != -1) {
        _catatanList[index] = updated;
      }

      _isSaving = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isSaving = false;
      notifyListeners();
      return false;
    }
  }

  /// Hapus catatan ustadz
  Future<bool> hapusCatatan(int id) async {
    try {
      final success = await _repo.hapusCatatan(id);
      if (success) {
        _catatanList.removeWhere((c) => c.id == id);
        notifyListeners();
      }
      return success;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      notifyListeners();
      return false;
    }
  }
}
