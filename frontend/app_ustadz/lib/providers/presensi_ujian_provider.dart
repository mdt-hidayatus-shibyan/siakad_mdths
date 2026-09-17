import 'package:flutter/material.dart';
import '../core/utils/haptic_helper.dart';
import '../data/models/presensi_ujian_model.dart';
import '../data/repositories/presensi_ujian_repository.dart';

class PresensiUjianProvider extends ChangeNotifier {
  final PresensiUjianRepository _repo = PresensiUjianRepository();

  PresensiUjianDataResponse? _data;
  int? _selectedUjianId;
  int? _selectedRuanganId;
  int? _selectedJadwalId;

  // Local mutable state
  List<MuridPresensiUjianItem> _muridList = [];
  PengawasUjianData? _pengawas;

  bool _isLoading = false;
  bool _isSaving = false;
  String? _errorMessage;

  PresensiUjianDataResponse? get data => _data;
  int? get selectedUjianId => _selectedUjianId;
  int? get selectedRuanganId => _selectedRuanganId;
  int? get selectedJadwalId => _selectedJadwalId;
  List<MuridPresensiUjianItem> get muridList => _muridList;
  PengawasUjianData? get pengawas => _pengawas;
  bool get isLoading => _isLoading;
  bool get isSaving => _isSaving;
  String? get errorMessage => _errorMessage;

  // Convenient Getters
  List<UjianOptionItem> get daftarUjian => _data?.daftarUjian ?? [];
  List<RuanganOptionItem> get daftarRuangan => _data?.daftarRuangan ?? [];
  List<JadwalUjianItem> get jadwalList => _data?.jadwalList ?? [];
  List<BadalUstadzOption> get daftarBadal => _data?.daftarBadal ?? [];

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

  JadwalUjianItem? get currentJadwal {
    if (jadwalList.isEmpty) return null;
    return jadwalList.firstWhere(
      (j) => j.id == _selectedJadwalId,
      orElse: () => jadwalList.first,
    );
  }

  bool get isWaliRuangan => _data?.isWaliRuangan ?? false;
  String get selectedRuanganNama => _data?.selectedRuanganNama ?? '';
  String get namaLevel => _data?.namaLevel ?? '';
  String get waliRuanganNama => _data?.waliRuanganNama ?? '-';

  // Live Summary Counters
  int get totalMurid => _muridList.length;
  int get countBelumDiisi =>
      _muridList.where((m) => m.status == null || m.status!.isEmpty).length;
  int get countSudahDiisi =>
      _muridList.where((m) => m.status != null && m.status!.isNotEmpty).length;
  int get countHadir => _muridList.where((m) => m.status == 'Hadir').length;
  int get countIzin => _muridList.where((m) => m.status == 'Izin').length;
  int get countSakit => _muridList.where((m) => m.status == 'Sakit').length;
  int get countAlpha => _muridList.where((m) => m.status == 'Alpha').length;
  int get countDispensasi =>
      _muridList.where((m) => m.status == 'Dispensasi').length;

