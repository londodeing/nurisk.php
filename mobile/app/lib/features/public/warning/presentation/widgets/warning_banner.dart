import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../notifiers/warning_provider.dart';
import '../../domain/entities/warning_entity.dart';

class WarningBanner extends ConsumerWidget {
  const WarningBanner({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final warningState = ref.watch(warningProvider);

    return warningState.when(
      data: (warnings) {
        if (warnings.isEmpty) {
          return const SizedBox.shrink(); // Hide if empty
        }

        // Display the most critical warning at the top
        final topWarning = warnings.first;
        final Color bgColor = _getSeverityColor(topWarning.severity);

        return Container(
          margin: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
          padding: const EdgeInsets.all(12.0),
          decoration: BoxDecoration(
            color: bgColor,
            borderRadius: BorderRadius.circular(8.0),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Icon(Icons.warning_amber_rounded, color: Colors.white),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '${topWarning.source} - ${topWarning.title}',
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      topWarning.description,
                      style: const TextStyle(color: Colors.white),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
              if (warnings.length > 1)
                Padding(
                  padding: const EdgeInsets.only(left: 8.0),
                  child: Chip(
                    label: Text('+${warnings.length - 1}'),
                    backgroundColor: Colors.white.withValues(alpha: 0.3),
                    labelStyle: const TextStyle(color: Colors.white),
                  ),
                )
            ],
          ),
        );
      },
      loading: () => const SizedBox.shrink(),
      error: (error, stack) => Container(
        margin: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
        padding: const EdgeInsets.all(12.0),
        decoration: BoxDecoration(
          color: Colors.grey.shade200,
          borderRadius: BorderRadius.circular(8.0),
        ),
        child: const Row(
          children: [
            Icon(Icons.cloud_off, color: Colors.grey),
            SizedBox(width: 12),
            Text('Peringatan tidak tersedia', style: TextStyle(color: Colors.grey)),
          ],
        ),
      ),
    );
  }

  Color _getSeverityColor(WarningSeverity severity) {
    switch (severity) {
      case WarningSeverity.critical:
        return Colors.red.shade700;
      case WarningSeverity.warning:
        return Colors.orange.shade800;
      case WarningSeverity.info:
        return Colors.blue.shade700;
    }
  }
}
