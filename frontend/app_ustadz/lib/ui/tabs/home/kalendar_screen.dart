import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/utils/haptic_helper.dart';
import '../../../core/utils/hijri_calendar_helper.dart';
import '../../../data/models/akademik_model.dart';
import '../../../providers/akademik_provider.dart';
import '../../widgets/custom_app_bar.dart';
import '../../widgets/glass_card.dart';
import '../../widgets/shimmer_loading.dart';

enum CalendarMode { hijri, gregorian }

class KalendarScreen extends StatefulWidget {
  const KalendarScreen({super.key});

  @override
  State<KalendarScreen> createState() => _KalendarScreenState();
}

class _KalendarScreenState extends State<KalendarScreen> {
  CalendarMode _mode =
      CalendarMode.hijri; // Default Hijriyah as in web index.blade.php

  // Gregorian view date
  DateTime _viewGDate = DateTime(DateTime.now().year, DateTime.now().month, 1);

  // Hijri view (year & month index 1..12)
  int _viewHYear = 1448;
  int _viewHMonthIndex = 3; // Default Rabiul Awal (August/September 2026)

  // Selected date
  DateTime _selectedDate = DateTime.now();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      final akademik = context.read<AkademikProvider>();
      await akademik.fetchKalendar();
      _syncViewWithDate(_selectedDate);
    });
  }

  void _syncViewWithDate(DateTime targetDate) {
    final akademik = context.read<AkademikProvider>();
    final bulanList = akademik.kalendarData?.bulanHijriyah ?? [];
    final h = HijriCalendarHelper.getHijriDate(targetDate, bulanList);
    setState(() {
      _viewHYear = h.year;
      _viewHMonthIndex = h.monthIndex;
      _viewGDate = DateTime(targetDate.year, targetDate.month, 1);
    });
  }

  void _switchMode(CalendarMode newMode) {
    if (_mode == newMode) return;
    HapticHelper.medium();
    setState(() {
      _mode = newMode;
      _syncViewWithDate(_selectedDate);
    });
  }

  void _shiftMonth(int delta) {
    HapticHelper.light();
    setState(() {
      if (_mode == CalendarMode.gregorian) {
        _viewGDate = DateTime(_viewGDate.year, _viewGDate.month + delta, 1);
      } else {
        int nextMonth = _viewHMonthIndex + delta;
        if (nextMonth > 12) {
          _viewHMonthIndex = 1;
          _viewHYear += 1;
        } else if (nextMonth < 1) {
          _viewHMonthIndex = 12;
          _viewHYear -= 1;
        } else {
          _viewHMonthIndex = nextMonth;
        }
      }
    });
  }

  void _jumpToToday() {
    HapticHelper.medium();
    final now = DateTime.now();
    setState(() {
      _selectedDate = now;
      _syncViewWithDate(now);
    });
  }

  Color _parseHexColor(String hex, bool isDark) {
    try {
      final clean = hex.replaceAll('#', '');
      return Color(int.parse('FF$clean', radix: 16));
    } catch (_) {
      return isDark ? AppColors.primaryDark : AppColors.primaryLight;
    }
  }

  List<KalendarEvent> _getEventsForDate(
    DateTime date,
    List<KalendarEvent> allEvents,
  ) {
    final dateStr =
        '${date.year.toString().padLeft(4, '0')}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';

    return allEvents.where((ev) {
      final start = ev.start.split(' ').first;
      final end = ev.end.split(' ').first;
      return (dateStr.compareTo(start) >= 0 && dateStr.compareTo(end) <= 0);
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final akademik = context.watch<AkademikProvider>();
    final kalendarData = akademik.kalendarData;
    final allEvents = kalendarData?.events ?? [];
    final bulanHijriyahList = kalendarData?.bulanHijriyah ?? [];

    final selectedHijri = HijriCalendarHelper.getHijriDate(
      _selectedDate,
      bulanHijriyahList,
    );
    final selectedPasaran = HijriCalendarHelper.getPasaran(_selectedDate);
    final selectedEvents = _getEventsForDate(_selectedDate, allEvents);

    return Scaffold(
      appBar: CustomAppBar(
        titleText: 'Kalender Pendidikan',
        actions: [
          CircularIconButton(
            tooltip: 'Hari Ini',
            icon: Icons.today_rounded,
            iconSize: 20,
            iconColor: isDark ? AppColors.primaryDark : AppColors.primaryLight,
            onPressed: _jumpToToday,
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () => akademik.fetchKalendar(),
        color: isDark ? AppColors.primaryDark : AppColors.primaryLight,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(14, 10, 14, 40),
          children: [
            // 1. TOP CARD: MONTH HEADER & MODE SWITCHER (Responsive & Compact)
            GlassCard(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
              child: Row(
                children: [
                  // Left: Month Navigation & Title (Expanded to flex)
                  Expanded(
                    child: Row(
                      children: [
                        IconButton(
                          visualDensity: VisualDensity.compact,
                          padding: EdgeInsets.zero,
                          constraints: const BoxConstraints(
                            minWidth: 26,
                            minHeight: 26,
                          ),
                          icon: const Icon(
                            Icons.chevron_left_rounded,
                            size: 22,
                          ),
                          onPressed: () => _shiftMonth(-1),
                        ),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              if (_mode == CalendarMode.hijri) ...[
                                Text(
                                  '${HijriCalendarHelper.hijriMonthsMeta[_viewHMonthIndex - 1].ar} ${HijriCalendarHelper.toArabicDigits(_viewHYear)} هـ',
                                  style: GoogleFonts.amiri(
                                    fontSize: 17,
                                    fontWeight: FontWeight.bold,
                                    letterSpacing: -0.3,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 1),
                                Text(
                                  _getHijriMonthDateRangeLabel(
                                    _viewHYear,
                                    _viewHMonthIndex,
                                    bulanHijriyahList,
                                  ),
                                  style: TextStyle(
                                    fontSize: 9.5,
                                    fontWeight: FontWeight.w700,
                                    letterSpacing: 0.3,
                                    color: isDark
                                        ? const Color(0xFF94A3B8)
                                        : const Color(0xFF64748B),
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ] else ...[
                                Text(
                                  '${HijriCalendarHelper.bulanMasehiNames[_viewGDate.month]} ${_viewGDate.year}',
                                  style: const TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 1),
                                Text(
                                  _getMonthHijriRangeSubtitle(
                                    _viewGDate,
                                    bulanHijriyahList,
                                  ),
                                  style: TextStyle(
                                    fontSize: 9.5,
                                    fontWeight: FontWeight.w700,
                                    letterSpacing: 0.3,
                                    color: isDark
                                        ? const Color(0xFF94A3B8)
                                        : const Color(0xFF64748B),
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ],
                            ],
                          ),
                        ),
                        const SizedBox(width: 4),
                        IconButton(
                          visualDensity: VisualDensity.compact,
                          padding: EdgeInsets.zero,
                          constraints: const BoxConstraints(
                            minWidth: 26,
                            minHeight: 26,
                          ),
                          icon: const Icon(
                            Icons.chevron_right_rounded,
                            size: 22,
                          ),
                          onPressed: () => _shiftMonth(1),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 6),

                  // Right: Mode Switcher Toggle [ Masehi | Hijriyah ]
                  Container(
                    padding: const EdgeInsets.all(2.5),
                    decoration: BoxDecoration(
                      color: isDark
                          ? const Color(0xFF1E293B)
                          : const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(
                        color: isDark
                            ? const Color(0xFF334155)
                            : const Color(0xFFE2E8F0),
                        width: 0.8,
                      ),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        _buildModeButton(
                          title: 'Masehi',
                          isActive: _mode == CalendarMode.gregorian,
                          isDark: isDark,
                          onTap: () => _switchMode(CalendarMode.gregorian),
                        ),
                        _buildModeButton(
                          title: 'Hijriyah',
                          isActive: _mode == CalendarMode.hijri,
                          isDark: isDark,
                          onTap: () => _switchMode(CalendarMode.hijri),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),

            // 2. CALENDAR GRID CARD (Ahd, Sen, Sel, Rab, Kam, Jum (RED), Sab)
            GlassCard(
              padding: const EdgeInsets.fromLTRB(8, 12, 8, 12),
              child: Column(
                children: [
                  // Weekdays Header (Ahd, Sen, Sel, Rab, Kam, Jum, Sab)
                  Row(
                    children: [
                      _buildDayHeader('Ahd', isDark),
                      _buildDayHeader('Sen', isDark),
                      _buildDayHeader('Sel', isDark),
                      _buildDayHeader('Rab', isDark),
                      _buildDayHeader('Kam', isDark),
                      _buildDayHeader(
                        'Jum',
                        isDark,
                        isJumatRed: true,
                      ), // JUMAT MERAH
                      _buildDayHeader('Sab', isDark),
                    ],
                  ),
                  const Divider(height: 14, thickness: 0.5),

                  // Matrix Days Grid
                  if (akademik.isLoadingKalendar)
                    const ShimmerLoadingList(count: 2)
                  else if (_mode == CalendarMode.hijri)
                    _buildHijriModeMatrix(
                      _viewHYear,
                      _viewHMonthIndex,
                      _selectedDate,
                      allEvents,
                      bulanHijriyahList,
                      isDark,
                    )
                  else
                    _buildGregorianModeMatrix(
                      _viewGDate,
                      _selectedDate,
                      allEvents,
                      bulanHijriyahList,
                      isDark,
                    ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // 3. DETAIL HARI INI CARD (Matching kalendar/index.blade.php)
            GlassCard(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Box Tanggal & Pasaran
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color:
                          (isDark
                                  ? AppColors.primaryDark
                                  : AppColors.primaryLight)
                              .withValues(alpha: isDark ? 0.15 : 0.12),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color:
                            (isDark
                                    ? AppColors.primaryDark
                                    : AppColors.primaryLight)
                                .withValues(alpha: isDark ? 0.35 : 0.25),
                      ),
                    ),
                    child: Column(
                      children: [
                        Text(
                          '${_getDayNameIndo(_selectedDate.weekday).toUpperCase()} ${selectedPasaran.toUpperCase()}',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 1.1,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                        ),
                        const SizedBox(height: 3),
                        Text(
                          '${_selectedDate.day} ${HijriCalendarHelper.bulanMasehiNames[_selectedDate.month]} ${_selectedDate.year}',
                          style: const TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.w900,
                            letterSpacing: -0.5,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          selectedHijri.fullArabicText,
                          style: GoogleFonts.amiri(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: isDark
                                ? AppColors.primaryDark
                                : AppColors.primaryLight,
                          ),
                        ),
                        Text(
                          selectedHijri.fullText,
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: isDark
                                ? const Color(0xFF8D9387)
                                : const Color(0xFF73796E),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 14),

                  // Header Agenda Hari Ini
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text(
                        'AGENDA PADA TANGGAL INI',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w900,
                          letterSpacing: 0.8,
                        ),
                      ),
                      if (_selectedDate.weekday == DateTime.friday)
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 6,
                            vertical: 2,
                          ),
                          decoration: BoxDecoration(
                            color: isDark
                                ? const Color(0xFF380C14)
                                : const Color(0xFFFFE4E6),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: const Text(
                            'Libur Jum\'at',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                              color: AppColors.roseDanger,
                            ),
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 8),

                  if (selectedEvents.isEmpty)
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      child: Center(
                        child: Text(
                          _selectedDate.weekday == DateTime.friday
                              ? 'Libur rutin mingguan madrasah (Hari Jum\'at).'
                              : 'Kosong. Tidak ada agenda khusus.',
                          style: TextStyle(
                            fontSize: 12,
                            fontStyle: FontStyle.italic,
                            color: isDark
                                ? const Color(0xFF8D9387)
                                : const Color(0xFF73796E),
                          ),
                        ),
                      ),
                    )
                  else
                    ...selectedEvents.map((ev) {
                      final evColor = _parseHexColor(ev.hexColor, isDark);
                      return Container(
                        margin: const EdgeInsets.only(bottom: 8),
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: evColor.withValues(alpha: isDark ? 0.15 : 0.1),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: evColor.withValues(alpha: 0.35),
                          ),
                        ),
                        child: Row(
                          children: [
                            Container(
                              width: 32,
                              height: 32,
                              decoration: BoxDecoration(
                                color: evColor.withValues(alpha: 0.25),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Icon(
                                ev.tipe == 'libur'
                                    ? Icons.event_busy_rounded
                                    : (ev.tipe == 'ujian'
                                          ? Icons.edit_calendar_rounded
                                          : Icons.event_available_rounded),
                                size: 16,
                                color: evColor,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    ev.title,
                                    style: const TextStyle(
                                      fontSize: 13,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    '${ev.kategori.toUpperCase()} • ${_formatEventRange(ev.start, ev.end)}',
                                    style: TextStyle(
                                      fontSize: 10,
                                      fontWeight: FontWeight.w700,
                                      color: evColor,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      );
                    }),
                ],
              ),
            ),
            const SizedBox(height: 18),

            // 4. CARD DAFTAR AGENDA 1 TAHUN & KATEGORI
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Daftar Agenda (1 Tahun)',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
                ),
                Text(
                  '${allEvents.length} Agenda',
                  style: TextStyle(
                    fontSize: 12,
                    color: isDark
                        ? const Color(0xFF8D9387)
                        : const Color(0xFF73796E),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),

            if (allEvents.isEmpty)
              const GlassCard(
                padding: EdgeInsets.all(24),
                child: Center(
                  child: Text('Belum ada agenda pendidikan yang terdaftar.'),
                ),
              )
            else
              ...allEvents.map((agenda) {
                final hex = _parseHexColor(agenda.hexColor, isDark);
                return GlassCard(
                  margin: const EdgeInsets.only(bottom: 8),
                  padding: const EdgeInsets.all(12),
                  child: Row(
                    children: [
                      // Vertical solid indicator bar
                      Container(
                        width: 4,
                        height: 36,
                        decoration: BoxDecoration(
                          color: hex,
                          borderRadius: BorderRadius.circular(4),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              agenda.title,
                              style: const TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 6,
                                    vertical: 1.5,
                                  ),
                                  decoration: BoxDecoration(
                                    color: hex.withValues(
                                      alpha: isDark ? 0.2 : 0.1,
                                    ),
                                    borderRadius: BorderRadius.circular(6),
                                    border: Border.all(
                                      color: hex.withValues(alpha: 0.3),
                                    ),
                                  ),
                                  child: Text(
                                    agenda.kategori.toUpperCase(),
                                    style: TextStyle(
                                      fontSize: 8,
                                      fontWeight: FontWeight.bold,
                                      color: hex,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Icon(
                                  Icons.calendar_today_rounded,
                                  size: 11,
                                  color: isDark
                                      ? const Color(0xFF8D9387)
                                      : const Color(0xFF73796E),
                                ),
                                const SizedBox(width: 4),
                                Expanded(
                                  child: Text(
                                    _formatEventRange(agenda.start, agenda.end),
                                    style: TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600,
                                      color: isDark
                                          ? const Color(0xFF8D9387)
                                          : const Color(0xFF73796E),
                                    ),
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                );
              }),
          ],
        ),
      ),
    );
  }

  String _formatEventRange(String startRaw, String endRaw) {
    try {
      final sClean = startRaw.split('T').first.split(' ').first;
      final eClean = endRaw.split('T').first.split(' ').first;

      final s = DateTime.tryParse(sClean);
      final e = DateTime.tryParse(eClean);

      if (s == null) return startRaw;
      if (e == null || sClean == eClean) {
        return '${s.day} ${HijriCalendarHelper.bulanMasehiShort[s.month]} ${s.year}';
      }

      if (s.year == e.year) {
        if (s.month == e.month) {
          return '${s.day} - ${e.day} ${HijriCalendarHelper.bulanMasehiShort[s.month]} ${s.year}';
        }
        return '${s.day} ${HijriCalendarHelper.bulanMasehiShort[s.month]} - ${e.day} ${HijriCalendarHelper.bulanMasehiShort[e.month]} ${s.year}';
      }

      return '${s.day} ${HijriCalendarHelper.bulanMasehiShort[s.month]} ${s.year} - ${e.day} ${HijriCalendarHelper.bulanMasehiShort[e.month]} ${e.year}';
    } catch (_) {
      return startRaw.split('T').first;
    }
  }

  Widget _buildModeButton({
    required String title,
    required bool isActive,
    required bool isDark,
    required VoidCallback onTap,
  }) {
    final primary = isDark ? AppColors.primaryDark : AppColors.primaryLight;
    final onPrimary = isDark ? AppColors.onPrimaryDark : Colors.white;

    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 5),
        decoration: BoxDecoration(
          color: isActive ? primary : Colors.transparent,
          borderRadius: BorderRadius.circular(8),
        ),
        child: Text(
          title,
          style: TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.bold,
            color: isActive
                ? onPrimary
                : (isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B)),
          ),
        ),
      ),
    );
  }

  Widget _buildDayHeader(String label, bool isDark, {bool isJumatRed = false}) {
    final col = isJumatRed
        ? AppColors.roseDanger
        : (isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B));

    return Expanded(
      child: Center(
        child: Text(
          label,
          style: TextStyle(
            fontSize: 10,
            fontWeight: FontWeight.w900,
            letterSpacing: 0.5,
            color: col,
          ),
        ),
      ),
    );
  }

  // === 1. HIJRIYAH MODE MATRIX BUILDER ===
  Widget _buildHijriModeMatrix(
    int hYear,
    int hMonthIdx,
    DateTime selectedDate,
    List<KalendarEvent> allEvents,
    List<BulanHijriyahItem> bulanHijriyahList,
    bool isDark,
  ) {
    BulanHijriyahItem? matchedBulan;
    for (final b in bulanHijriyahList) {
      final mMeta = HijriCalendarHelper.getMonthMetaByName(b.namaBulan);
      final bYear = int.tryParse(b.tahunHijriyah) ?? 1448;
      if (mMeta.index == hMonthIdx &&
          (bYear == hYear || b.tahunHijriyah.contains(hYear.toString()))) {
        matchedBulan = b;
        break;
      }
    }

    DateTime startDate;
    int totalDays = 30;

    if (matchedBulan != null && matchedBulan.tanggalMulai.isNotEmpty) {
      startDate =
          DateTime.tryParse(matchedBulan.tanggalMulai) ??
          DateTime(DateTime.now().year, DateTime.now().month, 1);
      final endDate = DateTime.tryParse(matchedBulan.tanggalSelesai);
      if (endDate != null) {
        totalDays = endDate.difference(startDate).inDays + 1;
      }
    } else {
      startDate = DateTime(2026, 6 + hMonthIdx, 1);
    }

    final startWeekday = startDate.weekday % 7; // 0 = Sunday, 1 = Monday...
    final today = DateTime(
      DateTime.now().year,
      DateTime.now().month,
      DateTime.now().day,
    );

    List<Widget> rows = [];
    List<Widget> currentCells = [];

    // Empty blank slots before start
    for (int i = 0; i < startWeekday; i++) {
      currentCells.add(const Expanded(child: SizedBox()));
    }

    // Days in Hijri month (1 to totalDays)
    for (int hDay = 1; hDay <= totalDays; hDay++) {
      final gDate = startDate.add(Duration(days: hDay - 1));
      currentCells.add(
        _buildCell(
          gDate: gDate,
          mainNumber: HijriCalendarHelper.toArabicDigits(hDay),
          subNumber: '${gDate.day}',
          isArabicMain: true,
          selectedDate: selectedDate,
          today: today,
          allEvents: allEvents,
          isDark: isDark,
        ),
      );

      if (currentCells.length == 7) {
        rows.add(Row(children: currentCells));
        currentCells = [];
      }
    }

    // Trailing blanks
    if (currentCells.isNotEmpty) {
      while (currentCells.length < 7) {
        currentCells.add(const Expanded(child: SizedBox()));
      }
      rows.add(Row(children: currentCells));
    }

    return Column(children: rows);
  }

  // === 2. GREGORIAN MODE MATRIX BUILDER ===
  Widget _buildGregorianModeMatrix(
    DateTime gMonth,
    DateTime selectedDate,
    List<KalendarEvent> allEvents,
    List<BulanHijriyahItem> bulanHijriyahList,
    bool isDark,
  ) {
    final firstDay = DateTime(gMonth.year, gMonth.month, 1);
    final daysInMonth = DateTime(gMonth.year, gMonth.month + 1, 0).day;
    final startWeekday = firstDay.weekday % 7; // 0 = Sunday, 1 = Monday...
    final today = DateTime(
      DateTime.now().year,
      DateTime.now().month,
      DateTime.now().day,
    );

    List<Widget> rows = [];
    List<Widget> currentCells = [];

    // Empty blanks before start
    for (int i = 0; i < startWeekday; i++) {
      currentCells.add(const Expanded(child: SizedBox()));
    }

    for (int d = 1; d <= daysInMonth; d++) {
      final gDate = DateTime(gMonth.year, gMonth.month, d);
      final hijri = HijriCalendarHelper.getHijriDate(gDate, bulanHijriyahList);

      currentCells.add(
        _buildCell(
          gDate: gDate,
          mainNumber: '$d',
          subNumber: '${hijri.day}',
          isArabicMain: false,
          selectedDate: selectedDate,
          today: today,
          allEvents: allEvents,
          isDark: isDark,
        ),
      );

      if (currentCells.length == 7) {
        rows.add(Row(children: currentCells));
        currentCells = [];
      }
    }

    if (currentCells.isNotEmpty) {
      while (currentCells.length < 7) {
        currentCells.add(const Expanded(child: SizedBox()));
      }
      rows.add(Row(children: currentCells));
    }

    return Column(children: rows);
  }

  // === CALENDAR CELL BUILDER (Matching Web index.blade.php exactly) ===
  Widget _buildCell({
    required DateTime gDate,
    required String mainNumber,
    required String subNumber,
    required bool isArabicMain,
    required DateTime selectedDate,
    required DateTime today,
    required List<KalendarEvent> allEvents,
    required bool isDark,
  }) {
    final isSelected =
        (gDate.year == selectedDate.year &&
        gDate.month == selectedDate.month &&
        gDate.day == selectedDate.day);
    final isToday =
        (gDate.year == today.year &&
        gDate.month == today.month &&
        gDate.day == today.day);

    final events = _getEventsForDate(gDate, allEvents);
    final isFriday = (gDate.weekday == DateTime.friday);
    final hasHoliday = isFriday || events.any((e) => e.tipe == 'libur');
    // Dynamic theme primary color
    final primary = isDark ? AppColors.primaryDark : AppColors.primaryLight;

    Color bgColor;
    Color textColor;
    Border? border;

    if (isToday) {
      bgColor = primary.withValues(alpha: isDark ? 0.20 : 0.12);
      textColor = primary;
      border = Border.all(color: primary, width: 1.3);
    } else if (isSelected) {
      bgColor = isDark ? const Color(0xFFE2E8F0) : const Color(0xFF1E293B);
      textColor = isDark ? const Color(0xFF0F172A) : Colors.white;
      border = Border.all(color: primary, width: 1.3);
    } else {
      // Default cell
      bgColor = isDark ? const Color(0xFF111827) : Colors.white;
      textColor = hasHoliday
          ? AppColors.roseDanger
          : (isDark ? const Color(0xFFE2E8F0) : const Color(0xFF334155));
      border = Border.all(
        color: isDark ? const Color(0xFF1F2937) : const Color(0xFFF1F5F9),
        width: 0.8,
      );
    }

    // Extract Event Dots Markers
    List<Color> markerColors = [];
    if (events.isNotEmpty) {
      for (final ev in events) {
        if (ev.tipe == 'libur') {
          if (!markerColors.contains(const Color(0xFFE11D48))) {
            markerColors.add(const Color(0xFFE11D48)); // Merah Libur
          }
        } else {
          final col = _parseHexColor(ev.hexColor, isDark);
          if (!markerColors.contains(col)) {
            markerColors.add(col);
          }
        }
      }
    } else if (isFriday && !isSelected && !isToday) {
      markerColors.add(const Color(0xFFE11D48)); // Merah Jumat
    }

    return Expanded(
      child: GestureDetector(
        onTap: () {
          HapticHelper.light();
          setState(() {
            _selectedDate = gDate;
          });
        },
        child: AspectRatio(
          aspectRatio: 1.0,
          child: Container(
            margin: const EdgeInsets.all(2.0),
            decoration: BoxDecoration(
              color: bgColor,
              borderRadius: BorderRadius.circular(10),
              border: border,
            ),
            child: Stack(
              children: [
                // Top-right Sub Number
                Positioned(
                  top: 2.5,
                  right: 4,
                  child: Text(
                    subNumber,
                    style: TextStyle(
                      fontSize: 8,
                      fontWeight: FontWeight.bold,
                      color: isSelected
                          ? (isDark ? Colors.black54 : Colors.white70)
                          : (isDark
                                ? const Color(0xFF64748B)
                                : const Color(0xFF94A3B8)),
                    ),
                  ),
                ),

                // Center Main Number
                Center(
                  child: Text(
                    mainNumber,
                    style: isArabicMain
                        ? GoogleFonts.amiri(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: textColor,
                          )
                        : TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w900,
                            color: textColor,
                          ),
                  ),
                ),

                // Bottom Dots Markers
                if (markerColors.isNotEmpty)
                  Positioned(
                    bottom: 3.5,
                    left: 0,
                    right: 0,
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: markerColors.take(3).map((c) {
                        return Container(
                          width: 4,
                          height: 4,
                          margin: const EdgeInsets.symmetric(horizontal: 1),
                          decoration: BoxDecoration(
                            color: c,
                            shape: BoxShape.circle,
                          ),
                        );
                      }).toList(),
                    ),
                  ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  String _getHijriMonthDateRangeLabel(
    int hYear,
    int hMonthIdx,
    List<BulanHijriyahItem> bulanHijriyahList,
  ) {
    BulanHijriyahItem? matchedBulan;
    for (final b in bulanHijriyahList) {
      final mMeta = HijriCalendarHelper.getMonthMetaByName(b.namaBulan);
      final bYear = int.tryParse(b.tahunHijriyah) ?? 1448;
      if (mMeta.index == hMonthIdx &&
          (bYear == hYear || b.tahunHijriyah.contains(hYear.toString()))) {
        matchedBulan = b;
        break;
      }
    }

    final meta = HijriCalendarHelper.hijriMonthsMeta[hMonthIdx - 1];

    if (matchedBulan != null && matchedBulan.tanggalMulai.isNotEmpty) {
      final start = DateTime.tryParse(matchedBulan.tanggalMulai);
      final end = DateTime.tryParse(matchedBulan.tanggalSelesai);
      if (start != null && end != null) {
        return '${meta.id.toUpperCase()} $hYear H · ${start.day} ${HijriCalendarHelper.bulanMasehiShort[start.month].toUpperCase()} - ${end.day} ${HijriCalendarHelper.bulanMasehiShort[end.month].toUpperCase()} ${end.year}';
      }
    }

    return '${meta.id.toUpperCase()} $hYear H';
  }

  String _getMonthHijriRangeSubtitle(
    DateTime month,
    List<BulanHijriyahItem> bulanHijriyahList,
  ) {
    final start = DateTime(month.year, month.month, 1);
    final end = DateTime(month.year, month.month + 1, 0);

    final startHijri = HijriCalendarHelper.getHijriDate(
      start,
      bulanHijriyahList,
    );
    final endHijri = HijriCalendarHelper.getHijriDate(end, bulanHijriyahList);

    if (startHijri.monthName == endHijri.monthName) {
      return '${startHijri.monthName.toUpperCase()} ${startHijri.year} H';
    }
    return '${startHijri.monthName.toUpperCase()} · ${endHijri.monthName.toUpperCase()} ${endHijri.year} H';
  }

  String _getDayNameIndo(int weekday) {
    switch (weekday) {
      case DateTime.monday:
        return 'Senin';
      case DateTime.tuesday:
        return 'Selasa';
      case DateTime.wednesday:
        return 'Rabu';
      case DateTime.thursday:
        return 'Kamis';
      case DateTime.friday:
        return 'Jum\'at';
      case DateTime.saturday:
        return 'Sabtu';
      case DateTime.sunday:
        return 'Ahad';
      default:
        return '';
    }
  }
}
