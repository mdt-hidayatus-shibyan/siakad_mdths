import '../../core/utils/jam_order_helper.dart';

class EventPresensiInfo {
  final int id;
  final String namaKegiatan;
  final String kategori;
  final String tipePresensi;
  final List<String> sesiList;
  final String? tanggalMulai;
  final String? tanggalSelesai;

  EventPresensiInfo({
    required this.id,
    required this.namaKegiatan,
    required this.kategori,
    required this.tipePresensi,
    required this.sesiList,
    this.tanggalMulai,
    this.tanggalSelesai,
  });

  factory EventPresensiInfo.fromJson(Map<String, dynamic> json) {
    final rawSesi = json['sesi_list'] as List? ?? [];
    return EventPresensiInfo(
      id: json['id'] ?? 0,
      namaKegiatan: json['nama_kegiatan'] ?? 'Kegiatan Khusus',
      kategori: json['kategori'] ?? 'Kegiatan',
      tipePresensi: json['tipe_presensi'] ?? 'harian',
      sesiList: rawSesi.map((e) => e.toString()).toList(),
      tanggalMulai: json['tanggal_mulai'],
      tanggalSelesai: json['tanggal_selesai'],
    );
  }
}

class SesiPresensiResponse {
  final bool isLibur;
  final String? keteranganLibur;
  final bool isUjian;
  final String? namaUjian;
  final int? ujianId;
  final bool isEvent;
  final EventPresensiInfo? eventInfo;
  final List<SesiPresensiItem> sesiList;

  SesiPresensiResponse({
    required this.isLibur,
    this.keteranganLibur,
    this.isUjian = false,
    this.namaUjian,
    this.ujianId,
    this.isEvent = false,
    this.eventInfo,
    required this.sesiList,
  });

  factory SesiPresensiResponse.fromJson(Map<String, dynamic> json) {
    final rawList = json['data'] as List? ?? [];
    final isEvent = json['is_event'] ?? false;
    final eventInfo = json['event_info'] != null
        ? EventPresensiInfo.fromJson(json['event_info'])
        : null;

    return SesiPresensiResponse(
      isLibur: json['is_libur'] ?? false,
      keteranganLibur: json['keterangan_libur'],
      isUjian: json['is_ujian'] ?? false,
      namaUjian: json['nama_ujian'],
      ujianId: json['ujian_id'],
      isEvent: isEvent,
      eventInfo: eventInfo,
      sesiList: (rawList.map((e) => SesiPresensiItem.fromJson(e)).toList())
        ..sort((a, b) {
          if (isEvent) {
            // Urutkan berdasarkan Sesi (Pagi, Siang, Malam, Harian) lalu Ruangan
            final sesiOrder = {'pagi': 1, 'siang': 2, 'malam': 3, 'harian': 4};
            final sA = sesiOrder[(a.sesi ?? '').toLowerCase()] ?? 9;
            final sB = sesiOrder[(b.sesi ?? '').toLowerCase()] ?? 9;
            if (sA != sB) return sA.compareTo(sB);
            return a.kelas.toLowerCase().compareTo(b.kelas.toLowerCase());
          }
          final wA = JamOrderHelper.getWeight(a.jam, a.jamKe);
          final wB = JamOrderHelper.getWeight(b.jam, b.jamKe);
          if (wA != wB) return wA.compareTo(wB);
          return a.kelas.toLowerCase().compareTo(b.kelas.toLowerCase());
        }),
    );
  }
}

class SesiPresensiItem {
  final int id;
  final String jamKe;
  final String jam;
  final String pelajaran;
  final String kelas;
  final String guru;
  final bool isMilikWali;
  final bool sudahAbsen;
  final bool isBebasKbm;
  final String? keteranganBebasKbm;
  final bool isEvent;
  final int? kalendarPendidikanId;
  final String? sesi;
  final String? namaKegiatan;
  final String? kategori;
  final int? ruanganId;
  final String? level;
  final int totalMurid;
  final int hadirCount;
  final int sakitCount;
  final int izinCount;
  final int alphaCount;

  SesiPresensiItem({
    required this.id,
    this.jamKe = '',
    required this.jam,
    required this.pelajaran,
    required this.kelas,
    required this.guru,
    required this.isMilikWali,
    required this.sudahAbsen,
    this.isBebasKbm = false,
    this.keteranganBebasKbm,
    this.isEvent = false,
    this.kalendarPendidikanId,
    this.sesi,
    this.namaKegiatan,
    this.kategori,
    this.ruanganId,
    this.level,
    this.totalMurid = 0,
    this.hadirCount = 0,
    this.sakitCount = 0,
    this.izinCount = 0,
    this.alphaCount = 0,
  });

