import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:nurisk_mobile/core/services/navigation_analytics_observer.dart';
import 'package:nurisk_mobile/features/auth/presentation/screens/splash_screen.dart';
import 'package:nurisk_mobile/features/auth/presentation/providers/auth_provider.dart';
import 'package:nurisk_mobile/features/public/dashboard/presentation/screens/public_dashboard_screen.dart';
import 'package:nurisk_mobile/features/public/dashboard/presentation/widgets/public_bottom_nav.dart';
import 'package:nurisk_mobile/core/sdui/sdui_remote_screen.dart';
import 'package:nurisk_mobile/features/public/map/presentation/screens/cop_map_screen.dart';
import 'package:nurisk_mobile/features/public/report/presentation/screens/report_wizard_screen.dart';
import 'package:nurisk_mobile/features/auth/presentation/screens/login_screen.dart';
import 'package:nurisk_mobile/features/auth/presentation/screens/register_screen.dart';
import 'package:nurisk_mobile/features/auth/presentation/screens/mandate_picker_screen.dart';
import 'package:nurisk_mobile/features/workspace/presentation/screens/workspace_screen.dart';
import 'package:nurisk_mobile/features/public/news/presentation/screens/news_list_screen.dart';
import 'package:nurisk_mobile/features/public/news/presentation/screens/news_detail_screen.dart';
import 'package:nurisk_mobile/features/public/resource/presentation/screens/resource_screen.dart';
import 'package:nurisk_mobile/features/public/report/presentation/screens/report_tracking_screen.dart';
import 'package:nurisk_mobile/features/public/report/presentation/screens/report_validation_list_screen.dart';
import 'package:nurisk_mobile/features/operasi/assessment/presentation/screens/assessment_wizard_screen.dart';
import 'package:nurisk_mobile/features/operasi/assignment/presentation/screens/trc_assignment_screen.dart';
import 'package:nurisk_mobile/features/public/report/data/models/laporan_kejadian_model.dart';
import 'package:nurisk_mobile/features/operasi/insiden/presentation/screens/insiden_list_screen.dart';
import 'package:nurisk_mobile/features/operasi/insiden/presentation/screens/insiden_detail_screen.dart';


class RoutePaths {
  static const splash = '/splash';
  static const login = '/auth/login';
  static const register = '/auth/register';
  static const mandate = '/auth/mandate';
  static const reportValidation = '/g/report-validation';
  static const trcAssignment = '/g/trc-assignment';
  static const home = '/p/home';

  static const map = '/p/map';
  static const report = '/p/report';
  static const resource = '/p/resource';
  static const profile = '/p/profile';
  static const newsList = '/p/news';
  static const newsDetail = '/p/news/:slug';
  static const incidentDetail = '/incident/:id';
  static const reportTracking = '/report/:trackingCode';
  static const reportTrackingBase = '/report';
  static const approvalDetail = '/governance/approval/:id';
  static const assessmentForm = '/assessment/:uuidInsiden';
  static const insidenList = '/g/insiden';
  static const insidenDetail = '/g/insiden/:id';

  // Route sets for guard rules
  static const _publicPrefixes = ['/p/', '/splash'];
  static const _authPrefixes = ['/auth/login', '/auth/register'];
  static const _protectedPrefixes = ['/g/', '/governance/'];

  static bool isPublic(String location) =>
      _publicPrefixes.any((p) => location == p || location.startsWith('/p/'));

  static bool isAuthPage(String location) =>
      _authPrefixes.any((p) => location.startsWith(p));

  static bool isProtected(String location) =>
      _protectedPrefixes.any((p) => location.startsWith(p));
}

class GoRouterNotifier extends ChangeNotifier {
  GoRouterNotifier(this.ref) {
    ref.listen(authProvider, (_, next) {
      debugPrint('[ROUTER_DEBUG] GoRouterNotifier fired. next.isLoading: ${next.isLoading}');
      notifyListeners();
    });
  }
  final Ref ref;
}

final goRouterNotifierProvider = Provider<GoRouterNotifier>((ref) {
  return GoRouterNotifier(ref);
});

