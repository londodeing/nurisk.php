import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/error/dio_exception_mapper.dart';
import 'package:nurisk_mobile/core/runtime/app_lifecycle_service.dart';
import 'package:nurisk_mobile/core/runtime/runtime_initializer.dart';
import '../../data/datasources/laporan_remote_datasource.dart';
import '../../data/models/tracking_step_model.dart';
import 'dart:async';

class ReportTrackingScreen extends ConsumerStatefulWidget {
  final String ticketId;

  const ReportTrackingScreen({Key? key, required this.ticketId}) : super(key: key);

  @override
  ConsumerState<ReportTrackingScreen> createState() => _ReportTrackingScreenState();
}

class _ReportTrackingScreenState extends ConsumerState<ReportTrackingScreen> with AppLifecycleObserver {
  bool _isLoading = true;
  bool _isPolling = false;
  List<TrackingStepModel> _trackingSteps = [];
  bool _hasError = false;
  String _errorMessage = '';
  Timer? _pollingTimer;

  @override
  void initState() {
    super.initState();
    RuntimeServicesScope.instance.lifecycle.registerObserver(this);
    _fetchTracking();
    _startPolling();
  }

  void _startPolling() {
    _pollingTimer?.cancel();
    _pollingTimer = Timer.periodic(const Duration(seconds: 15), (_) {
      if (!_isLoading && mounted) {
        _fetchTracking(isPolling: true);
      }
    });
  }

  @override
  void onBackground() {
    _pollingTimer?.cancel();
    _pollingTimer = null;
  }

  @override
  void onForeground() {
    _startPolling();
    _fetchTracking(isPolling: true);
  }

  @override
  void dispose() {
    _pollingTimer?.cancel();
    RuntimeServicesScope.instance.lifecycle.unregisterObserver(this);
    super.dispose();
  }

  Future<void> _fetchTracking({bool isPolling = false}) async {
    if (isPolling) {
      _isPolling = true;
    }
    try {
      final datasource = ref.read(laporanRemoteDatasourceProvider);
      final steps = await datasource.getTracking(widget.ticketId);

      if (mounted) {
        setState(() {
          _trackingSteps = steps;
          _isLoading = false;
          _isPolling = false;
          _hasError = false;
        });
      }
    } catch (e) {
      if (mounted && !isPolling) {
        setState(() {
          _hasError = true;
          _errorMessage = DioExceptionMapper.toUserMessage(e);
          _isLoading = false;
          _isPolling = false;
        });
      }
    }
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'DITERIMA':
        return Colors.grey;
      case 'VERIFIED':
        return Colors.orange;
      case 'ASSESSMENT':
        return Colors.blue;
      case 'RESPONSE':
        return Colors.red;
      case 'RECOVERY':
        return Colors.green;
      case 'REJECTED':
        return Colors.redAccent;
      default:
        return Colors.grey;
    }
  }

  String _formatTime(DateTime? time) {
    if (time == null) return '';
    final now = DateTime.now();
    final diff = now.difference(time);

    if (diff.inMinutes < 60) {
      return '${diff.inMinutes} menit lalu';
    } else if (diff.inHours < 24) {
      return '${diff.inHours} jam lalu';
    } else {
      return '${time.day.toString().padLeft(2, '0')}/'
          '${time.month.toString().padLeft(2, '0')}/'
          '${time.year} '
          '${time.hour.toString().padLeft(2, '0')}:'
          '${time.minute.toString().padLeft(2, '0')}';
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('Pelacakan: ${widget.ticketId}'),
            if (_isPolling) ...[
              const SizedBox(width: 8),
              const SizedBox(
                width: 12,
                height: 12,
                child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
              ),
            ]
          ],
        ),
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _hasError
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.search_off, size: 64, color: Colors.grey),
                        const SizedBox(height: 16),
                        Text(
                          _errorMessage,
                          textAlign: TextAlign.center,
                          style: const TextStyle(fontSize: 16, color: Colors.grey),
                        ),
                        const SizedBox(height: 24),
                        ElevatedButton.icon(
                          onPressed: () {
                            setState(() {
                              _isLoading = true;
                              _hasError = false;
                            });
                            _fetchTracking();
                          },
                          icon: const Icon(Icons.refresh),
                          label: const Text('Coba Lagi'),
                        ),
                      ],
                    ),
                  ),
                )
              : _trackingSteps.isEmpty
                  ? const Center(child: Text('Belum ada informasi tracking'))
                  : Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: ListView.builder(
                        itemCount: _trackingSteps.length,
                        itemBuilder: (context, index) {
                          final step = _trackingSteps[index];
                          final isLast = index == _trackingSteps.length - 1;
                          final color = _getStatusColor(step.status);

                          return IntrinsicHeight(
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                Column(
                                  children: [
                                    Container(
                                      width: 24,
                                      height: 24,
                                      decoration: BoxDecoration(
                                        color: color,
                                        shape: BoxShape.circle,
                                      ),
                                      child: const Icon(Icons.check, size: 14, color: Colors.white),
                                    ),
                                    if (!isLast)
                                      Expanded(
                                        child: Container(
                                          width: 2,
                                          color: Colors.grey[300],
                                        ),
                                      ),
                                  ],
                                ),
                                const SizedBox(width: 16),
                                Expanded(
                                  child: Padding(
                                    padding: const EdgeInsets.only(bottom: 24.0),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Row(
                                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                          children: [
                                            Text(
                                              step.status,
                                              style: TextStyle(
                                                fontWeight: FontWeight.bold,
                                                fontSize: 16,
                                                color: color,
                                              ),
                                            ),
                                            Text(
                                              _formatTime(step.time),
                                              style: const TextStyle(fontSize: 12, color: Colors.grey),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 4),
                                        Text(
                                          step.description,
                                          style: const TextStyle(fontSize: 14),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
                    ),
    );
  }
}
