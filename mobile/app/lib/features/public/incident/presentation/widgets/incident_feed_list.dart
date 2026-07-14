import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/error/dio_exception_mapper.dart';
import 'package:nurisk_mobile/core/widgets/skeleton.dart';
import '../notifiers/incident_provider.dart';
import 'incident_feed_card.dart';

class IncidentFeedList extends ConsumerWidget {
  const IncidentFeedList({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final feedStateAsync = ref.watch(incidentFeedProvider);

    return feedStateAsync.when(
      data: (feedState) {
        if (feedState.incidents.isEmpty) {
          return const Center(
            child: Padding(
              padding: EdgeInsets.all(32.0),
              child: Text('Belum ada laporan insiden tervalidasi.'),
            ),
          );
        }

        return Column(
          children: [
            ListView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: feedState.incidents.length,
              itemBuilder: (context, index) {
                return IncidentFeedCard(incident: feedState.incidents[index]);
              },
            ),
            
            if (feedState.loadMoreError != null)
              Padding(
                padding: const EdgeInsets.all(16.0),
                child: Text(
                  DioExceptionMapper.toUserMessage(feedState.loadMoreError != null ? Exception(feedState.loadMoreError!) : 'Unknown'),
                  style: const TextStyle(color: Colors.red),
                ),
              ),

            if (!feedState.isLastPage)
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 16.0),
                child: feedState.isLoadingMore
                    ? const CircularProgressIndicator()
                    : ElevatedButton(
                        onPressed: () {
                          ref.read(incidentFeedProvider.notifier).loadMore();
                        },
                        child: const Text('Muat Lebih Banyak'),
                      ),
              )
            else if (feedState.incidents.isNotEmpty)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 16.0),
                child: Text('Semua insiden telah dimuat.', style: TextStyle(color: Colors.grey)),
              )
          ],
        );
      },
      loading: () => Column(
        children: List.generate(3, (index) => const IncidentFeedCardSkeleton()),
      ),
      error: (error, stack) => Padding(
        padding: const EdgeInsets.all(16.0),
        child: Card(
          child: Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              children: [
                const Icon(Icons.error_outline, color: Colors.red),
                const SizedBox(height: 8),
                const Text('Gagal memuat umpan insiden.'),
                TextButton(
                  onPressed: () => ref.read(incidentFeedProvider.notifier).refresh(),
                  child: const Text('Coba Lagi'),
                )
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class IncidentFeedCardSkeleton extends StatelessWidget {
  const IncidentFeedCardSkeleton({super.key});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Skeleton(height: 140, width: double.infinity),
          Padding(
            padding: const EdgeInsets.all(16.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Skeleton(width: 60, height: 20),
                    Row(
                      children: [
                        Skeleton(width: 14, height: 14),
                        SizedBox(width: 4),
                        Skeleton(width: 60, height: 10),
                      ],
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                const Skeleton(width: double.infinity, height: 18),
                const SizedBox(height: 8),
                const Skeleton(width: 180, height: 18),
                const SizedBox(height: 12),
                const Row(
                  children: [
                    Skeleton(width: 14, height: 14),
                    SizedBox(width: 4),
                    Skeleton(width: 100, height: 12),
                  ],
                ),
                const SizedBox(height: 8),
                const Row(
                  children: [
                    Skeleton(width: 14, height: 14),
                    SizedBox(width: 4),
                    Skeleton(width: 140, height: 12),
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
