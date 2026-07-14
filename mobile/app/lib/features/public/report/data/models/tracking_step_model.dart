class TrackingStepModel {
  final String status;
  final DateTime? time;
  final String description;

  const TrackingStepModel({
    required this.status,
    this.time,
    required this.description,
  });

  factory TrackingStepModel.fromJson(Map<String, dynamic> json) {
    return TrackingStepModel(
      status: json['status'] ?? '',
      time: DateTime.tryParse(json['time'] ?? ''),
      description: json['description'] ?? '',
    );
  }
}
