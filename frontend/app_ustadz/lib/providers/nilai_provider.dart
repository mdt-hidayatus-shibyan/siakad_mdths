import 'package:flutter/material.dart';
import '../core/utils/haptic_helper.dart';
import '../data/models/nilai_ujian_model.dart';
import '../data/repositories/nilai_repository.dart';

class NilaiProvider extends ChangeNotifier {
  final NilaiRepository _repo = NilaiRepository();

  MapelJadwalNilaiResponse? _mapelJadwalData;
  List<MuridNilaiItem> _muridNilaiList = [];
  LegerResponse? _legerData;

  int? _selectedRuanganId;
  int? _selectedUjianId;
  JadwalMapelNilaiItem? _selectedJadwal;

  bool _isLoading = false;
  bool _isSaving = false;
  String? _errorMessage;

  // Getters
  MapelJadwalNilaiResponse? get mapelJadwalData => _mapelJadwalData;
  LegerResponse? get legerData => _legerData;
  List<RuanganNilaiItem> get daftarRuangan =>
      _mapelJadwalData?.daftarRuangan ?? [];
  List<UjianItem> get daftarUjian => _mapelJadwalData?.daftarUjian ?? [];
  List<JadwalMapelNilaiItem> get jadwalList =>
      _mapelJadwalData?.jadwalList ?? [];
  List<MuridNilaiItem> get muridNilaiList => _muridNilaiList;
  List<LegerRowItem> get legerList => _legerData?.leger ?? [];

  int? get selectedRuanganId => _selectedRuanganId;
  int? get selectedUjianId => _selectedUjianId;
  JadwalMapelNilaiItem? get selectedJadwal => _selectedJadwal;

  UjianItem? get currentUjian {
    if (_selectedUjianId == null || daftarUjian.isEmpty) return null;
    try {
      return daftarUjian.firstWhere((u) => u.id == _selectedUjianId);
    } catch (_) {
      return daftarUjian.firstOrNull;
    }
  }

  RuanganNilaiItem? get currentRuangan {
    if (_selectedRuanganId == null || daftarRuangan.isEmpty) return null;
    try {
      return daftarRuangan.firstWhere((r) => r.id == _selectedRuanganId);
    } catch (_) {
      return daftarRuangan.firstOrNull;
    }
  }

  bool get isWaliRuangan => _mapelJadwalData?.isWaliRuangan ?? false;
  String get selectedRuanganNama => _mapelJadwalData?.selectedRuanganNama ?? '';
  String get namaLevel => _mapelJadwalData?.namaLevel ?? '';

  bool get isLoading => _isLoading;
  bool get isSaving => _isSaving;
  String? get errorMessage => _errorMessage;

  // Compatibility getter for older references
  List<UjianItem> get ujianList => daftarUjian;
  UjianItem? get selectedUjian => currentUjian;

  Future<void> fetchMapelJadwal({int? ruanganId, int? ujianId}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final rId = ruanganId ?? _selectedRuanganId;
      final uId = ujianId ?? _selectedUjianId;
      final res = await _repo.getMapelJadwal(ruanganId: rId, ujianId: uId);
      _mapelJadwalData = res;

      _selectedRuanganId = res.selectedRuanganId;
      _selectedUjianId = res.selectedUjianId;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> selectRuangan(int ruanganId) async {
    if (_selectedRuanganId == ruanganId) return;
    _selectedRuanganId = ruanganId;
    _selectedUjianId = null;
    notifyListeners();
    await fetchMapelJadwal(ruanganId: ruanganId);
  }

  Future<void> selectUjian(int ujianId) async {
    if (_selectedUjianId == ujianId) return;
    _selectedUjianId = ujianId;
    notifyListeners();
    await fetchMapelJadwal(ruanganId: _selectedRuanganId, ujianId: ujianId);
  }

  void setSelectedJadwal(JadwalMapelNilaiItem? jadwal) {
    _selectedJadwal = jadwal;
    notifyListeners();
  }

  Future<void> fetchInputData({
    required int ruanganId,
    required int ujianId,
    int? jadwalUjianId,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _muridNilaiList = await _repo.getInputData(
        ujianId: ujianId,
        ruanganId: ruanganId,
        jadwalUjianId: jadwalUjianId,
      );
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void updateScore(int muridId, double value) {
    final index = _muridNilaiList.indexWhere((m) => m.muridId == muridId);
    if (index != -1) {
      _muridNilaiList[index].nilai = value;
      notifyListeners();
    }
  }

  Future<bool> simpanNilai({
    required int ruanganId,
    required int ujianId,
    int? jadwalUjianId,
    required String action,
  }) async {
    _isSaving = true;
    notifyListeners();

    final Map<String, double> scores = {};
    for (var m in _muridNilaiList) {
      if (m.nilai != null && !m.isLocked) {
        scores[m.muridId.toString()] = m.nilai!;
      }
    }

    try {
      final success = await _repo.simpanNilai(
        ujianId: ujianId,
        ruanganId: ruanganId,
        jadwalUjianId: jadwalUjianId,
        action: action,
        nilaiMap: scores,
      );
      if (success) {
        HapticHelper.confirmSuccess();
        await fetchInputData(
          ruanganId: ruanganId,
          ujianId: ujianId,
          jadwalUjianId: jadwalUjianId,
        );
        // Refresh overview jadwal progress
        await fetchMapelJadwal(ruanganId: ruanganId, ujianId: ujianId);
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

  Future<void> fetchLeger({
    required int ruanganId,
    required int ujianId,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _legerData = await _repo.getLeger(ujianId: ujianId, ruanganId: ruanganId);
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> beriDispensasi({
    required int ujianId,
    required int muridId,
    required int ruanganId,
    int? jadwalUjianId,
    String? alasanIzin,
  }) async {
    _isSaving = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final success = await _repo.beriDispensasi(
        ujianId: ujianId,
        muridId: muridId,
        alasanIzin: alasanIzin,
      );
      if (success) {
        HapticHelper.confirmSuccess();
        await fetchInputData(
          ujianId: ujianId,
          ruanganId: ruanganId,
          jadwalUjianId: jadwalUjianId,
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

  Future<bool> batalkanDispensasi({
    required int ujianId,
    required int muridId,
    required int ruanganId,
    int? jadwalUjianId,
  }) async {
    _isSaving = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final success = await _repo.batalkanDispensasi(
        ujianId: ujianId,
        muridId: muridId,
      );
      if (success) {
        HapticHelper.confirmSuccess();
        await fetchInputData(
          ujianId: ujianId,
          ruanganId: ruanganId,
          jadwalUjianId: jadwalUjianId,
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

  // Compatibility method
  Future<void> fetchUjianList({int? ruanganId}) async {
    await fetchMapelJadwal(ruanganId: ruanganId);
  }

  void reset() {
    _mapelJadwalData = null;
    _muridNilaiList = [];
    _legerData = null;
    _selectedRuanganId = null;
    _selectedUjianId = null;
    _selectedJadwal = null;
    _isLoading = false;
    _isSaving = false;
    _errorMessage = null;
    notifyListeners();
  }
}
