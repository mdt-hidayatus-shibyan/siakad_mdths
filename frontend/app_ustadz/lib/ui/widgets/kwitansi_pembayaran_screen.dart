import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/network/api_client.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/date_helper.dart';
import '../../../core/utils/haptic_helper.dart';

/// Halaman Kwitansi / Bukti Pembayaran Digital berdesain ala Mobile Banking (M-Banking)
/// Dibuat khusus agar rapi, resmi, dan mudah di-screenshot maupun dibagikan ke wali murid.
class KwitansiPembayaranScreen extends StatelessWidget {
  final String
  judulKegiatan; // Misal: "Ikhtibar Ke-XX" atau "Imtihan Nihai (IMNI)"
  final String? subJudul; // Misal: "Tahun Ajaran 2026/2027"
  final String noTransaksi; // Nomor Transaksi / No. Kwitansi
  final String status; // "Lunas", "Cicilan", "Menunggu Konfirmasi"
  final num nominalBayar; // Nominal yang dibayarkan
  final num? totalTagihan; // Total kewajiban tagihan
  final num? sisaTagihan; // Sisa tagihan saat ini
  final String namaPembayar; // Nama Kepala Keluarga atau Murid
  final String? noRegistrasi; // No. Registrasi KK atau No. Peserta
  final String? kampung; // Dusun / Wilayah
  final String? rincianSubjek; // Rincian Murid / anak (opsional)
  final String metodePembayaran; // Tunai, Transfer, Potong Tabungan
  final String? nomorTabungan; // Jika potong tabungan
  final String? namaNasabah; // Jika potong tabungan
  final String? ruanganNasabah; // Jika potong tabungan
  final DateTime? tanggalBayar; // Waktu transaksi
  final String? namaPenyetor; // Penyetor (opsional)
  final String? kasir; // Nama ustadz / kasir
  final String? catatan; // Catatan tambahan (opsional)
  final String? kwitansiUrl; // URL PDF / Cetak Web dari backend (opsional)

  const KwitansiPembayaranScreen({
    super.key,
    required this.judulKegiatan,
    this.subJudul,
    required this.noTransaksi,
    this.status = 'Lunas',
    required this.nominalBayar,
    this.totalTagihan,
    this.sisaTagihan,
    required this.namaPembayar,
    this.noRegistrasi,
    this.kampung,
    this.rincianSubjek,
    this.metodePembayaran = 'Tunai',
    this.nomorTabungan,
    this.namaNasabah,
    this.ruanganNasabah,
    this.tanggalBayar,
    this.namaPenyetor,
    this.kasir,
    this.catatan,
    this.kwitansiUrl,
  });

