import 'dart:async';
import 'package:flutter/material.dart';
import '../core/services/bell_service.dart';
import '../data/models/akademik_model.dart';
import '../data/repositories/akademik_repository.dart';

class AkademikProvider extends ChangeNotifier {
  final AkademikRepository _repository = AkademikRepository();

  // ==========================================
  // 1. KALENDER PENDIDIKAN STATE
  // ==========================================
  bool _isLoadingKalendar = false;
  bool get isLoadingKalendar => _isLoadingKalendar;

  String? _kalendarError;
  String? get kalendarError => _kalendarError;

  KalendarPendidikanResponse? _kalendarData;
  KalendarPendidikanResponse? get kalendarData => _kalendarData;

  int? _selectedTahunId;
  int? get selectedTahunId => _selectedTahunId;

  DateTime _focusedMonth = DateTime.now();
  DateTime get focusedMonth => _focusedMonth;

  void setFocusedMonth(DateTime month) {
    _focusedMonth = month;
    notifyListeners();
  }

  Future<void> fetchKalendar({int? tahunId}) async {
    _isLoadingKalendar = true;
    _kalendarError = null;
    notifyListeners();

    try {
      _selectedTahunId = tahunId ?? _selectedTahunId;
      _kalendarData = await _repository.getKalendar(tahunId: _selectedTahunId);
    } catch (e) {
      _kalendarError = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoadingKalendar = false;
      notifyListeners();
    }
  }

  // ==========================================
  // 2. REFERENSI PELANGGARAN STATE
  // ==========================================
  bool _isLoadingReferensi = false;
  bool get isLoadingReferensi => _isLoadingReferensi;

  String? _referensiError;
  String? get referensiError => _referensiError;

  ReferensiPelanggaranResponse? _referensiData;
  ReferensiPelanggaranResponse? get referensiData => _referensiData;

  String _selectedKategori = 'Semua';
  String get selectedKategori => _selectedKategori;

  String _searchReferensi = '';
  String get searchReferensi => _searchReferensi;

  void setSelectedKategori(String kategori) {
    _selectedKategori = kategori;
    fetchReferensiPelanggaran();
  }

  void setSearchReferensi(String search) {
    _searchReferensi = search;
    fetchReferensiPelanggaran();
  }

  Future<void> fetchReferensiPelanggaran() async {
    _isLoadingReferensi = true;
    _referensiError = null;
    notifyListeners();

    try {
      _referensiData = await _repository.getReferensiPelanggaran(
        kategori: _selectedKategori,
        search: _searchReferensi,
      );
    } catch (e) {
      _referensiError = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoadingReferensi = false;
      notifyListeners();
    }
  }

  // ==========================================
  // 3. MATA PELAJARAN STATE
  // ==========================================
  bool _isLoadingMapel = false;
  bool get isLoadingMapel => _isLoadingMapel;

  String? _mapelError;
  String? get mapelError => _mapelError;

  MataPelajaranResponse? _mapelData;
  MataPelajaranResponse? get mapelData => _mapelData;

  int _selectedLevelId = 0; // 0 = Semua
  int get selectedLevelId => _selectedLevelId;

  String _searchMapel = '';
  String get searchMapel => _searchMapel;

  void setSelectedLevelId(int levelId) {
    _selectedLevelId = levelId;
    fetchMataPelajaran();
  }

  void setSearchMapel(String search) {
    _searchMapel = search;
    fetchMataPelajaran();
  }

  Future<void> fetchMataPelajaran() async {
    _isLoadingMapel = true;
    _mapelError = null;
    notifyListeners();

    try {
      _mapelData = await _repository.getMataPelajaran(
        levelId: _selectedLevelId,
        search: _searchMapel,
      );

      // Jika level belum dipilih (0) dan ada daftar level, otomatis pilih level pertama
      if (_selectedLevelId == 0 && (_mapelData?.levels.isNotEmpty ?? false)) {
        _selectedLevelId = _mapelData!.levels.first.id;
        _mapelData = await _repository.getMataPelajaran(
          levelId: _selectedLevelId,
          search: _searchMapel,
        );
      }
    } catch (e) {
      _mapelError = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoadingMapel = false;
      notifyListeners();
    }
  }

  // ==========================================
  // 4. JADWAL PELAJARAN STATE
  // ==========================================
  bool _isLoadingJadwal = false;
  bool get isLoadingJadwal => _isLoadingJadwal;

  String? _jadwalError;
  String? get jadwalError => _jadwalError;

  JadwalPelajaranResponse? _jadwalData;
  JadwalPelajaranResponse? get jadwalData => _jadwalData;

  String _selectedHari = 'Ahad';
  String get selectedHari => _selectedHari;

