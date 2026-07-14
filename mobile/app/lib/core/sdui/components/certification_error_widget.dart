import 'package:flutter/material.dart';
import 'package:nurisk_mobile/core/runtime/certification/certification_entry.dart';
import 'package:nurisk_mobile/core/runtime/certification/certification_report.dart';

class CertificationErrorWidget extends StatelessWidget {
  final CertificationReport report;

  const CertificationErrorWidget({super.key, required this.report});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.error_outline, size: 64, color: Colors.red.shade300),
            const SizedBox(height: 16),
            Text(
              'Scene Certification Failed',
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                    color: Colors.grey.shade800,
                    fontWeight: FontWeight.w600,
                  ),
            ),
            const SizedBox(height: 8),
            Text(
              '${report.errors.length} error(s) found',
              style: TextStyle(color: Colors.grey.shade600),
            ),
            if (report.warnings.isNotEmpty) ...[
              const SizedBox(height: 4),
              Text(
                '${report.warnings.length} warning(s)',
                style: TextStyle(color: Colors.orange.shade600, fontSize: 13),
              ),
            ],
            const SizedBox(height: 24),
            ...report.errors.map((e) => _entryTile(e)),
            if (report.warnings.isNotEmpty) ...[
              const Divider(height: 32),
              ...report.warnings.map((e) => _entryTile(e)),
            ],
          ],
        ),
      ),
    );
  }

  Widget _entryTile(CertificationEntry entry) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(entry.icon, style: const TextStyle(fontSize: 14)),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              entry.message,
              style: TextStyle(
                fontSize: 12,
                color: entry.severity == CertificationSeverity.warning
                    ? Colors.orange.shade800
                    : Colors.red.shade800,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
