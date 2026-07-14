import 'package:dio/dio.dart';
import '../models/wilayah.dart';

class OrganizationRepository {
  final Dio _dio;

  List<Pcnu>? _cachedPcnu;
  DateTime? _pcnuCachedAt;
  static const Duration _pcnuTtl = Duration(hours: 24);

  OrganizationRepository(this._dio);

  Future<List<Pcnu>> getPcnuList() async {
    if (_cachedPcnu != null &&
        _pcnuCachedAt != null &&
        DateTime.now().difference(_pcnuCachedAt!) < _pcnuTtl) {
      return _cachedPcnu!;
    }

    try {
      final response = await _dio.get('wilayah/pcnu');
      final data = response.data['data'] as List<dynamic>? ?? [];
      _cachedPcnu = data.map((e) => Pcnu(
        id: e['id'] as int,
        nama: e['nama'] as String,
      )).toList();
      _pcnuCachedAt = DateTime.now();
      return _cachedPcnu!;
    } catch (_) {
      if (_cachedPcnu != null) return _cachedPcnu!;
      rethrow;
    }
  }

  void clearCache() {
    _cachedPcnu = null;
    _pcnuCachedAt = null;
  }
}
