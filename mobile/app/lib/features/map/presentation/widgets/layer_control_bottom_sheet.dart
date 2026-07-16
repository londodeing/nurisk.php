import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/api/public_api_client.dart';

class LayerControlBottomSheet extends ConsumerStatefulWidget {
  final Function(List<String>) onLayersChanged;

  const LayerControlBottomSheet({Key? key, required this.onLayersChanged}) : super(key: key);

  @override
  ConsumerState<LayerControlBottomSheet> createState() => _LayerControlBottomSheetState();
}

class _LayerControlBottomSheetState extends ConsumerState<LayerControlBottomSheet> {
  bool _isLoading = true;
  List<dynamic> _groups = [];
  final Map<String, bool> _activeLayers = {};

  @override
  void initState() {
    super.initState();
    _fetchConfig();
  }

  Future<void> _fetchConfig() async {
    try {
      final dio = ref.read(publicApiClientProvider);
      final res = await dio.get('public/map/config');
      if (!mounted) return;

      if (res.statusCode == 200) {
        final data = res.data;
        final groups = data['groups'] as List<dynamic>;

        for (var group in groups) {
          for (var layer in group['layers']) {
            _activeLayers[layer['id']] = layer['default_visible'] ?? false;
          }
        }

        setState(() {
          _groups = groups;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _notifyLayersChanged() {
    final activeList = _activeLayers.entries
        .where((e) => e.value)
        .map((e) => e.key)
        .toList();
    widget.onLayersChanged(activeList);
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const SizedBox(
        height: 200,
        child: Center(child: CircularProgressIndicator()),
      );
    }

    return Container(
      padding: const EdgeInsets.symmetric(vertical: 16),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const Padding(
            padding: EdgeInsets.symmetric(horizontal: 16.0),
            child: Text(
              'Pengaturan Layer & Legend',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
            ),
          ),
          const Divider(),
          Expanded(
            child: ListView.builder(
              shrinkWrap: true,
              itemCount: _groups.length,
              itemBuilder: (context, index) {
                final group = _groups[index];
                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
                      child: Text(
                        group['name'].toString().toUpperCase(),
                        style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.grey, fontSize: 12),
                      ),
                    ),
                    ...((group['layers'] as List<dynamic>).map((layer) {
                      final colorStr = layer['color'].toString().replaceAll('#', '0xFF');
                      final color = Color(int.parse(colorStr));

                      return CheckboxListTile(
                        value: _activeLayers[layer['id']] ?? false,
                        onChanged: (val) {
                          setState(() {
                            _activeLayers[layer['id']] = val ?? false;
                          });
                          _notifyLayersChanged();
                        },
                        title: Row(
                          children: [
                            Container(
                              width: 16,
                              height: 16,
                              decoration: BoxDecoration(
                                color: color,
                                shape: BoxShape.circle,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Text(layer['name']),
                          ],
                        ),
                      );
                    }).toList()),
                  ],
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
