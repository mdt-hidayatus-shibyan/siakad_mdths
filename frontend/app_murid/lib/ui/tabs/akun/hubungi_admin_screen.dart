import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/bantuan_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';

class HubungiAdminScreen extends StatefulWidget {
  const HubungiAdminScreen({super.key});

  @override
  State<HubungiAdminScreen> createState() => _HubungiAdminScreenState();
}

class _HubungiAdminScreenState extends State<HubungiAdminScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<BantuanProvider>().fetchKontak();
    });
  }

  void _openWhatsApp(String noWa) async {
    HapticHelper.medium();
    final url = Uri.parse(
      'https://wa.me/$noWa?text=Assalamu%27alaikum%20Admin%20MDT%20Hidayatus%20Shibyan,%20saya%20Wali%20Murid%20ingin%20bertanya...',
    );
    try {
      if (await canLaunchUrl(url)) {
        await launchUrl(url, mode: LaunchMode.externalApplication);
      }
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final bantuan = context.watch<BantuanProvider>();

    return Scaffold(
      backgroundColor: isDark ? AppColors.surfaceDark : AppColors.surfaceLight,
      appBar: const CustomAppBar(
        titleText: 'Pusat Bantuan & Kontak',
        subtitleText: 'Layanan Hotline & Bantuan Madrasah',
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        physics: const BouncingScrollPhysics(),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Card WhatsApp Hotline
            GlassCard(
              padding: const EdgeInsets.all(20),
              borderRadius: 24,
              child: Column(
                children: [
                  Container(
                    width: 56,
                    height: 56,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: const Color(0xFF25D366).withValues(alpha: 0.15),
                    ),
                    child: const Icon(
                      Icons.headset_mic_rounded,
                      color: Color(0xFF25D366),
                      size: 28,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    'Layanan Bantuan & Informasi',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w900,
                      color: isDark ? Colors.white : Colors.black87,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Hubungi pengurus madrasah untuk pertanyaan administrasi, SPP, perizinan, atau kendala sistem.',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w500,
                      color: isDark ? Colors.white60 : Colors.black54,
                    ),
                  ),
                  const SizedBox(height: 16),
                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: ElevatedButton.icon(
                      onPressed: () => _openWhatsApp(bantuan.noWaClean),
                      icon: const Icon(Icons.chat_rounded, size: 18),
                      label: const Text(
                        'Hubungi via WhatsApp',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF25D366),
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                        elevation: 0,
                      ),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 16),

            // Informasi Kontak & Alamat
            GlassCard(
              padding: const EdgeInsets.all(18),
              borderRadius: 24,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'INFORMASI LEMBAGA',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w900,
                      letterSpacing: 0.8,
                      color: isDark ? Colors.white60 : Colors.black54,
                    ),
                  ),
                  const SizedBox(height: 12),
                  _buildContactItem(
                    Icons.school_rounded,
                    'MDT Hidayatus Shibyan Somorkoneng',
                    'Lembaga Pendidikan Keagamaan Islam',
                    isDark,
                  ),
                  _buildContactItem(
                    Icons.location_on_rounded,
                    'Alamat',
                    bantuan.alamat,
                    isDark,
                  ),
                  _buildContactItem(
                    Icons.email_rounded,
                    'Email Resmi',
                    bantuan.email,
                    isDark,
                  ),
                ],
              ),
            ),

            const SizedBox(height: 16),

            // Tanya Jawab Sering Ditanyakan (FAQ)
            if (bantuan.faqs.isNotEmpty) ...[
              Text(
                'PERTANYAAN SERING DIAJUKAN (FAQ)',
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w900,
                  letterSpacing: 0.8,
                  color: isDark ? Colors.white60 : Colors.black54,
                ),
              ),
              const SizedBox(height: 10),
              ...bantuan.faqs.map((faq) {
                return Padding(
                  padding: const EdgeInsets.only(bottom: 8),
                  child: GlassCard(
                    padding: const EdgeInsets.all(16),
                    borderRadius: 20,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          faq['tanya']?.toString() ?? '',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w800,
                            color: isDark ? Colors.white : Colors.black87,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          faq['jawab']?.toString() ?? '',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w500,
                            color: isDark ? Colors.white60 : Colors.black54,
                            height: 1.4,
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              }),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildContactItem(
    IconData icon,
    String title,
    String value,
    bool isDark,
  ) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(
            icon,
            size: 18,
            color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: isDark ? Colors.white54 : Colors.black54,
                  ),
                ),
                Text(
                  value,
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w800,
                    color: isDark ? Colors.white : Colors.black87,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
