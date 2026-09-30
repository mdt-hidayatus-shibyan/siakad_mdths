class JamOrderHelper {
  JamOrderHelper._();

  static int getWeight(String? jamText, [String? jamKe]) {
    final jk = (jamKe ?? '').trim().toLowerCase();
    if (jk == 'nadzoman') return 1;
    if (jk == '1') return 2;
    if (jk == '2') return 3;
    if (jk == 'ekstra') return 4;

    final parsedJk = int.tryParse(jk);
    if (parsedJk != null) return parsedJk + 10;

    final lower = (jamText ?? '').toLowerCase();
    if (lower.contains('nadzoman') || lower.startsWith('13:')) {
      return 1;
    }
    if (lower.contains('jam ke-1') ||
        lower.contains('jam 1') ||
        lower.contains('14:00') ||
        lower.startsWith('14:')) {
      return 2;
    }
    if (lower.contains('jam ke-2') ||
        lower.contains('jam 2') ||
        lower.contains('15:30') ||
        lower.startsWith('15:')) {
      return 3;
    }
    if (lower.contains('ekstra') ||
        lower.contains('20:00') ||
        lower.startsWith('20:')) {
      return 4;
    }

    return 99;
  }
}
