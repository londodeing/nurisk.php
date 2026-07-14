import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:go_router/go_router.dart';
import 'package:nurisk_mobile/core/router/app_router.dart';
import 'package:nurisk_mobile/core/runtime/runtime_initializer.dart';

class PublicBottomNav extends StatefulWidget {
  final StatefulNavigationShell navigationShell;

  const PublicBottomNav({super.key, required this.navigationShell});

  @override
  State<PublicBottomNav> createState() => _PublicBottomNavState();
}

class _PublicBottomNavState extends State<PublicBottomNav> {
  DateTime? _lastBackPress;

  void _onItemTapped(int index) {
    widget.navigationShell.goBranch(
      index,
      initialLocation: index == widget.navigationShell.currentIndex,
    );
  }

  void _handleBack(BuildContext context) {
    final location = GoRouterState.of(context).uri.path;
    final currentIndex = widget.navigationShell.currentIndex;

    const rootPaths = [
      RoutePaths.home,
      RoutePaths.map,
      RoutePaths.report,
      RoutePaths.resource,
      RoutePaths.profile,
    ];
    final currentRoot = rootPaths[currentIndex];

    if (location != currentRoot) {
      RuntimeServicesScope.instance.navigation.pop();
      return;
    }

    if (currentIndex != 0) {
      widget.navigationShell.goBranch(0);
      return;
    }

    final now = DateTime.now();
    if (_lastBackPress == null ||
        now.difference(_lastBackPress!) > const Duration(seconds: 2)) {
      _lastBackPress = now;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Tekan sekali lagi untuk keluar'),
          behavior: SnackBarBehavior.floating,
          duration: Duration(seconds: 2),
        ),
      );
      return;
    }

    SystemNavigator.pop();
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) {
        if (didPop) return;
        _handleBack(context);
      },
      child: Scaffold(
        body: widget.navigationShell,
        bottomNavigationBar: NavigationBar(
          selectedIndex: widget.navigationShell.currentIndex,
          onDestinationSelected: _onItemTapped,
          destinations: const [
            NavigationDestination(
              icon: Icon(Icons.dashboard_outlined),
              selectedIcon: Icon(Icons.dashboard),
              label: 'Beranda',
            ),
            NavigationDestination(
              icon: Icon(Icons.map_outlined),
              selectedIcon: Icon(Icons.map),
              label: 'Peta',
            ),
            NavigationDestination(
              icon: Icon(Icons.add_alert_outlined),
              selectedIcon: Icon(Icons.add_alert),
              label: 'Lapor',
            ),
            NavigationDestination(
              icon: Icon(Icons.info_outline),
              selectedIcon: Icon(Icons.info),
              label: 'Info',
            ),
            NavigationDestination(
              icon: Icon(Icons.person_outline),
              selectedIcon: Icon(Icons.person),
              label: 'Akun',
            ),
          ],
        ),
      ),
    );
  }
}
