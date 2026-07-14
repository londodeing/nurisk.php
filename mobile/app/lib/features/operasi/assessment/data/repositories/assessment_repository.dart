import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/auth_api_client.dart';
import 'package:nurisk_mobile/features/operasi/assessment/domain/models/assessment_model.dart';

final assessmentRepositoryProvider = Provider<AssessmentRepository>((ref) {
  final dio = ref.watch(authApiClientProvider);
  return AssessmentRepository(dio);
});

class AssessmentRepository {
  final Dio _dio;

  AssessmentRepository(this._dio);

  Future<AssessmentModel> createAssessment(String uuidInsiden, Map<String, dynamic> data) async {
    final response = await _dio.post(
      'insiden/$uuidInsiden/assessment',
      data: data,
    );
    return AssessmentModel.fromJson(response.data['data']);
  }
}
