import 'dart:async';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/date_helper.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/panitia_ikhtibar_model.dart';
import '../../../providers/panitia_ikhtibar_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/kwitansi_pembayaran_screen.dart';
import '../../widgets/segmented_tab_bar.dart';
import '../../widgets/shimmer_loading.dart';

class KasirIkhtibarScreen extends StatefulWidget {
  const KasirIkhtibarScreen({super.key});

  @override
  State<KasirIkhtibarScreen> createState() => _KasirIkhtibarScreenState();
}

class _KasirIkhtibarScreenState extends State<KasirIkhtibarScreen> {
  int _selectedTab = 0; // 0: Kasir, 1: Leger, 2: Riwayat

  final TextEditingController _kasirSearchCtrl = TextEditingController();
  final TextEditingController _legerSearchCtrl = TextEditingController();
  Timer? _searchDebounce;
  String _activeKasirQuery = '';
  bool _isSearchingKasirDirect = false;
  TagihanKkIkhtibarItem? _kasirDirectResult;

  @override
  void initState() {
    super.initState();
    _legerSearchCtrl.addListener(() {
      if (mounted) setState(() {});
    });
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadData();
    });
  }

  @override
  void dispose() {
    _searchDebounce?.cancel();
    _kasirSearchCtrl.dispose();
    _legerSearchCtrl.dispose();
    super.dispose();
  }

  Future<void> _performKasirSearch() async {
    FocusScope.of(context).unfocus();
    HapticHelper.light();
    final query = _kasirSearchCtrl.text.trim();
    if (query.isEmpty) {
      setState(() {
        _activeKasirQuery = '';
        _kasirDirectResult = null;
        _isSearchingKasirDirect = false;
      });
      return;
    }

    setState(() {
      _activeKasirQuery = query;
      _isSearchingKasirDirect = true;
    });

    final provider = context.read<PanitiaIkhtibarProvider>();
    final cleanQ = query.replaceAll(RegExp(r'[^0-9]'), '');

    // 1. Coba cari di memori lokal (_tagihanList)
    TagihanKkIkhtibarItem? match;
    for (final t in provider.tagihanList) {
      if (t.noRegistrasi == query ||
          (cleanQ.isNotEmpty && t.noRegistrasi == cleanQ)) {
        match = t;
        break;
      }
    }
    if (match == null && cleanQ.isNotEmpty) {
      final intVal = int.tryParse(cleanQ);
      if (intVal != null && intVal < 1000) {
        final offset = (50000 + intVal).toString();
        for (final t in provider.tagihanList) {
          if (t.noRegistrasi == offset) {
            match = t;
            break;
          }
        }
      }
    }
    if (match == null) {
      for (final t in provider.tagihanList) {
        if (t.namaKepalaKeluarga.toLowerCase().contains(query.toLowerCase())) {
          match = t;
          break;
        }
      }
    }

    // 2. Jika tidak ditemukan di memori lokal, panggil API backend secara langsung!
    if (match == null) {
      try {
        final serverResults = await provider.searchKasirDirect(query);
        if (serverResults.isNotEmpty) {
          for (final t in serverResults) {
            if (t.noRegistrasi == query ||
                (cleanQ.isNotEmpty && t.noRegistrasi == cleanQ)) {
              match = t;
              break;
            }
          }
          if (match == null && cleanQ.isNotEmpty) {
            final intVal = int.tryParse(cleanQ);
            if (intVal != null && intVal < 1000) {
              final offset = (50000 + intVal).toString();
              for (final t in serverResults) {
                if (t.noRegistrasi == offset) {
                  match = t;
                  break;
                }
              }
            }
          }
          match ??= serverResults.first;
        }
      } catch (e) {
        debugPrint('Error direct search kasir: $e');
      }
    }

    if (mounted) {
      setState(() {
        _kasirDirectResult = match;
        _isSearchingKasirDirect = false;
      });
    }
  }

  void _clearKasirSearch() {
    _kasirSearchCtrl.clear();
    setState(() {
      _activeKasirQuery = '';
      _kasirDirectResult = null;
      _isSearchingKasirDirect = false;
    });
  }

  void _onLegerSearchChanged(String val) {
    _searchDebounce?.cancel();
    _searchDebounce = Timer(const Duration(milliseconds: 300), () {
      if (mounted) {
        context.read<PanitiaIkhtibarProvider>().setSearchQuery(val.trim());
      }
    });
  }

  Future<void> _loadData() async {
    final provider = context.read<PanitiaIkhtibarProvider>();
    await provider.fetchKasirRingkasan();
    provider.fetchKasirList();
    provider.fetchLaporanRiwayat();
  }

  void _shiftDate(int days) {
    HapticHelper.selection();
    final provider = context.read<PanitiaIkhtibarProvider>();
    final current = provider.filterLaporanTanggal;
    provider.setFilterLaporanTanggal(current.add(Duration(days: days)));
  }

  Future<void> _pickDate(BuildContext context) async {
    final provider = context.read<PanitiaIkhtibarProvider>();
    final picked = await showDatePicker(
      context: context,
      initialDate: provider.filterLaporanTanggal,
      firstDate: DateTime(2020),
      lastDate: DateTime(2035),
    );
    if (picked != null) {
      HapticHelper.light();
      provider.setFilterLaporanTanggal(picked);
    }
  }

  String _formatRupiah(num number) {
    final formatter = NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp ',
      decimalDigits: 0,
    );
    return formatter.format(number);
  }

  void _bukaHalamanKwitansiResult(
    Map<String, dynamic> data, {
    List<AnakKkItem>? daftarAnak,
  }) {
    HapticHelper.confirmSuccess();
    final provider = context.read<PanitiaIkhtibarProvider>();
    final ringkasan = provider.kasirRingkasan;
    final rincianSantri = (daftarAnak != null && daftarAnak.isNotEmpty)
        ? daftarAnak.map((a) => '${a.nama} (${a.ruangan})').join(', ')
        : null;

    final tglRaw = data['tanggal_bayar'] as String?;
    final tglBayar = tglRaw != null
        ? DateTime.tryParse(tglRaw)
        : DateTime.now();

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => KwitansiPembayaranScreen(
          judulKegiatan: 'Ikhtibar ${ringkasan?.ikhtibarKe ?? ""}'.trim(),
          subJudul: 'Tagihan Iuran Tingkat Keluarga (KK)',
          noTransaksi:
              data['no_transaksi'] ??
              'TRX/IKH/WLI/${data['no_registrasi'] ?? ""}',
          status: data['status_bayar'] ?? 'Lunas',
          nominalBayar: (data['nominal_bayar'] as num?) ?? 0,
          totalTagihan: (data['nominal_tagihan'] as num?),
          sisaTagihan: (data['sisa_tagihan'] as num?) ?? 0,
          namaPembayar: data['nama_kepala_keluarga'] ?? '-',
          noRegistrasi: data['no_registrasi'],
          kampung: data['kampung'],
          rincianSubjek: rincianSantri,
          metodePembayaran: data['metode_pembayaran'] ?? 'Tunai',
          tanggalBayar: tglBayar,
          kwitansiUrl: data['kwitansi_url'],
        ),
      ),
    );
  }

  void _bukaHalamanKwitansiTagihan(TagihanKkIkhtibarItem t) {
    HapticHelper.light();
    final provider = context.read<PanitiaIkhtibarProvider>();
    final ringkasan = provider.kasirRingkasan;
    final rincianSantri = t.daftarAnak.isNotEmpty
        ? t.daftarAnak.map((a) => '${a.nama} (${a.ruangan})').join(', ')
        : null;

    final noTrx =
        t.pembayaranTerakhir?.noTransaksi ??
        'TRX/IKH/WLI/${t.noRegistrasi}/${t.tagihanId}';

    final tglBayar = t.pembayaranTerakhir?.tanggal != null
        ? DateTime.tryParse(t.pembayaranTerakhir!.tanggal!)
        : null;

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => KwitansiPembayaranScreen(
          judulKegiatan: 'Ikhtibar ${ringkasan?.ikhtibarKe ?? ""}'.trim(),
          subJudul: 'Tagihan Iuran Tingkat Keluarga (KK)',
          noTransaksi: noTrx,
          status: t.statusBayar,
          nominalBayar: t.nominalBayar > 0 ? t.nominalBayar : t.nominalTagihan,
          totalTagihan: t.nominalTagihan,
          sisaTagihan: t.sisaTagihan,
          namaPembayar: t.namaKepalaKeluarga,
          noRegistrasi: t.noRegistrasi,
          kampung: t.kampung,
          rincianSubjek: rincianSantri,
          metodePembayaran: t.pembayaranTerakhir?.metode ?? 'Tunai',
          nomorTabungan: t.pembayaranTerakhir?.nomorTabungan,
          namaNasabah: t.pembayaranTerakhir?.namaNasabah,
          ruanganNasabah: t.pembayaranTerakhir?.ruanganNasabah,
          tanggalBayar: tglBayar,
          kwitansiUrl: t.kwitansiUrl ?? t.pembayaranTerakhir?.kwitansiUrl,
        ),
      ),
    );
  }

  void _bukaHalamanKwitansiRiwayat(RiwayatPembayaranIkhtibarItem r) {
    HapticHelper.light();
    final provider = context.read<PanitiaIkhtibarProvider>();
    final ikhtibarKe = provider.kasirRingkasan?.ikhtibarKe;
    final judulKegiatan = (ikhtibarKe != null && ikhtibarKe.isNotEmpty)
        ? 'Ikhtibar $ikhtibarKe'
        : 'Ikhtibar';
    final tglBayar = r.tanggal != null ? DateTime.tryParse(r.tanggal!) : null;

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => KwitansiPembayaranScreen(
          judulKegiatan: judulKegiatan,
          subJudul: 'Riwayat Setoran Panitia Ikhtibar',
          noTransaksi: r.noTransaksi,
          status: r.tipePembayaran,
          nominalBayar: r.nominal,
          namaPembayar: r.namaPenyetor,
          kampung: r.kampung,
          metodePembayaran: r.metode,
          nomorTabungan: r.nomorTabungan,
          namaNasabah: r.namaNasabah,
          ruanganNasabah: r.ruanganNasabah,
          tanggalBayar: tglBayar,
          kasir: r.diterimaOleh,
          kwitansiUrl: r.kwitansiUrl ?? r.kwitansiTagihanUrl,
        ),
      ),
    );
  }

  // =========================================================================
  // MODAL BOTTOM SHEET: PROSES BAYAR TAGIHAN KK
  // =========================================================================
  void _openBayarSheet(TagihanKkIkhtibarItem tagihan) {
    HapticHelper.light();
    DateTime selectedTanggalBayar = DateTime.now();
    final nominalCtrl = TextEditingController(
      text: tagihan.sisaTagihan.toString(),
    );
    final penyetorCtrl = TextEditingController(
      text: tagihan.namaKepalaKeluarga,
    );
    final catatanCtrl = TextEditingController();

    String metode = 'Tunai'; // 'Tunai', 'Potong Tabungan', 'Cicilan'
    TabunganKkItem? selectedTabungan = tagihan.daftarTabungan.isNotEmpty
        ? tagihan.daftarTabungan.first
        : null;

    final noTabunganManualCtrl = TextEditingController();
    final namaNasabahManualCtrl = TextEditingController();
    final ruanganNasabahManualCtrl = TextEditingController();

    if (selectedTabungan != null) {
      noTabunganManualCtrl.text = selectedTabungan.nomorRekening;
      namaNasabahManualCtrl.text = selectedTabungan.namaNasabah;
      ruanganNasabahManualCtrl.text = selectedTabungan.ruangan;
    }

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          final isDark = Theme.of(context).brightness == Brightness.dark;
          final bottomInset = MediaQuery.of(context).viewInsets.bottom;
          final systemBottom = MediaQuery.of(context).padding.bottom;
          final bottomPadding = bottomInset > 0
              ? (bottomInset + 20)
              : (systemBottom + 24);

          return Container(
            constraints: BoxConstraints(
              maxHeight: MediaQuery.of(context).size.height * 0.9,
            ),
            padding: EdgeInsets.fromLTRB(20, 16, 20, bottomPadding),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF1E2129) : Colors.white,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(24),
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.15),
                  blurRadius: 20,
                  offset: const Offset(0, -4),
                ),
              ],
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Handle Bar
                  Center(
                    child: Container(
                      width: 44,
                      height: 4,
                      decoration: BoxDecoration(
                        color: isDark ? Colors.white24 : Colors.black12,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 14),

                  // Header Modal
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Loket Kasir Tagihan Ikhtibar',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            '${tagihan.namaKepalaKeluarga} (${tagihan.kampung})',
                            style: TextStyle(
                              fontSize: 12,
                              color: isDark ? Colors.white60 : Colors.black54,
                            ),
                          ),
                        ],
                      ),
                      IconButton(
                        onPressed: () => Navigator.pop(ctx),
                        icon: const Icon(Icons.close_rounded),
                        visualDensity: VisualDensity.compact,
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),

                  // Card Sisa Tagihan
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 10,
                    ),
                    decoration: BoxDecoration(
                      color: isDark
                          ? AppColors.primaryDark.withValues(alpha: 0.12)
                          : AppColors.primaryLight.withValues(alpha: 0.08),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color:
                            (isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight)
                                .withValues(alpha: 0.25),
                      ),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Sisa Tagihan Saat Ini',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        Text(
                          _formatRupiah(tagihan.sisaTagihan),
                          style: TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.bold,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),

                  // Input Tanggal Pembayaran
                  const Text(
                    'Tanggal Pembayaran *',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  InkWell(
                    onTap: () async {
                      final picked = await showDatePicker(
                        context: context,
                        initialDate: selectedTanggalBayar,
                        firstDate: DateTime(2020),
                        lastDate: DateTime(2035),
                      );
                      if (picked != null) {
                        setModalState(() => selectedTanggalBayar = picked);
                      }
                    },
                    borderRadius: BorderRadius.circular(12),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 12,
                      ),
                      decoration: BoxDecoration(
                        border: Border.all(
                          color: isDark ? Colors.white24 : Colors.black26,
                        ),
                        borderRadius: BorderRadius.circular(12),
                        color: isDark
                            ? Colors.white.withValues(alpha: 0.03)
                            : Colors.black.withValues(alpha: 0.02),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            Icons.calendar_today_rounded,
                            size: 18,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              DateHelper.formatTanggalIndo(
                                selectedTanggalBayar,
                              ),
                              style: const TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 3,
                            ),
                            decoration: BoxDecoration(
                              color:
                                  (isDark
                                          ? AppColors.primaryDark
                                          : AppColors.primaryLight)
                                      .withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              'Ubah',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                                color: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 14),

                  // Pilihan 3 Metode Pembayaran
                  const Text(
                    'Pilih Metode Pembayaran',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      _buildMetodeChip(
                        label: 'Tunai / Cash',
                        icon: Icons.payments_rounded,
                        isSelected: metode == 'Tunai',
                        onTap: () {
                          setModalState(() => metode = 'Tunai');
                        },
                        isDark: isDark,
                      ),
                      const SizedBox(width: 8),
                      _buildMetodeChip(
                        label: 'Potong Tabungan',
                        icon: Icons.account_balance_rounded,
                        isSelected: metode == 'Potong Tabungan',
                        onTap: () {
                          setModalState(() => metode = 'Potong Tabungan');
                        },
                        isDark: isDark,
                      ),
                      const SizedBox(width: 8),
                      _buildMetodeChip(
                        label: 'Cicilan',
                        icon: Icons.pie_chart_rounded,
                        isSelected: metode == 'Cicilan',
                        onTap: () {
                          setModalState(() => metode = 'Cicilan');
                        },
                        isDark: isDark,
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Bagian Khusus Potong Tabungan Manual
                  if (metode == 'Potong Tabungan') ...[
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.amber.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: Colors.amber.withValues(alpha: 0.3),
                        ),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Row(
                            children: [
                              Icon(
                                Icons.info_outline_rounded,
                                size: 16,
                                color: Colors.amber,
                              ),
                              SizedBox(width: 6),
                              Expanded(
                                child: Text(
                                  'Potongan Tabungan Manual membutuhkan konfirmasi admin sebelum masuk hitungan kas riil.',
                                  style: TextStyle(
                                    fontSize: 11,
                                    color: Colors.amber,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),

                          if (tagihan.daftarTabungan.isNotEmpty) ...[
                            const Text(
                              'Pilih Rekening Tabungan Anak di KK:',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 6),
                            Wrap(
                              spacing: 8,
                              children: tagihan.daftarTabungan.map((t) {
                                final isSelected = selectedTabungan?.id == t.id;
                                return ChoiceChip(
                                  selected: isSelected,
                                  label: Text(
                                    '${t.namaNasabah} (${t.ruangan}) - ${_formatRupiah(t.saldo)}',
                                    style: const TextStyle(fontSize: 11),
                                  ),
                                  onSelected: (val) {
                                    setModalState(() {
                                      selectedTabungan = t;
                                      noTabunganManualCtrl.text =
                                          t.nomorRekening;
                                      namaNasabahManualCtrl.text =
                                          t.namaNasabah;
                                      ruanganNasabahManualCtrl.text = t.ruangan;
                                    });
                                  },
                                );
                              }).toList(),
                            ),
                            const SizedBox(height: 10),
                          ],

                          TextField(
                            controller: noTabunganManualCtrl,
                            decoration: const InputDecoration(
                              labelText: 'Nomor Tabungan / Rekening *',
                              isDense: true,
                              border: OutlineInputBorder(),
                            ),
                          ),
                          const SizedBox(height: 8),
                          Row(
                            children: [
                              Expanded(
                                child: TextField(
                                  controller: namaNasabahManualCtrl,
                                  decoration: const InputDecoration(
                                    labelText: 'Nama Nasabah *',
                                    isDense: true,
                                    border: OutlineInputBorder(),
                                  ),
                                ),
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: TextField(
                                  controller: ruanganNasabahManualCtrl,
                                  decoration: const InputDecoration(
                                    labelText: 'Ruangan / Kelas *',
                                    isDense: true,
                                    border: OutlineInputBorder(),
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),

                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 8,
                            ),
                            decoration: BoxDecoration(
                              color: Colors.amber.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(
                                color: Colors.amber.withValues(alpha: 0.25),
                              ),
                            ),
                            child: const Row(
                              children: [
                                Icon(
                                  Icons.shield_outlined,
                                  size: 16,
                                  color: Colors.amber,
                                ),
                                SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    'Pembayaran via potongan tabungan membutuhkan verifikasi admin di backend.',
                                    style: TextStyle(
                                      fontSize: 11,
                                      color: Colors.amber,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),
                  ],

                  // Input Nominal Bayar
                  const Text(
                    'Nominal Pembayaran (Rp) *',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  TextField(
                    controller: nominalCtrl,
                    keyboardType: TextInputType.number,
                    decoration: InputDecoration(
                      prefixText: 'Rp ',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 12,
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),

                  // Shortcut Nominal Cepat
                  Row(
                    children: [
                      _buildQuickNominal(
                        'Lunas (${_formatRupiah(tagihan.sisaTagihan)})',
                        tagihan.sisaTagihan,
                        nominalCtrl,
                        setModalState,
                      ),
                      const SizedBox(width: 8),
                      if (tagihan.sisaTagihan > 100000)
                        _buildQuickNominal(
                          'Rp 100.000',
                          100000,
                          nominalCtrl,
                          setModalState,
                        ),
                      const SizedBox(width: 8),
                      if (tagihan.sisaTagihan > 50000)
                        _buildQuickNominal(
                          'Rp 50.000',
                          50000,
                          nominalCtrl,
                          setModalState,
                        ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Nama Penyetor
                  const Text(
                    'Nama Penyetor',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  TextField(
                    controller: penyetorCtrl,
                    decoration: InputDecoration(
                      hintText: 'Nama penyetor...',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 12,
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Catatan
                  const Text(
                    'Catatan / Keterangan',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  TextField(
                    controller: catatanCtrl,
                    decoration: InputDecoration(
                      hintText: 'Catatan opsional...',
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 12,
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),

                  // Tombol Konfirmasi Simpan
                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: ElevatedButton.icon(
                      onPressed: () async {
                        final rawNominal = nominalCtrl.text.replaceAll(
                          RegExp(r'[^0-9]'),
                          '',
                        );
                        final nominal = double.tryParse(rawNominal);

                        if (nominal == null || nominal <= 0) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Nominal pembayaran tidak valid'),
                            ),
                          );
                          return;
                        }

                        final isPotongTabungan = metode == 'Potong Tabungan';
                        if (isPotongTabungan) {
                          if (noTabunganManualCtrl.text.trim().isEmpty ||
                              namaNasabahManualCtrl.text.trim().isEmpty ||
                              ruanganNasabahManualCtrl.text.trim().isEmpty) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                content: Text(
                                  'Nomor tabungan, nama, dan ruangan nasabah wajib diisi',
                                ),
                              ),
                            );
                            return;
                          }
                        }

                        HapticHelper.medium();
                        final messenger = ScaffoldMessenger.of(context);
                        final provider = context
                            .read<PanitiaIkhtibarProvider>();
                        Navigator.pop(ctx);
                        final result = await provider.simpanPembayaran(
                          tagihanId: tagihan.tagihanId,
                          nominalBayar: nominal,
                          metodePembayaran: metode,
                          tanggalBayar: DateHelper.toYmd(selectedTanggalBayar),
                          namaPenyetor: penyetorCtrl.text.trim(),
                          catatan: catatanCtrl.text.trim(),
                          nomorTabungan: isPotongTabungan
                              ? noTabunganManualCtrl.text.trim()
                              : null,
                          namaNasabah: isPotongTabungan
                              ? namaNasabahManualCtrl.text.trim()
                              : null,
                          ruanganNasabah: isPotongTabungan
                              ? ruanganNasabahManualCtrl.text.trim()
                              : null,
                          isVerified: false,
                        );

                        if (!mounted) return;
                        if (result != null) {
                          final statusBayar =
                              result['status_bayar'] as String? ?? '';
                          final sisaTagihan =
                              (result['sisa_tagihan'] as num?)?.toInt() ?? 0;
                          final isLunas =
                              statusBayar == 'Lunas' || sisaTagihan <= 0;

                          if (isLunas) {
                            _bukaHalamanKwitansiResult(
                              result,
                              daftarAnak: tagihan.daftarAnak,
                            );
                          } else {
                            final statusMsg = isPotongTabungan
                                ? ' (Menunggu verifikasi admin backend)'
                                : '!';
                            messenger.showSnackBar(
                              SnackBar(
                                content: Row(
                                  children: [
                                    const Icon(
                                      Icons.check_circle_rounded,
                                      color: Colors.white,
                                    ),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: Text(
                                        'Pembayaran ${tagihan.namaKepalaKeluarga} sebesar ${_formatRupiah(nominal)} berhasil dicatat$statusMsg',
                                      ),
                                    ),
                                  ],
                                ),
                                backgroundColor: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                                behavior: SnackBarBehavior.floating,
                              ),
                            );
                          }
                        } else {
                          messenger.showSnackBar(
                            SnackBar(
                              content: Text(
                                provider.kasirError ??
                                    'Gagal memproses pembayaran',
                              ),
                              backgroundColor: Colors.redAccent,
                              behavior: SnackBarBehavior.floating,
                            ),
                          );
                        }
                      },
                      icon: const Icon(Icons.check_circle_outline_rounded),
                      label: const Text(
                        'Konfirmasi & Cetak Kwitansi',
                        style: TextStyle(fontWeight: FontWeight.bold),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildMetodeChip({
    required String label,
    required IconData icon,
    required bool isSelected,
    required VoidCallback onTap,
    required bool isDark,
  }) {
    return Expanded(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            color: isSelected
                ? (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                      .withValues(alpha: 0.15)
                : (isDark
                      ? Colors.white10
                      : Colors.black.withValues(alpha: 0.04)),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: isSelected
                  ? (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                  : Colors.transparent,
              width: 1.5,
            ),
          ),
          child: Column(
            children: [
              Icon(
                icon,
                size: 18,
                color: isSelected
                    ? (isDark ? AppColors.primaryDark : AppColors.primaryLight)
                    : (isDark ? Colors.white60 : Colors.black54),
              ),
              const SizedBox(height: 4),
              Text(
                label,
                style: TextStyle(
                  fontSize: 10,
                  fontWeight: isSelected ? FontWeight.bold : FontWeight.w500,
                  color: isSelected
                      ? (isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight)
                      : null,
                ),
                textAlign: TextAlign.center,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildQuickNominal(
    String label,
    int nominal,
    TextEditingController controller,
    StateSetter setModalState,
  ) {
    return InkWell(
      onTap: () {
        setModalState(() {
          controller.text = nominal.toString();
        });
      },
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          border: Border.all(color: Colors.grey.shade400),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Text(
          label,
          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final primaryColor = isDark
        ? AppColors.primaryDark
        : AppColors.primaryLight;
    final provider = context.watch<PanitiaIkhtibarProvider>();
    final ringkasan = provider.kasirRingkasan;

    return Scaffold(
      appBar: CustomAppBar(
        titleText: 'Pembayaran Ikhtibar (${ringkasan?.ikhtibarKe ?? "-"})',
        showBackButton: true,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Segarkan Data',
            onPressed: () {
              HapticHelper.light();
              _loadData();
            },
          ),
        ],
      ),
      body: Column(
        children: [
          // 1. SEGMENTED NAVIGATION TAB (Kasir, Leger, Riwayat)
          SegmentedTabBar(
            margin: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            selectedIndex: _selectedTab,
            onTabChanged: (idx) {
              setState(() {
                _selectedTab = idx;
              });
              if (idx == 2) {
                provider.fetchLaporanRiwayat();
              }
            },
            items: [
              SegmentedTabItem(
                activeIcon: Icons.point_of_sale_rounded,
                inactiveIcon: Icons.point_of_sale_outlined,
                label: 'Kasir',
                activeColor: primaryColor,
              ),
              SegmentedTabItem(
                activeIcon: Icons.menu_book_rounded,
                inactiveIcon: Icons.menu_book_outlined,
                label: 'Leger',
                activeColor: primaryColor,
              ),
              SegmentedTabItem(
                activeIcon: Icons.history_rounded,
                inactiveIcon: Icons.history_outlined,
                label: 'Riwayat',
                activeColor: primaryColor,
              ),
            ],
          ),

          // 2. TAB CONTENT
          Expanded(
            child: RefreshIndicator(
              onRefresh: () async => _loadData(),
              color: primaryColor,
              child: IndexedStack(
                index: _selectedTab,
                children: [
                  _buildTabKasir(provider, isDark, primaryColor),
                  _buildTabLeger(provider, isDark, primaryColor),
                  _buildTabRiwayat(provider, isDark, primaryColor),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  // =========================================================================
  // TAB 1: KASIR (FOKUS NO REG & HANYA TAMPILKAN SATU CARD)
  // =========================================================================
  Widget _buildTabKasir(
    PanitiaIkhtibarProvider provider,
    bool isDark,
    Color primaryColor,
  ) {
    final query = _activeKasirQuery.trim();
    final cleanQ = query.replaceAll(RegExp(r'[^0-9]'), '');

    // Cari kartu yang cocok berdasarkan No. Registrasi
    TagihanKkIkhtibarItem? heroCard = _kasirDirectResult;
    if (query.isNotEmpty) {
      final list = provider.tagihanList;
      final currentHero = heroCard;
      if (currentHero != null) {
        // Sinkronisasi data jika ada data terupdate di tagihanList (misal setelah bayar)
        for (final t in list) {
          if (t.tagihanId == currentHero.tagihanId) {
            heroCard = t;
            _kasirDirectResult = t;
            break;
          }
        }
      } else {
        // 1. Coba exact match no registrasi
        if (cleanQ.isNotEmpty) {
          for (final t in list) {
            if (t.noRegistrasi == cleanQ) {
              heroCard = t;
              break;
            }
          }
          // 1b. Coba dengan offset 50000 (jika user ketik 1, cari 50001)
          if (heroCard == null) {
            final intVal = int.tryParse(cleanQ);
            if (intVal != null && intVal < 1000) {
              final offset = (50000 + intVal).toString();
              for (final t in list) {
                if (t.noRegistrasi == offset) {
                  heroCard = t;
                  break;
                }
              }
            }
          }
        }
        // 2. Coba contains no registrasi
        if (heroCard == null) {
          for (final t in list) {
            if (t.noRegistrasi.toLowerCase().contains(query.toLowerCase()) ||
                (cleanQ.isNotEmpty && t.noRegistrasi.contains(cleanQ))) {
              heroCard = t;
              break;
            }
          }
        }
        // 3. Fallback nama KK
        if (heroCard == null) {
          for (final t in list) {
            if (t.namaKepalaKeluarga.toLowerCase().contains(
              query.toLowerCase(),
            )) {
              heroCard = t;
              break;
            }
          }
        }
      }
    }

    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // POS Search Card Fokus No. Registrasi
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF1E2129) : Colors.white,
              borderRadius: BorderRadius.circular(18),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: isDark ? 0.3 : 0.05),
                  blurRadius: 10,
                  offset: const Offset(0, 3),
                ),
              ],
              border: Border.all(
                color: query.isNotEmpty
                    ? primaryColor.withValues(alpha: 0.5)
                    : (isDark
                          ? Colors.white12
                          : Colors.black.withValues(alpha: 0.08)),
                width: query.isNotEmpty ? 1.5 : 1,
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.confirmation_number_rounded,
                      size: 20,
                      color: primaryColor,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      'Loket Cepat No. Registrasi',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: isDark ? Colors.white : Colors.black87,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _kasirSearchCtrl,
                        keyboardType: TextInputType.text,
                        textInputAction: TextInputAction.search,
                        onSubmitted: (_) => _performKasirSearch(),
                        autofocus: false,
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                        ),
                        decoration: InputDecoration(
                          hintText: 'Ketik No. Reg (misal: 50001 atau 1)...',
                          hintStyle: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.normal,
                            color: isDark ? Colors.white38 : Colors.black38,
                          ),
                          prefixIcon: Icon(
                            Icons.confirmation_number_outlined,
                            color: primaryColor,
                            size: 20,
                          ),
                          suffixIcon: ValueListenableBuilder<TextEditingValue>(
                            valueListenable: _kasirSearchCtrl,
                            builder: (context, val, _) {
                              if (val.text.isEmpty) {
                                return const SizedBox.shrink();
                              }
                              return IconButton(
                                icon: const Icon(Icons.clear_rounded, size: 20),
                                tooltip: 'Bersihkan',
                                onPressed: _clearKasirSearch,
                              );
                            },
                          ),
                          filled: true,
                          fillColor: isDark
                              ? Colors.white.withValues(alpha: 0.04)
                              : const Color(0xFFF8FAFC),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide.none,
                          ),
                          contentPadding: const EdgeInsets.symmetric(
                            horizontal: 14,
                            vertical: 14,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    SizedBox(
                      height: 48,
                      child: ElevatedButton.icon(
                        onPressed: _performKasirSearch,
                        icon: const Icon(Icons.search_rounded, size: 18),
                        label: const Text(
                          'Cari',
                          style: TextStyle(
                            fontSize: 13.5,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: primaryColor,
                          foregroundColor: Colors.white,
                          elevation: 0,
                          padding: const EdgeInsets.symmetric(horizontal: 16),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  'Masukkan No. Reg lalu tekan Enter atau klik Cari untuk menampilkan kartu transaksi.',
                  style: TextStyle(
                    fontSize: 11,
                    color: isDark ? Colors.white54 : Colors.black45,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Tampilan Card Tunggal / State
          if (_isSearchingKasirDirect ||
              (provider.isLoadingKasir && provider.tagihanList.isEmpty))
            const ShimmerLoadingList(count: 1, height: 260)
          else if (query.isEmpty)
            // Empty State yang bersih & informatif
            _buildKasirEmptyPrompt(provider, isDark, primaryColor)
          else if (heroCard != null)
            // SATU CARD TUNGGAL LOKET KASIR
            _buildHeroCardKasir(heroCard, isDark, primaryColor)
          else
            // State tidak ditemukan
            _buildKasirNotFound(query, isDark, primaryColor),
        ],
      ),
    );
  }

  Widget _buildKasirEmptyPrompt(
    PanitiaIkhtibarProvider provider,
    bool isDark,
    Color primaryColor,
  ) {
    return GlassCard(
      padding: const EdgeInsets.all(22),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: primaryColor.withValues(alpha: 0.12),
              shape: BoxShape.circle,
            ),
            child: Icon(
              Icons.point_of_sale_rounded,
              size: 44,
              color: primaryColor,
            ),
          ),
          const SizedBox(height: 14),
          const Text(
            'Loket Kasir (Individu)',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 6),
          Text(
            'Silakan masukkan Nomor Registrasi KK pada kolom pencarian di atas untuk memuat tagihan keluarga.',
            style: TextStyle(
              fontSize: 12,
              color: isDark ? Colors.white60 : Colors.black54,
              height: 1.4,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildKasirNotFound(String query, bool isDark, Color primaryColor) {
    return GlassCard(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 32),
      child: Column(
        children: [
          Icon(
            Icons.search_off_rounded,
            size: 48,
            color: AppColors.roseDanger.withValues(alpha: 0.8),
          ),
          const SizedBox(height: 12),
          Text(
            'No. Registrasi "$query" Tidak Ditemukan',
            style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.bold),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 6),
          Text(
            'Pastikan nomor registrasi sudah benar atau periksa daftar lengkap di tab Leger.',
            style: TextStyle(
              fontSize: 12,
              color: isDark ? Colors.white60 : Colors.black54,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 16),
          ElevatedButton.icon(
            onPressed: _clearKasirSearch,
            icon: const Icon(Icons.clear_rounded, size: 16),
            label: const Text('Reset Pencarian'),
            style: ElevatedButton.styleFrom(
              backgroundColor: isDark
                  ? Colors.white.withValues(alpha: 0.1)
                  : Colors.black.withValues(alpha: 0.05),
              foregroundColor: isDark ? Colors.white : Colors.black87,
              elevation: 0,
            ),
          ),
        ],
      ),
    );
  }

  // SATU CARD TUNGGAL HERO KASIR
  Widget _buildHeroCardKasir(
    TagihanKkIkhtibarItem t,
    bool isDark,
    Color primaryColor,
  ) {
    final isLunas = t.statusBayar == 'Lunas' || t.sisaTagihan <= 0;
    Color statusColor;
    String statusText;
    if (t.hasPendingTabungan) {
      statusColor = AppColors.amberAccent;
      statusText = 'Menunggu Konfirmasi';
    } else if (isLunas) {
      statusColor = const Color(0xFF10B981);
      statusText = 'Lunas';
    } else if (t.statusBayar == 'Cicilan') {
      statusColor = AppColors.amberAccent;
      statusText = 'Cicilan';
    } else {
      statusColor = AppColors.roseDanger;
      statusText = 'Belum Lunas';
    }

    return Container(
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E2129) : Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.35 : 0.08),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
        border: Border.all(
          color: isLunas
              ? const Color(0xFF10B981).withValues(alpha: 0.4)
              : primaryColor.withValues(alpha: 0.4),
          width: 1.5,
        ),
      ),
      padding: const EdgeInsets.all(18),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Card: Badge Registrasi & Status
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: primaryColor.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(
                    color: primaryColor.withValues(alpha: 0.3),
                  ),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.confirmation_number_rounded,
                      size: 14,
                      color: primaryColor,
                    ),
                    const SizedBox(width: 5),
                    Text(
                      'REG: ${t.noRegistrasi}',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                        color: primaryColor,
                        letterSpacing: 0.5,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  statusText.toUpperCase(),
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 0.5,
                    color: statusColor,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Nama Kepala Keluarga & Lokasi
          Text(
            t.namaKepalaKeluarga,
            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
          ),
          const SizedBox(height: 4),
          Row(
            children: [
              Icon(
                Icons.location_on_rounded,
                size: 14,
                color: isDark ? Colors.white60 : Colors.black54,
              ),
              const SizedBox(width: 4),
              Text(
                'Kampung: ${t.kampung}',
                style: TextStyle(
                  fontSize: 12,
                  color: isDark ? Colors.white70 : Colors.black54,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Daftar Anak Santri
          if (t.daftarAnak.isNotEmpty) ...[
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: isDark
                    ? Colors.white.withValues(alpha: 0.04)
                    : const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(
                  color: isDark
                      ? Colors.white10
                      : Colors.black.withValues(alpha: 0.05),
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(Icons.school_rounded, size: 14, color: primaryColor),
                      const SizedBox(width: 6),
                      Text(
                        'Rincian Anak (${t.daftarAnak.length} Anak):',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: isDark ? Colors.white70 : Colors.black87,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Wrap(
                    spacing: 6,
                    runSpacing: 6,
                    children: t.daftarAnak.map((anak) {
                      return Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 3,
                        ),
                        decoration: BoxDecoration(
                          color: isDark
                              ? Colors.white.withValues(alpha: 0.06)
                              : Colors.white,
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(
                            color: isDark ? Colors.white12 : Colors.black12,
                          ),
                        ),
                        child: Text(
                          '${anak.nama} (${anak.ruangan})',
                          style: const TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 14),
          ],

          // Kotak Ringkasan Finansial Hero
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
            decoration: BoxDecoration(
              color: isDark
                  ? Colors.white.withValues(alpha: 0.03)
                  : const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Total Tagihan',
                        style: TextStyle(
                          fontSize: 11,
                          color: isDark ? Colors.white60 : Colors.black54,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        _formatRupiah(t.nominalTagihan),
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      Text(
                        'Sudah Dibayar',
                        style: TextStyle(
                          fontSize: 11,
                          color: isDark ? Colors.white60 : Colors.black54,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        _formatRupiah(t.nominalBayar),
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                          color: t.nominalBayar > 0
                              ? const Color(0xFF10B981)
                              : null,
                        ),
                      ),
                    ],
                  ),
                ),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(
                        'Sisa Tagihan',
                        style: TextStyle(
                          fontSize: 11,
                          color: isDark ? Colors.white60 : Colors.black54,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        _formatRupiah(t.sisaTagihan),
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w900,
                          color: t.sisaTagihan > 0
                              ? AppColors.roseDanger
                              : const Color(0xFF10B981),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Tombol Aksi Kasir Utama
          if (!isLunas) ...[
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton.icon(
                onPressed: () => _openBayarSheet(t),
                icon: const Icon(Icons.payment_rounded, size: 20),
                label: const Text(
                  'Proses Pembayaran Kasir',
                  style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: primaryColor,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                  elevation: 2,
                ),
              ),
            ),
            if (t.nominalBayar > 0) ...[
              const SizedBox(height: 8),
              SizedBox(
                width: double.infinity,
                height: 42,
                child: OutlinedButton.icon(
                  onPressed: () => _bukaHalamanKwitansiTagihan(t),
                  icon: const Icon(Icons.receipt_rounded, size: 16),
                  label: const Text(
                    'Lihat Bukti Cicilan Terakhir',
                    style: TextStyle(
                      fontSize: 12.5,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: primaryColor,
                    side: BorderSide(
                      color: primaryColor.withValues(alpha: 0.5),
                    ),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ),
              ),
            ],
          ] else ...[
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 8),
              margin: const EdgeInsets.only(bottom: 10),
              decoration: BoxDecoration(
                color: const Color(0xFF10B981).withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.check_circle_rounded,
                    size: 16,
                    color: Color(0xFF10B981),
                  ),
                  SizedBox(width: 6),
                  Text(
                    'Tagihan keluarga ini sudah LUNAS sepenuhnya',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF10B981),
                    ),
                  ),
                ],
              ),
            ),
            SizedBox(
              width: double.infinity,
              height: 46,
              child: ElevatedButton.icon(
                onPressed: () => _bukaHalamanKwitansiTagihan(t),
                icon: const Icon(Icons.receipt_long_rounded, size: 20),
                label: const Text(
                  'Buka Kwitansi Lunas Resmi',
                  style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.bold),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF10B981),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                  elevation: 1,
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  // =========================================================================
  // TAB 2: LEGER (FILTER KAMPUNG & PENCARIAN NO. REG / NAMA KK)
  // =========================================================================
  Widget _buildTabLeger(
    PanitiaIkhtibarProvider provider,
    bool isDark,
    Color primaryColor,
  ) {
    final ringkasan = provider.kasirRingkasan;

    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // 1. Filter Dusun / Kampung (Dropdown & Quick Select)
          if (ringkasan?.daftarKampung.isNotEmpty ?? false) ...[
            Builder(
              builder: (ctx) {
                final listKampung = ringkasan!.daftarKampung;
                final currentId = provider.filterKampungId;
                final selectedId = listKampung.any((k) => k.id == currentId)
                    ? currentId
                    : listKampung.first.id;

                return DropdownButtonFormField<int>(
                  key: ValueKey('dropdown_kampung_$selectedId'),
                  initialValue: selectedId,
                  isExpanded: true,
                  decoration: InputDecoration(
                    labelText: 'Filter Wilayah Kampung / Dusun',
                    labelStyle: TextStyle(
                      fontSize: 12,
                      color: isDark ? Colors.white70 : Colors.black87,
                    ),
                    prefixIcon: Icon(
                      Icons.location_city_rounded,
                      size: 20,
                      color: primaryColor,
                    ),
                    filled: true,
                    fillColor: isDark ? const Color(0xFF1E2129) : Colors.white,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(14),
                      borderSide: BorderSide(
                        color: isDark ? Colors.white12 : Colors.black12,
                      ),
                    ),
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 12,
                    ),
                  ),
                  items: listKampung
                      .map(
                        (k) => DropdownMenuItem<int>(
                          value: k.id,
                          child: Text(
                            '${k.kode} - ${k.namaKampung}',
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      )
                      .toList(),
                  onChanged: (val) {
                    if (val != null) {
                      provider.setFilterKampung(val);
                    }
                  },
                );
              },
            ),
            const SizedBox(height: 10),
          ],

          // 2. Kotak Pencarian Leger (No. Reg / Nama KK)
          TextField(
            controller: _legerSearchCtrl,
            textInputAction: TextInputAction.search,
            onChanged: _onLegerSearchChanged,
            decoration: InputDecoration(
              hintText: 'Cari No. Reg atau Nama Kepala Keluarga...',
              hintStyle: TextStyle(
                fontSize: 13,
                color: isDark ? Colors.white38 : Colors.black38,
              ),
              prefixIcon: Icon(Icons.search_rounded, color: primaryColor),
              suffixIcon: _legerSearchCtrl.text.isNotEmpty
                  ? IconButton(
                      icon: const Icon(Icons.clear_rounded),
                      onPressed: () {
                        _legerSearchCtrl.clear();
                        provider.setSearchQuery('');
                      },
                    )
                  : null,
              filled: true,
              fillColor: isDark ? const Color(0xFF1E2129) : Colors.white,
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: BorderSide(
                  color: isDark ? Colors.white12 : Colors.black12,
                ),
              ),
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 14,
                vertical: 12,
              ),
            ),
          ),
          const SizedBox(height: 10),

          // 3. Mini Segmented Filter Status Bayar
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: ['Semua', 'Belum Lunas', 'Cicilan', 'Lunas'].map((st) {
                final isSelected = provider.filterStatusBayar == st;
                return Padding(
                  padding: const EdgeInsets.only(right: 6),
                  child: ChoiceChip(
                    label: Text(
                      st,
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: isSelected
                            ? FontWeight.bold
                            : FontWeight.w500,
                        color: isSelected
                            ? Colors.white
                            : (isDark ? Colors.white70 : Colors.black87),
                      ),
                    ),
                    selected: isSelected,
                    selectedColor: primaryColor,
                    backgroundColor: isDark
                        ? Colors.white.withValues(alpha: 0.05)
                        : Colors.black.withValues(alpha: 0.03),
                    onSelected: (val) {
                      if (val) provider.setFilterStatusBayar(st);
                    },
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                      side: BorderSide(
                        color: isSelected
                            ? primaryColor
                            : (isDark ? Colors.white10 : Colors.black12),
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
          const SizedBox(height: 12),

          // 4. Mini Statistik Ringkasan Leger
          if (ringkasan != null)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              margin: const EdgeInsets.only(bottom: 12),
              decoration: BoxDecoration(
                color: primaryColor.withValues(alpha: isDark ? 0.15 : 0.06),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: primaryColor.withValues(alpha: 0.2)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: [
                  _buildLegerStatItem(
                    'Total KK',
                    '${ringkasan.totalKk}',
                    isDark,
                  ),
                  _buildLegerStatItem(
                    'Lunas',
                    '${ringkasan.totalLunas}',
                    isDark,
                    color: const Color(0xFF10B981),
                  ),
                  _buildLegerStatItem(
                    'Belum',
                    '${ringkasan.totalBelumLunas}',
                    isDark,
                    color: AppColors.roseDanger,
                  ),
                  _buildLegerStatItem(
                    'Kelunasan',
                    '${ringkasan.persentaseKelunasan.toStringAsFixed(1)}%',
                    isDark,
                    color: primaryColor,
                  ),
                ],
              ),
            ),

          // 5. Daftar Tagihan Leger
          if (provider.isLoadingKasir)
            const ShimmerLoadingList(count: 4)
          else if (provider.tagihanList.isEmpty)
            Center(
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 40),
                child: Column(
                  children: [
                    Icon(
                      Icons.folder_off_rounded,
                      size: 44,
                      color: isDark ? Colors.white24 : Colors.black26,
                    ),
                    const SizedBox(height: 10),
                    const Text(
                      'Tidak ada data tagihan yang sesuai filter',
                      style: TextStyle(fontWeight: FontWeight.w600),
                    ),
                  ],
                ),
              ),
            )
          else
            ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: provider.tagihanList.length,
              separatorBuilder: (_, __) => const SizedBox(height: 10),
              itemBuilder: (ctx, idx) {
                final t = provider.tagihanList[idx];
                return _buildTagihanCard(t, isDark, primaryColor);
              },
            ),
        ],
      ),
    );
  }

  Widget _buildLegerStatItem(
    String label,
    String value,
    bool isDark, {
    Color? color,
  }) {
    return Column(
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 10.5,
            color: isDark ? Colors.white60 : Colors.black54,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          value,
          style: TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w800,
            color: color ?? (isDark ? Colors.white : Colors.black87),
          ),
        ),
      ],
    );
  }

  // =========================================================================
  // TAB 3: RIWAYAT (FILTER TANGGAL & METODE PEMBAYARAN)
  // =========================================================================
  Widget _buildTabRiwayat(
    PanitiaIkhtibarProvider provider,
    bool isDark,
    Color primaryColor,
  ) {
    num totalMasukHariIni = 0;
    for (final r in provider.riwayatList) {
      totalMasukHariIni += r.nominal;
    }

    final now = DateTime.now();
    final isToday =
        provider.filterLaporanTanggal.year == now.year &&
        provider.filterLaporanTanggal.month == now.month &&
        provider.filterLaporanTanggal.day == now.day;

    return Column(
      children: [
        // 1. Filter Tanggal & Navigasi Tanggal
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 6),
          child: Container(
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF1E2129) : Colors.white,
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: isDark ? 0.3 : 0.05),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
              border: Border.all(
                color: isDark ? Colors.white12 : Colors.black12,
              ),
            ),
            child: Row(
              children: [
                IconButton(
                  visualDensity: VisualDensity.compact,
                  icon: const Icon(Icons.chevron_left_rounded),
                  tooltip: 'Hari Sebelumnya',
                  onPressed: () => _shiftDate(-1),
                ),
                Expanded(
                  child: InkWell(
                    borderRadius: BorderRadius.circular(16),
                    onTap: () => _pickDate(context),
                    child: Padding(
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.calendar_today_rounded,
                            size: 14,
                            color: primaryColor,
                          ),
                          const SizedBox(width: 8),
                          Flexible(
                            child: Text(
                              isToday
                                  ? '${DateHelper.formatTanggalIndo(provider.filterLaporanTanggal)} (Hari Ini)'
                                  : DateHelper.formatTanggalIndo(
                                      provider.filterLaporanTanggal,
                                    ),
                              style: TextStyle(
                                fontSize: 12.5,
                                fontWeight: FontWeight.bold,
                                color: isDark ? Colors.white : Colors.black87,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),

                IconButton(
                  visualDensity: VisualDensity.compact,
                  icon: const Icon(Icons.chevron_right_rounded),
                  tooltip: 'Hari Berikutnya',
                  onPressed: () => _shiftDate(1),
                ),
              ],
            ),
          ),
        ),

        // 2. Filter Metode Pembayaran
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: ['Semua', 'Tunai', 'Potong Tabungan', 'Cicilan'].map((
                metode,
              ) {
                final isSelected = provider.filterLaporanMetode == metode;
                return Padding(
                  padding: const EdgeInsets.only(right: 6),
                  child: ChoiceChip(
                    label: Text(
                      metode == 'Semua' ? 'Semua Metode' : metode,
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: isSelected
                            ? FontWeight.bold
                            : FontWeight.w500,
                        color: isSelected
                            ? Colors.white
                            : (isDark ? Colors.white70 : Colors.black87),
                      ),
                    ),
                    selected: isSelected,
                    selectedColor: primaryColor,
                    backgroundColor: isDark
                        ? Colors.white.withValues(alpha: 0.05)
                        : Colors.black.withValues(alpha: 0.03),
                    onSelected: (val) {
                      if (val) provider.setFilterLaporanMetode(metode);
                    },
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                      side: BorderSide(
                        color: isSelected
                            ? primaryColor
                            : (isDark ? Colors.white10 : Colors.black12),
                      ),
                    ),
                  ),
                );
              }).toList(),
            ),
          ),
        ),

        // 3. Mini Summary Header Riwayat
        Padding(
          padding: const EdgeInsets.fromLTRB(16, 6, 16, 6),
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
            decoration: BoxDecoration(
              color: isDark
                  ? Colors.white.withValues(alpha: 0.04)
                  : const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  '${provider.riwayatList.length} Transaksi Tercatat',
                  style: TextStyle(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w600,
                    color: isDark ? Colors.white70 : Colors.black54,
                  ),
                ),
                Text(
                  'Total: ${_formatRupiah(totalMasukHariIni)}',
                  style: const TextStyle(
                    fontSize: 12.5,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF10B981),
                  ),
                ),
              ],
            ),
          ),
        ),

        // 4. Daftar Riwayat Pembayar
        Expanded(
          child: provider.isLoadingLaporan && provider.riwayatList.isEmpty
              ? const Padding(
                  padding: EdgeInsets.all(16),
                  child: ShimmerLoadingList(count: 4),
                )
              : provider.riwayatList.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.receipt_long_rounded,
                        size: 44,
                        color: isDark ? Colors.white24 : Colors.black26,
                      ),
                      const SizedBox(height: 10),
                      const Text(
                        'Belum ada transaksi pembayaran sesuai filter',
                        style: TextStyle(fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                )
              : ListView.separated(
                  padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
                  itemCount: provider.riwayatList.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 10),
                  itemBuilder: (ctx, idx) {
                    final r = provider.riwayatList[idx];
                    return _buildRiwayatCard(r, isDark, primaryColor);
                  },
                ),
        ),
      ],
    );
  }

  Widget _buildRiwayatCard(
    RiwayatPembayaranIkhtibarItem r,
    bool isDark,
    Color primaryColor,
  ) {
    final isTabungan = r.metode == 'Potong Tabungan';
    final badgeColor = r.isVerified
        ? (isDark ? AppColors.primaryDark : AppColors.primaryLight)
        : Colors.amber;
    String badgeText;
    if (isTabungan) {
      badgeText = r.isVerified ? 'Potong Tabungan' : 'Tabungan (Pending)';
    } else {
      badgeText = r.metode;
    }

    final tglFormatted = r.tanggal != null
        ? DateHelper.formatTanggalIndo(
            DateTime.tryParse(r.tanggal!) ?? DateTime.now(),
          )
        : '-';

    return InkWell(
      onTap: () => _bukaHalamanKwitansiRiwayat(r),
      borderRadius: BorderRadius.circular(16),
      child: GlassCard(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 7,
                    vertical: 2.5,
                  ),
                  decoration: BoxDecoration(
                    color: isDark
                        ? Colors.white.withValues(alpha: 0.06)
                        : Colors.black.withValues(alpha: 0.04),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    r.noTransaksi,
                    style: const TextStyle(
                      fontSize: 10.5,
                      fontFamily: 'monospace',
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 3,
                  ),
                  decoration: BoxDecoration(
                    color: badgeColor.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    badgeText,
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                      color: badgeColor,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),

            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        r.namaPenyetor,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Kampung: ${r.kampung}',
                        style: TextStyle(
                          fontSize: 11,
                          color: isDark ? Colors.white60 : Colors.black54,
                        ),
                      ),
                    ],
                  ),
                ),
                Text(
                  _formatRupiah(r.nominal),
                  style: const TextStyle(
                    fontSize: 14.5,
                    fontWeight: FontWeight.w900,
                    color: Color(0xFF10B981),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),

            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.access_time_rounded,
                      size: 12,
                      color: isDark ? Colors.white54 : Colors.black45,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      tglFormatted,
                      style: TextStyle(
                        fontSize: 10.5,
                        color: isDark ? Colors.white54 : Colors.black45,
                      ),
                    ),
                  ],
                ),
                Row(
                  children: [
                    Text(
                      'Petugas: ${r.diterimaOleh}',
                      style: TextStyle(
                        fontSize: 10.5,
                        color: isDark ? Colors.white54 : Colors.black45,
                      ),
                    ),
                    const SizedBox(width: 4),
                    Icon(
                      Icons.arrow_forward_ios_rounded,
                      size: 11,
                      color: primaryColor,
                    ),
                  ],
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  // =========================================================================
  // KARTU LEGER
  // =========================================================================
  Widget _buildTagihanCard(
    TagihanKkIkhtibarItem t,
    bool isDark,
    Color primaryColor,
  ) {
    Color statusColor;
    String statusText;
    if (t.hasPendingTabungan) {
      statusColor = AppColors.amberAccent;
      statusText = 'Menunggu Konfirmasi';
    } else if (t.statusBayar == 'Lunas') {
      statusColor = const Color(0xFF10B981);
      statusText = 'Lunas';
    } else if (t.statusBayar == 'Cicilan') {
      statusColor = AppColors.amberAccent;
      statusText = 'Cicilan';
    } else {
      statusColor = AppColors.roseDanger;
      statusText = 'Belum Lunas';
    }

    return GlassCard(
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      t.namaKepalaKeluarga,
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Wrap(
                      crossAxisAlignment: WrapCrossAlignment.center,
                      spacing: 6,
                      runSpacing: 4,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 6,
                            vertical: 2,
                          ),
                          decoration: BoxDecoration(
                            color: primaryColor.withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(6),
                            border: Border.all(
                              color: primaryColor.withValues(alpha: 0.25),
                            ),
                          ),
                          child: Text(
                            'Reg: ${t.noRegistrasi}',
                            style: TextStyle(
                              fontSize: 10.5,
                              fontWeight: FontWeight.bold,
                              color: primaryColor,
                            ),
                          ),
                        ),
                        Text(
                          'Dusun: ${t.kampung}',
                          style: TextStyle(
                            fontSize: 11,
                            color: isDark ? Colors.white60 : Colors.black54,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  statusText,
                  style: TextStyle(
                    fontSize: 10.5,
                    fontWeight: FontWeight.bold,
                    color: statusColor,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),

          // Anak santri
          if (t.daftarAnak.isNotEmpty) ...[
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
              decoration: BoxDecoration(
                color: isDark
                    ? Colors.white.withValues(alpha: 0.03)
                    : Colors.black.withValues(alpha: 0.02),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                children: [
                  const Icon(Icons.people_alt_outlined, size: 13),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      'Anak: ${t.daftarAnak.map((a) => "${a.nama} (${a.ruangan})").join(", ")}',
                      style: const TextStyle(fontSize: 10.5),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 8),
          ],

          // Baris Angka
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Tagihan', style: TextStyle(fontSize: 10)),
                  Text(
                    _formatRupiah(t.nominalTagihan),
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Terbayar', style: TextStyle(fontSize: 10)),
                  Text(
                    _formatRupiah(t.nominalBayar),
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: t.nominalBayar > 0
                          ? const Color(0xFF10B981)
                          : null,
                    ),
                  ),
                ],
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  const Text('Sisa Tagihan', style: TextStyle(fontSize: 10)),
                  Text(
                    _formatRupiah(t.sisaTagihan),
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                      color: t.sisaTagihan > 0
                          ? AppColors.roseDanger
                          : const Color(0xFF10B981),
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 10),

          // Tombol Aksi
          Row(
            children: [
              if (t.sisaTagihan > 0) ...[
                Expanded(
                  child: SizedBox(
                    height: 36,
                    child: ElevatedButton.icon(
                      onPressed: () => _openBayarSheet(t),
                      icon: const Icon(Icons.payment_rounded, size: 15),
                      label: const Text(
                        'Setor / Bayar',
                        style: TextStyle(
                          fontSize: 11.5,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: primaryColor,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                        elevation: 0,
                      ),
                    ),
                  ),
                ),
                if (t.nominalBayar > 0) const SizedBox(width: 8),
              ],
              if (t.nominalBayar > 0)
                Expanded(
                  child: SizedBox(
                    height: 36,
                    child: OutlinedButton.icon(
                      onPressed: () => _bukaHalamanKwitansiTagihan(t),
                      icon: const Icon(Icons.receipt_rounded, size: 15),
                      label: Text(
                        t.sisaTagihan <= 0 ? 'Kwitansi Lunas' : 'Kwitansi',
                        style: const TextStyle(
                          fontSize: 11.5,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: t.sisaTagihan <= 0
                            ? const Color(0xFF10B981)
                            : primaryColor,
                        side: BorderSide(
                          color:
                              (t.sisaTagihan <= 0
                                      ? const Color(0xFF10B981)
                                      : primaryColor)
                                  .withValues(alpha: 0.5),
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                      ),
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }
}