  int _jadwalModeIndex =
      0; // 0 = Jadwal Mengajar Saya, 1 = Jadwal Kelas / Ruangan Binaan
  int get jadwalModeIndex => _jadwalModeIndex;

  void setJadwalModeIndex(int index) {
    _jadwalModeIndex = index;
    notifyListeners();
  }

  List<HariJadwalItem> get activeJadwalPerHari {
    if (_jadwalData == null) return [];
    if (_jadwalModeIndex == 1 && _jadwalData!.isWaliRuangan) {
      return _jadwalData!.jadwalRuanganPerHari;
    }
    return _jadwalData!.jadwalPerHari;
  }

  void setSelectedHari(String hari) {
    _selectedHari = hari;
    notifyListeners();
  }

  Future<void> fetchJadwalPelajaran() async {
    _isLoadingJadwal = true;
    _jadwalError = null;
    notifyListeners();

    try {
      _jadwalData = await _repository.getJadwalPelajaran();
      if (_jadwalData != null) {
        unawaited(
          BellService.instance.updateTeachingScheduleFromJadwal(
            _jadwalData!.jadwalPerHari,
          ),
        );
      }
    } catch (e) {
      _jadwalError = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoadingJadwal = false;
      notifyListeners();
    }
  }

  // ==========================================
  // 5. JADWAL UJIAN STATE (Filter Ruangan Kelas & Agenda Ujian)
  // ==========================================
  bool _isLoadingJadwalUjian = false;
  bool get isLoadingJadwalUjian => _isLoadingJadwalUjian;

  String? _jadwalUjianError;
  String? get jadwalUjianError => _jadwalUjianError;

  JadwalUjianResponse? _jadwalUjianData;
  JadwalUjianResponse? get jadwalUjianData => _jadwalUjianData;

  int? _selectedRuanganUjianId;
  int? get selectedRuanganUjianId => _selectedRuanganUjianId;

  int? _selectedAgendaUjianId;
  int? get selectedAgendaUjianId => _selectedAgendaUjianId;

  bool _onlyMyJadwalUjian = false;
  bool get onlyMyJadwalUjian => _onlyMyJadwalUjian;

  void selectRuanganUjian(int ruanganId) {
    if (_selectedRuanganUjianId == ruanganId) return;
    _selectedRuanganUjianId = ruanganId;
    _selectedAgendaUjianId =
        null; // Reset agar otomatis ambil agenda valid untuk ruangan itu
    fetchJadwalUjian(ruanganId: ruanganId);
  }

  void selectAgendaUjian(int ujianId) {
    if (_selectedAgendaUjianId == ujianId) return;
    _selectedAgendaUjianId = ujianId;
    fetchJadwalUjian(ruanganId: _selectedRuanganUjianId, ujianId: ujianId);
  }

  void setOnlyMyJadwalUjian(bool onlyMe) {
    if (_onlyMyJadwalUjian == onlyMe) return;
    _onlyMyJadwalUjian = onlyMe;
    fetchJadwalUjian();
  }

  Future<void> fetchJadwalUjian({int? ruanganId, int? ujianId}) async {
    _isLoadingJadwalUjian = true;
    _jadwalUjianError = null;
    notifyListeners();

    try {
      _jadwalUjianData = await _repository.getJadwalUjian(
        ruanganId: ruanganId ?? _selectedRuanganUjianId,
        ujianId: ujianId ?? _selectedAgendaUjianId,
        onlyMe: _onlyMyJadwalUjian,
      );

      if (_jadwalUjianData != null) {
        _selectedRuanganUjianId = _jadwalUjianData!.selectedRuanganId;
        _selectedAgendaUjianId = _jadwalUjianData!.selectedUjianId;
      }
    } catch (e) {
      _jadwalUjianError = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoadingJadwalUjian = false;
      notifyListeners();
    }
  }

  void reset() {
    _isLoadingKalendar = false;
    _kalendarError = null;
    _kalendarData = null;
    _selectedTahunId = null;
    _focusedMonth = DateTime.now();

    _isLoadingReferensi = false;
    _referensiError = null;
    _referensiData = null;
    _selectedKategori = 'Semua';

    _isLoadingMapel = false;
    _mapelError = null;
    _mapelData = null;
    _selectedLevelId = 0;
    _searchMapel = '';

    _isLoadingJadwal = false;
    _jadwalError = null;
    _jadwalData = null;
    _selectedHari = 'Ahad';
    _jadwalModeIndex = 0;

    _isLoadingJadwalUjian = false;
    _jadwalUjianError = null;
    _jadwalUjianData = null;
    _selectedRuanganUjianId = null;
    _selectedAgendaUjianId = null;
    _onlyMyJadwalUjian = false;
    notifyListeners();
  }
}
