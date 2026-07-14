import 'package:flutter/material.dart';

class TimelineRenderer extends StatelessWidget {
  final List<dynamic> timelineData;

  const TimelineRenderer({Key? key, required this.timelineData}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    if (timelineData.isEmpty) {
      return const Padding(
        padding: EdgeInsets.all(16.0),
        child: Text('No timeline data available.'),
      );
    }

    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: timelineData.length,
      itemBuilder: (context, index) {
        final item = timelineData[index] as Map<String, dynamic>;
        
        final String time = item['time'] ?? '';
        final String title = item['title'] ?? '';
        final String? subtitle = item['subtitle'];
        final String? description = item['description'];
        
        // Parse simple string color if backend sends it (e.g. '#EF4444')
        // For MVP we just use default theme colors if missing
        
        return Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Column(
                children: [
                  Container(
                    width: 12,
                    height: 12,
                    decoration: BoxDecoration(
                      color: Theme.of(context).primaryColor,
                      shape: BoxShape.circle,
                    ),
                  ),
                  if (index != timelineData.length - 1)
                    Container(
                      width: 2,
                      height: 40,
                      color: Colors.grey[300],
                    ),
                ],
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      time,
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: Colors.grey[600],
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      title,
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    if (subtitle != null) ...[
                      const SizedBox(height: 2),
                      Text(
                        subtitle,
                        style: Theme.of(context).textTheme.bodyMedium,
                      ),
                    ],
                    if (description != null) ...[
                      const SizedBox(height: 4),
                      Text(
                        description,
                        style: Theme.of(context).textTheme.bodySmall,
                      ),
                    ]
                  ],
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
