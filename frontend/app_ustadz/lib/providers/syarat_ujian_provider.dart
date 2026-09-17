import 'package:flutter/material.dart';
import '../core/utils/haptic_helper.dart';
import '../data/models/presensi_ujian_model.dart';
import '../data/models/syarat_ujian_model.dart';
import '../data/repositories/syarat_ujian_repository.dart';

class SyaratUjianProvider extends ChangeNotifier {
  final SyaratUjianRepository _repo = SyaratUjianRepository();

  SyaratUjianDataResponse? _data;
  int? _selectedRuanganId;
  int? _selectedUjianId;

  String _searchQuery = '';
  String _filterStatus = 'Semua'; // 'Semua', 'Lunas', 'Dispensasi', 'Terkunci'

  bool _isLoading = false;
  bool _isSaving = false;
  String? _errorMessage;

  // Getters
  SyaratUjianDataResponse? get data => _data;
  int? get selectedRuanganId => _selectedRuanganId;
  int? get selectedUjianId => _selectedUjianId;
  String get searchQuery => _searchQuery;
  String get filterStatus => _filterStatus;
  bool get isLoading => _isLoading;
  bool get isSaving => _isSaving;
  String? get errorMessage => _errorMessage;

  List<RuanganOptionItem> get daftarRuangan => _data?.daftarRuangan ?? [];
  List<UjianOptionItem> get daftarUjian => _data?.daftarUjian ?? [];
  SyaratUjianSummary get summary => _data?.summary ?? SyaratUjianSummary();
  bool get isWaliRuangan => _data?.isWaliRuangan ?? false;
  String get selectedRuanganNama => _data?.selectedRuanganNama ?? '';
  String get namaLevel => _data?.namaLevel ?? '';
  String get waliRuanganNama => _data?.waliRuanganNama ?? '-';

  UjianOptionItem? get currentUjian {
    if (daftarUjian.isEmpty) return null;
    return daftarUjian.firstWhere(
      (u) => u.id == _selectedUjianId,
      orElse: () => daftarUjian.first,
    );
  }

  RuanganOptionItem? get currentRuangan {
    if (daftarRuangan.isEmpty) return null;
    return daftarRuangan.firstWhere(
      (r) => r.id == _selectedRuanganId,
      orElse: () => daftarRuangan.first,
    );
  }

  List<MuridSyaratUjianItem> get filteredMuridList {
    if (_data == null) return [];
    var list = _data!.muridList;

    // Filter by status chip
    if (_filterStatus != 'Semua') {
      list = list.where((m) => m.statusSyarat == _filterStatus).toList();
    }

    // Filter by search query
    if (_searchQuery.trim().isNotEmpty) {
      final q = _searchQuery.trim().toLowerCase();
      list = list.where((m) {
        final nama = m.nama.toLowerCase();
        final nism = m.nism.toLowerCase();
        return nama.contains(q) || nism.contains(q);
      }).toList();
    }

    return list;
  }

  Future<void> fetchData({int? ruanganId, int? ujianId}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await _repo.getSyaratUjianData(
        ruanganId: ruanganId ?? _selectedRuanganId,
        ujianId: ujianId ?? _selectedUjianId,
      );

      _data = res;
      _selectedRuanganId = res.selectedRuanganId;
      _selectedUjianId = res.selectedUjianId;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void selectRuangan(int ruanganId) {
    if (_selectedRuanganId == ruanganId) return;
    _selectedRuanganId = ruanganId;
    _selectedUjianId = null;
    fetchData(ruanganId: ruanganId);
  }

  void selectUjian(int ujianId) {
    if (_selectedUjianId == ujianId) return;
    _selectedUjianId = ujianId;
    fetchData(ruanganId: _selectedRuanganId, ujianId: ujianId);
  }

  void setSearchQuery(String q) {
    _searchQuery = q;
    notifyListeners();
  }

  void setFilterStatus(String status) {
    if (_filterStatus == status) return;
    HapticHelper.selection();
    _filterStatus = status;
    notifyListeners();
  }

  Future<bool> beriDispensasi({
    required int muridId,
    String? alasanIzin,
  }) async {
    if (_selectedUjianId == null || _selectedRuanganId == null) return false;

    _isSaving = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final success = await _repo.beriDispensasi(
        ujianId: _selectedUjianId!,
        muridId: muridId,
        ruanganId: _selectedRuanganId!,
        alasanIzin: alasanIzin,
      );

      if (success) {
        HapticHelper.confirmSuccess();
        await fetchData(
          ruanganId: _selectedRuanganId,
          ujianId: _selectedUjianId,
        );
      }
      return success;
    } catch (e) {
      HapticHelper.warning();
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isSaving = false;
      notifyListeners();
    }
  }

  Future<bool> batalkanDispensasi({required int muridId}) async {
    if (_selectedUjianId == null || _selectedRuanganId == null) return false;

    _isSaving = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final success = await _repo.batalkanDispensasi(
        ujianId: _selectedUjianId!,
        muridId: muridId,
        ruanganId: _selectedRuanganId!,
      );

      if (success) {
        HapticHelper.confirmSuccess();
        await fetchData(
          ruanganId: _selectedRuanganId,
          ujianId: _selectedUjianId,
        );
      }
      return success;
    } catch (e) {
      HapticHelper.warning();
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isSaving = false;
      notifyListeners();
    }
  }

  void reset() {
    _data = null;
    _selectedRuanganId = null;
    _selectedUjianId = null;
    _searchQuery = '';
    _filterStatus = 'Semua';
    _isLoading = false;
    _isSaving = false;
    _errorMessage = null;
    notifyListeners();
  }
}
