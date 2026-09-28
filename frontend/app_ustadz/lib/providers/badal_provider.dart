import 'package:flutter/material.dart';
import '../core/utils/date_helper.dart';
import '../core/utils/haptic_helper.dart';
import '../data/models/badal_model.dart';
import '../data/repositories/badal_repository.dart';

class BadalProvider extends ChangeNotifier {
  final BadalRepository _repo = BadalRepository();

  List<BadalRuanganItem> _ruanganList = [];
  BadalRuanganItem? _selectedRuangan;
  DateTime _selectedDate = DateTime.now();
  BadalJadwalResponse? _jadwalData;

  bool _isLoadingRuangan = false;
  bool _isLoadingJadwal = false;
  String? _errorMessage;

  // Getters
  List<BadalRuanganItem> get ruanganList => _ruanganList;
  BadalRuanganItem? get selectedRuangan => _selectedRuangan;
  DateTime get selectedDate => _selectedDate;
  BadalJadwalResponse? get jadwalData => _jadwalData;
  List<BadalJadwalItem> get jadwalList => _jadwalData?.data ?? [];

  bool get isLoadingRuangan => _isLoadingRuangan;
  bool get isLoadingJadwal => _isLoadingJadwal;
  bool get isLoading => _isLoadingRuangan || _isLoadingJadwal;
  String? get errorMessage => _errorMessage;

  bool get isLibur => _jadwalData?.isLibur ?? false;
  String? get keteranganLibur => _jadwalData?.keteranganLibur;
  bool get isUjian => _jadwalData?.isUjian ?? false;
  String? get namaUjian => _jadwalData?.namaUjian;
  int? get ujianId => _jadwalData?.ujianId;

  /// Ambil daftar seluruh ruangan aktif
  Future<void> fetchRuanganList({String? query}) async {
    _isLoadingRuangan = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _ruanganList = await _repo.getRuanganList(query: query);
      // Jika ruangan belum dipilih dan daftar ada, auto-pilih ruangan pertama
      if (_selectedRuangan == null && _ruanganList.isNotEmpty) {
        _selectedRuangan = _ruanganList.first;
        await fetchJadwalRuangan();
      }
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoadingRuangan = false;
      notifyListeners();
    }
  }

  /// Pilih ruangan dan langsung fetch jadwalnya
  void setSelectedRuangan(BadalRuanganItem? ruangan) {
    if (_selectedRuangan?.id == ruangan?.id) return;
    _selectedRuangan = ruangan;
    notifyListeners();

    if (_selectedRuangan != null) {
      fetchJadwalRuangan();
    } else {
      _jadwalData = null;
      notifyListeners();
    }
  }

  /// Ubah tanggal dan refresh jadwal
  void setSelectedDate(DateTime date) {
    _selectedDate = date;
    notifyListeners();
    if (_selectedRuangan != null) {
      fetchJadwalRuangan();
    }
  }

  /// Geser tanggal (prev/next day)
  void shiftDate(int days) {
    HapticHelper.selection();
    setSelectedDate(_selectedDate.add(Duration(days: days)));
  }

  /// Ambil data jadwal untuk ruangan dan tanggal terpilih
  Future<void> fetchJadwalRuangan() async {
    if (_selectedRuangan == null) return;

    _isLoadingJadwal = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final dateStr = DateHelper.toYmd(_selectedDate);
      _jadwalData = await _repo.getJadwalRuangan(_selectedRuangan!.id, dateStr);
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoadingJadwal = false;
      notifyListeners();
    }
  }

  /// Refresh penuh data ruangan dan jadwal
  Future<void> refresh() async {
    HapticHelper.light();
    await fetchRuanganList();
    if (_selectedRuangan != null) {
      await fetchJadwalRuangan();
    }
  }
}
