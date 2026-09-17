import 'package:flutter/material.dart';
import '../core/utils/date_helper.dart';
import '../core/utils/haptic_helper.dart';
import '../data/models/presensi_model.dart';
import '../data/repositories/presensi_repository.dart';

class PresensiProvider extends ChangeNotifier {
  final PresensiRepository _repo = PresensiRepository();

  // === 1. STATE PRESENSI MURID ===
  DateTime _selectedDate = DateTime.now();
  List<SesiPresensiItem> _sesiList = [];
  List<MuridPresensiItem> _muridList = [];
  bool _isLibur = false;
  String? _keteranganLibur;
  bool _isUjian = false;
  String? _namaUjian;
  int? _ujianId;
  bool _isLoading = false;
  bool _isSaving = false;
  String? _errorMessage;

  DateTime get selectedDate => _selectedDate;
  List<SesiPresensiItem> get sesiList => _sesiList;
  List<MuridPresensiItem> get muridList => _muridList;
  bool get isLibur => _isLibur;
  String? get keteranganLibur => _keteranganLibur;
  bool get isUjian => _isUjian;
  String? get namaUjian => _namaUjian;
  int? get ujianId => _ujianId;
  bool get isLoading => _isLoading;
  bool get isSaving => _isSaving;
  String? get errorMessage => _errorMessage;

  // Live Summary Counters for Sticky Bottom Bar
  int get totalMurid => _muridList.length;
  int get countHadir => _muridList.where((m) => m.status == 'Hadir').length;
  int get countSakit => _muridList.where((m) => m.status == 'Sakit').length;
  int get countIzin => _muridList.where((m) => m.status == 'Izin').length;
  int get countAlpha => _muridList.where((m) => m.status == 'Alpha').length;
  int get countDispensasi =>
      _muridList.where((m) => m.status == 'Dispensasi').length;
  int get countBelumDiisi =>
      _muridList.where((m) => m.status == null || m.status!.isEmpty).length;
  int get countSudahDiisi =>
      _muridList.where((m) => m.status != null && m.status!.isNotEmpty).length;

  void setSelectedDate(DateTime date) {
    _selectedDate = date;
    fetchSesi();
  }

  Future<void> fetchSesi() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final dateStr = DateHelper.toYmd(_selectedDate);
      final response = await _repo.getSesiHarian(dateStr);
      _sesiList = response.sesiList;
      _sortSesiList();
      _isLibur = response.isLibur;
      _keteranganLibur = response.keteranganLibur;
      _isUjian = response.isUjian;
      _namaUjian = response.namaUjian;
      _ujianId = response.ujianId;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchMurid(int jadwalId) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final dateStr = DateHelper.toYmd(_selectedDate);
      _muridList = await _repo.getMuridPerJadwal(jadwalId, dateStr);
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void updateMuridStatus(int muridId, String newStatus) {
    final index = _muridList.indexWhere((m) => m.muridId == muridId);
    if (index != -1) {
      // Jika status yang diklik sama dengan status saat ini, toggle menjadi null (kosongkan)
      if (_muridList[index].status == newStatus) {
        _muridList[index].status = null;
      } else {
        _muridList[index].status = newStatus;
      }
      HapticHelper.segmentTick();
      notifyListeners();
    }
  }

  // 1-Tap Quick Action: Set Semua Hadir
  void setSemuaHadir() {
    for (var murid in _muridList) {
      murid.status = 'Hadir';
    }
    HapticHelper.medium();
    notifyListeners();
  }

  // 1-Tap Quick Action: Kosongkan Semua
  void setSemuaKosong() {
    for (var murid in _muridList) {
      murid.status = null;
    }
    HapticHelper.light();
    notifyListeners();
  }

