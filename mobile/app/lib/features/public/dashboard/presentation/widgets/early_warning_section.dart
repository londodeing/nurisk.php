import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/features/public/warning/presentation/notifiers/warning_provider.dart';
import 'package:nurisk_mobile/features/public/warning/domain/entities/warning_entity.dart';

class EarlyWarningSection extends ConsumerWidget {
  const EarlyWarningSection({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final warningState = ref.watch(warningProvider);

    return warningState.when(
      data: (warnings) {
        if (warnings.isEmpty) return const SizedBox.shrink();
        final top = warnings.first;
        return Padding(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
          child: _WarningCard(warning: top, count: warnings.length),
        );
      },
      loading: () => const SizedBox(height: 4),
      error: (_, _) => const SizedBox(height: 4),
    );
  }
}

class _WarningCard extends StatefulWidget {
  final WarningEntity warning;
  final int count;
  const _WarningCard({required this.warning, required this.count});

  @override
  State<_WarningCard> createState() => _WarningCardState();
}

class _WarningCardState extends State<_WarningCard>
    with SingleTickerProviderStateMixin {
  late AnimationController _pulseController;

  @override
  void initState() {
    super.initState();
    _pulseController = AnimationController(
      duration: const Duration(milliseconds: 1800),
      vsync: this,
    )..repeat(reverse: true);
  }

  @override
  void dispose() {
    _pulseController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final w = widget.warning;
    final (Color bg, Color border, IconData icon, String severityLabel) = switch (w.severity) {
      WarningSeverity.critical => (
        const Color(0xFF3A0D0D),
        const Color(0xFFE74C3C),
        Icons.gpp_bad_rounded,
        'KRITIS',
      ),
      WarningSeverity.warning => (
        const Color(0xFF3D2910),
        const Color(0xFFF39C12),
        Icons.warning_amber_rounded,
        'SIAGA',
      ),
      WarningSeverity.info => (
        const Color(0xFF0D2137),
        const Color(0xFF3498DB),
        Icons.info_rounded,
        'INFO',
      ),
    };

    return AnimatedBuilder(
      animation: _pulseController,
      builder: (context, child) {
        final pulse = _pulseController.value;
        return Container(
          clipBehavior: Clip.antiAlias,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            gradient: LinearGradient(
              colors: [bg, bg.withValues(alpha: 0.85)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            border: Border.all(
              color: border.withValues(alpha: 0.25 + pulse * 0.55),
              width: 0.5,
            ),
            boxShadow: [
              BoxShadow(
                color: border.withValues(alpha: pulse * 0.2),
                blurRadius: 10,
                spreadRadius: 1,
              ),
            ],
          ),
          child: Padding(
            padding: const EdgeInsets.fromLTRB(12, 10, 12, 10),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 24,
                  height: 24,
                  decoration: BoxDecoration(
                    color: border.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Icon(icon, color: border, size: 14),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Text(
                            w.source,
                            style: TextStyle(
                              fontWeight: FontWeight.w700,
                              color: Colors.white.withValues(alpha: 0.9),
                              fontSize: 12,
                            ),
                          ),
                          const SizedBox(width: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                            decoration: BoxDecoration(
                              color: border.withValues(alpha: 0.25),
                              borderRadius: BorderRadius.circular(3),
                            ),
                            child: Text(
                              severityLabel,
                              style: TextStyle(
                                fontSize: 8,
                                fontWeight: FontWeight.w700,
                                color: border,
                                letterSpacing: 0.8,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 2),
                      Text(
                        w.title,
                        style: const TextStyle(
                          fontWeight: FontWeight.w600,
                          color: Colors.white,
                          fontSize: 13,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 1),
                      Text(
                        w.description,
                        style: TextStyle(
                          color: Colors.white.withValues(alpha: 0.7),
                          fontSize: 11,
                          height: 1.3,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                if (widget.count > 1)
                  Container(
                    margin: const EdgeInsets.only(left: 8),
                    padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                    decoration: BoxDecoration(
                      color: border.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      '+${widget.count - 1}',
                      style: TextStyle(
                        color: border,
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
              ],
            ),
          ),
        );
      },
    );
  }
}
