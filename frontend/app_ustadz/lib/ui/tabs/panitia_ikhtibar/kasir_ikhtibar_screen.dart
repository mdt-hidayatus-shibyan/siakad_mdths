import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../data/models/panitia_ikhtibar_model.dart';
import '../../../providers/panitia_ikhtibar_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/segmented_tab_bar.dart';
import '../../widgets/shimmer_loading.dart';

class KasirIkhtibarScreen extends StatefulWidget {
  const KasirIkhtibarScreen({super.key});

  @override
  State<KasirIkhtibarScreen> createState() => _KasirIkhtibarScreenState();
}

class _KasirIkhtibarScreenState extends State<KasirIkhtibarScreen> {
  final TextEditingController _searchCtrl = TextEditingController();

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

  void _loadData() {
    final provider = context.read<PanitiaIkhtibarProvider>();
    provider.fetchKasirRingkasan();
    provider.fetchKasirList();
  }

  String _formatRupiah(num number) {
    final formatter = NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp ',
      decimalDigits: 0,
    );
    return formatter.format(number);
  }

  Future<void> _bukaKwitansi(String? url) async {
    if (url == null || url.isEmpty) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Tautan kwitansi tidak tersedia')),
        );
      }
      return;
    }
    HapticHelper.light();
    try {
      final uri = Uri.parse(url);
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Tidak dapat membuka tautan kwitansi'),
            ),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Gagal membuka kwitansi: $e')));
      }
    }
  }

  void _showModalDetailLunas(Map<String, dynamic> data) {
    HapticHelper.confirmSuccess();
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final primaryColor = isDark
        ? AppColors.primaryDark
        : AppColors.primaryLight;
    final namaKk = data['nama_kepala_keluarga'] ?? '-';
    final noReg = data['no_registrasi'] ?? '-';
    final kampung = data['kampung'] ?? '-';
    final totalTerbayar = (data['total_terbayar'] as num?)?.toInt() ?? 0;
    final nominalBayar = (data['nominal_bayar'] as num?)?.toInt() ?? 0;
    final metode = data['metode_pembayaran'] ?? 'Tunai';
    final kwitansiUrl = data['kwitansi_url'] as String?;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (dlgCtx) => Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
        backgroundColor: isDark ? const Color(0xFF1E2129) : Colors.white,
        child: Padding(
          padding: const EdgeInsets.all(22),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 64,
                height: 64,
                decoration: BoxDecoration(
                  color: primaryColor.withValues(alpha: 0.15),
                  shape: BoxShape.circle,
                ),
                child: Icon(
                  Icons.check_circle_rounded,
                  color: primaryColor,
                  size: 40,
                ),
              ),
              const SizedBox(height: 16),
              const Text(
                'Pembayaran Lunas!',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 6),
              Text(
                'Alhamdulillah, seluruh tagihan iuran ikhtibar KK ini telah selesai dibayarkan.',
                style: TextStyle(
                  fontSize: 12,
                  color: isDark ? Colors.white60 : Colors.black54,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 16),
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: isDark
                      ? Colors.white.withValues(alpha: 0.04)
                      : Colors.black.withValues(alpha: 0.03),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                    color: isDark ? Colors.white12 : Colors.black12,
                  ),
                ),
                child: Column(
                  children: [
                    _buildDetailRow(
                      'Kepala Keluarga',
                      namaKk,
                      isDark,
                      isBold: true,
                    ),
                    const Divider(height: 14),
                    _buildDetailRow('No. Registrasi', noReg, isDark),
                    const Divider(height: 14),
                    _buildDetailRow('Dusun / Kampung', kampung, isDark),
                    const Divider(height: 14),
                    _buildDetailRow('Metode Bayar', metode, isDark),
                    const Divider(height: 14),
                    _buildDetailRow(
                      'Bayar Sekarang',
                      _formatRupiah(nominalBayar),
                      isDark,
                      valueColor: primaryColor,
                    ),
                    const Divider(height: 14),
                    _buildDetailRow(
                      'Total Terbayar',
                      _formatRupiah(totalTerbayar),
                      isDark,
                      isBold: true,
                      valueColor: primaryColor,
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),
              if (kwitansiUrl != null && kwitansiUrl.isNotEmpty) ...[
                SizedBox(
                  width: double.infinity,
                  height: 46,
                  child: ElevatedButton.icon(
                    onPressed: () => _bukaKwitansi(kwitansiUrl),
                    icon: const Icon(Icons.receipt_long_rounded, size: 18),
                    label: const Text(
                      'Lihat & Cetak Kwitansi',
                      style: TextStyle(fontWeight: FontWeight.bold),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: primaryColor,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 10),
              ],
              SizedBox(
                width: double.infinity,
                height: 42,
                child: TextButton(
                  onPressed: () => Navigator.pop(dlgCtx),
                  child: Text(
                    'Selesai',
                    style: TextStyle(
                      fontWeight: FontWeight.w600,
                      color: isDark ? Colors.white70 : Colors.black87,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDetailRow(
    String label,
    String value,
    bool isDark, {
    bool isBold = false,
    Color? valueColor,
  }) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 11,
            color: isDark ? Colors.white60 : Colors.black54,
          ),
        ),
        Text(
          value,
          style: TextStyle(
            fontSize: 11,
            fontWeight: isBold ? FontWeight.bold : FontWeight.w500,
            color: valueColor ?? (isDark ? Colors.white : Colors.black87),
          ),
        ),
      ],
    );
  }

  // =========================================================================
  // MODAL BOTTOM SHEET: PROSES BAYAR TAGIHAN KK
  // =========================================================================
  void _openBayarSheet(TagihanKkIkhtibarItem tagihan) {
    HapticHelper.light();
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

          return Container(
            padding: EdgeInsets.fromLTRB(20, 16, 20, bottomInset + 20),
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
                            _showModalDetailLunas(result);
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
    final provider = context.watch<PanitiaIkhtibarProvider>();
    final ringkasan = provider.kasirRingkasan;

    return Scaffold(
      appBar: CustomAppBar(
        titleText: 'Kasir Ikhtibar (${ringkasan?.ikhtibarKe ?? "52 Th"})',
        showBackButton: true,
      ),
      body: RefreshIndicator(
        onRefresh: () async => _loadData(),
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // 1. Ringkasan Eksekutif Kasir Card
              if (ringkasan != null) ...[
                GlassCard(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.all(8),
                                decoration: BoxDecoration(
                                  color:
                                      (isDark
                                              ? AppColors.primaryDark
                                              : AppColors.primaryLight)
                                          .withValues(alpha: 0.15),
                                  shape: BoxShape.circle,
                                ),
                                child: Icon(
                                  Icons.point_of_sale_rounded,
                                  color: isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight,
                                  size: 20,
                                ),
                              ),
                              const SizedBox(width: 10),
                              const Text(
                                'Progres Tagihan Ikhtibar',
                                style: TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 4,
                            ),
                            decoration: BoxDecoration(
                              color:
                                  (isDark
                                          ? AppColors.primaryDark
                                          : AppColors.primaryLight)
                                      .withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Text(
                              '${ringkasan.persentaseKelunasan}% Lunas',
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
                      const SizedBox(height: 14),

                      // Progress Bar
                      ClipRRect(
                        borderRadius: BorderRadius.circular(6),
                        child: LinearProgressIndicator(
                          value: (ringkasan.persentaseKelunasan / 100).clamp(
                            0.0,
                            1.0,
                          ),
                          minHeight: 8,
                          backgroundColor: isDark
                              ? Colors.white12
                              : Colors.black12,
                          color: isDark
                              ? AppColors.primaryDark
                              : AppColors.primaryLight,
                        ),
                      ),
                      const SizedBox(height: 14),

                      Row(
                        children: [
                          Expanded(
                            child: _buildMetricCol(
                              'Target Total',
                              _formatRupiah(ringkasan.targetNominal),
                              isDark,
                            ),
                          ),
                          Expanded(
                            child: _buildMetricCol(
                              'Terkumpul',
                              _formatRupiah(ringkasan.totalMasuk),
                              isDark,
                              color: isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight,
                            ),
                          ),
                          Expanded(
                            child: _buildMetricCol(
                              'Sisa Piutang',
                              _formatRupiah(ringkasan.totalSisa),
                              isDark,
                              color: AppColors.roseDanger,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                // const SizedBox(height: 12),

                const SizedBox(height: 8),
              ],

              // 2. Search Box
              TextField(
                controller: _searchCtrl,
                onSubmitted: (val) => provider.setSearchQuery(val.trim()),
                decoration: InputDecoration(
                  hintText:
                      'Cari No. Registrasi / KK / Nama Kepala Keluarga...',
                  prefixIcon: const Icon(Icons.search_rounded),
                  suffixIcon: _searchCtrl.text.isNotEmpty
                      ? IconButton(
                          icon: const Icon(Icons.clear_rounded),
                          onPressed: () {
                            _searchCtrl.clear();
                            provider.setSearchQuery('');
                          },
                        )
                      : null,
                  filled: true,
                  fillColor: isDark
                      ? Colors.white.withValues(alpha: 0.05)
                      : Colors.black.withValues(alpha: 0.03),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(14),
                    borderSide: BorderSide.none,
                  ),
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical: 12,
                  ),
                ),
              ),
              const SizedBox(height: 12),

              // 3. Filter Kampung Input Select
              if (ringkasan?.daftarKampung.isNotEmpty ?? false) ...[
                DropdownButtonFormField<int?>(
                  initialValue: provider.filterKampungId,
                  isExpanded: true,
                  decoration: InputDecoration(
                    prefixIcon: const Icon(
                      Icons.location_on_outlined,
                      size: 20,
                    ),
                    filled: true,
                    fillColor: isDark
                        ? Colors.white.withValues(alpha: 0.05)
                        : Colors.black.withValues(alpha: 0.03),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(14),
                      borderSide: BorderSide.none,
                    ),
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 12,
                    ),
                  ),
                  hint: const Text(
                    'Pilih Kampung',
                    style: TextStyle(fontSize: 13),
                  ),
                  items: [
                    const DropdownMenuItem<int?>(
                      value: null,
                      child: Text(
                        'Semua Kampung',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                    ...ringkasan!.daftarKampung.map(
                      (k) => DropdownMenuItem<int?>(
                        value: k.id,
                        child: Text(
                          '${k.kode} - ${k.namaKampung}',
                          style: const TextStyle(fontSize: 13),
                        ),
                      ),
                    ),
                  ],
                  onChanged: (val) {
                    provider.setFilterKampung(val);
                  },
                ),
                const SizedBox(height: 12),
              ],

              // 4. Filter Status Bayar (SegmentedTabBar konsisten M3)
              SegmentedTabBar(
                margin: const EdgeInsets.only(bottom: 12),
                selectedIndex: [
                  'Semua',
                  'Belum Lunas',
                  'Cicilan',
                  'Lunas',
                ].indexOf(provider.filterStatusBayar).clamp(0, 3),
                onTabChanged: (idx) {
                  const statuses = ['Semua', 'Belum Lunas', 'Cicilan', 'Lunas'];
                  if (idx >= 0 && idx < statuses.length) {
                    provider.setFilterStatusBayar(statuses[idx]);
                  }
                },
                items: [
                  SegmentedTabItem(
                    activeIcon: Icons.all_inclusive_rounded,
                    inactiveIcon: Icons.all_inclusive_outlined,
                    label: 'Semua',
                    activeColor: isDark
                        ? AppColors.primaryDark
                        : AppColors.primaryLight,
                  ),
                  SegmentedTabItem(
                    activeIcon: Icons.pending_actions_rounded,
                    inactiveIcon: Icons.pending_actions_outlined,
                    label: 'Belum Lunas',
                    activeColor: AppColors.roseDanger,
                  ),
                  SegmentedTabItem(
                    activeIcon: Icons.timelapse_rounded,
                    inactiveIcon: Icons.timelapse_outlined,
                    label: 'Cicilan',
                    activeColor: AppColors.amberAccent,
                  ),
                  SegmentedTabItem(
                    activeIcon: Icons.check_circle_rounded,
                    inactiveIcon: Icons.check_circle_outline_rounded,
                    label: 'Lunas',
                    activeColor: isDark
                        ? AppColors.primaryDark
                        : AppColors.primaryLight,
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // 5. Daftar Tagihan KK Items
              if (provider.isLoadingKasir)
                const ShimmerLoadingList(count: 4)
              else if (provider.tagihanList.isEmpty)
                Center(
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 40),
                    child: Column(
                      children: [
                        Icon(
                          Icons.inbox_rounded,
                          size: 48,
                          color: isDark ? Colors.white24 : Colors.black26,
                        ),
                        const SizedBox(height: 10),
                        const Text(
                          'Tidak ada data tagihan KK yang cocok',
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
                  separatorBuilder: (_, __) => const SizedBox(height: 12),
                  itemBuilder: (ctx, idx) {
                    final t = provider.tagihanList[idx];
                    return _buildTagihanCard(t, isDark);
                  },
                ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMetricCol(
    String title,
    String value,
    bool isDark, {
    Color? color,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: TextStyle(
            fontSize: 10,
            color: isDark ? Colors.white60 : Colors.black54,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          value,
          style: TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
      ],
    );
  }

  Widget _buildTagihanCard(TagihanKkIkhtibarItem t, bool isDark) {
    Color statusColor;
    String statusText;
    if (t.hasPendingTabungan) {
      statusColor = AppColors.amberAccent;
      statusText = 'Menunggu Konfirmasi';
    } else if (t.statusBayar == 'Lunas') {
      statusColor = isDark ? AppColors.primaryDark : AppColors.primaryLight;
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
                    const SizedBox(height: 2),
                    Text(
                      'No. Reg: ${t.noRegistrasi} • Dusun: ${t.kampung}',
                      style: TextStyle(
                        fontSize: 11,
                        color: isDark ? Colors.white60 : Colors.black54,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 3,
                ),
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  statusText,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: statusColor,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),

          // Info Menunggu Konfirmasi Tabungan
          if (t.hasPendingTabungan) ...[
            Container(
              margin: const EdgeInsets.only(bottom: 10),
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
              decoration: BoxDecoration(
                color: Colors.amber.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: Colors.amber.withValues(alpha: 0.3)),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.info_outline_rounded,
                    size: 16,
                    color: Colors.amber,
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Potongan tabungan menunggu konfirmasi admin',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                            color: Colors.amber,
                          ),
                        ),
                        if (t.pembayaranTerakhir?.nomorTabungan != null)
                          Text(
                            'Rek: ${t.pembayaranTerakhir!.nomorTabungan} (${t.pembayaranTerakhir!.namaNasabah ?? t.namaKepalaKeluarga})',
                            style: TextStyle(
                              fontSize: 10,
                              color: isDark ? Colors.white60 : Colors.black54,
                            ),
                          ),
                      ],
                    ),
                  ),
                  if (t.pembayaranTerakhir != null &&
                      !t.pembayaranTerakhir!.isVerified &&
                      t.pembayaranTerakhir!.id > 0)
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 3,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.amber.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: const Text(
                        'Menunggu Admin',
                        style: TextStyle(
                          fontSize: 9,
                          fontWeight: FontWeight.bold,
                          color: Colors.amber,
                        ),
                      ),
                    ),
                ],
              ),
            ),
          ],

          // Daftar Anak Murid
          if (t.daftarAnak.isNotEmpty) ...[
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
              decoration: BoxDecoration(
                color: isDark
                    ? Colors.white.withValues(alpha: 0.03)
                    : Colors.black.withValues(alpha: 0.02),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                children: [
                  const Icon(Icons.people_alt_outlined, size: 14),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      'Anak: ${t.daftarAnak.map((a) => "${a.nama} (${a.ruangan})").join(", ")}',
                      style: const TextStyle(fontSize: 11),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 10),
          ],

          // Baris Keuangan
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Tagihan KK', style: TextStyle(fontSize: 10)),
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
                      color: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
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
                          : (isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight),
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Tombol Aksi Pembayaran & Kwitansi
          if (t.sisaTagihan <= 0) ...[
            if (t.kwitansiUrl != null && t.kwitansiUrl!.isNotEmpty)
              SizedBox(
                width: double.infinity,
                height: 38,
                child: OutlinedButton.icon(
                  onPressed: () => _bukaKwitansi(t.kwitansiUrl),
                  icon: const Icon(Icons.receipt_long_rounded, size: 16),
                  label: const Text(
                    'Lihat Kwitansi Resmi (Lunas)',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold),
                  ),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: isDark
                        ? AppColors.primaryDark
                        : AppColors.primaryLight,
                    side: BorderSide(
                      color: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                    ),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                ),
              ),
          ] else ...[
            Row(
              children: [
                Expanded(
                  child: SizedBox(
                    height: 38,
                    child: ElevatedButton.icon(
                      onPressed: () => _openBayarSheet(t),
                      icon: const Icon(Icons.payment_rounded, size: 16),
                      label: const Text(
                        'Setor / Bayar',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                      ),
                    ),
                  ),
                ),
                if (t.nominalBayar > 0 &&
                    t.kwitansiUrl != null &&
                    t.kwitansiUrl!.isNotEmpty) ...[
                  const SizedBox(width: 8),
                  SizedBox(
                    height: 38,
                    child: OutlinedButton.icon(
                      onPressed: () => _bukaKwitansi(t.kwitansiUrl),
                      icon: const Icon(Icons.receipt_rounded, size: 15),
                      label: const Text(
                        'Kwitansi',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                        side: BorderSide(
                          color:
                              (isDark
                                      ? AppColors.primaryDark
                                      : AppColors.primaryLight)
                                  .withValues(alpha: 0.5),
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                        padding: const EdgeInsets.symmetric(horizontal: 10),
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ],
        ],
      ),
    );
  }
}