  Future<bool> simpanPresensi(int jadwalId) async {
    _isSaving = true;
    notifyListeners();

    try {
      final dateStr = DateHelper.toYmd(_selectedDate);
      final success = await _repo.simpanPresensiMassal(
        jadwalId,
        dateStr,
        _muridList,
      );
      if (success) {
        HapticHelper.confirmSuccess();
        await fetchSesi(); // Refresh session badge
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

  // === 2. STATE PRESENSI USTADZ (CHECK-IN PER JADWAL) ===
  DateTime _selectedDateUstadz = DateTime.now();
  List<SesiPresensiUstadzItem> _sesiUstadzList = [];
  List<UstadzBadalItem> _daftarBadalList = [];
  RiwayatPresensiUstadz? _riwayatUstadz;
  bool _isLiburUstadz = false;
  String? _keteranganLiburUstadz;
  bool _isUjianUstadz = false;
  String? _namaUjianUstadz;
  int? _ujianIdUstadz;
  bool _isLoadingUstadz = false;
  bool _isCheckingInUstadz = false;

  DateTime get selectedDateUstadz => _selectedDateUstadz;
  List<SesiPresensiUstadzItem> get sesiUstadzList => _sesiUstadzList;
  List<UstadzBadalItem> get daftarBadalList => _daftarBadalList;
  RiwayatPresensiUstadz? get riwayatUstadz => _riwayatUstadz;
  bool get isLiburUstadz => _isLiburUstadz;
  String? get keteranganLiburUstadz => _keteranganLiburUstadz;
  bool get isUjianUstadz => _isUjianUstadz;
  String? get namaUjianUstadz => _namaUjianUstadz;
  int? get ujianIdUstadz => _ujianIdUstadz;
  bool get isLoadingUstadz => _isLoadingUstadz;
  bool get isCheckingInUstadz => _isCheckingInUstadz;

  void setSelectedDateUstadz(DateTime date) {
    _selectedDateUstadz = date;
    fetchSesiUstadz();
  }

  Future<void> fetchSesiUstadz() async {
    _isLoadingUstadz = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final dateStr = DateHelper.toYmd(_selectedDateUstadz);
      final response = await _repo.getSesiUstadzHarian(dateStr);
      _sesiUstadzList = response.sesiList;
      _sortSesiUstadzList();
      _isLiburUstadz = response.isLibur;
      _keteranganLiburUstadz = response.keteranganLibur;
      _isUjianUstadz = response.isUjian;
      _namaUjianUstadz = response.namaUjian;
      _ujianIdUstadz = response.ujianId;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoadingUstadz = false;
      notifyListeners();
    }
  }

  // === SORTING HELPERS (URUT BERDASARKAN RUANGAN & JAM) ===
  int _getJamWeight(String jamText, [String? jamKe]) {
    final jk = (jamKe ?? '').trim().toLowerCase();
    if (jk == 'nadzoman') return 1;
    if (jk == '1') return 2;
    if (jk == '2') return 3;
    if (jk == 'ekstra') return 4;

    final lower = jamText.toLowerCase();
    if (lower.contains('nadzoman') || lower.startsWith('13:')) {
      return 1;
    }
    if (lower.contains('jam ke-1') ||
        lower.contains('14:00') ||
        lower.startsWith('14:')) {
      return 2;
    }
    if (lower.contains('jam ke-2') ||
        lower.contains('15:30') ||
        lower.startsWith('15:')) {
      return 3;
    }
    if (lower.contains('ekstra') ||
        lower.contains('20:00') ||
        lower.startsWith('20:')) {
      return 4;
    }

    return 99;
  }

  void _sortSesiList() {
    _sesiList.sort((a, b) {
      final roomCmp = a.kelas.toLowerCase().compareTo(b.kelas.toLowerCase());
      if (roomCmp != 0) return roomCmp;
      final weightA = _getJamWeight(a.jam);
      final weightB = _getJamWeight(b.jam);
      if (weightA != weightB) return weightA.compareTo(weightB);
      return a.jam.compareTo(b.jam);
    });
  }

  void _sortSesiUstadzList() {
    _sesiUstadzList.sort((a, b) {
      final roomCmp = a.ruangan.toLowerCase().compareTo(
        b.ruangan.toLowerCase(),
      );
      if (roomCmp != 0) return roomCmp;
      final weightA = _getJamWeight(a.jam, a.jamKe);
      final weightB = _getJamWeight(b.jam, b.jamKe);
      if (weightA != weightB) return weightA.compareTo(weightB);
      return a.jam.compareTo(b.jam);
    });
  }

  Future<void> fetchDaftarBadal() async {
    try {
      _daftarBadalList = await _repo.getDaftarUstadzBadal();
      notifyListeners();
    } catch (_) {}
  }

  Future<bool> checkinUstadz({
    required int jadwalId,
    required String status,
    int? ustadzPenggantiId,
    String? keterangan,
  }) async {
    _isCheckingInUstadz = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final dateStr = DateHelper.toYmd(_selectedDateUstadz);
      final success = await _repo.checkinUstadz(
        jadwalId: jadwalId,
        tanggal: dateStr,
        status: status,
        ustadzPenggantiId: ustadzPenggantiId,
        keterangan: keterangan,
      );
      if (success) {
        HapticHelper.confirmSuccess();
        await fetchSesiUstadz();
        await fetchRiwayatUstadz();
      }
      return success;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      HapticHelper.warning();
      return false;
    } finally {
      _isCheckingInUstadz = false;
      notifyListeners();
    }
  }

  Future<void> fetchRiwayatUstadz() async {
    try {
      _riwayatUstadz = await _repo.getRiwayatUstadz();
      notifyListeners();
    } catch (_) {}
  }

  void reset() {
    _selectedDate = DateTime.now();
    _sesiList = [];
    _muridList = [];
    _isLibur = false;
    _keteranganLibur = null;
    _isUjian = false;
    _namaUjian = null;
    _ujianId = null;
    _isLoading = false;
    _isSaving = false;
    _errorMessage = null;

    _selectedDateUstadz = DateTime.now();
    _sesiUstadzList = [];
    _daftarBadalList = [];
    _riwayatUstadz = null;
    _isLiburUstadz = false;
    _keteranganLiburUstadz = null;
    _isUjianUstadz = false;
    _namaUjianUstadz = null;
    _ujianIdUstadz = null;
    _isLoadingUstadz = false;
    _isCheckingInUstadz = false;
    notifyListeners();
  }
}
