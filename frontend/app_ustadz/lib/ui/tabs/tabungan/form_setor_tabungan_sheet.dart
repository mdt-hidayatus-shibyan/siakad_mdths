import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/date_helper.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../providers/tabungan_provider.dart';

class FormSetorTabunganSheet extends StatefulWidget {
  final int tabunganId;
  final String namaNasabah;
  final String nomorRekening;
  final int saldoSaatIni;
  final int? ruanganId;

  const FormSetorTabunganSheet({
    super.key,
    required this.tabunganId,
    required this.namaNasabah,
    required this.nomorRekening,
    required this.saldoSaatIni,
    this.ruanganId,
  });

  @override
  State<FormSetorTabunganSheet> createState() => _FormSetorTabunganSheetState();
}

class _FormSetorTabunganSheetState extends State<FormSetorTabunganSheet> {
  final TextEditingController _nominalController = TextEditingController();
  final TextEditingController _keteranganController = TextEditingController();
  DateTime _selectedDate = DateTime.now();

  final List<int> _presetNominals = [5000, 10000, 20000, 50000, 100000];

  @override
  void dispose() {
    _nominalController.dispose();
    _keteranganController.dispose();
    super.dispose();
  }

  void _setPreset(int nominal) {
    HapticHelper.light();
    setState(() {
      _nominalController.text = nominal.toString();
    });
  }

  Future<void> _submit() async {
    final nominalStr = _nominalController.text.trim();
    final nominal = num.tryParse(
      nominalStr.replaceAll('.', '').replaceAll(',', ''),
    );

    if (nominal == null || nominal < 1000) {
      HapticHelper.warning();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Nominal setoran minimal Rp 1.000'),
          backgroundColor: AppColors.roseDanger,
        ),
      );
      return;
    }

    HapticHelper.medium();
    final formattedDate =
        '${_selectedDate.year}-${_selectedDate.month.toString().padLeft(2, '0')}-${_selectedDate.day.toString().padLeft(2, '0')}';

    final success = await context.read<TabunganProvider>().setorTunai(
      tabunganId: widget.tabunganId,
      nominal: nominal,
      tanggal: formattedDate,
      keterangan: _keteranganController.text.trim().isNotEmpty
          ? _keteranganController.text.trim()
          : 'Setoran Tabungan via App Ustadz',
      ruanganId: widget.ruanganId,
    );

    if (mounted) {
      if (success) {
        HapticHelper.confirmSuccess();
        Navigator.pop(context, true);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Alhamdulillah! Setoran Rp ${DateHelper.formatRupiah(nominal.toInt())} berhasil dicatat.',
            ),
            backgroundColor: const Color(0xFF10B981),
          ),
        );
      } else {
        final err = context.read<TabunganProvider>().errorMessage;
        HapticHelper.warning();
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(err ?? 'Gagal mencatat setoran'),
            backgroundColor: AppColors.roseDanger,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final provider = context.watch<TabunganProvider>();

    return Container(
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF161E16) : Colors.white,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
      ),
      padding: EdgeInsets.fromLTRB(
        20,
        14,
        20,
        MediaQuery.of(context).viewInsets.bottom + 24,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Drag handle
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
          const SizedBox(height: 16),

          // Header Info
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: isDark
                      ? const Color(0xFF0F2313)
                      : AppColors.primaryContainerLight,
                  shape: BoxShape.circle,
                ),
                child: Icon(
                  Icons.account_balance_wallet_rounded,
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
                      'Setor Tabungan Tunai',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    Text(
                      '${widget.namaNasabah} • No: ${widget.nomorRekening}',
                      style: TextStyle(
                        fontSize: 12,
                        color: isDark ? Colors.white60 : Colors.black54,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),

          // Card Saldo Saat Ini
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF1F291F) : const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'Saldo Terakhir:',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: isDark ? Colors.white70 : Colors.black87,
                  ),
                ),
                Text(
                  'Rp ${DateHelper.formatRupiah(widget.saldoSaatIni)}',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w900,
                    color: isDark
                        ? AppColors.primaryDark
                        : AppColors.primaryLight,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // Input Nominal
          const Text(
            'Nominal Setoran (Rp)',
            style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 6),
          TextField(
            controller: _nominalController,
            keyboardType: TextInputType.number,
            autofocus: true,
            style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900),
            decoration: InputDecoration(
              hintText: 'Contoh: 20000',
              prefixText: 'Rp ',
              prefixStyle: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: isDark ? Colors.white70 : Colors.black87,
              ),
              filled: true,
              fillColor: isDark
                  ? const Color(0xFF1C241C)
                  : const Color(0xFFF8FAFC),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(16),
                borderSide: BorderSide(
                  color: isDark
                      ? AppColors.outlineDark
                      : AppColors.outlineLight,
                ),
              ),
            ),
          ),
          const SizedBox(height: 10),

          // Preset Chips
          Wrap(
            spacing: 6,
            runSpacing: 6,
            children: _presetNominals.map((nom) {
              return InkWell(
                onTap: () => _setPreset(nom),
                borderRadius: BorderRadius.circular(12),
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: isDark
                        ? const Color(0xFF263326)
                        : const Color(0xFFE2E8F0),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    '+${DateHelper.formatRupiah(nom)}',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: isDark ? Colors.white : Colors.black87,
                    ),
                  ),
                ),
              );
            }).toList(),
          ),
          const SizedBox(height: 14),

          // Tanggal & Keterangan
          Row(
            children: [
              Expanded(
                child: InkWell(
                  onTap: () async {
                    final picked = await showDatePicker(
                      context: context,
                      initialDate: _selectedDate,
                      firstDate: DateTime(2020),
                      lastDate: DateTime.now().add(const Duration(days: 1)),
                    );
                    if (picked != null) {
                      setState(() {
                        _selectedDate = picked;
                      });
                    }
                  },
                  borderRadius: BorderRadius.circular(14),
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 10,
                    ),
                    decoration: BoxDecoration(
                      color: isDark
                          ? const Color(0xFF1C241C)
                          : const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color: isDark
                            ? AppColors.outlineDark
                            : AppColors.outlineLight,
                      ),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          Icons.calendar_today_rounded,
                          size: 16,
                          color: AppColors.primaryLight,
                        ),
                        const SizedBox(width: 8),
                        Text(
                          '${_selectedDate.day}/${_selectedDate.month}/${_selectedDate.year}',
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),

          // Keterangan / Catatan
          TextField(
            controller: _keteranganController,
            decoration: InputDecoration(
              hintText: 'Keterangan (opsional, contoh: Setoran harian)',
              filled: true,
              fillColor: isDark
                  ? const Color(0xFF1C241C)
                  : const Color(0xFFF8FAFC),
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 14,
                vertical: 12,
              ),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(14),
                borderSide: BorderSide(
                  color: isDark
                      ? AppColors.outlineDark
                      : AppColors.outlineLight,
                ),
              ),
            ),
          ),
          const SizedBox(height: 18),

          // Submit Button
          SizedBox(
            width: double.infinity,
            height: 50,
            child: ElevatedButton(
              onPressed: provider.isSubmitting ? null : _submit,
              style: ElevatedButton.styleFrom(
                backgroundColor: isDark
                    ? AppColors.primaryDark
                    : AppColors.primaryLight,
                foregroundColor: isDark
                    ? AppColors.onPrimaryDark
                    : AppColors.onPrimaryLight,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                elevation: 0,
              ),
              child: provider.isSubmitting
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                      ),
                    )
                  : const Text(
                      'Simpan Setoran Tunai',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
            ),
          ),
        ],
      ),
    );
  }
}
