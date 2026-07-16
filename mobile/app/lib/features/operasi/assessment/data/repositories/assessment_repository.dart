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

  /// POST /v1/assessment — buat assessment baru
  Future<AssessmentModel> createAssessment(String uuidInsiden, Map<String, dynamic> data) async {
    final response = await _dio.post(
      'v1/assessment',
      data: data,
    );
    return AssessmentModel.fromJson(response.data['data'] ?? response.data);
  }

  /// GET /v1/assessment/{assessmentId} — ambil data assessment lama
  Future<AssessmentFormData> fetchAssessment(String uuidInsiden, int assessmentId) async {
    final response = await _dio.get(
      'v1/assessment/$assessmentId',
    );
    final raw = response.data;
    final assessmentJson = raw['data'] ?? raw;
    final formDataJson = raw['form_data'] ?? {};
    return AssessmentFormData(
      assessment: AssessmentModel.fromJson(assessmentJson),
      formData: Map<String, dynamic>.from(formDataJson),
    );
  }

  /// PUT /v1/assessment/{assessmentId} — update assessment yang sudah ada
  Future<AssessmentModel> updateAssessment(
      String uuidInsiden, int assessmentId, Map<String, dynamic> data) async {
    final response = await _dio.put(
      'v1/assessment/$assessmentId',
      data: data,
    );
    return AssessmentModel.fromJson(response.data['data'] ?? response.data);
  }
}

/// Hasil fetch assessment: data model + form_data untuk populate form
class AssessmentFormData {
  final AssessmentModel assessment;
  final Map<String, dynamic> formData;

  AssessmentFormData({required this.assessment, required this.formData});
}
