import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/widgets/skeleton.dart';
import '../notifiers/weather_provider.dart';

class WeatherCard extends ConsumerWidget {
  const WeatherCard({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final weatherState = ref.watch(weatherProvider);

    return Card(
      margin: const EdgeInsets.all(16.0),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: weatherState.when(
          data: (weather) => Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Cuaca Terkini: ${weather.locationName}',
                style: Theme.of(context).textTheme.titleMedium,
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  weather.iconUrl.isNotEmpty
                      ? Image.network(weather.iconUrl, width: 48, height: 48, errorBuilder: (context, error, stackTrace) => const Icon(Icons.cloud, size: 48, color: Colors.grey))
                      : const Icon(Icons.cloud, size: 48, color: Colors.grey),
                  const SizedBox(width: 16),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        '${weather.temperature.toStringAsFixed(1)}°C',
                        style: Theme.of(context).textTheme.headlineMedium,
                      ),
                      Text(weather.condition),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Text(
                'Diperbarui: ${weather.updatedAt.toLocal().toString().split('.')[0]}',
                style: Theme.of(context).textTheme.bodySmall,
              ),
            ],
          ),
          loading: () => const Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Skeleton(width: 150, height: 16),
              SizedBox(height: 12),
              Row(
                children: [
                  Skeleton(width: 48, height: 48, borderRadius: BorderRadius.all(Radius.circular(24))),
                  SizedBox(width: 16),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Skeleton(width: 80, height: 24),
                      SizedBox(height: 8),
                      Skeleton(width: 120, height: 14),
                    ],
                  ),
                ],
              ),
              SizedBox(height: 12),
              Skeleton(width: 180, height: 12),
            ],
          ),
          error: (error, stack) => Column(
            children: [
              const Icon(Icons.error_outline, color: Colors.red, size: 48),
              const SizedBox(height: 8),
              const Text('Gagal memuat data cuaca.'),
              TextButton(
                onPressed: () => ref.read(weatherProvider.notifier).refresh(),
                child: const Text('Coba Lagi'),
              )
            ],
          ),
        ),
      ),
    );
  }
}