  factory SesiPresensiItem.fromJson(Map<String, dynamic> json) {
    return SesiPresensiItem(
      id: json['id'] ?? 0,
      jamKe: json['jam_ke']?.toString() ?? '',
      jam: json['jam'] ?? '',
      pelajaran: json['pelajaran'] ?? json['mapel'] ?? '',
      kelas: json['kelas'] ?? json['ruangan'] ?? '',
      guru: json['guru'] ?? '',
      isMilikWali: json['is_milik_wali'] ?? false,
      sudahAbsen: json['sudah_absen'] ?? false,
      isBebasKbm: json['is_bebas_kbm'] ?? false,
      keteranganBebasKbm: json['keterangan_bebas_kbm'],
      isEvent: json['is_event'] ?? false,
      kalendarPendidikanId: json['kalendar_pendidikan_id'],
      sesi: json['sesi'],
      namaKegiatan: json['nama_kegiatan'],
      kategori: json['kategori'],
      ruanganId: json['ruangan_id'],
      level: json['level'],
      totalMurid: json['total_murid'] ?? 0,
      hadirCount: json['hadir_count'] ?? 0,
      sakitCount: json['sakit_count'] ?? 0,
      izinCount: json['izin_count'] ?? 0,
      alphaCount: json['alpha_count'] ?? 0,
    );
  }
}

class MuridPresensiItem {
  final int muridId;
  final String nama;
  final String nism;
  final String jenisKelamin;
  final String? foto;
  final String? catatan;
  String? status; // Hadir, Sakit, Izin, Alpha, Dispensasi, or null

  MuridPresensiItem({
    required this.muridId,
    required this.nama,
    required this.nism,
    required this.jenisKelamin,
    this.foto,
    this.catatan,
    this.status,
  });

  factory MuridPresensiItem.fromJson(Map<String, dynamic> json) {
    return MuridPresensiItem(
      muridId: json['murid_id'] ?? json['id'] ?? 0,
      nama: json['nama'] ?? json['nama_lengkap'] ?? '',
      nism: json['nism'] ?? json['nis'] ?? '',
      jenisKelamin: json['jenis_kelamin'] ?? 'L',
      foto: json['foto'],
      catatan: json['catatan'],
      status: json['status'],
    );
  }

  Map<String, dynamic> toJson() {
    return {'murid_id': muridId, 'status': status, 'catatan': catatan};
  }
}

class RiwayatPresensiUstadz {
  final int totalHadir;
  final int totalIzin;
  final int totalSakit;
  final int totalAlpha;
  final List<RiwayatUstadzItem> riwayat;

  RiwayatPresensiUstadz({
    required this.totalHadir,
    required this.totalIzin,
    required this.totalSakit,
    required this.totalAlpha,
    required this.riwayat,
  });

  factory RiwayatPresensiUstadz.fromJson(Map<String, dynamic> json) {
    final rawList = json['riwayat'] as List? ?? [];
    return RiwayatPresensiUstadz(
      totalHadir: json['total_hadir'] ?? 0,
      totalIzin: json['total_izin'] ?? 0,
      totalSakit: json['total_sakit'] ?? 0,
      totalAlpha: json['total_alpha'] ?? 0,
      riwayat: rawList.map((e) => RiwayatUstadzItem.fromJson(e)).toList(),
    );
  }
}

class RiwayatUstadzItem {
  final int id;
  final String tanggal;
  final String mapel;
  final String ruangan;
  final String status;
  final String? ustadzPengganti;
  final String? keterangan;

  RiwayatUstadzItem({
    required this.id,
    required this.tanggal,
    required this.mapel,
    required this.ruangan,
    required this.status,
    this.ustadzPengganti,
    this.keterangan,
  });

  factory RiwayatUstadzItem.fromJson(Map<String, dynamic> json) {
    return RiwayatUstadzItem(
      id: json['id'] ?? 0,
      tanggal: json['tanggal'] ?? '',
      mapel: json['mapel'] ?? '',
      ruangan: json['ruangan'] ?? '',
      status: json['status'] ?? 'Hadir',
      ustadzPengganti: json['ustadz_pengganti'],
      keterangan: json['keterangan'],
    );
  }
}

class SesiPresensiUstadzResponse {
  final bool isLibur;
  final String? keteranganLibur;
  final bool isUjian;
  final String? namaUjian;
  final int? ujianId;
  final bool isEvent;
  final EventPresensiInfo? eventInfo;
  final List<SesiPresensiUstadzItem> sesiList;

  SesiPresensiUstadzResponse({
    required this.isLibur,
    this.keteranganLibur,
    this.isUjian = false,
    this.namaUjian,
    this.ujianId,
    this.isEvent = false,
    this.eventInfo,
    required this.sesiList,
  });

