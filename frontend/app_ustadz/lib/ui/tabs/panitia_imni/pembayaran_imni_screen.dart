import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/panitia_imni_model.dart';
import '../../../providers/panitia_imni_provider.dart';
import '../../widgets/app_avatar.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/kwitansi_pembayaran_screen.dart';
import '../../widgets/shimmer_loading.dart';

class PembayaranImniScreen extends StatefulWidget {
  const PembayaranImniScreen({super.key});

  @override
  State<PembayaranImniScreen> createState() => _PembayaranImniScreenState();
}

class _PembayaranImniScreenState extends State<PembayaranImniScreen> {
  final TextEditingController _searchCtrl = TextEditingController();
  int? _selectedTingkatId;
  int? _selectedRuanganId;
  String _selectedStatus = 'Semua';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadData();
    });
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  void _loadData() async {
    final provider = context.read<PanitiaImniProvider>();
    await provider.fetchPembayaranRingkasan();
    if (!mounted) return;

    final daftarRuangan = provider.pembayaranDaftarRuangan;
    if (daftarRuangan.isNotEmpty &&
        (_selectedRuanganId == null ||
            !daftarRuangan.any((r) => r.id == _selectedRuanganId))) {
      setState(() {
        _selectedRuanganId = daftarRuangan.first.id;
      });
      provider.setFilterRuangan(_selectedRuanganId);
    } else {
      provider.fetchPesertaList(
        tingkatId: _selectedTingkatId,
        ruanganId: _selectedRuanganId,
        statusPembayaran: _selectedStatus == 'Semua' ? null : _selectedStatus,
        search: _searchCtrl.text.trim(),
      );
    }
  }

  String _formatRupiah(num number) {
    final str = number.toInt().toString();
    final buffer = StringBuffer();
    int count = 0;
    for (int i = str.length - 1; i >= 0; i--) {
      buffer.write(str[i]);
      count++;
      if (count % 3 == 0 && i != 0) {
        buffer.write('.');
      }
    }
    return 'Rp ${buffer.toString().split('').reversed.join('')}';
  }



  // =========================================================================
  // BOTTOM SHEET: SETOR BAYAR TUNAI
  // =========================================================================
  void _openSetorBayarSheet(PesertaImniItem peserta) {
    HapticHelper.light();
    final nominalCtrl = TextEditingController(
      text: peserta.pembayaran.sisaTagihan > 0
          ? peserta.pembayaran.sisaTagihan.toInt().toString()
          : (peserta.pembayaran.nominalTagihan > 0
                ? peserta.pembayaran.nominalTagihan.toInt().toString()
                : '150000'),
    );
    final penyetorCtrl = TextEditingController(text: peserta.namaLengkap);
    final keteranganCtrl = TextEditingController();
    String metodePembayaran = 'Tunai';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          final isDark = Theme.of(context).brightness == Brightness.dark;
          final provider = context.watch<PanitiaImniProvider>();
          final bottomInset = MediaQuery.of(context).viewInsets.bottom;
          final systemBottom = MediaQuery.of(context).padding.bottom;
          final bottomPadding = bottomInset > 0
              ? (bottomInset + 20)
              : (systemBottom + 24);

          return Container(
            constraints: BoxConstraints(
              maxHeight: MediaQuery.of(context).size.height * 0.9,
            ),
            padding: EdgeInsets.only(
              bottom: bottomPadding,
              left: 20,
              right: 20,
              top: 20,
            ),
            decoration: BoxDecoration(
              color: isDark
                  ? AppColors.surfaceContainerLowDark
                  : AppColors.surfaceContainerLowLight,
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(28),
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.2),
                  blurRadius: 20,
                  offset: const Offset(0, -5),
                ),
              ],
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 40,
                      height: 4,
                      decoration: BoxDecoration(
                        color: isDark ? Colors.white24 : Colors.black12,
                        borderRadius: BorderRadius.circular(2),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color:
                              (isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight)
                                  .withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: Icon(
                          Icons.payments_rounded,
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                          size: 24,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Terima Kas / Tagihan IMNI',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            Text(
                              peserta.namaLengkap,
                              style: TextStyle(
                                fontSize: 13,
                                color: isDark
                                    ? const Color(0xFF8D9387)
                                    : const Color(0xFF73796E),
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  const Divider(height: 1),
                  const SizedBox(height: 16),

                  // Info Tagihan
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: isDark
                          ? const Color(0xFF1E261E)
                          : const Color(0xFFF3F7F2),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Total Tagihan',
                              style: TextStyle(
                                fontSize: 11,
                                color: Colors.grey,
                              ),
                            ),
                            Text(
                              _formatRupiah(peserta.pembayaran.nominalTagihan),
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 13,
                              ),
                            ),
                          ],
                        ),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            const Text(
                              'Sisa Piutang',
                              style: TextStyle(
                                fontSize: 11,
                                color: Colors.grey,
                              ),
                            ),
                            Text(
                              _formatRupiah(peserta.pembayaran.sisaTagihan),
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 13,
                                color: Colors.orange,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Input Nominal
                  const Text(
                    'Nominal Diterima (Rp) *',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  TextField(
                    controller: nominalCtrl,
                    keyboardType: TextInputType.number,
                    decoration: InputDecoration(
                      hintText: 'Contoh: 150000',
                      prefixIcon: const Icon(Icons.money_rounded, size: 20),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 12,
                      ),
                    ),
                  ),
                  const SizedBox(height: 14),

                  // Nama Penyetor
                  const Text(
                    'Nama Penyetor (Wali / Santri)',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  TextField(
                    controller: penyetorCtrl,
                    decoration: InputDecoration(
                      hintText: 'Nama penyetor...',
                      prefixIcon: const Icon(
                        Icons.person_outline_rounded,
                        size: 20,
                      ),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 12,
                      ),
                    ),
                  ),
                  const SizedBox(height: 14),

                  // Metode Pembayaran
                  const Text(
                    'Metode Pembayaran',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  Row(
                    children: ['Tunai', 'Transfer', 'Lainnya'].map((m) {
                      final isSelected = (metodePembayaran == m);
                      return Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: ChoiceChip(
                          label: Text(m),
                          selected: isSelected,
                          onSelected: (val) {
                            if (val) {
                              setModalState(() {
                                metodePembayaran = m;
                              });
                            }
                          },
                          selectedColor:
                              (isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight)
                                  .withValues(alpha: 0.2),
                          labelStyle: TextStyle(
                            color: isSelected
                                ? (isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight)
                                : null,
                            fontWeight: isSelected
                                ? FontWeight.bold
                                : FontWeight.normal,
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                  const SizedBox(height: 14),

                  // Keterangan / Catatan
                  const Text(
                    'Keterangan (Opsional)',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  TextField(
                    controller: keteranganCtrl,
                    decoration: InputDecoration(
                      hintText: 'Misal: Titipan Ustadz / Lunas langsung',
                      prefixIcon: const Icon(Icons.note_alt_outlined, size: 20),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 12,
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Tombol Simpan
                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: ElevatedButton.icon(
                      onPressed: provider.isSubmittingPembayaran
                          ? null
                          : () async {
                              final nom = double.tryParse(
                                nominalCtrl.text.replaceAll(
                                  RegExp(r'[^0-9]'),
                                  '',
                                ),
                              );
                              if (nom == null || nom <= 0) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(
                                    content: Text(
                                      'Masukkan nominal pembayaran yang valid!',
                                    ),
                                  ),
                                );
                                return;
                              }

                              final result = await provider.simpanPembayaran(
                                pesertaId: peserta.id,
                                nominalBayar: nom,
                                namaPenyetor: penyetorCtrl.text.trim().isEmpty
                                    ? null
                                    : penyetorCtrl.text.trim(),
                                metodePembayaran: metodePembayaran,
                                keterangan: keteranganCtrl.text.trim().isEmpty
                                    ? null
                                    : keteranganCtrl.text.trim(),
                              );

                              if (context.mounted) {
                                Navigator.pop(context);
                                if (result != null) {
                                  final statusBayar =
                                      result['status_pembayaran'] as String? ??
                                      '';
                                  final sisaTagihan =
                                      (result['sisa_tagihan'] as num?)
                                          ?.toDouble() ??
                                      0.0;
                                  final isLunas =
                                      statusBayar == 'Lunas' ||
                                      sisaTagihan <= 0;

                                  if (isLunas) {
                                    _bukaHalamanKwitansiImniResult(
                                      peserta,
                                      result,
                                      nom,
                                      metodePembayaran,
                                    );
                                  } else {
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      SnackBar(
                                        content: Text(
                                          'Pembayaran untuk ${peserta.namaLengkap} sebesar ${_formatRupiah(nom)} berhasil dicatat!',
                                        ),
                                        backgroundColor: Colors.green,
                                      ),
                                    );
                                  }
                                } else if (provider.errorMessage != null) {
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      content: Text(provider.errorMessage!),
                                      backgroundColor: Colors.redAccent,
                                    ),
                                  );
                                }
                              }
                            },
                      icon: provider.isSubmittingPembayaran
                          ? const SizedBox(
                              width: 20,
                              height: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white,
                              ),
                            )
                          : const Icon(Icons.check_circle_rounded),
                      label: Text(
                        provider.isSubmittingPembayaran
                            ? 'Menyimpan...'
                            : 'Simpan Pembayaran Kas',
                        style: const TextStyle(fontWeight: FontWeight.bold),
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

  void _bukaHalamanKwitansiImniResult(
    PesertaImniItem peserta,
    Map<String, dynamic> data,
    double nominalBayar,
    String metode,
  ) {
    HapticHelper.confirmSuccess();
    final noKwitansi =
        data['no_kwitansi'] as String? ?? peserta.pembayaran.noKwitansi;
    final kwitansiUrl =
        data['kwitansi_url'] as String? ?? peserta.pembayaran.kwitansiUrl;
    final sisaTagihan = (data['sisa_tagihan'] as num?)?.toDouble() ?? 0.0;
    final statusBayar = data['status_pembayaran'] as String? ?? 'Lunas';

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => KwitansiPembayaranScreen(
          judulKegiatan: 'Imtihan Nihai (IMNI)',
          subJudul: 'Ujian Akhir Madrasah Diniyah Takmiliyah',
          noTransaksi: (noKwitansi.isNotEmpty && noKwitansi != '-')
              ? noKwitansi
              : 'TRX/IMNI/${peserta.nomorPeserta}',
          status: statusBayar,
          nominalBayar: nominalBayar,
          totalTagihan: peserta.pembayaran.nominalTagihan,
          sisaTagihan: sisaTagihan,
          namaPembayar: peserta.namaLengkap,
          noRegistrasi: peserta.nomorPeserta,
          rincianSubjek:
              'Ruangan: ${peserta.ruanganUjian} (${peserta.namaTingkat})',
          metodePembayaran: metode,
          tanggalBayar: DateTime.now(),
          kwitansiUrl: kwitansiUrl,
        ),
      ),
    );
  }

  void _bukaHalamanKwitansiPeserta(PesertaImniItem peserta) {
    HapticHelper.light();
    final pem = peserta.pembayaran;
    final tglBayar = pem.tanggalBayar != null
        ? DateTime.tryParse(pem.tanggalBayar!)
        : null;

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => KwitansiPembayaranScreen(
          judulKegiatan: 'Imtihan Nihai (IMNI)',
          subJudul: 'Ujian Akhir Madrasah Diniyah Takmiliyah',
          noTransaksi: (pem.noKwitansi.isNotEmpty && pem.noKwitansi != '-')
              ? pem.noKwitansi
              : 'TRX/IMNI/${peserta.nomorPeserta}',
          status: pem.statusPembayaran,
          nominalBayar:
              pem.nominalBayar > 0 ? pem.nominalBayar : pem.nominalTagihan,
          totalTagihan: pem.nominalTagihan,
          sisaTagihan: pem.sisaTagihan,
          namaPembayar: peserta.namaLengkap,
          noRegistrasi: peserta.nomorPeserta,
          rincianSubjek:
              'Ruangan: ${peserta.ruanganUjian} (${peserta.namaTingkat})',
          metodePembayaran: pem.metodePembayaran,
          tanggalBayar: tglBayar,
          kasir: pem.penerimaNama,
          namaPenyetor: pem.namaPenyetor,
          kwitansiUrl: pem.kwitansiUrl,
        ),
      ),
    );
  }

  void _confirmBatalBayar(PesertaImniItem peserta) {
    if (peserta.pembayaran.id == null) return;
    HapticHelper.warning();

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Batalkan Pembayaran?'),
        content: Text(
          'Apakah Anda yakin ingin membatalkan pembayaran untuk ${peserta.namaLengkap}? Status akan dikembalikan menjadi Belum Lunas.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Tidak'),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(ctx);
              final provider = context.read<PanitiaImniProvider>();
              final ok = await provider.batalPembayaran(peserta.pembayaran.id!);
              if (mounted) {
                if (ok) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(
                        'Pembayaran ${peserta.namaLengkap} dibatalkan',
                      ),
                      backgroundColor: Colors.orange,
                    ),
                  );
                }
              }
            },
            style: ElevatedButton.styleFrom(backgroundColor: Colors.redAccent),
            child: const Text(
              'Ya, Batalkan',
              style: TextStyle(color: Colors.white),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final provider = context.watch<PanitiaImniProvider>();
    final ringkasan = provider.pembayaranRingkasan;
    final daftarRuangan = provider.pembayaranDaftarRuangan;

    if (daftarRuangan.isNotEmpty &&
        (_selectedRuanganId == null ||
            !daftarRuangan.any((r) => r.id == _selectedRuanganId))) {
      _selectedRuanganId = daftarRuangan.first.id;
    }

    return Scaffold(
      appBar: const CustomAppBar(
        titleText: 'Pembayaran IMNI',
        subtitleText: 'Penerimaan Tagihan Murid Kelas Akhir',
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          _loadData();
        },
        child: CustomScrollView(
          slivers: [
            // 1. STATISTIK KEUANGAN & PROGRESS BAR
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 12),
                child: Column(
                  children: [
                    GlassCard(
                      padding: const EdgeInsets.all(18),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Total Penerimaan IMNI',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: isDark
                                          ? const Color(0xFF8D9387)
                                          : const Color(0xFF73796E),
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    _formatRupiah(
                                      ringkasan?.totalTerbayar ?? 0,
                                    ),
                                    style: TextStyle(
                                      fontSize: 22,
                                      fontWeight: FontWeight.bold,
                                      color: isDark
                                          ? AppColors.primaryDark
                                          : AppColors.primaryLight,
                                    ),
                                  ),
                                ],
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 6,
                                ),
                                decoration: BoxDecoration(
                                  color:
                                      (isDark
                                              ? AppColors.primaryDark
                                              : AppColors.primaryLight)
                                          .withValues(alpha: 0.12),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Text(
                                  '${ringkasan?.persentaseTerkumpul ?? 0}%',
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 14,
                                    color: isDark
                                        ? AppColors.primaryDark
                                        : AppColors.primaryLight,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          // Progress Bar
                          ClipRRect(
                            borderRadius: BorderRadius.circular(6),
                            child: LinearProgressIndicator(
                              value:
                                  (ringkasan?.persentaseTerkumpul ?? 0) / 100,
                              minHeight: 8,
                              backgroundColor: isDark
                                  ? Colors.white12
                                  : Colors.black12,
                              valueColor: AlwaysStoppedAnimation<Color>(
                                isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                              ),
                            ),
                          ),
                          const SizedBox(height: 14),
                          const Divider(height: 1),
                          const SizedBox(height: 14),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              _buildMiniStat(
                                label: 'Total Tagihan',
                                value: _formatRupiah(
                                  ringkasan?.totalTagihan ?? 0,
                                ),
                                isDark: isDark,
                              ),
                              _buildMiniStat(
                                label: 'Sisa Piutang',
                                value: _formatRupiah(
                                  ringkasan?.sisaPiutang ?? 0,
                                ),
                                isDark: isDark,
                                isAccent: true,
                              ),
                              _buildMiniStat(
                                label: 'Lunas / Peserta',
                                value:
                                    '${ringkasan?.totalLunas ?? 0} / ${ringkasan?.totalPeserta ?? 0}',
                                isDark: isDark,
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),

                    // Breakdown Tingkat Accordion
                    if (ringkasan != null &&
                        ringkasan.breakdownTingkat.isNotEmpty) ...[
                      const SizedBox(height: 10),
                      Row(
                        children: ringkasan.breakdownTingkat.map((b) {
                          return Expanded(
                            child: Container(
                              margin: const EdgeInsets.symmetric(horizontal: 3),
                              padding: const EdgeInsets.symmetric(
                                vertical: 8,
                                horizontal: 8,
                              ),
                              decoration: BoxDecoration(
                                color: isDark
                                    ? const Color(0xFF161F16)
                                    : Colors.white,
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(
                                  color: isDark
                                      ? Colors.white10
                                      : Colors.black.withValues(alpha: 0.06),
                                ),
                              ),
                              child: Column(
                                children: [
                                  Text(
                                    b.kodeTingkat,
                                    style: const TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 12,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    '${b.totalLunas}/${b.totalPeserta} Lunas',
                                    style: TextStyle(
                                      fontSize: 10,
                                      color: isDark
                                          ? const Color(0xFF8D9387)
                                          : const Color(0xFF73796E),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          );
                        }).toList(),
                      ),
                    ],
                  ],
                ),
              ),
            ),

            // 2. SEARCH & FILTER CHIPS
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Filter Ruangan Kelas IMNI
                    if (provider.pembayaranDaftarRuangan.isNotEmpty) ...[
                      Row(
                        children: [
                          Icon(
                            Icons.meeting_room_rounded,
                            size: 16,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                          const SizedBox(width: 6),
                          const Text(
                            'Ruangan / Kelas IMNI',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14),
                        decoration: BoxDecoration(
                          color: isDark
                              ? const Color(0xFF161F16)
                              : Colors.white,
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(
                            color: isDark ? Colors.white12 : Colors.black12,
                          ),
                        ),
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<int?>(
                            isExpanded: true,
                            value:
                                daftarRuangan.any(
                                  (r) => r.id == _selectedRuanganId,
                                )
                                ? _selectedRuanganId
                                : (daftarRuangan.isNotEmpty
                                      ? daftarRuangan.first.id
                                      : null),
                            hint: const Text(
                              'Pilih Ruangan / Kelas IMNI',
                              style: TextStyle(fontSize: 13),
                            ),
                            items: daftarRuangan.map((r) {
                              return DropdownMenuItem<int?>(
                                value: r.id,
                                child: Text(
                                  '${r.namaRuangan} (${r.namaLevel} - ${r.kodeTingkat})',
                                  style: const TextStyle(
                                    fontSize: 13,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              );
                            }).toList(),
                            onChanged: (val) {
                              if (val != null) {
                                setState(() {
                                  _selectedRuanganId = val;
                                });
                                provider.setFilterRuangan(val);
                              }
                            },
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),
                    ],

                    // Search bar
                    TextField(
                      controller: _searchCtrl,
                      onChanged: (val) {
                        provider.setSearchPeserta(val.trim());
                      },
                      decoration: InputDecoration(
                        hintText: 'Cari Nama, NISM, atau No. Peserta...',
                        hintStyle: const TextStyle(fontSize: 13),
                        prefixIcon: const Icon(Icons.search_rounded, size: 20),
                        suffixIcon: _searchCtrl.text.isNotEmpty
                            ? IconButton(
                                icon: const Icon(Icons.clear, size: 18),
                                onPressed: () {
                                  _searchCtrl.clear();
                                  provider.setSearchPeserta('');
                                },
                              )
                            : null,
                        filled: true,
                        fillColor: isDark
                            ? const Color(0xFF161F16)
                            : Colors.white,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(14),
                          borderSide: BorderSide(
                            color: isDark ? Colors.white12 : Colors.black12,
                          ),
                        ),
                        enabledBorder: OutlineInputBorder(
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

                    // Filter Status Chips
                    SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: Row(
                        children: [
                          ...[
                            'Semua',
                            'Lunas',
                            'Belum Lunas',
                            'Dispensasi',
                          ].map((st) {
                            final isSelected = (_selectedStatus == st);
                            return Padding(
                              padding: const EdgeInsets.only(right: 6),
                              child: ChoiceChip(
                                label: Text(st),
                                selected: isSelected,
                                onSelected: (val) {
                                  if (val) {
                                    setState(() {
                                      _selectedStatus = st;
                                    });
                                    provider.setFilterStatusBayar(
                                      st == 'Semua' ? null : st,
                                    );
                                  }
                                },
                                selectedColor:
                                    (isDark
                                            ? AppColors.primaryDark
                                            : AppColors.primaryLight)
                                        .withValues(alpha: 0.2),
                                labelStyle: TextStyle(
                                  fontSize: 12,
                                  color: isSelected
                                      ? (isDark
                                            ? AppColors.primaryDark
                                            : AppColors.primaryLight)
                                      : null,
                                  fontWeight: isSelected
                                      ? FontWeight.bold
                                      : FontWeight.normal,
                                ),
                              ),
                            );
                          }),
                        ],
                      ),
                    ),
                    const SizedBox(height: 10),
                  ],
                ),
              ),
            ),

            // 3. DAFTAR PESERTA IMNI
            if (provider.isLoadingPembayaran)
              const SliverToBoxAdapter(
                child: Padding(
                  padding: EdgeInsets.symmetric(horizontal: 16),
                  child: ShimmerLoadingList(count: 5, height: 100),
                ),
              )
            else if (provider.pesertaList.isEmpty)
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.all(32),
                  child: Center(
                    child: Column(
                      children: [
                        Icon(
                          Icons.person_search_rounded,
                          size: 48,
                          color: isDark ? Colors.white24 : Colors.black26,
                        ),
                        const SizedBox(height: 12),
                        Text(
                          'Tidak ada data peserta IMNI ditemukan.',
                          style: TextStyle(
                            color: isDark
                                ? const Color(0xFF8D9387)
                                : const Color(0xFF73796E),
                            fontSize: 13,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              )
            else
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(16, 4, 16, 32),
                sliver: SliverList(
                  delegate: SliverChildBuilderDelegate((context, index) {
                    final p = provider.pesertaList[index];
                    return _buildPesertaCard(context, p, isDark);
                  }, childCount: provider.pesertaList.length),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildMiniStat({
    required String label,
    required String value,
    required bool isDark,
    bool isAccent = false,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 11,
            color: isDark ? const Color(0xFF8D9387) : const Color(0xFF73796E),
          ),
        ),
        const SizedBox(height: 2),
        Text(
          value,
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.bold,
            color: isAccent ? Colors.orange : null,
          ),
        ),
      ],
    );
  }

  Widget _buildPesertaCard(
    BuildContext context,
    PesertaImniItem p,
    bool isDark,
  ) {
    final pem = p.pembayaran;
    final isLunas = pem.statusPembayaran == 'Lunas';
    final isDispensasi = p.statusKelayakan == 'Dispensasi';

    Color statusBg;
    Color statusText;
    String statusLabel;

    if (isLunas) {
      statusBg = Colors.green.withValues(alpha: 0.12);
      statusText = Colors.green;
      statusLabel = 'Lunas';
    } else if (isDispensasi) {
      statusBg = Colors.orange.withValues(alpha: 0.12);
      statusText = Colors.orange;
      statusLabel = 'Dispensasi';
    } else {
      statusBg = Colors.red.withValues(alpha: 0.12);
      statusText = Colors.red;
      statusLabel = 'Belum Lunas';
    }

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF161F16) : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isDark ? Colors.white10 : Colors.black.withValues(alpha: 0.06),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.03),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          children: [
            Row(
              children: [
                AppAvatar(imageUrl: p.foto, name: p.namaLengkap, radius: 21),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              p.namaLengkap,
                              style: const TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 14,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 3,
                            ),
                            decoration: BoxDecoration(
                              color: statusBg,
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              statusLabel,
                              style: TextStyle(
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                color: statusText,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'NISM: ${p.nism}',
                        style: TextStyle(
                          fontSize: 11,
                          color: isDark
                              ? const Color(0xFF8D9387)
                              : const Color(0xFF73796E),
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Ruangan: ${p.ruanganAsal} (${p.kodeTingkat})',
                        style: TextStyle(
                          fontSize: 11,
                          color: isDark
                              ? const Color(0xFF8D9387)
                              : const Color(0xFF73796E),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            const Divider(height: 1),
            const SizedBox(height: 10),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Bayar: ${_formatRupiah(pem.nominalBayar)}',
                      style: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    Text(
                      'Sisa: ${_formatRupiah(pem.sisaTagihan)}',
                      style: TextStyle(
                        fontSize: 11,
                        color: pem.sisaTagihan > 0
                            ? Colors.orange
                            : Colors.grey,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
                Row(
                  children: [
                    if (pem.nominalBayar > 0) ...[
                      OutlinedButton.icon(
                        onPressed: () => _bukaHalamanKwitansiPeserta(p),
                        icon: const Icon(Icons.receipt_rounded, size: 14),
                        label: const Text(
                          'Kwitansi',
                          style: TextStyle(fontSize: 11),
                        ),
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 6,
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                        ),
                      ),
                      PopupMenuButton<String>(
                        icon: Icon(
                          Icons.more_vert_rounded,
                          size: 18,
                          color: isDark ? Colors.white60 : Colors.black54,
                        ),
                        padding: EdgeInsets.zero,
                        constraints: const BoxConstraints(),
                        tooltip: 'Opsi Pembayaran',
                        onSelected: (val) {
                          if (val == 'batal') {
                            _confirmBatalBayar(p);
                          }
                        },
                        itemBuilder: (ctx) => [
                          const PopupMenuItem(
                            value: 'batal',
                            child: Row(
                              children: [
                                Icon(
                                  Icons.undo_rounded,
                                  size: 16,
                                  color: Colors.redAccent,
                                ),
                                SizedBox(width: 8),
                                Text(
                                  'Batalkan Pembayaran',
                                  style: TextStyle(
                                    color: Colors.redAccent,
                                    fontSize: 12,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ],
                    if (!isLunas) ...[
                      if (pem.nominalBayar > 0) const SizedBox(width: 6),
                      ElevatedButton.icon(
                        onPressed: () => _openSetorBayarSheet(p),
                        icon: const Icon(Icons.add_card_rounded, size: 14),
                        label: const Text(
                          'Setor Kas',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 6,
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(8),
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