final appRouterProvider = Provider<GoRouter>((ref) {
  final rootNavigatorKey = GlobalKey<NavigatorState>();
  final notifier = ref.watch(goRouterNotifierProvider);

  return GoRouter(
    navigatorKey: rootNavigatorKey,
    initialLocation: RoutePaths.splash,
    debugLogDiagnostics: true,
    restorationScopeId: 'nurisk',
    refreshListenable: notifier,

    observers: [NavigationAnalyticsObserver()],

    redirect: (context, state) {
      final location = state.uri.toString();
      final auth = ref.read(authProvider);

      debugPrint('[ROUTER_DEBUG] redirect called. location: $location | isLoading: ${auth.isLoading} | isAuth: ${auth.value != null}');

      if (auth.isLoading) return null;

      final isAuthenticated = auth.value != null;

      if (isAuthenticated) {
        if (RoutePaths.isAuthPage(location) || location == RoutePaths.splash) {
          debugPrint('[ROUTER_DEBUG] redirecting to home because isAuthenticated on auth page');
          return RoutePaths.home; 
        }
        return null;
      }

      if (location == RoutePaths.splash) {
        debugPrint('[ROUTER_DEBUG] redirecting to home because unauthenticated on splash');
        return RoutePaths.home; 
      }

      if (RoutePaths.isProtected(location)) {
        debugPrint('[ROUTER_DEBUG] redirecting to login because unauthenticated on protected route');
        return RoutePaths.login;
      }

      return null;
    },

    routes: [
      GoRoute(
        name: 'splash',
        path: RoutePaths.splash,
        builder: (context, state) => const SplashScreen(),
      ),

      GoRoute(
        name: 'login',
        path: RoutePaths.login,
        builder: (context, state) => const LoginScreen(),
      ),
      GoRoute(
        name: 'register',
        path: RoutePaths.register,
        builder: (context, state) => const RegisterScreen(),
      ),
      GoRoute(
        name: 'mandate',
        path: RoutePaths.mandate,
        builder: (context, state) {
          final extra = state.extra as Map<String, dynamic>?;
          return MandatePickerScreen(
            userId: extra?['userId'] ?? '',
            userName: extra?['userName'] ?? '',
            mandates: extra?['mandates'] ?? [],
          );
        },
      ),
      GoRoute(
        name: 'reportValidation',
        path: RoutePaths.reportValidation,
        builder: (context, state) => const ReportValidationListScreen(),
      ),
      GoRoute(
        name: 'trcAssignment',
        path: RoutePaths.trcAssignment,
        builder: (context, state) {
          final laporan = state.extra as LaporanKejadianModel;
          return TrcAssignmentScreen(laporan: laporan);
        },
      ),
      GoRoute(
        name: 'insidenList',
        path: RoutePaths.insidenList,
        builder: (context, state) => const InsidenListScreen(),
      ),
      GoRoute(
        name: 'insidenDetail',
        path: RoutePaths.insidenDetail,
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
          return InsidenDetailScreen(insidenId: id);
        },
      ),

      GoRoute(
        name: 'incidentList',
        path: '/incident/list',
        builder: (context, state) => const SduiRemoteScreen(
          title: 'Daftar Insiden Aktif',
          endpoint: 'public/incident/list',
        ),
      ),
      GoRoute(
        name: 'missionList',
        path: '/mission/list',
        builder: (context, state) => const SduiRemoteScreen(
          title: 'Daftar Misi Berjalan',
          endpoint: 'public/mission/list',
        ),
      ),
      GoRoute(
        name: 'incident',
        path: RoutePaths.incidentDetail,
        builder: (context, state) {
          final id = state.pathParameters['id'] ?? '';
          return SduiRemoteScreen(
            title: 'Detail Insiden #$id',
            endpoint: 'public/incident/$id/detail',
          );
        },
      ),
      GoRoute(
        name: 'reportTracking',
        path: RoutePaths.reportTracking,
        builder: (context, state) {
          final code = state.pathParameters['trackingCode'] ?? '';
          return ReportTrackingScreen(ticketId: code);
        },
      ),
      GoRoute(
        name: 'approval',
        path: RoutePaths.approvalDetail,
        builder: (context, state) {
          final id = state.pathParameters['id'] ?? '';
          return Scaffold(
            appBar: AppBar(title: Text('Approval #$id')),
            body: Center(child: Text('Approval Detail: $id')),
          );
        },
      ),
      GoRoute(
        name: 'assessmentForm',
        path: RoutePaths.assessmentForm,
        builder: (context, state) {
          final uuid = state.pathParameters['uuidInsiden'] ?? '';
          return AssessmentWizardScreen(uuidInsiden: uuid);
        },
      ),

      GoRoute(
        name: 'newsList',
        path: RoutePaths.newsList,
        builder: (context, state) => const NewsListScreen(),
        routes: [
          GoRoute(
            name: 'newsDetail',
            path: ':slug',
            builder: (context, state) {
              final slug = state.pathParameters['slug'] ?? '';
              return NewsDetailScreen(slug: slug);
            },
          ),
        ],
      ),

      StatefulShellRoute.indexedStack(
        builder: (context, state, navigationShell) {
          return PublicBottomNav(navigationShell: navigationShell);
        },
        branches: [
          StatefulShellBranch(
            routes: [
              GoRoute(
                name: 'home',
                path: RoutePaths.home,
                builder: (context, state) => const PublicDashboardScreen(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              GoRoute(
                name: 'map',
                path: RoutePaths.map,
                builder: (context, state) => const CopMapScreen(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              GoRoute(
                name: 'report',
                path: RoutePaths.report,
                builder: (context, state) => const ReportWizardScreen(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              GoRoute(
                name: 'resource',
                path: RoutePaths.resource,
                builder: (context, state) => const ResourceScreen(),
              ),
            ],
          ),
          StatefulShellBranch(
            routes: [
              GoRoute(
                name: 'profile',
                path: RoutePaths.profile,
                builder: (context, state) => const WorkspaceScreen(),
              ),
            ],
          ),
        ],
      ),
    ],
  );
});