  Future<void> fetchData({
    int? ujianId,
    int? ruanganId,
    int? jadwalUjianId,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await _repo.getPresensiUjianData(
        ujianId: ujianId ?? _selectedUjianId,
        ruanganId: ruanganId ?? _selectedRuanganId,
        jadwalUjianId: jadwalUjianId ?? _selectedJadwalId,
      );

      _data = res;
      _selectedUjianId = res.selectedUjianId;
      _selectedRuanganId = res.selectedRuanganId;
      _selectedJadwalId = res.selectedJadwalId;
      _muridList = List<MuridPresensiUjianItem>.from(res.muridList);
      _pengawas = res.pengawas;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void selectUjian(int ujianId) {
    if (_selectedUjianId == ujianId) return;
    _selectedUjianId = ujianId;
    _selectedJadwalId = null;
    fetchData(ujianId: ujianId, ruanganId: _selectedRuanganId);
  }

  void selectRuangan(int ruanganId) {
    if (_selectedRuanganId == ruanganId) return;
    _selectedRuanganId = ruanganId;
    _selectedJadwalId = null;
    fetchData(ujianId: _selectedUjianId, ruanganId: ruanganId);
  }

  void selectJadwal(int jadwalId) {
    if (_selectedJadwalId == jadwalId) return;
    _selectedJadwalId = jadwalId;
    fetchData(
      ujianId: _selectedUjianId,
      ruanganId: _selectedRuanganId,
      jadwalUjianId: jadwalId,
    );
  }

  void updateMuridStatus(int muridId, String newStatus) {
    final idx = _muridList.indexWhere((m) => m.muridId == muridId);
    if (idx != -1) {
      HapticHelper.selection();
      if (_muridList[idx].status == newStatus) {
        _muridList[idx].status = null;
      } else {
        _muridList[idx].status = newStatus;
      }
      notifyListeners();
    }
  }

  void setAllMuridStatus(String? status) {
    HapticHelper.medium();
    for (var m in _muridList) {
      if (!m.isLocked) {
        m.status = status;
      }
    }
    notifyListeners();
  }

  void setSemuaKosong() {
    setAllMuridStatus(null);
  }

  void updateMuridCatatan(int muridId, String? catatan) {
    final idx = _muridList.indexWhere((m) => m.muridId == muridId);
    if (idx != -1) {
      _muridList[idx].catatan = catatan;
      notifyListeners();
    }
  }

  void updatePengawasStatus(String status) {
    HapticHelper.selection();
    if (_pengawas != null) {
      _pengawas!.status = status;
      if (status != 'Badal') {
        _pengawas!.ustadzPenggantiId = null;
        _pengawas!.ustadzPenggantiNama = null;
      }
      notifyListeners();
    }
  }

  void updatePengawasPengganti(int? badalId, String? badalNama) {
    HapticHelper.selection();
    if (_pengawas != null) {
      _pengawas!.ustadzPenggantiId = badalId;
      _pengawas!.ustadzPenggantiNama = badalNama;
      _pengawas!.status = 'Badal';
      notifyListeners();
    }
  }

  void updateBeritaAcara(String? catatan) {
    if (_pengawas != null) {
      _pengawas!.catatanBeritaAcara = catatan;
      notifyListeners();
    }
  }

  Future<bool> simpanPresensi() async {
    if (_selectedUjianId == null ||
        _selectedRuanganId == null ||
        _selectedJadwalId == null) {
      _errorMessage =
          'Silakan pilih Ujian, Ruangan, dan Jadwal terlebih dahulu.';
      notifyListeners();
      return false;
    }

    _isSaving = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final presensiMap = <String, dynamic>{};
      for (var m in _muridList) {
        presensiMap[m.muridId.toString()] = {
          'status': m.status,
          'catatan': m.catatan,
        };
      }

      final pengawasPayload = _pengawas?.toJson();

      final success = await _repo.simpanPresensiUjian(
        ujianId: _selectedUjianId!,
        ruanganId: _selectedRuanganId!,
        jadwalUjianId: _selectedJadwalId!,
        presensi: presensiMap,
        pengawas: pengawasPayload,
      );

      if (success) {
        HapticHelper.confirmSuccess();
        await fetchData(
          ujianId: _selectedUjianId,
          ruanganId: _selectedRuanganId,
          jadwalUjianId: _selectedJadwalId,
        );
      }
      return success;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      HapticHelper.warning();
      return false;
    } finally {
      _isSaving = false;
      notifyListeners();
    }
  }

  void reset() {
    _data = null;
    _selectedUjianId = null;
    _selectedRuanganId = null;
    _selectedJadwalId = null;
    _muridList = [];
    _pengawas = null;
    _isLoading = false;
    _isSaving = false;
    _errorMessage = null;
    notifyListeners();
  }
}