  String _formatRupiah(num amount) {
    return NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp ',
      decimalDigits: 0,
    ).format(amount);
  }

  String _formatWaktu(DateTime dt) {
    final tgl = DateHelper.formatTanggalIndo(dt);
    final jam = DateFormat('HH:mm').format(dt);
    return '$tgl • $jam WIB';
  }

  void _salinKeClipboard(BuildContext context, String teks, String pesan) {
    HapticHelper.light();
    Clipboard.setData(ClipboardData(text: teks));
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(
              Icons.check_circle_outline_rounded,
              color: Colors.white,
              size: 20,
            ),
            const SizedBox(width: 10),
            Expanded(child: Text(pesan)),
          ],
        ),
        behavior: SnackBarBehavior.floating,
        duration: const Duration(seconds: 2),
      ),
    );
  }

  void _salinFormatWhatsApp(BuildContext context) {
    final tglStr = _formatWaktu(tanggalBayar ?? DateTime.now());
    final isLunas =
        status.toLowerCase() == 'lunas' ||
        (sisaTagihan != null && sisaTagihan! <= 0);

    final buffer = StringBuffer();
    buffer.writeln('━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    buffer.writeln(' *BUKTI PEMBAYARAN RESMI*');
    buffer.writeln(' *MDT HIDAYATUS SHIBYAN*');
    buffer.writeln('━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    buffer.writeln('No. Transaksi : $noTransaksi');
    buffer.writeln(
      'Status        : ${isLunas ? "LUNAS [V]" : status.toUpperCase()}',
    );
    buffer.writeln('Waktu         : $tglStr');
    buffer.writeln('━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    buffer.writeln('Kegiatan      : $judulKegiatan');
    buffer.writeln('Atas Nama     : $namaPembayar');
    if (noRegistrasi != null && noRegistrasi!.isNotEmpty) {
      buffer.writeln('No. Reg/ID    : $noRegistrasi');
    }
    if (kampung != null && kampung!.isNotEmpty) {
      buffer.writeln('Dusun/Wilayah : $kampung');
    }
    if (rincianSubjek != null && rincianSubjek!.isNotEmpty) {
      buffer.writeln('Murid        : $rincianSubjek');
    }
    buffer.writeln('Metode Bayar  : $metodePembayaran');
    if (nomorTabungan != null && nomorTabungan!.isNotEmpty) {
      buffer.writeln(
        'Rek. Tabungan : $nomorTabungan (a.n. ${namaNasabah ?? namaPembayar})',
      );
    }
    if (namaPenyetor != null &&
        namaPenyetor!.isNotEmpty &&
        namaPenyetor != namaPembayar) {
      buffer.writeln('Penyetor      : $namaPenyetor');
    }
    buffer.writeln('━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    buffer.writeln('Nominal Bayar : ${_formatRupiah(nominalBayar)}');
    if (totalTagihan != null && totalTagihan! > 0) {
      buffer.writeln('Total Tagihan : ${_formatRupiah(totalTagihan!)}');
    }
    if (sisaTagihan != null) {
      buffer.writeln(
        'Sisa Tagihan  : ${sisaTagihan! <= 0 ? "Rp 0 (LUNAS)" : _formatRupiah(sisaTagihan!)}',
      );
    }
    if (kasir != null && kasir!.isNotEmpty) {
      buffer.writeln('Petugas: $kasir');
    }
    if (catatan != null && catatan!.trim().isNotEmpty) {
      buffer.writeln('Catatan       : ${catatan!.trim()}');
    }
    buffer.writeln('━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    buffer.writeln(
      'Terima kasih. Bukti pembayaran ini sah dan diterbitkan otomatis oleh Sistem MDT Hidayatus Shibyan.',
    );

    _salinKeClipboard(
      context,
      buffer.toString(),
      'Teks bukti pembayaran berhasil disalin untuk dibagikan!',
    );
  }

  Future<void> _bukaWebCetak(BuildContext context) async {
    if (kwitansiUrl == null || kwitansiUrl!.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tautan cetak dokumen tidak tersedia')),
      );
      return;
    }
    HapticHelper.light();
    try {
      final formattedUrl =
          ApiClient.resolveWebUrl(kwitansiUrl!) ?? kwitansiUrl!.trim();
      final uri = Uri.parse(formattedUrl);
      final launched = await launchUrl(
        uri,
        mode: LaunchMode.externalApplication,
      );
      if (!launched && context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Tidak dapat membuka tautan di peramban'),
          ),
        );
      }
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal membuka dokumen cetak: $e')),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final isDark = theme.brightness == Brightness.dark;
    final primaryColor = isDark
        ? AppColors.primaryDark
        : AppColors.primaryLight;
    final isLunas =
        status.toLowerCase() == 'lunas' ||
        (sisaTagihan != null && sisaTagihan! <= 0);
    final waktuTrx = tanggalBayar ?? DateTime.now();

    // Background color lembut ala M-Banking
    final scaffoldBg = isDark
        ? const Color(0xFF0F1115)
        : const Color(0xFFF1F4F9);
    final cardBg = isDark ? const Color(0xFF1B1E26) : Colors.white;

    return Scaffold(
      backgroundColor: scaffoldBg,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          icon: Icon(
            Icons.close_rounded,
            color: isDark ? Colors.white : Colors.black87,
            size: 24,
          ),
          tooltip: 'Tutup',
          onPressed: () => Navigator.pop(context),
        ),
        title: Text(
          'Bukti Pembayaran',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
            color: isDark ? Colors.white : Colors.black87,
          ),
        ),
        centerTitle: true,
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          child: Column(
            children: [
              // 1. DIGITAL RECEIPT SLIP CARD (STRUK ALA M-BANKING)
              Container(
                width: double.infinity,
                decoration: BoxDecoration(
                  color: cardBg,
                  borderRadius: BorderRadius.circular(22),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(
                        alpha: isDark ? 0.4 : 0.07,
                      ),
                      blurRadius: 24,
                      offset: const Offset(0, 8),
                    ),
                  ],
                  border: Border.all(
                    color: isDark
                        ? Colors.white12
                        : Colors.black.withValues(alpha: 0.05),
                    width: 1,
                  ),
                ),
                child: Column(
                  children: [
                    // Header Institusi: Kop Surat Resmi MDT Hidayatus Shibyan
                    Padding(
                      padding: const EdgeInsets.fromLTRB(16, 18, 16, 14),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.center,
                        children: [
                          // Kop Surat MDT Hidayatus Shibyan
                          Container(
                            width: double.infinity,
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 6,
                            ),
                            decoration: BoxDecoration(
                              color: Colors.transparent,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Center(
                              child: ConstrainedBox(
                                constraints: const BoxConstraints(
                                  maxHeight: 80,
                                  maxWidth: 320,
                                ),
                                child: Image.asset(
                                  isDark
                                      ? 'assets/kop_dark.png'
                                      : 'assets/kop.png',
                                  fit: BoxFit.contain,
                                  errorBuilder: (_, __, ___) => Image.asset(
                                    'assets/kop.png',
                                    fit: BoxFit.contain,
                                    errorBuilder: (_, __, ___) => Image.asset(
                                      'assets/logo_mdt.png',
                                      height: 54,
                                      fit: BoxFit.contain,
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(height: 12),

                          // Garis Pemisah Kop Surat Formal
                          Row(
                            children: [
                              Expanded(
                                child: Container(
                                  height: 1,
                                  color: isDark
                                      ? Colors.white12
                                      : Colors.black.withValues(alpha: 0.08),
                                ),
                              ),
                              Padding(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                ),
                                child: Icon(
                                  Icons.verified_outlined,
                                  size: 13,
                                  color: primaryColor.withValues(alpha: 0.7),
                                ),
                              ),
                              Expanded(
                                child: Container(
                                  height: 1,
                                  color: isDark
                                      ? Colors.white12
                                      : Colors.black.withValues(alpha: 0.08),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),

                          // Badge Bukti Resmi
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 3.5,
                            ),
                            decoration: BoxDecoration(
                              color: primaryColor.withValues(
                                alpha: isDark ? 0.2 : 0.08,
                              ),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(
                                color: primaryColor.withValues(alpha: 0.25),
                                width: 1,
                              ),
                            ),
                            child: Text(
                              'BUKTI PEMBAYARAN',
                              style: TextStyle(
                                fontSize: 9.5,
                                fontWeight: FontWeight.w700,
                                letterSpacing: 0.6,
                                color: primaryColor,
                              ),
                            ),
                          ),
                          const SizedBox(height: 8),

                          // Judul & Subjudul Kegiatan
                          Text(
                            judulKegiatan,
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w800,
                              color: isDark ? Colors.white : Colors.black87,
                            ),
                          ),
                          if (subJudul != null && subJudul!.isNotEmpty) ...[
                            const SizedBox(height: 3),
                            Text(
                              subJudul!,
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                fontSize: 11.5,
                                fontWeight: FontWeight.w500,
                                color: isDark ? Colors.white60 : Colors.black54,
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),

                    Divider(
                      height: 1,
                      thickness: 1,
                      color: isDark
                          ? Colors.white10
                          : Colors.black.withValues(alpha: 0.06),
                    ),

                    // Success Icon & Status Hero
                    Padding(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 20,
                        vertical: 18,
                      ),
                      child: Column(
                        children: [
                          Container(
                            width: 60,
                            height: 60,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: const Color(
                                0xFF10B981,
                              ).withValues(alpha: 0.14),
                            ),
                            child: const Center(
                              child: Icon(
                                Icons.check_circle_rounded,
                                color: Color(0xFF10B981),
                                size: 40,
                              ),
                            ),
                          ),
                          const SizedBox(height: 10),
                          const Text(
                            'Transaksi Berhasil',
                            style: TextStyle(
                              fontSize: 17,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                          const SizedBox(height: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 14,
                              vertical: 4,
                            ),
                            decoration: BoxDecoration(
                              color: isLunas
                                  ? const Color(
                                      0xFF10B981,
                                    ).withValues(alpha: 0.15)
                                  : AppColors.amberAccent.withValues(
                                      alpha: 0.15,
                                    ),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(
                                color: isLunas
                                    ? const Color(
                                        0xFF10B981,
                                      ).withValues(alpha: 0.4)
                                    : AppColors.amberAccent.withValues(
                                        alpha: 0.4,
                                      ),
                              ),
                            ),
                            child: Text(
                              isLunas ? 'LUNAS' : status.toUpperCase(),
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w800,
                                letterSpacing: 0.5,
                                color: isLunas
                                    ? const Color(0xFF10B981)
                                    : AppColors.amberAccent,
                              ),
                            ),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            _formatWaktu(waktuTrx),
                            style: TextStyle(
                              fontSize: 11,
                              color: isDark ? Colors.white54 : Colors.black45,
                            ),
                          ),
                          const SizedBox(height: 14),
                          Text(
                            'TOTAL PEMBAYARAN',
                            style: TextStyle(
                              fontSize: 10.5,
                              fontWeight: FontWeight.w700,
                              letterSpacing: 1.2,
                              color: isDark ? Colors.white54 : Colors.black45,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            _formatRupiah(nominalBayar),
                            style: TextStyle(
                              fontSize: 27,
                              fontWeight: FontWeight.w900,
                              letterSpacing: -0.5,
                              color: isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight,
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Tiket Notch / Garis Putus-putus Pemotong Struk
                    _buildTicketDivider(isDark, scaffoldBg),

                    // Detail Informasi Transaksi
                    Padding(
                      padding: const EdgeInsets.fromLTRB(20, 16, 20, 18),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          _buildDetailRow(
                            context: context,
                            label: 'No. Transaksi',
                            value: noTransaksi,
                            isDark: isDark,
                            isCopyable: true,
                          ),
                          _buildDivider(isDark),
                          _buildDetailRow(
                            context: context,
                            label: 'Atas Nama',
                            value: namaPembayar,
                            isDark: isDark,
                            isBold: true,
                          ),
                          if (noRegistrasi != null &&
                              noRegistrasi!.isNotEmpty) ...[
                            _buildDivider(isDark),
                            _buildDetailRow(
                              context: context,
                              label: 'No. Registrasi',
                              value: noRegistrasi!,
                              isDark: isDark,
                            ),
                          ],
                          if (kampung != null && kampung!.isNotEmpty) ...[
                            _buildDivider(isDark),
                            _buildDetailRow(
                              context: context,
                              label: 'Kampung',
                              value: kampung!,
                              isDark: isDark,
                            ),
                          ],
                          if (rincianSubjek != null &&
                              rincianSubjek!.isNotEmpty) ...[
                            _buildDivider(isDark),
                            _buildDetailRow(
                              context: context,
                              label: 'Murid Terdaftar',
                              value: rincianSubjek!,
                              isDark: isDark,
                            ),
                          ],
                          _buildDivider(isDark),
                          _buildDetailRow(
                            context: context,
                            label: 'Metode Pembayaran',
                            value: metodePembayaran,
                            isDark: isDark,
                            badgeColor: _getMetodeColor(metodePembayaran),
                          ),
                          if (nomorTabungan != null &&
                              nomorTabungan!.isNotEmpty) ...[
                            _buildDivider(isDark),
                            _buildDetailRow(
                              context: context,
                              label: 'Rekening Tabungan',
                              value:
                                  '$nomorTabungan (${namaNasabah ?? namaPembayar})',
                              isDark: isDark,
                            ),
                          ],
                          if (totalTagihan != null && totalTagihan! > 0) ...[
                            _buildDivider(isDark),
                            _buildDetailRow(
                              context: context,
                              label: 'Total Tagihan Kewajiban',
                              value: _formatRupiah(totalTagihan!),
                              isDark: isDark,
                            ),
                          ],
                          if (sisaTagihan != null) ...[
                            _buildDivider(isDark),
                            _buildDetailRow(
                              context: context,
                              label: 'Sisa Tagihan',
                              value: sisaTagihan! <= 0
                                  ? 'Rp 0 (LUNAS)'
                                  : _formatRupiah(sisaTagihan!),
                              isDark: isDark,
                              valueColor: sisaTagihan! <= 0
                                  ? const Color(0xFF10B981)
                                  : AppColors.amberAccent,
                              isBold: true,
                            ),
                          ],
                          if (namaPenyetor != null &&
                              namaPenyetor!.isNotEmpty &&
                              namaPenyetor != namaPembayar) ...[
                            _buildDivider(isDark),
                            _buildDetailRow(
                              context: context,
                              label: 'Nama Penyetor',
                              value: namaPenyetor!,
                              isDark: isDark,
                            ),
                          ],
                          if (kasir != null && kasir!.isNotEmpty) ...[
                            _buildDivider(isDark),
                            _buildDetailRow(
                              context: context,
                              label: 'Petugas',
                              value: kasir!,
                              isDark: isDark,
                            ),
                          ],
                          if (catatan != null &&
                              catatan!.trim().isNotEmpty) ...[
                            _buildDivider(isDark),
                            _buildDetailRow(
                              context: context,
                              label: 'Catatan',
                              value: catatan!.trim(),
                              isDark: isDark,
                            ),
                          ],
                        ],
                      ),
                    ),

                    // Verifikasi Keaslian / Watermark Box
                    Container(
                      margin: const EdgeInsets.fromLTRB(16, 0, 16, 18),
                      padding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 10,
                      ),
                      decoration: BoxDecoration(
                        color: isDark
                            ? Colors.white.withValues(alpha: 0.04)
                            : Colors.black.withValues(alpha: 0.025),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: isDark
                              ? Colors.white10
                              : Colors.black.withValues(alpha: 0.05),
                        ),
                      ),
                      child: Row(
                        children: [
                          const Icon(
                            Icons.verified_user_rounded,
                            size: 20,
                            color: Color(0xFF10B981),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              'Dokumen ini merupakan bukti pembayaran elektronik resmi yang sah dari MDT Hidayatus Shibyan.',
                              style: TextStyle(
                                fontSize: 10.5,
                                color: isDark ? Colors.white54 : Colors.black54,
                                height: 1.3,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 20),

              // 2. TOMBOL AKSI UTAMA
              // Tombol Bagikan Bukti (WhatsApp / Salin)
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton.icon(
                  onPressed: () => _salinFormatWhatsApp(context),
                  icon: const Icon(Icons.share_rounded, size: 18),
                  label: const Text(
                    'Bagikan',
                    style: TextStyle(
                      fontSize: 13.5,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: primaryColor,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                    elevation: 0,
                  ),
                ),
              ),

              // Tombol Versi Cetak PDF (jika ada kwitansiUrl)
              if (kwitansiUrl != null && kwitansiUrl!.isNotEmpty) ...[
                const SizedBox(height: 10),
                SizedBox(
                  width: double.infinity,
                  height: 46,
                  child: OutlinedButton.icon(
                    onPressed: () => _bukaWebCetak(context),
                    icon: const Icon(Icons.print_outlined, size: 18),
                    label: const Text(
                      'Buka PDF',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: primaryColor,
                      side: BorderSide(
                        color: primaryColor.withValues(alpha: 0.5),
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                    ),
                  ),
                ),
              ],

              const SizedBox(height: 10),

              // Tombol Kembali
              SizedBox(
                width: double.infinity,
                height: 44,
                child: TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: Text(
                    'Kembali',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: isDark ? Colors.white60 : Colors.black54,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 16),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTicketDivider(bool isDark, Color cutoutColor) {
    return SizedBox(
      height: 24,
      child: Stack(
        alignment: Alignment.center,
        children: [
          // Garis Putus-putus di Tengah
          Positioned(
            left: 20,
            right: 20,
            child: LayoutBuilder(
              builder: (context, constraints) {
                final boxWidth = constraints.constrainWidth();
                const dashWidth = 5.0;
                const dashSpace = 4.0;
                final dashCount = (boxWidth / (dashWidth + dashSpace)).floor();
                return Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: List.generate(dashCount, (_) {
                    return SizedBox(
                      width: dashWidth,
                      height: 1.2,
                      child: DecoratedBox(
                        decoration: BoxDecoration(
                          color: isDark ? Colors.white24 : Colors.black12,
                        ),
                      ),
                    );
                  }),
                );
              },
            ),
          ),
          // Setengah lingkaran kiri
          Positioned(
            left: -12,
            child: Container(
              width: 24,
              height: 24,
              decoration: BoxDecoration(
                color: cutoutColor,
                shape: BoxShape.circle,
              ),
            ),
          ),
          // Setengah lingkaran kanan
          Positioned(
            right: -12,
            child: Container(
              width: 24,
              height: 24,
              decoration: BoxDecoration(
                color: cutoutColor,
                shape: BoxShape.circle,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDetailRow({
    required BuildContext context,
    required String label,
    required String value,
    required bool isDark,
    bool isBold = false,
    bool isCopyable = false,
    Color? valueColor,
    Color? badgeColor,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        SizedBox(
          width: 120,
          child: Text(
            label,
            style: TextStyle(
              fontSize: 11.5,
              color: isDark ? Colors.white54 : Colors.black45,
            ),
          ),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              Flexible(
                child: badgeColor != null
                    ? Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: badgeColor.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          value,
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                            color: badgeColor,
                          ),
                          textAlign: TextAlign.right,
                        ),
                      )
                    : Text(
                        value,
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: isBold
                              ? FontWeight.w800
                              : FontWeight.w600,
                          color:
                              valueColor ??
                              (isDark ? Colors.white : Colors.black87),
                        ),
                        textAlign: TextAlign.right,
                      ),
              ),
              if (isCopyable) ...[
                const SizedBox(width: 4),
                InkWell(
                  onTap: () => _salinKeClipboard(
                    context,
                    value,
                    '$label disalin ke papan klip!',
                  ),
                  borderRadius: BorderRadius.circular(4),
                  child: Padding(
                    padding: const EdgeInsets.all(2),
                    child: Icon(
                      Icons.copy_rounded,
                      size: 14,
                      color: isDark ? Colors.white60 : Colors.black54,
                    ),
                  ),
                ),
              ],
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildDivider(bool isDark) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Divider(
        height: 1,
        thickness: 0.6,
        color: isDark ? Colors.white10 : Colors.black.withValues(alpha: 0.04),
      ),
    );
  }

  Color _getMetodeColor(String metode) {
    final m = metode.toLowerCase();
    if (m.contains('tabung')) return AppColors.amberAccent;
    if (m.contains('transfer')) return AppColors.skyBlueAccent;
    return const Color(0xFF10B981); // Tunai / Cash
  }
}
