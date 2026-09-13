import 'package:flutter/material.dart';
import '../data/models/kas_ruangan_model.dart';
import '../data/models/koperasi_model.dart';
import '../data/models/tabungan_model.dart';
import '../data/models/tagihan_model.dart';
import '../data/repositories/wali_repository.dart';

class KeuanganProvider extends ChangeNotifier {
  final WaliRepository _repo = WaliRepository();

  bool _isLoading = false;
  bool _isLoadingTagihanWali = false;
  bool _isLoadingTabungan = false;
  bool _isLoadingKoperasi = false;
  bool _isLoadingKasRuangan = false;
  String? _errorMessage;

  final Map<int, RekapTagihanAnakModel> _rekapTagihanMap = {};
  RekapTagihanWaliModel? _rekapTagihanWali;
  final Map<int, TabunganAnakData> _tabunganDataMap = {};
  final Map<int, KoperasiAnakData> _koperasiDataMap = {};
  final Map<int, KasRuanganAnakData> _kasRuanganDataMap = {};

  int? _loadedAnakId;
  String? _selectedTabunganBulan;

  bool get isLoading => _isLoading;
  bool get isLoadingTagihanWali => _isLoadingTagihanWali;
  bool get isLoadingTabungan => _isLoadingTabungan;
  bool get isLoadingKoperasi => _isLoadingKoperasi;
  bool get isLoadingKasRuangan => _isLoadingKasRuangan;
  String? get errorMessage => _errorMessage;

  int? get loadedAnakId => _loadedAnakId;
  String? get selectedTabunganBulan => _selectedTabunganBulan;

  // Active child getters
  RekapTagihanAnakModel? get rekapTagihan =>
      _loadedAnakId != null ? _rekapTagihanMap[_loadedAnakId] : null;
  List<ItemSppModel> get sppList => rekapTagihan?.sppList ?? [];
  List<ItemNonSppModel> get nonSppList => rekapTagihan?.nonSppList ?? [];

  // Tagihan Wali Murid (KK / Keluarga)
  RekapTagihanWaliModel? get rekapTagihanWali => _rekapTagihanWali;
  List<ItemTagihanWaliModel> get tagihanWaliList =>
      _rekapTagihanWali?.items ?? [];

  TabunganAnakData? get tabunganData =>
      _loadedAnakId != null ? _tabunganDataMap[_loadedAnakId] : null;
  KoperasiAnakData? get koperasiData =>
      _loadedAnakId != null ? _koperasiDataMap[_loadedAnakId] : null;
  KasRuanganAnakData? get kasRuanganData =>
      _loadedAnakId != null ? _kasRuanganDataMap[_loadedAnakId] : null;

  // Multi-child specific helpers
  TabunganAnakData? getTabunganFor(int anakId) => _tabunganDataMap[anakId];
  RekapTagihanAnakModel? getTagihanFor(int anakId) => _rekapTagihanMap[anakId];
  KoperasiAnakData? getKoperasiFor(int anakId) => _koperasiDataMap[anakId];
  KasRuanganAnakData? getKasRuanganFor(int anakId) =>
      _kasRuanganDataMap[anakId];

  int getTotalFamilySavings(List<int> anakIds) {
    int total = 0;
    for (final id in anakIds) {
      total += _tabunganDataMap[id]?.rekening?.saldo ?? 0;
    }
    return total;
  }

  Future<void> fetchTagihan(int anakId, {bool force = false}) async {
    if (!force && _rekapTagihanMap.containsKey(anakId)) {
      _loadedAnakId = anakId;
      notifyListeners();
      return;
    }

    _isLoading = true;
    _loadedAnakId = anakId;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await _repo.getTagihanAnak(anakId);
      if (res != null) {
        _rekapTagihanMap[anakId] = res;
      }
    } catch (e) {
      _errorMessage = 'Gagal memuat tagihan: $e';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchTagihanWali({bool force = false}) async {
    if (!force && _rekapTagihanWali != null) {
      return;
    }

    _isLoadingTagihanWali = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await _repo.getTagihanWali();
      if (res != null) {
        _rekapTagihanWali = res;
      }
    } catch (e) {
      _errorMessage = 'Gagal memuat tagihan keluarga: $e';
    } finally {
      _isLoadingTagihanWali = false;
      notifyListeners();
    }
  }

  final Map<int, int?> _selectedTabunganIdMap = {};

  int? getSelectedTabunganId(int anakId) => _selectedTabunganIdMap[anakId];

