class OperationalFilter {
  final List<String> status;
  final List<String> severity;
  final List<String> objectType;
  final String keyword;
  final String timeRange;

  const OperationalFilter({
    this.status = const [],
    this.severity = const [],
    this.objectType = const [],
    this.keyword = '',
    this.timeRange = '24h',
  });

  OperationalFilter copyWith({
    List<String>? status,
    List<String>? severity,
    List<String>? objectType,
    String? keyword,
    String? timeRange,
  }) {
    return OperationalFilter(
      status: status ?? this.status,
      severity: severity ?? this.severity,
      objectType: objectType ?? this.objectType,
      keyword: keyword ?? this.keyword,
      timeRange: timeRange ?? this.timeRange,
    );
  }
}
