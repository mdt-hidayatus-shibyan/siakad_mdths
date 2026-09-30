import 'package:flutter/material.dart';
import '../core/utils/date_helper.dart';
import '../core/utils/jam_order_helper.dart';
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
  bool _isEvent = false;
  EventPresensiInfo? _eventInfo;
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
  bool get isEvent => _isEvent;
  EventPresensiInfo? get eventInfo => _eventInfo;
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
      _isLibur = response.isLibur;
      _keteranganLibur = response.keteranganLibur;
      _isUjian = response.isUjian;
      _namaUjian = response.namaUjian;
      _ujianId = response.ujianId;
      _isEvent = response.isEvent;
      _eventInfo = response.eventInfo;
      _sortSesiList();
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchMurid(int jadwalId, [DateTime? customDate]) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final targetDate = customDate ?? _selectedDate;
      final dateStr = DateHelper.toYmd(targetDate);
      _muridList = await _repo.getMuridPerJadwal(jadwalId, dateStr);
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchMuridKegiatan(
    int kalendarId,
    int ruanganId,
    String sesi, [
    DateTime? customDate,
  ]) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final targetDate = customDate ?? _selectedDate;
      final dateStr = DateHelper.toYmd(targetDate);
      _muridList = await _repo.getMuridKegiatan(
        kalendarId: kalendarId,
        ruanganId: ruanganId,
        tanggal: dateStr,
        sesi: sesi,
      );
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

  Future<bool> simpanPresensi(
    int jadwalId, [
    DateTime? customDate,
    bool isBadal = false,
    String? statusUstadz,
    String? alasanBadal,
  ]) async {
    _isSaving = true;
    notifyListeners();

    try {
      final targetDate = customDate ?? _selectedDate;
      final dateStr = DateHelper.toYmd(targetDate);
      final success = await _repo.simpanPresensiMassal(
        jadwalId,
        dateStr,
        _muridList,
        isBadal: isBadal,
        statusUstadz: statusUstadz,
        alasanBadal: alasanBadal,
      );
      if (success) {
        HapticHelper.confirmSuccess();
        await fetchSesi();
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

  Future<bool> simpanPresensiKegiatan({
    required int kalendarId,
    required int ruanganId,
    required String sesi,
    DateTime? customDate,
  }) async {
    _isSaving = true;
    notifyListeners();

    try {
      final targetDate = customDate ?? _selectedDate;
      final dateStr = DateHelper.toYmd(targetDate);
      final success = await _repo.simpanPresensiKegiatanMassal(
        kalendarId: kalendarId,
        ruanganId: ruanganId,
        tanggal: dateStr,
        sesi: sesi,
        items: _muridList,
      );
      if (success) {
        HapticHelper.confirmSuccess();
        await fetchSesi();
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

  // === 2. STATE PRESENSI USTADZ (CHECK-IN PER JADWAL & EVENT) ===
  DateTime _selectedDateUstadz = DateTime.now();
  List<SesiPresensiUstadzItem> _sesiUstadzList = [];
  List<UstadzBadalItem> _daftarBadalList = [];
  RiwayatPresensiUstadz? _riwayatUstadz;
  bool _isLiburUstadz = false;
  String? _keteranganLiburUstadz;
  bool _isUjianUstadz = false;
  String? _namaUjianUstadz;
  int? _ujianIdUstadz;
  bool _isEventUstadz = false;
  EventPresensiInfo? _eventInfoUstadz;
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
  bool get isEventUstadz => _isEventUstadz;
  EventPresensiInfo? get eventInfoUstadz => _eventInfoUstadz;
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
      _isLiburUstadz = response.isLibur;
      _keteranganLiburUstadz = response.keteranganLibur;
      _isUjianUstadz = response.isUjian;
      _namaUjianUstadz = response.namaUjian;
      _ujianIdUstadz = response.ujianId;
      _isEventUstadz = response.isEvent;
      _eventInfoUstadz = response.eventInfo;
      _sortSesiUstadzList();
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoadingUstadz = false;
      notifyListeners();
    }
  }

  // === SORTING HELPERS (URUT BERDASARKAN URUTAN JAM & RUANGAN) ===
  void _sortSesiList() {
    _sesiList.sort((a, b) {
      if (_isEvent) {
        final sesiOrder = {'pagi': 1, 'siang': 2, 'malam': 3, 'harian': 4};
        final sA = sesiOrder[(a.sesi ?? '').toLowerCase()] ?? 9;
        final sB = sesiOrder[(b.sesi ?? '').toLowerCase()] ?? 9;
        if (sA != sB) return sA.compareTo(sB);
        return a.kelas.toLowerCase().compareTo(b.kelas.toLowerCase());
      }
      final weightA = JamOrderHelper.getWeight(a.jam, a.jamKe);
      final weightB = JamOrderHelper.getWeight(b.jam, b.jamKe);
      if (weightA != weightB) return weightA.compareTo(weightB);
      return a.kelas.toLowerCase().compareTo(b.kelas.toLowerCase());
    });
  }

  void _sortSesiUstadzList() {
    _sesiUstadzList.sort((a, b) {
      if (_isEventUstadz) {
        final sesiOrder = {'pagi': 1, 'siang': 2, 'malam': 3, 'harian': 4};
        final sA = sesiOrder[(a.sesi ?? '').toLowerCase()] ?? 9;
        final sB = sesiOrder[(b.sesi ?? '').toLowerCase()] ?? 9;
        return sA.compareTo(sB);
      }
      final weightA = JamOrderHelper.getWeight(a.jam, a.jamKe);
      final weightB = JamOrderHelper.getWeight(b.jam, b.jamKe);
      if (weightA != weightB) return weightA.compareTo(weightB);
      return a.ruangan.toLowerCase().compareTo(b.ruangan.toLowerCase());
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
    int? ustadzId,
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
        ustadzId: ustadzId,
        ustadzPenggantiId: ustadzPenggantiId,
        keterangan: keterangan,
      );
      if (success) {
        HapticHelper.confirmSuccess();
        await fetchSesiUstadz();
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

  Future<bool> checkinKegiatanUstadz({
    required int kalendarId,
    required String sesi,
    required String status,
    String? keterangan,
    DateTime? customDate,
  }) async {
    _isCheckingInUstadz = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final targetDate = customDate ?? _selectedDateUstadz;
      final dateStr = DateHelper.toYmd(targetDate);
      final success = await _repo.checkinKegiatanUstadz(
        kalendarId: kalendarId,
        tanggal: dateStr,
        sesi: sesi,
        status: status,
        keterangan: keterangan,
      );
      if (success) {
        HapticHelper.confirmSuccess();
        await fetchSesiUstadz();
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
    _isEvent = false;
    _eventInfo = null;
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
    _isEventUstadz = false;
    _eventInfoUstadz = null;
    _isLoadingUstadz = false;
    _isCheckingInUstadz = false;
    notifyListeners();
  }
}