  factory SesiPresensiUstadzResponse.fromJson(Map<String, dynamic> json) {
    final rawList = json['data'] as List? ?? [];
    final isEvent = json['is_event'] ?? false;
    final eventInfo = json['event_info'] != null
        ? EventPresensiInfo.fromJson(json['event_info'])
        : null;

    return SesiPresensiUstadzResponse(
      isLibur: json['is_libur'] ?? false,
      keteranganLibur: json['keterangan_libur'],
      isUjian: json['is_ujian'] ?? false,
      namaUjian: json['nama_ujian'],
      ujianId: json['ujian_id'],
      isEvent: isEvent,
      eventInfo: eventInfo,
      sesiList:
          (rawList.map((e) => SesiPresensiUstadzItem.fromJson(e)).toList())
            ..sort((a, b) {
              if (isEvent) {
                final sesiOrder = {
                  'pagi': 1,
                  'siang': 2,
                  'malam': 3,
                  'harian': 4,
                };
                final sA = sesiOrder[(a.sesi ?? '').toLowerCase()] ?? 9;
                final sB = sesiOrder[(b.sesi ?? '').toLowerCase()] ?? 9;
                return sA.compareTo(sB);
              }
              final wA = JamOrderHelper.getWeight(a.jam, a.jamKe);
              final wB = JamOrderHelper.getWeight(b.jam, b.jamKe);
              if (wA != wB) return wA.compareTo(wB);
              return a.ruangan.toLowerCase().compareTo(b.ruangan.toLowerCase());
            }),
    );
  }
}

class SesiPresensiUstadzItem {
  final int jadwalId;
  final String jamKe;
  final String jam;
  final String mapel;
  final String ruangan;
  final String guruPengajar;
  final bool isMilikWali;
  final bool isTeamTeaching;
  final List<UstadzPresensiStatusItem> daftarUstadz;
  final bool sudahCheckin;
  final String status;
  final bool isBebasKbm;
  final String? keteranganBebasKbm;
  final int? ustadzPenggantiId;
  final String? ustadzPenggantiNama;
  final String? keterangan;
  final String? waktuCheckin;
  final bool isEvent;
  final int? kalendarPendidikanId;
  final String? sesi;
  final String? namaKegiatan;

  SesiPresensiUstadzItem({
    required this.jadwalId,
    required this.jamKe,
    required this.jam,
    required this.mapel,
    required this.ruangan,
    required this.guruPengajar,
    this.isMilikWali = false,
    this.isTeamTeaching = false,
    this.daftarUstadz = const [],
    required this.sudahCheckin,
    required this.status,
    this.isBebasKbm = false,
    this.keteranganBebasKbm,
    this.ustadzPenggantiId,
    this.ustadzPenggantiNama,
    this.keterangan,
    this.waktuCheckin,
    this.isEvent = false,
    this.kalendarPendidikanId,
    this.sesi,
    this.namaKegiatan,
  });

  factory SesiPresensiUstadzItem.fromJson(Map<String, dynamic> json) {
    final rawUstadz = json['daftar_ustadz'] as List? ?? [];
    return SesiPresensiUstadzItem(
      jadwalId: json['jadwal_id'] ?? json['id'] ?? 0,
      jamKe: json['jam_ke'] ?? '',
      jam: json['jam'] ?? '',
      mapel: json['mapel'] ?? json['pelajaran'] ?? '',
      ruangan: json['ruangan'] ?? json['kelas'] ?? '',
      guruPengajar: json['guru_pengajar'] ?? json['guru'] ?? '-',
      isMilikWali: json['is_milik_wali'] ?? false,
      isTeamTeaching: json['is_team_teaching'] ?? false,
      daftarUstadz: rawUstadz
          .map((e) => UstadzPresensiStatusItem.fromJson(e))
          .toList(),
      sudahCheckin: json['sudah_checkin'] ?? false,
      status: json['status_kehadiran'] ?? json['status'] ?? 'Belum Absen',
      isBebasKbm: json['is_bebas_kbm'] ?? false,
      keteranganBebasKbm: json['keterangan_bebas_kbm'],
      ustadzPenggantiId: json['ustadz_pengganti_id'],
      ustadzPenggantiNama: json['ustadz_pengganti_nama'],
      keterangan: json['keterangan'],
      waktuCheckin: json['waktu_checkin'],
      isEvent: json['is_event'] ?? false,
      kalendarPendidikanId: json['kalendar_pendidikan_id'],
      sesi: json['sesi'],
      namaKegiatan: json['nama_kegiatan'],
    );
  }
}

class UstadzPresensiStatusItem {
  final int id;
  final String nama;
  final bool isUtama;
  final bool sudahCheckin;
  final String status;

  UstadzPresensiStatusItem({
    required this.id,
    required this.nama,
    required this.isUtama,
    required this.sudahCheckin,
    required this.status,
  });

  factory UstadzPresensiStatusItem.fromJson(Map<String, dynamic> json) {
    return UstadzPresensiStatusItem(
      id: json['id'] ?? 0,
      nama: json['nama'] ?? '',
      isUtama: json['is_utama'] ?? false,
      sudahCheckin: json['sudah_checkin'] ?? false,
      status: json['status'] ?? 'Belum Absen',
    );
  }
}

class UstadzBadalItem {
  final int id;
  final String namaLengkap;
  final String? jenisKelamin;
  final String? noHp;

  UstadzBadalItem({
    required this.id,
    required this.namaLengkap,
    this.jenisKelamin,
    this.noHp,
  });

  factory UstadzBadalItem.fromJson(Map<String, dynamic> json) {
    return UstadzBadalItem(
      id: json['id'] ?? 0,
      namaLengkap: json['nama_lengkap'] ?? '',
      jenisKelamin: json['jenis_kelamin'],
      noHp: json['no_hp'],
    );
  }
}
