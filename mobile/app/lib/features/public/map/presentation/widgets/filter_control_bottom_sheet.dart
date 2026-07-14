import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../providers/operational_filter_provider.dart';

class FilterControlBottomSheet extends ConsumerWidget {
  const FilterControlBottomSheet({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final filter = ref.watch(operationalFilterProvider);

    return Container(
      padding: const EdgeInsets.all(16.0),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Center(
            child: Container(
              margin: const EdgeInsets.only(bottom: 16),
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: Colors.grey[300],
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          const Text(
            'Filter Operasional',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 16),
          const Text('Rentang Waktu', style: TextStyle(fontWeight: FontWeight.bold)),
          Row(
            children: [
              Expanded(
                child: RadioListTile<String>(
                  title: const Text('24 Jam'),
                  value: '24h',
                  groupValue: filter.timeRange,
                  onChanged: (val) {
                    if (val != null) {
                      ref.read(operationalFilterProvider.notifier).updateFilter(timeRange: val);
                    }
                  },
                ),
              ),
              Expanded(
                child: RadioListTile<String>(
                  title: const Text('7 Hari'),
                  value: '7d',
                  groupValue: filter.timeRange,
                  onChanged: (val) {
                    if (val != null) {
                      ref.read(operationalFilterProvider.notifier).updateFilter(timeRange: val);
                    }
                  },
                ),
              ),
            ],
          ),
          const Divider(),
          const Text('Status', style: TextStyle(fontWeight: FontWeight.bold)),
          CheckboxListTile(
            title: const Text('Aktif'),
            value: filter.status.contains('active'),
            onChanged: (val) {
              final newStatus = Set<String>.from(filter.status);
              if (val == true) {
                newStatus.add('active');
              } else {
                newStatus.remove('active');
              }
              ref.read(operationalFilterProvider.notifier).updateFilter(status: newStatus.toList());
            },
          ),
          CheckboxListTile(
            title: const Text('Selesai'),
            value: filter.status.contains('resolved'),
            onChanged: (val) {
              final newStatus = Set<String>.from(filter.status);
              if (val == true) {
                newStatus.add('resolved');
              } else {
                newStatus.remove('resolved');
              }
              ref.read(operationalFilterProvider.notifier).updateFilter(status: newStatus.toList());
            },
          ),
          const SizedBox(height: 16),
        ],
      ),
    );
  }
}
