import 'package:flutter/material.dart';
import '../../../core/network/api_client.dart';
import '../../../core/theme/app_colors.dart';
import '../../../data/models/anak_model.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';

class BiodataAnakScreen extends StatelessWidget {
  final AnakModel anak;

  const BiodataAnakScreen({super.key, required this.anak});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final fotoUrl = ApiClient.resolveImageUrl(anak.foto);

    return Scaffold(
      backgroundColor: isDark ? AppColors.surfaceDark : AppColors.surfaceLight,
      appBar: const CustomAppBar(
        titleText: 'Biodata Murid',
        subtitleText: 'Identitas & Informasi Lengkap',
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        physics: const BouncingScrollPhysics(),
        child: Column(
          children: [
            // Profile Card Top
            GlassCard(
              padding: const EdgeInsets.all(20),
              borderRadius: 24,
              child: Column(
                children: [
                  Container(
                    width: 76,
                    height: 76,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: isDark
                          ? AppColors.primaryDark.withValues(alpha: 0.2)
                          : AppColors.primaryLight.withValues(alpha: 0.1),
                      border: Border.all(
                        color: isDark
                            ? AppColors.primaryDark
                            : AppColors.primaryLight,
                        width: 2.5,
                      ),
                      image: fotoUrl != null
                          ? DecorationImage(
                              image: NetworkImage(fotoUrl),
                              fit: BoxFit.cover,
                            )
                          : null,
                    ),
                    child: fotoUrl == null
                        ? Center(
                            child: Text(
                              anak.namaLengkap.isNotEmpty
                                  ? anak.namaLengkap
                                        .substring(0, 1)
                                        .toUpperCase()
                                  : 'M',
                              style: TextStyle(
                                fontSize: 28,
                                fontWeight: FontWeight.w900,
                                color: isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight,
                              ),
                            ),
                          )
                        : null,
                  ),
                  const SizedBox(height: 12),
                  Text(
                    anak.namaLengkap,
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w900,
                      letterSpacing: -0.3,
                      color: isDark ? Colors.white : Colors.black87,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'NISM: ${anak.nism} • Ruangan: ${anak.ruangan ?? "-"}',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: isDark
                          ? AppColors.primaryDark
                          : AppColors.primaryLight,
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 16),

            // Identitas Lengkap
            GlassCard(
              padding: const EdgeInsets.all(18),
              borderRadius: 24,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'INFORMASI IDENTITAS MURID',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w900,
                      letterSpacing: 0.8,
                      color: isDark ? Colors.white60 : Colors.black54,
                    ),
                  ),
                  const SizedBox(height: 14),
                  _buildDataRow('Nama Lengkap', anak.namaLengkap, isDark),
                  _buildDataRow(
                    'Nama Panggilan',
                    anak.namaPanggilan ?? '-',
                    isDark,
                  ),
                  _buildDataRow('NISM', anak.nism, isDark),
                  _buildDataRow('NISN', anak.nisn ?? '-', isDark),
                  _buildDataRow('NIK', anak.nik ?? '-', isDark),
                  _buildDataRow(
                    'Jenis Kelamin',
                    anak.jenisKelamin == 'L' ? 'Laki-laki' : 'Perempuan',
                    isDark,
                  ),
                  _buildDataRow(
                    'Tempat / Tgl Lahir',
                    '${anak.tempatLahir ?? "-"}, ${anak.tanggalLahir ?? "-"}',
                    isDark,
                  ),
                  _buildDataRow('Nama Ayah', anak.namaAyah ?? '-', isDark),
                  _buildDataRow('Nama Ibu', anak.namaIbu ?? '-', isDark),
                  _buildDataRow(
                    'Kampung / Domisili',
                    anak.kampung ?? '-',
                    isDark,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDataRow(String label, String value, bool isDark) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 130,
            child: Text(
              label,
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: isDark ? Colors.white54 : Colors.black54,
              ),
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              value,
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w800,
                color: isDark ? Colors.white : Colors.black87,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
