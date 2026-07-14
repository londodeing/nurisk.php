import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:nurisk_mobile/core/runtime/runtime_initializer.dart';
import 'package:nurisk_mobile/core/runtime/runtime_state.dart';
import 'package:nurisk_mobile/features/auth/presentation/notifiers/auth_state_provider.dart';

class SplashScreen extends ConsumerStatefulWidget {
  const SplashScreen({super.key});

  @override
  ConsumerState<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends ConsumerState<SplashScreen> {
  bool _minTimeElapsed = false;
  bool _hasNavigated = false;

  @override
  void initState() {
    super.initState();
    Future.delayed(const Duration(milliseconds: 1500), () {
      if (mounted) {
        setState(() => _minTimeElapsed = true);
        _tryNavigate();
      }
    });
  }

  void _tryNavigate() {
    print('SPLASH: _tryNavigate() called, _hasNavigated: $_hasNavigated, _minTimeElapsed: $_minTimeElapsed');
    if (_hasNavigated) return;
    if (!_minTimeElapsed) return;

    final runtimeState = ref.read(runtimeStateProvider);
    print('SPLASH: runtimeState.status: ${runtimeState.status}');
    if (runtimeState.status != RuntimeStatus.ok) return;

    final auth = ref.read(authStateProvider);
    print('SPLASH: auth.isLoading: ${auth.isLoading}');
    if (auth.isLoading) return;

    _hasNavigated = true;
    final nav = ref.read(runtimeServicesProvider).navigation;
    print('SPLASH: NAVIGATING HOME');
    if (auth.isAuthenticated && auth.activeRole != null) {
      nav.goHome();
    } else {
      nav.goHome();
    }
  }

  @override
  Widget build(BuildContext context) {
    ref.listen<RuntimeState>(runtimeStateProvider, (_, state) {
      if (state.status == RuntimeStatus.ok && _minTimeElapsed) {
        _tryNavigate();
      }
    });
    ref.listen<AuthState>(authStateProvider, (_, auth) {
      if (!auth.isLoading && _minTimeElapsed) {
        _tryNavigate();
      }
    });

    return Scaffold(
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.shield_outlined,
              size: 80,
              color: Theme.of(context).colorScheme.primary,
            ),
            const SizedBox(height: 24),
            Text(
              'NURISK',
              style: Theme.of(context).textTheme.headlineLarge?.copyWith(
                    fontWeight: FontWeight.bold,
                    color: Theme.of(context).colorScheme.primary,
                  ),
            ),
            const SizedBox(height: 8),
            Text(
              'NU Responsif Informasi & Komunikasi',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                    color: Theme.of(context).colorScheme.onSurfaceVariant,
                  ),
            ),
            const SizedBox(height: 48),
            const CircularProgressIndicator(),
          ],
        ),
      ),
    );
  }
}
