import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../domain/models/operational_filter.dart';

class OperationalFilterNotifier extends Notifier<OperationalFilter> {
  @override
  OperationalFilter build() => const OperationalFilter();

  void updateFilter({
    List<String>? status,
    List<String>? severity,
    List<String>? objectType,
    String? keyword,
    String? timeRange,
  }) {
    state = state.copyWith(
      status: status,
      severity: severity,
      objectType: objectType,
      keyword: keyword,
      timeRange: timeRange,
    );
  }

  void clearFilter() {
    state = const OperationalFilter();
  }
}

final operationalFilterProvider = NotifierProvider<OperationalFilterNotifier, OperationalFilter>(() {
  return OperationalFilterNotifier();
});
