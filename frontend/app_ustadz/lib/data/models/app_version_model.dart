import 'package:flutter/material.dart';
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

  /// Buat instance model dari JSON API backend
  factory AppVersionModel.fromJson(Map<String, dynamic> json) {
    // Parsing new_features
    final rawFeatures = json['new_features'] as List<dynamic>? ?? [];
    final List<ChangelogEntry> parsedFeatures = rawFeatures.map((item) {
      if (item is Map<String, dynamic>) {
        return ChangelogEntry(
          title: item['title']?.toString() ?? '',
          description: item['description']?.toString() ?? '',
        );
      }
      return ChangelogEntry(title: item.toString(), description: '');
    }).toList();

    // Parsing improvements
    final rawImprovements = json['improvements'] as List<dynamic>? ?? [];
    final List<ChangelogEntry> parsedImprovements = rawImprovements.map((item) {
      if (item is Map<String, dynamic>) {
        return ChangelogEntry(
          title: item['title']?.toString() ?? '',
          description: item['description']?.toString() ?? '',
        );
      }
      return ChangelogEntry(title: item.toString(), description: '');
    }).toList();

    // Parsing dev_details
    final rawDevDetails = json['dev_details'] as List<dynamic>? ?? [];
    final List<DeveloperInfoEntry> parsedDevDetails = rawDevDetails.map((item) {
      if (item is Map<String, dynamic>) {
        final iconStr = item['icon']?.toString() ?? '';
        IconData icon = Icons.info_outline_rounded;
        if (iconStr == 'developer_mode') {
          icon = Icons.developer_mode_rounded;
        } else if (iconStr == 'dns') {
          icon = Icons.dns_rounded;
        } else if (iconStr == 'security') {
          icon = Icons.security_rounded;
        } else if (iconStr == 'domain') {
          icon = Icons.domain_rounded;
        }
        return DeveloperInfoEntry(
          icon: icon,
          label: item['label']?.toString() ?? '',
          value: item['value']?.toString() ?? '',
        );
      }
      return const DeveloperInfoEntry(
        icon: Icons.info_outline_rounded,
        label: '',
        value: '',
      );
    }).toList();

    // Parsing tech_stacks
    final rawTechStacks = json['tech_stacks'] as List<dynamic>? ?? [];
    final List<String> parsedTechStacks = rawTechStacks
        .map((s) => s.toString())
        .toList();

    return AppVersionModel(
      appTitle: json['app_title']?.toString() ?? AppVersionConfig.appTitle,
      appSubtitle:
          json['app_subtitle']?.toString() ?? AppVersionConfig.appSubtitle,
      version: json['version']?.toString() ?? AppVersionConfig.version,
      buildNumber:
          json['build_number']?.toString() ?? AppVersionConfig.buildNumber,
      releaseDate:
          json['release_date']?.toString() ?? AppVersionConfig.releaseDate,
      releaseSubtitle:
          json['release_subtitle']?.toString() ??
          AppVersionConfig.releaseSubtitle,
      releaseBadge:
          json['release_badge']?.toString() ?? AppVersionConfig.releaseBadge,
      statusBadge:
          json['status_badge']?.toString() ?? AppVersionConfig.statusBadge,
      isLatest: json['is_latest'] == true || json['is_latest'] == 1,
      newFeatures: parsedFeatures.isNotEmpty
          ? parsedFeatures
          : AppVersionConfig.newFeatures,
      improvements: parsedImprovements.isNotEmpty
          ? parsedImprovements
          : AppVersionConfig.improvements,
      devName: json['dev_name']?.toString() ?? AppVersionConfig.devName,
      devRole: json['dev_role']?.toString() ?? AppVersionConfig.devRole,
      devInstitution:
          json['dev_institution']?.toString() ??
          AppVersionConfig.devInstitution,
      devDescription:
          json['dev_description']?.toString() ??
          AppVersionConfig.devDescription,
      devDetails: parsedDevDetails.isNotEmpty
          ? parsedDevDetails
          : AppVersionConfig.devDetails,
      techStacks: parsedTechStacks.isNotEmpty
          ? parsedTechStacks
          : AppVersionConfig.techStacks,
      copyrightYear:
          json['copyright_year']?.toString() ?? AppVersionConfig.copyrightYear,
      copyrightOwner:
          json['copyright_owner']?.toString() ??
          AppVersionConfig.copyrightOwner,
      copyrightSubtitle:
          json['copyright_subtitle']?.toString() ??
          AppVersionConfig.copyrightSubtitle,
    );
  }

  /// Fallback dari file konstanta lokal (AppVersionConfig) saat offline
  factory AppVersionModel.fromConfigFallback() {
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
}
