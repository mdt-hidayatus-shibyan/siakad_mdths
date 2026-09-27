import '../../core/constants/app_version_config.dart';

class AppVersionModel {
  final String appTitle;
  final String appSubtitle;
  final String version;
  final String buildNumber;
  final String releaseDate;
  final String releaseSubtitle;
  final String releaseBadge;
  final String statusBadge;
  final bool isLatest;
  final List<ChangelogEntry> newFeatures;
  final List<ChangelogEntry> improvements;
  final String devName;
  final String devRole;
  final String devInstitution;
  final String devDescription;
  final List<DeveloperInfoEntry> devDetails;
  final List<String> techStacks;
  final String copyrightYear;
  final String copyrightOwner;
  final String copyrightSubtitle;

  const AppVersionModel({
    required this.appTitle,
    required this.appSubtitle,
    required this.version,
    required this.buildNumber,
    required this.releaseDate,
    required this.releaseSubtitle,
    required this.releaseBadge,
    required this.statusBadge,
    required this.isLatest,
    required this.newFeatures,
    required this.improvements,
    required this.devName,
    required this.devRole,
    required this.devInstitution,
    required this.devDescription,
    required this.devDetails,
    required this.techStacks,
    required this.copyrightYear,
    required this.copyrightOwner,
    required this.copyrightSubtitle,
  });

  /// Inisialisasi model langsung dari file konfigurasi lokal manual
  factory AppVersionModel.fromConfig() {
    return const AppVersionModel(
      appTitle: AppVersionConfig.appTitle,
      appSubtitle: AppVersionConfig.appSubtitle,
      version: AppVersionConfig.version,
      buildNumber: AppVersionConfig.buildNumber,
      releaseDate: AppVersionConfig.releaseDate,
      releaseSubtitle: AppVersionConfig.releaseSubtitle,
      releaseBadge: AppVersionConfig.releaseBadge,
      statusBadge: AppVersionConfig.statusBadge,
      isLatest: AppVersionConfig.isLatest,
      newFeatures: AppVersionConfig.newFeatures,
      improvements: AppVersionConfig.improvements,
      devName: AppVersionConfig.devName,
      devRole: AppVersionConfig.devRole,
      devInstitution: AppVersionConfig.devInstitution,
      devDescription: AppVersionConfig.devDescription,
      devDetails: AppVersionConfig.devDetails,
      techStacks: AppVersionConfig.techStacks,
      copyrightYear: AppVersionConfig.copyrightYear,
      copyrightOwner: AppVersionConfig.copyrightOwner,
      copyrightSubtitle: AppVersionConfig.copyrightSubtitle,
    );
  }

  /// Kompatibilitas mundur
  factory AppVersionModel.fromConfigFallback() => AppVersionModel.fromConfig();
}
