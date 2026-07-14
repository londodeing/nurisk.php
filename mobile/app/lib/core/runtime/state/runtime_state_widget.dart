import 'package:flutter/material.dart';
import 'runtime_node_state.dart';

class RuntimeStateWidget extends StatelessWidget {
  final RuntimeNodeState state;
  final Widget child;

  const RuntimeStateWidget({
    super.key,
    required this.state,
    required this.child,
  });

  @override
  Widget build(BuildContext context) {
    // --------------------------------------------------
    // 1. Visibility — not rendered at all
    // --------------------------------------------------
    if (!state.visible) {
      return const SizedBox.shrink();
    }

    Widget result = child;

    // --------------------------------------------------
    // 2. Loading — replace with skeleton
    // --------------------------------------------------
    if (state.loading) {
      result = _LoadingPlaceholder(child: child);
    }

    // --------------------------------------------------
    // 3. Enabled — block gestures (but keep visual)
    // --------------------------------------------------
    if (!state.enabled) {
      result = AbsorbPointer(
        absorbing: true,
        child: Opacity(
          opacity: 0.5,
          child: result,
        ),
      );
    }

    // --------------------------------------------------
    // 4. ReadOnly — block input (for form fields)
    // --------------------------------------------------
    if (state.readOnly) {
      result = AbsorbPointer(
        absorbing: true,
        child: result,
      );
    }

    return result;
  }
}

/// Shows a loading skeleton in place of the child.
/// Later this can be swapped for a proper shimmer/skeleton widget.
class _LoadingPlaceholder extends StatelessWidget {
  final Widget child;

  const _LoadingPlaceholder({required this.child});

  @override
  Widget build(BuildContext context) {
    return const Center(
      child: Padding(
        padding: EdgeInsets.all(24.0),
        child: CircularProgressIndicator(),
      ),
    );
  }
}
