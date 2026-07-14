import 'package:flutter/material.dart';
import '../../domain/entities/incident_entity.dart';

class IncidentFeedCard extends StatelessWidget {
  final IncidentEntity incident;

  const IncidentFeedCard({super.key, required this.incident});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (incident.thumbnailUrl != null)
            Image.network(
              incident.thumbnailUrl!,
              height: 140,
              width: double.infinity,
              fit: BoxFit.cover,
              errorBuilder: (context, error, stackTrace) => Container(
                height: 140,
                color: Colors.grey.shade300,
                child: const Icon(Icons.broken_image, color: Colors.grey),
              ),
            ),
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Chip(
                      label: Text(
                        incident.category,
                        style: const TextStyle(fontSize: 10, fontWeight: FontWeight.bold),
                      ),
                      visualDensity: VisualDensity.compact,
                    ),
                    if (incident.isVerified)
                      const Row(
                        children: [
                          Icon(Icons.verified, size: 14, color: Colors.green),
                          SizedBox(width: 4),
                          Text('Tervalidasi', style: TextStyle(fontSize: 10, color: Colors.green)),
                        ],
                      ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  incident.title,
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    const Icon(Icons.location_on, size: 14, color: Colors.grey),
                    const SizedBox(width: 4),
                    Expanded(
                      child: Text(
                        incident.district,
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey.shade700),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    const Icon(Icons.access_time, size: 14, color: Colors.grey),
                    const SizedBox(width: 4),
                    Text(
                      incident.occurredAt.toLocal().toString().split('.')[0],
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(color: Colors.grey.shade700),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
