import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/widgets/skeleton.dart';
import 'package:nurisk_mobile/features/public/weather/presentation/notifiers/weather_provider.dart';

class WeatherStripSection extends ConsumerWidget {
  const WeatherStripSection({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final weatherState = ref.watch(weatherProvider);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return weatherState.when(
      data: (weather) => Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
        child: Container(
          padding: const EdgeInsets.fromLTRB(16, 14, 18, 14),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            gradient: LinearGradient(
              colors: isDark
                  ? [const Color(0xFF1A3348), const Color(0xFF0F1F2E)]
                  : [const Color(0xFFE8F0FE), const Color(0xFFD4E4FC)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            boxShadow: [
              BoxShadow(
                color: (isDark ? Colors.black : const Color(0xFF2F80ED))
                    .withValues(alpha: isDark ? 0.15 : 0.06),
                blurRadius: 8,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Row(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: (isDark ? Colors.white : Colors.white)
                      .withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: weather.iconUrl.isNotEmpty
                    ? ClipRRect(
                        borderRadius: BorderRadius.circular(12),
                        child: Image.network(weather.iconUrl,
                            width: 44,
                            height: 44,
                            errorBuilder: (_, __, ___) => const Icon(
                                Icons.wb_sunny_rounded,
                                size: 28,
                                color: Color(0xFFF39C12)))
                      )
                    : const Icon(Icons.wb_sunny_rounded,
                        size: 28, color: Color(0xFFF39C12)),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text(
                          weather.temperature.toStringAsFixed(0),
                          style: TextStyle(
                            fontSize: 32,
                            fontWeight: FontWeight.w700,
                            height: 1,
                            color: isDark
                                ? Colors.white
                                : const Color(0xFF1A1D1F),
                          ),
                        ),
                        Padding(
                          padding: const EdgeInsets.only(bottom: 3, left: 2),
                          child: Text(
                            '°C',
                            style: TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w500,
                              color: isDark
                                  ? Colors.white54
                                  : Colors.black38,
                              height: 1,
                            ),
                          ),
                        ),
                      ],
                    ),
                    Text(
                      weather.condition,
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        color: isDark
                            ? Colors.white70
                            : Colors.black54,
                        height: 1.2,
                      ),
                      maxLines: 1,
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(
                  color: (isDark ? Colors.white : Colors.black)
                      .withValues(alpha: 0.06),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.location_on_rounded,
                      size: 12,
                      color: isDark ? Colors.white54 : Colors.black38,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      weather.locationName,
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: isDark
                            ? Colors.white60
                            : Colors.black45,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
      loading: () => Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
        child: _WeatherSkeleton(isDark: isDark),
      ),
      error: (_, _) => const SizedBox(height: 8),
    );
  }
}

class _WeatherSkeleton extends StatelessWidget {
  final bool isDark;
  const _WeatherSkeleton({required this.isDark});

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 72,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(16),
        color: isDark ? const Color(0xFF1B211D) : const Color(0xFFF1F3F5),
      ),
      padding: const EdgeInsets.all(16),
      child: const Row(
        children: [
          Skeleton(width: 44, height: 44,
              borderRadius: BorderRadius.all(Radius.circular(12))),
          SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Skeleton(width: 52, height: 28),
                    SizedBox(width: 4),
                    Skeleton(width: 20, height: 12),
                  ],
                ),
                SizedBox(height: 4),
                Skeleton(width: 100, height: 12),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
