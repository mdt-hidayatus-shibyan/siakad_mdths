import 'package:flutter/material.dart';
import '../core/utils/haptic_helper.dart';
import '../data/models/panitia_imni_model.dart';
import '../data/repositories/panitia_imni_repository.dart';

class PanitiaImniProvider extends ChangeNotifier {
  final PanitiaImniRepository _repo = PanitiaImniRepository();

  // =========================================================================
  // STATE: GENERAL & ERROR
  // =========================================================================
  String? _errorMessage;
  String? get errorMessage => _errorMessage;
  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }

  // =========================================================================
  // STATE: 1. PEMBAYARAN IMNI
  // =========================================================================
  PembayaranImniRingkasanModel? _pembayaranRingkasan;
  List<PesertaImniItem> _pesertaList = [];
  int? _filterTingkatId;
  int? _filterRuanganId;
  String? _filterStatusBayar;
  String _searchQueryPeserta = '';
  bool _isLoadingPembayaran = false;
  bool _isSubmittingPembayaran = false;

  PembayaranImniRingkasanModel? get pembayaranRingkasan => _pembayaranRingkasan;
  List<PesertaImniItem> get pesertaList => _pesertaList;
  int? get filterTingkatId => _filterTingkatId;
  int? get filterRuanganId => _filterRuanganId;
  String? get filterStatusBayar => _filterStatusBayar;
  String get searchQueryPeserta => _searchQueryPeserta;
  bool get isLoadingPembayaran => _isLoadingPembayaran;
  bool get isSubmittingPembayaran => _isSubmittingPembayaran;

  List<RuanganOptionItem> get pembayaranDaftarRuangan => _pembayaranRingkasan?.daftarRuangan ?? [];

  Future<void> fetchPembayaranRingkasan({int? tahunId}) async {
    try {
      _pembayaranRingkasan = await _repo.getPembayaranRingkasan(tahunId: tahunId);
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      notifyListeners();
    }
  }

  Future<void> fetchPesertaList({
    int? tahunId,
    int? tingkatId,
    int? ruanganId,
    String? statusPembayaran,
    String? search,
  }) async {
    _isLoadingPembayaran = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final tId = tingkatId ?? _filterTingkatId;
      final rId = ruanganId ?? _filterRuanganId;
      final sBayar = statusPembayaran ?? _filterStatusBayar;
      final qSearch = search ?? _searchQueryPeserta;

      _pesertaList = await _repo.getPembayaranPesertaList(
        tahunId: tahunId,
        tingkatId: tId,
        ruanganId: rId,
        statusPembayaran: sBayar,
        search: qSearch,
      );
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoadingPembayaran = false;
      notifyListeners();
    }
  }

  void setFilterTingkat(int? tingkatId) {
    if (_filterTingkatId == tingkatId) return;
    _filterTingkatId = tingkatId;
    fetchPesertaList();
  }

  void setFilterRuangan(int? ruanganId) {
    if (_filterRuanganId == ruanganId) return;
    _filterRuanganId = ruanganId;
    fetchPesertaList();
  }

  void setFilterStatusBayar(String? status) {
    if (_filterStatusBayar == status) return;
    _filterStatusBayar = status;
    fetchPesertaList();
  }

  void setSearchPeserta(String query) {
    _searchQueryPeserta = query;
    fetchPesertaList();
  }

  Future<bool> simpanPembayaran({
    required int pesertaId,
    required double nominalBayar,
    String? tanggalBayar,
    String? namaPenyetor,
    String? metodePembayaran,
    String? keterangan,
  }) async {
    _isSubmittingPembayaran = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await _repo.simpanPembayaran(
        pesertaId: pesertaId,
        nominalBayar: nominalBayar,
        tanggalBayar: tanggalBayar,
        namaPenyetor: namaPenyetor,
        metodePembayaran: metodePembayaran,
        keterangan: keterangan,
      );
      HapticHelper.confirmSuccess();
      // Refresh ringkasan & list
      await fetchPembayaranRingkasan();
      await fetchPesertaList();
      return true;
    } catch (e) {
      HapticHelper.warning();
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isSubmittingPembayaran = false;
      notifyListeners();
    }
  }

  Future<bool> batalPembayaran(int pembayaranId) async {
    _isSubmittingPembayaran = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final ok = await _repo.batalPembayaran(pembayaranId);
      if (ok) {
        HapticHelper.medium();
        await fetchPembayaranRingkasan();
        await fetchPesertaList();
      }
      return ok;
    } catch (e) {
      HapticHelper.warning();
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isSubmittingPembayaran = false;
      notifyListeners();
    }
  }

  // =========================================================================
  // STATE: 2. PENGELUARAN IMNI
  // =========================================================================
  PengeluaranImniRingkasanModel? _pengeluaranRingkasan;
  List<PengeluaranImniItem> _pengeluaranList = [];
  String? _filterKategoriPengeluaran;
  String _searchQueryPengeluaran = '';
  bool _isLoadingPengeluaran = false;
  bool _isSubmittingPengeluaran = false;

  PengeluaranImniRingkasanModel? get pengeluaranRingkasan => _pengeluaranRingkasan;
  List<PengeluaranImniItem> get pengeluaranList => _pengeluaranList;
  String? get filterKategoriPengeluaran => _filterKategoriPengeluaran;
  String get searchQueryPengeluaran => _searchQueryPengeluaran;
  bool get isLoadingPengeluaran => _isLoadingPengeluaran;
  bool get isSubmittingPengeluaran => _isSubmittingPengeluaran;

  Future<void> fetchPengeluaranRingkasan({int? tahunId}) async {
    try {
      _pengeluaranRingkasan = await _repo.getPengeluaranRingkasan(tahunId: tahunId);
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      notifyListeners();
    }
  }

  Future<void> fetchPengeluaranList({
    int? tahunId,
    String? kategori,
    String? search,
  }) async {
    _isLoadingPengeluaran = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final kat = kategori ?? _filterKategoriPengeluaran;
      final qSearch = search ?? _searchQueryPengeluaran;

      _pengeluaranList = await _repo.getPengeluaranList(
        tahunId: tahunId,
        kategori: kat,
        search: qSearch,
      );
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoadingPengeluaran = false;
      notifyListeners();
    }
  }

  void setFilterKategori(String? kategori) {
    if (_filterKategoriPengeluaran == kategori) return;
    _filterKategoriPengeluaran = kategori;
    fetchPengeluaranList();
  }

  void setSearchPengeluaran(String query) {
    _searchQueryPengeluaran = query;
    fetchPengeluaranList();
  }

  Future<bool> simpanPengeluaran({
    required String kategori,
    required String judulPengeluaran,
    required double nominal,
    required String tanggalPengeluaran,
    String? penerimaDana,
    String? metodePembayaran,
    String? keterangan,
    String? buktiNotaPath,
  }) async {
    _isSubmittingPengeluaran = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await _repo.simpanPengeluaran(
        kategori: kategori,
        judulPengeluaran: judulPengeluaran,
        nominal: nominal,
        tanggalPengeluaran: tanggalPengeluaran,
        penerimaDana: penerimaDana,
        metodePembayaran: metodePembayaran,
        keterangan: keterangan,
        buktiNotaPath: buktiNotaPath,
      );
      HapticHelper.confirmSuccess();
      await fetchPengeluaranRingkasan();
      await fetchPengeluaranList();
      return true;
    } catch (e) {
      HapticHelper.warning();
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isSubmittingPengeluaran = false;
      notifyListeners();
    }
  }

  Future<bool> updatePengeluaran({
    required int id,
    required String kategori,
    required String judulPengeluaran,
    required double nominal,
    required String tanggalPengeluaran,
    String? penerimaDana,
    String? metodePembayaran,
    String? keterangan,
    String? buktiNotaPath,
  }) async {
    _isSubmittingPengeluaran = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await _repo.updatePengeluaran(
        id: id,
        kategori: kategori,
        judulPengeluaran: judulPengeluaran,
        nominal: nominal,
        tanggalPengeluaran: tanggalPengeluaran,
        penerimaDana: penerimaDana,
        metodePembayaran: metodePembayaran,
        keterangan: keterangan,
        buktiNotaPath: buktiNotaPath,
      );
      HapticHelper.confirmSuccess();
      await fetchPengeluaranRingkasan();
      await fetchPengeluaranList();
      return true;
    } catch (e) {
      HapticHelper.warning();
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isSubmittingPengeluaran = false;
      notifyListeners();
    }
  }

  Future<bool> hapusPengeluaran(int id) async {
    _isSubmittingPengeluaran = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final ok = await _repo.hapusPengeluaran(id);
      if (ok) {
        HapticHelper.medium();
        await fetchPengeluaranRingkasan();
        await fetchPengeluaranList();
      }
      return ok;
    } catch (e) {
      HapticHelper.warning();
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isSubmittingPengeluaran = false;
      notifyListeners();
    }
  }

  // =========================================================================
  // STATE: 3. PRESENSI UJIAN IMNI (TAB TPQ & TAB 6 IBT / 3 TSA)
  // =========================================================================
  PresensiImniDataResponse? _presensiData;
  String _presensiKategori = 'tpq'; // 'tpq' or 'imni'
  int? _presensiRuanganId;
  int? _presensiJadwalId;
  String? _presensiTanggalUjian;
  int? _presensiRuanganImniId;
  List<PresensiImniMuridItem> _presensiMuridList = [];
  PengawasPresensiData? _presensiPengawas;
  bool _isLoadingPresensi = false;
  bool _isSavingPresensi = false;

  PresensiImniDataResponse? get presensiData => _presensiData;
  String get presensiKategori => _presensiKategori;
  int? get presensiRuanganId => _presensiRuanganId;
  int? get presensiJadwalId => _presensiJadwalId;
  String? get presensiTanggalUjian => _presensiTanggalUjian;
  int? get presensiRuanganImniId => _presensiRuanganImniId;
  List<PresensiImniMuridItem> get presensiMuridList => _presensiMuridList;
  PengawasPresensiData? get presensiPengawas => _presensiPengawas;
  bool get isLoadingPresensi => _isLoadingPresensi;
  bool get isSavingPresensi => _isSavingPresensi;

  // Mode TPQ Getters
  List<RuanganOptionItem> get presensiDaftarRuangan => _presensiData?.daftarRuangan ?? [];
  List<JadwalOptionItem> get presensiJadwalList => _presensiData?.jadwalList ?? [];
  RuanganOptionItem? get currentPresensiRuangan {
    if (_presensiData == null || _presensiData!.daftarRuangan.isEmpty) return null;
    return _presensiData!.daftarRuangan.firstWhere(
      (r) => r.id == _presensiRuanganId,
      orElse: () => _presensiData!.daftarRuangan.first,
    );
  }
  JadwalOptionItem? get currentPresensiJadwal {
    if (_presensiData == null || _presensiData!.jadwalList.isEmpty) return null;
    return _presensiData!.jadwalList.firstWhere(
      (j) => j.id == _presensiJadwalId,
      orElse: () => _presensiData!.jadwalList.first,
    );
  }

  // Mode IMNI Getters
  List<HariUjianImniItem> get presensiDaftarHariUjian => _presensiData?.daftarHariUjian ?? [];
  String? get presensiSelectedTanggal => _presensiTanggalUjian ?? _presensiData?.selectedTanggal;
  HariUjianImniItem? get presensiSelectedHariInfo => _presensiData?.selectedHariInfo;
  List<RuanganImniOptionItem> get presensiDaftarRuanganImni => _presensiData?.daftarRuanganImni ?? [];
  int? get presensiSelectedRuanganImniId => _presensiRuanganImniId ?? _presensiData?.selectedRuanganImniId;
  RuanganImniOptionItem? get presensiSelectedRuanganImni => _presensiData?.selectedRuanganImni;

  List<Map<String, dynamic>> get presensiDaftarBadal => _presensiData?.daftarBadal ?? [];

  // Live Summary Presensi
  int get totalPresensiMurid => _presensiMuridList.length;
  int get countPresensiHadir => _presensiMuridList.where((m) => m.status == 'Hadir').length;
  int get countPresensiIzin => _presensiMuridList.where((m) => m.status == 'Izin').length;
  int get countPresensiSakit => _presensiMuridList.where((m) => m.status == 'Sakit').length;
  int get countPresensiAlpha => _presensiMuridList.where((m) => m.status == 'Alpha').length;
  int get countPresensiDispensasi => _presensiMuridList.where((m) => m.status == 'Dispensasi').length;
  int get countPresensiBelum => _presensiMuridList.where((m) => m.status == null || m.status!.isEmpty).length;

  Future<void> fetchPresensiData({
    String? kategori,
    int? tahunId,
    int? ruanganId,
    int? jadwalUjianId,
    String? tanggalUjian,
    int? ruanganImniId,
  }) async {
    _isLoadingPresensi = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final kat = kategori ?? _presensiKategori;
      final rId = ruanganId ?? _presensiRuanganId;
      final jId = jadwalUjianId ?? _presensiJadwalId;
      final tgl = tanggalUjian ?? _presensiTanggalUjian;
      final rImniId = ruanganImniId ?? _presensiRuanganImniId;

      _presensiData = await _repo.getPresensiData(
        kategori: kat,
        tahunId: tahunId,
        ruanganId: rId,
        jadwalUjianId: jId,
        tanggalUjian: tgl,
        ruanganImniId: rImniId,
      );

      _presensiKategori = _presensiData?.kategori ?? kat;
      _presensiRuanganId = _presensiData?.selectedRuanganId;
      _presensiJadwalId = _presensiData?.selectedJadwalId;
      _presensiTanggalUjian = _presensiData?.selectedTanggal;
      _presensiRuanganImniId = _presensiData?.selectedRuanganImniId;
      _presensiMuridList = List<PresensiImniMuridItem>.from(_presensiData?.muridList ?? []);
      _presensiPengawas = _presensiData?.pengawas != null
          ? PengawasPresensiData(
              ustadzId: _presensiData!.pengawas!.ustadzId,
              ustadzNama: _presensiData!.pengawas!.ustadzNama,
              ustadzPenggantiId: _presensiData!.pengawas!.ustadzPenggantiId,
              ustadzPenggantiNama: _presensiData!.pengawas!.ustadzPenggantiNama,
              status: _presensiData!.pengawas!.status,
              catatanBeritaAcara: _presensiData!.pengawas!.catatanBeritaAcara,
            )
          : null;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoadingPresensi = false;
      notifyListeners();
    }
  }

  void setPresensiKategori(String kategori) {
    if (_presensiKategori == kategori) return;
    _presensiKategori = kategori;
    _presensiRuanganId = null;
    _presensiJadwalId = null;
    _presensiTanggalUjian = null;
    _presensiRuanganImniId = null;
    fetchPresensiData(kategori: kategori);
  }

  void selectPresensiRuangan(int ruanganId) {
    if (_presensiRuanganId == ruanganId) return;
    _presensiRuanganId = ruanganId;
    _presensiJadwalId = null;
    fetchPresensiData(kategori: 'tpq', ruanganId: ruanganId);
  }

  void selectPresensiJadwal(int jadwalId) {
    if (_presensiJadwalId == jadwalId) return;
    _presensiJadwalId = jadwalId;
    fetchPresensiData(kategori: 'tpq', jadwalUjianId: jadwalId);
  }

  void selectPresensiTanggal(String tanggal) {
    if (_presensiTanggalUjian == tanggal) return;
    _presensiTanggalUjian = tanggal;
    fetchPresensiData(kategori: 'imni', tanggalUjian: tanggal);
  }

  void selectPresensiRuanganImni(int ruanganImniId) {
    if (_presensiRuanganImniId == ruanganImniId) return;
    _presensiRuanganImniId = ruanganImniId;
    fetchPresensiData(kategori: 'imni', ruanganImniId: ruanganImniId);
  }

  void setPresensiStatus(int muridId, String? status) {
    final idx = _presensiMuridList.indexWhere((m) => m.muridId == muridId);
    if (idx != -1) {
      _presensiMuridList[idx].status = status;
      HapticHelper.light();
      notifyListeners();
    }
  }

  void setPresensiCatatan(int muridId, String? catatan) {
    final idx = _presensiMuridList.indexWhere((m) => m.muridId == muridId);
    if (idx != -1) {
      _presensiMuridList[idx].catatan = catatan;
      notifyListeners();
    }
  }

  void setAllPresensiStatus(String status) {
    for (var m in _presensiMuridList) {
      m.status = status;
    }
    HapticHelper.segmentTick();
    notifyListeners();
  }

  void setSemuaPresensiKosong() {
    for (var m in _presensiMuridList) {
      m.status = null;
    }
    HapticHelper.segmentTick();
    notifyListeners();
  }

  void updatePengawasStatus(String status) {
    HapticHelper.selection();
    _presensiPengawas ??= PengawasPresensiData();
    _presensiPengawas!.status = status;
    if (status != 'Badal' && status != 'Digantikan') {
      _presensiPengawas!.ustadzPenggantiId = null;
      _presensiPengawas!.ustadzPenggantiNama = null;
    }
    notifyListeners();
  }

  void updatePengawasPengganti(int? badalId, String? badalNama) {
    HapticHelper.selection();
    _presensiPengawas ??= PengawasPresensiData();
    _presensiPengawas!.ustadzPenggantiId = badalId;
    _presensiPengawas!.ustadzPenggantiNama = badalNama;
    _presensiPengawas!.status = 'Badal';
    notifyListeners();
  }

  void updateBeritaAcara(String? catatan) {
    _presensiPengawas ??= PengawasPresensiData();
    _presensiPengawas!.catatanBeritaAcara = catatan;
    notifyListeners();
  }

  void updatePengawasData({
    int? ustadzId,
    String? ustadzNama,
    int? ustadzPenggantiId,
    String? ustadzPenggantiNama,
    String? status,
    String? catatanBeritaAcara,
  }) {
    _presensiPengawas ??= PengawasPresensiData();
    if (ustadzId != null) _presensiPengawas!.ustadzId = ustadzId;
    if (ustadzNama != null) _presensiPengawas!.ustadzNama = ustadzNama;
    if (ustadzPenggantiId != null) _presensiPengawas!.ustadzPenggantiId = ustadzPenggantiId;
    if (ustadzPenggantiNama != null) _presensiPengawas!.ustadzPenggantiNama = ustadzPenggantiNama;
    if (status != null) _presensiPengawas!.status = status;
    if (catatanBeritaAcara != null) _presensiPengawas!.catatanBeritaAcara = catatanBeritaAcara;
    notifyListeners();
  }

  Future<bool> simpanPresensi() async {
    if (_presensiKategori == 'tpq') {
      if (_presensiRuanganId == null || _presensiJadwalId == null) {
        _errorMessage = 'Pilih ruangan kelas dan mata pelajaran ujian terlebih dahulu!';
        notifyListeners();
        return false;
      }
    } else {
      if (_presensiRuanganImniId == null || _presensiTanggalUjian == null) {
        _errorMessage = 'Pilih tanggal ujian dan ruangan IMNI terlebih dahulu!';
        notifyListeners();
        return false;
      }
    }

    _isSavingPresensi = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final presensiPayload = <String, dynamic>{};

      for (var m in _presensiMuridList) {
        if (m.status != null) {
          presensiPayload[m.muridId.toString()] = {
            'status': m.status,
            'catatan': m.catatan,
            'tingkat_id': m.tingkatId,
          };
        }
      }

      final pengawasPayload = _presensiPengawas?.toJson();

      final ok = await _repo.simpanPresensi(
        kategori: _presensiKategori,
        ujianId: _presensiData?.ujian != null ? _presensiData!.ujian['id'] : null,
        ruanganId: _presensiRuanganId,
        jadwalUjianId: _presensiJadwalId,
        ruanganImniId: _presensiRuanganImniId,
        tanggalUjian: _presensiTanggalUjian,
        presensi: presensiPayload,
        pengawas: pengawasPayload,
      );

      if (ok) {
        HapticHelper.confirmSuccess();
        await fetchPresensiData();
      }
      return ok;
    } catch (e) {
      HapticHelper.warning();
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isSavingPresensi = false;
      notifyListeners();
    }
  }

  // =========================================================================
  // STATE: 4. NILAI & LEGER IMNI
  // =========================================================================
  NilaiImniDataResponse? _nilaiData;
  int? _nilaiRuanganId;
  int? _nilaiJadwalId;
  List<NilaiImniMuridItem> _nilaiMuridList = [];
  bool _isLoadingNilai = false;
  bool _isSavingNilai = false;

  LegerImniDataResponse? _legerData;
  int? _legerRuanganId;
  bool _isLoadingLeger = false;

  NilaiImniDataResponse? get nilaiData => _nilaiData;
  int? get nilaiRuanganId => _nilaiRuanganId;
  int? get nilaiJadwalId => _nilaiJadwalId;
  List<NilaiImniMuridItem> get nilaiMuridList => _nilaiMuridList;
  bool get isLoadingNilai => _isLoadingNilai;
  bool get isSavingNilai => _isSavingNilai;

  List<RuanganOptionItem> get nilaiDaftarRuangan => _nilaiData?.daftarRuangan ?? [];
  List<JadwalOptionItem> get nilaiJadwalList => _nilaiData?.jadwalList ?? [];

  LegerImniDataResponse? get legerData => _legerData;
  int? get legerRuanganId => _legerRuanganId;
  List<RuanganOptionItem> get legerDaftarRuangan =>
      (_legerData?.daftarRuangan.isNotEmpty == true)
          ? _legerData!.daftarRuangan
          : nilaiDaftarRuangan;
  bool get isLoadingLeger => _isLoadingLeger;

  Future<void> fetchNilaiData({
    int? tahunId,
    int? ruanganId,
    int? jadwalUjianId,
  }) async {
    _isLoadingNilai = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final rId = ruanganId ?? _nilaiRuanganId;
      final jId = jadwalUjianId ?? _nilaiJadwalId;

      _nilaiData = await _repo.getNilaiData(
        tahunId: tahunId,
        ruanganId: rId,
        jadwalUjianId: jId,
      );

      _nilaiRuanganId = _nilaiData?.selectedRuanganId;
      _nilaiJadwalId = _nilaiData?.selectedJadwalId;
      _nilaiMuridList = List<NilaiImniMuridItem>.from(_nilaiData?.murids ?? []);
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoadingNilai = false;
      notifyListeners();
    }
  }

  void selectNilaiRuangan(int ruanganId) {
    if (_nilaiRuanganId == ruanganId) return;
    _nilaiRuanganId = ruanganId;
    _nilaiJadwalId = null;
    fetchNilaiData(ruanganId: ruanganId);
  }

  void selectNilaiJadwal(int jadwalId) {
    if (_nilaiJadwalId == jadwalId) return;
    _nilaiJadwalId = jadwalId;
    fetchNilaiData(jadwalUjianId: jadwalId);
  }

  void updateNilaiMurid(int muridId, double? score) {
    final idx = _nilaiMuridList.indexWhere((m) => m.muridId == muridId);
    if (idx != -1) {
      _nilaiMuridList[idx].nilai = score;
      notifyListeners();
    }
  }

  Future<bool> simpanNilai({required String action}) async {
    if (_nilaiData?.ujian == null || _nilaiRuanganId == null || _nilaiJadwalId == null) {
      _errorMessage = 'Data form nilai belum lengkap';
      notifyListeners();
      return false;
    }

    _isSavingNilai = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final ujianId = _nilaiData!.ujian['id'] ?? 0;
      final nilaiPayload = <String, dynamic>{};

      for (var m in _nilaiMuridList) {
        if (!m.isLocked && m.nilai != null) {
          nilaiPayload[m.muridId.toString()] = m.nilai;
        }
      }

      await _repo.simpanNilai(
        ujianId: ujianId,
        ruanganId: _nilaiRuanganId!,
        jadwalUjianId: _nilaiJadwalId!,
        action: action,
        nilai: nilaiPayload,
      );

      HapticHelper.confirmSuccess();
      await fetchNilaiData();
      return true;
    } catch (e) {
      HapticHelper.warning();
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      return false;
    } finally {
      _isSavingNilai = false;
      notifyListeners();
    }
  }

  Future<void> fetchLegerData({int? tahunId, int? ruanganId}) async {
    _isLoadingLeger = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final rId = ruanganId ?? _legerRuanganId;
      _legerData = await _repo.getLegerNilai(tahunId: tahunId, ruanganId: rId);
      _legerRuanganId = _legerData?.selectedRuanganId ?? rId;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
    } finally {
      _isLoadingLeger = false;
      notifyListeners();
    }
  }

  void selectLegerRuangan(int? ruanganId) {
    _legerRuanganId = ruanganId;
    fetchLegerData(ruanganId: ruanganId);
  }
}
