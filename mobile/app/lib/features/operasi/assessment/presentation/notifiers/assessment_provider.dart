import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/error/dio_exception_mapper.dart';
import 'package:nurisk_mobile/features/operasi/assessment/data/repositories/assessment_repository.dart';
import 'package:nurisk_mobile/features/operasi/assessment/domain/models/assessment_model.dart';

import 'package:nurisk_mobile/features/operasi/insiden/data/models/insiden_model.dart';

class AssessmentState {
  final bool isLoading;
  final String? error;
  final AssessmentModel? assessment;
  final int currentStep;
  final Map<String, dynamic> formData;
  /// ID assessment yang sedang diedit. null = mode create baru.
  final int? targetAssessmentId;
  /// true ketika data existing sudah berhasil di-load ke formData
  final bool isDataLoaded;

  AssessmentState({
    this.isLoading = false,
    this.error,
    this.assessment,
    this.currentStep = 0,
    this.formData = const {},
    this.targetAssessmentId,
    this.isDataLoaded = false,
  });

  AssessmentState copyWith({
    bool? isLoading,
    String? error,
    AssessmentModel? assessment,
    int? currentStep,
    Map<String, dynamic>? formData,
    int? targetAssessmentId,
    bool? isDataLoaded,
  }) {
    return AssessmentState(
      isLoading: isLoading ?? this.isLoading,
      error: error ?? this.error,
      assessment: assessment ?? this.assessment,
      currentStep: currentStep ?? this.currentStep,
      formData: formData ?? this.formData,
      targetAssessmentId: targetAssessmentId ?? this.targetAssessmentId,
      isDataLoaded: isDataLoaded ?? this.isDataLoaded,
    );
  }

  bool get isEditMode => targetAssessmentId != null;
}

final assessmentProvider =
    NotifierProvider<AssessmentNotifier, AssessmentState>(AssessmentNotifier.new);

class AssessmentNotifier extends Notifier<AssessmentState> {
  @override
  AssessmentState build() {
    return AssessmentState();
  }

  /// Inisialisasi wizard.
  /// [uuidInsiden] — UUID insiden
  /// [targetAssessmentId] — jika tidak null, load data assessment lama untuk diedit
  Future<void> initialize(String uuidInsiden, {int? targetAssessmentId, InsidenModel? insiden}) async {
    state = AssessmentState(targetAssessmentId: targetAssessmentId);

    if (targetAssessmentId != null) {
      // Mode edit: fetch data dari API untuk populate form
      state = state.copyWith(isLoading: true, error: null);
      try {
        final repository = ref.read(assessmentRepositoryProvider);
        final result = await repository.fetchAssessment(uuidInsiden, targetAssessmentId);
        state = state.copyWith(
          isLoading: false,
          isDataLoaded: true,
          formData: result.formData,
        );
      } catch (e) {
        state = state.copyWith(
          isLoading: false,
          error: DioExceptionMapper.toUserMessage(e),
        );
      }
    } else {
      // Create mode
      Map<String, dynamic> initialFormData = {};
      if (insiden != null) {
        // Auto-fill dari insiden
        initialFormData = {
          'uuid_insiden': insiden.uuid,
          'jenis_laporan': 'kaji_cepat', // default
          'waktu_assesment': DateTime.now().toIso8601String(),
          // Lokasi Detail (Auto-field)
          'lokasi_detail': {
            'id_kec': null, 
            'id_desa': null,
          },
          // Biodata Kejadian (Auto-field)
          'biodata_kejadian': {
            'tanggal_mulai_kejadian': insiden.laporanAsal?.waktuKejadian?.toIso8601String().split('T').first ?? insiden.waktuMulai?.toIso8601String().split('T').first,
            'jam_mulai_kejadian': insiden.laporanAsal?.waktuKejadian != null 
                ? '${insiden.laporanAsal!.waktuKejadian!.hour.toString().padLeft(2, '0')}:${insiden.laporanAsal!.waktuKejadian!.minute.toString().padLeft(2, '0')}' 
                : null,
          }
        };
      }
      state = state.copyWith(
        isDataLoaded: true,
        formData: initialFormData,
      );
    }
  }

  void nextStep() {
    if (state.currentStep < 6) {
      state = state.copyWith(currentStep: state.currentStep + 1);
    }
  }

  void previousStep() {
    if (state.currentStep > 0) {
      state = state.copyWith(currentStep: state.currentStep - 1);
    }
  }

  void setStep(int step) {
    state = state.copyWith(currentStep: step);
  }

  void updateFormData(Map<String, dynamic> data) {
    final newFormData = Map<String, dynamic>.from(state.formData)..addAll(data);
    state = state.copyWith(formData: newFormData);
  }

  /// Submit: create baru atau update tergantung mode
  Future<bool> submitAssessment(String uuidInsiden) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final repository = ref.read(assessmentRepositoryProvider);
      final payload = Map<String, dynamic>.from(state.formData);
      payload['uuid_insiden'] = uuidInsiden;

      AssessmentModel assessment;
      if (state.isEditMode) {
        // Mode edit: PUT ke endpoint update
        assessment = await repository.updateAssessment(
          uuidInsiden,
          state.targetAssessmentId!,
          payload,
        );
      } else {
        // Mode create: POST ke endpoint create
        assessment = await repository.createAssessment(uuidInsiden, payload);
      }

      state = state.copyWith(
        isLoading: false,
        assessment: assessment,
      );
      return true;
    } catch (e) {
      if (e is DioException) {
        final errorData = e.response?.data;
        print("DIO ERROR SUBMIT ASSESSMENT: $errorData");
        state = state.copyWith(
          isLoading: false,
          error: "Validasi gagal: $errorData"
        );
      } else {
        print("ERROR SUBMIT ASSESSMENT: $e");
        state = state.copyWith(
            isLoading: false, error: DioExceptionMapper.toUserMessage(e));
      }
      return false;
    }
  }
}