  Future<void> fetchTabungan(
    int anakId, {
    String? bulan,
    int? tabunganId,
    bool force = false,
  }) async {
    final effectiveTabunganId = tabunganId ?? _selectedTabunganIdMap[anakId];
    if (!force &&
        _tabunganDataMap.containsKey(anakId) &&
        _selectedTabunganBulan == bulan &&
        (effectiveTabunganId == null ||
            _tabunganDataMap[anakId]?.rekening?.id == effectiveTabunganId)) {
      _loadedAnakId = anakId;
      notifyListeners();
      return;
    }

    _isLoadingTabungan = true;
    _loadedAnakId = anakId;
    _selectedTabunganBulan = bulan;
    if (tabunganId != null) {
      _selectedTabunganIdMap[anakId] = tabunganId;
    }
    notifyListeners();

    try {
      final res = await _repo.getTabunganAnak(
        anakId,
        bulan: bulan,
        tabunganId: effectiveTabunganId,
      );
      if (res != null) {
        _tabunganDataMap[anakId] = res;
        if (res.rekening != null) {
          _selectedTabunganIdMap[anakId] = res.rekening!.id;
        }
      }
    } catch (e) {
      _errorMessage = 'Gagal memuat tabungan: $e';
    } finally {
      _isLoadingTabungan = false;
      notifyListeners();
    }
  }

  Future<void> fetchKoperasi(int anakId, {bool force = false}) async {
    if (!force && _koperasiDataMap.containsKey(anakId)) {
      _loadedAnakId = anakId;
      notifyListeners();
      return;
    }

    _isLoadingKoperasi = true;
    _loadedAnakId = anakId;
    notifyListeners();

    try {
      final res = await _repo.getKoperasiAnak(anakId);
      if (res != null) {
        _koperasiDataMap[anakId] = res;
      }
    } catch (e) {
      _errorMessage = 'Gagal memuat riwayat koperasi: $e';
    } finally {
      _isLoadingKoperasi = false;
      notifyListeners();
    }
  }

  Future<void> fetchKasRuangan(int anakId, {bool force = false}) async {
    if (!force && _kasRuanganDataMap.containsKey(anakId)) {
      _loadedAnakId = anakId;
      notifyListeners();
      return;
    }

    _isLoadingKasRuangan = true;
    _loadedAnakId = anakId;
    notifyListeners();

    try {
      final res = await _repo.getKasRuanganAnak(anakId);
      if (res != null) {
        _kasRuanganDataMap[anakId] = res;
      }
    } catch (e) {
      _errorMessage = 'Gagal memuat kas ruangan: $e';
    } finally {
      _isLoadingKasRuangan = false;
      notifyListeners();
    }
  }

  Future<void> fetchAllKeuangan(int anakId, {bool force = false}) async {
    _loadedAnakId = anakId;
    await Future.wait([
      fetchTagihan(anakId, force: force),
      fetchTagihanWali(force: force),
      fetchTabungan(anakId, force: force),
      fetchKoperasi(anakId, force: force),
      fetchKasRuangan(anakId, force: force),
    ]);
  }

  /// Memuat data tabungan untuk semua anak dalam keluarga sekaligus
  Future<void> fetchAllChildrenTabungan(
    List<int> anakIds, {
    bool force = false,
  }) async {
    await Future.wait(anakIds.map((id) => fetchTabungan(id, force: force)));
  }

  bool _isSubmittingKomplain = false;
  bool get isSubmittingKomplain => _isSubmittingKomplain;

  /// Ajukan komplain setoran tunai oleh wali murid
  Future<Map<String, dynamic>> ajukanKomplain({
    required int anakId,
    required int transaksiId,
    required int nominalKlaim,
    required String alasan,
    String? fotoBukti,
  }) async {
    _isSubmittingKomplain = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await _repo.ajukanKomplainTabungan(
        transaksiId: transaksiId,
        nominalKlaim: nominalKlaim,
        alasan: alasan,
        fotoBukti: fotoBukti,
      );

      if (res['success'] == true) {
        await fetchTabungan(anakId, bulan: _selectedTabunganBulan, force: true);
      }
      return res;
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    } finally {
      _isSubmittingKomplain = false;
      notifyListeners();
    }
  }

  /// Batalkan komplain setoran tunai yang masih menunggu verifikasi
  Future<Map<String, dynamic>> batalkanKomplain({
    required int anakId,
    required int komplainId,
  }) async {
    _isSubmittingKomplain = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await _repo.batalkanKomplainTabungan(komplainId);
      if (res['success'] == true) {
        await fetchTabungan(anakId, bulan: _selectedTabunganBulan, force: true);
      }
      return res;
    } catch (e) {
      return {'success': false, 'message': e.toString()};
    } finally {
      _isSubmittingKomplain = false;
      notifyListeners();
    }
  }
}
