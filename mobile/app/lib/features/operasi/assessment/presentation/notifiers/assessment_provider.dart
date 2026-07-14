import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/error/dio_exception_mapper.dart';
import 'package:nurisk_mobile/features/operasi/assessment/data/repositories/assessment_repository.dart';
import 'package:nurisk_mobile/features/operasi/assessment/domain/models/assessment_model.dart';

class AssessmentState {
  final bool isLoading;
  final String? error;
  final AssessmentModel? assessment;
  final int currentStep;
  final Map<String, dynamic> formData;

  AssessmentState({
    this.isLoading = false,
    this.error,
    this.assessment,
    this.currentStep = 0,
    this.formData = const {},
  });

  AssessmentState copyWith({
    bool? isLoading,
    String? error,
    AssessmentModel? assessment,
    int? currentStep,
    Map<String, dynamic>? formData,
  }) {
    return AssessmentState(
      isLoading: isLoading ?? this.isLoading,
      error: error ?? this.error,
      assessment: assessment ?? this.assessment,
      currentStep: currentStep ?? this.currentStep,
      formData: formData ?? this.formData,
    );
  }
}

final assessmentProvider = NotifierProvider<AssessmentNotifier, AssessmentState>(AssessmentNotifier.new);

class AssessmentNotifier extends Notifier<AssessmentState> {
  @override
  AssessmentState build() {
    return AssessmentState();
  }

  void initialize(String uuidInsiden) {
    state = AssessmentState();
  }

  void nextStep() {
    if (state.currentStep < 3) {
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

  Future<bool> submitAssessment(String uuidInsiden) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final repository = ref.read(assessmentRepositoryProvider);
      
      // Ensure uuid is present in payload
      final payload = Map<String, dynamic>.from(state.formData);
      payload['uuid_insiden'] = uuidInsiden;
      
      final assessment = await repository.createAssessment(uuidInsiden, payload);
      state = state.copyWith(
        isLoading: false,
        assessment: assessment,
      );
      return true;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: DioExceptionMapper.toUserMessage(e));
      return false;
    }
  }
}
