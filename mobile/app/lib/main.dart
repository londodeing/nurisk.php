import 'package:flutter/material.dart';
import 'package:flutter/gestures.dart';
import 'package:flutter/rendering.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:nurisk_mobile/core/runtime/runtime_initializer.dart';
import 'package:nurisk_mobile/core/runtime/runtime_state.dart';
import 'package:nurisk_mobile/core/router/app_router.dart';
import 'package:nurisk_mobile/core/theme/nurisk_theme.dart';
import 'package:nurisk_mobile/features/auth/presentation/notifiers/auth_state_provider.dart';
import 'package:nurisk_mobile/core/sdui/sdui_registry_initializer.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await dotenv.load(fileName: '.env');
  SduiRegistryInitializer.initialize();
  
  // FORENSIC: Enable Gesture Arena, HitTest, and Callback Tracing
  debugPrintGestureArenaDiagnostics = true;
  debugPrintHitTestResults = true;
  debugPrintRecognizerCallbacksTrace = true;


  runApp(
    ProviderScope(
      child: const NuriskApp(),
    ),
  );
}

class NuriskApp extends ConsumerStatefulWidget {
  const NuriskApp({super.key});

  @override
  ConsumerState<NuriskApp> createState() => _NuriskAppState();
}

class _NuriskAppState extends ConsumerState<NuriskApp> {
  late final GoRouter _router;

  @override
  void initState() {
    super.initState();
    _router = ref.read(appRouterProvider);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _initializeRuntime();
    });
  }

  Future<void> _initializeRuntime() async {
    final state = await RuntimeInitializer.initialize(_router);
    ref.read(runtimeStateProvider.notifier).set(state);
  }

  void _dumpGestureBlockers() {
    debugPrint('\n======================================================');
    debugPrint(' FORENSIC RENDER TREE DUMP: GESTURE BLOCKERS ');
    debugPrint('======================================================');
    final renderView = RendererBinding.instance.renderView;
    _walkRenderTree(renderView, 0);
    debugPrint('======================================================\n');
  }

  void _walkRenderTree(RenderObject node, int depth) {
    if (node is RenderAbsorbPointer || node is RenderIgnorePointer || node is RenderPointerListener || node is RenderSemanticsGestureHandler) {
      final size = node.semanticBounds;
      String extra = '';
      if (node is RenderAbsorbPointer) {
        extra = 'absorbing: ${node.absorbing}';
      } else if (node is RenderIgnorePointer) {
        extra = 'ignoring: ${node.ignoring}';
      }
      debugPrint('[FORENSIC] depth:$depth ${node.runtimeType} size:$size $extra -> ${node.debugCreator}');
    }
    node.visitChildren((child) {
      _walkRenderTree(child, depth + 1);
    });
  }

  @override
  Widget build(BuildContext context) {
    ref.listen(authStateProvider, (prev, next) {
      if (prev?.isAuthenticated == true && !next.isAuthenticated) {
        _router.go(RoutePaths.home);
      }
    });

    final runtime = ref.watch(runtimeStateProvider);

    if (runtime.status == RuntimeStatus.failed) {
      return MaterialApp(
        home: Scaffold(
          body: Center(
            child: Text('Initialization failed: ${runtime.failedComponents.join(", ")}'),
          ),
        ),
      );
    }

    return MaterialApp.router(
      routerConfig: _router,
      restorationScopeId: 'nurisk',
      title: 'NURISK',
      theme: nuriskLightTheme,
      darkTheme: nuriskDarkTheme,
      themeMode: ThemeMode.system,
      debugShowCheckedModeBanner: false,
      builder: (context, child) {
        final mq = MediaQuery.of(context);
        return MediaQuery(
          data: mq.copyWith(textScaler: mq.textScaler.clamp(minScaleFactor: 1.0, maxScaleFactor: 2.0)),
          child: Listener(
            behavior: HitTestBehavior.translucent,
            onPointerDown: (event) {
              final hitTestResult = HitTestResult();
              RendererBinding.instance.hitTest(hitTestResult, event.position);
              debugPrint('\n======================================================');
              debugPrint('[FORENSIC_HIT_TEST] Pointer Down at ${event.position}');
              for (var entry in hitTestResult.path) {
                final target = entry.target;
                debugPrint('[FORENSIC_HIT_TEST] -> ${target.runtimeType} | ${target.toString()}');
              }
              debugPrint('======================================================\n');
            },
            child: child ?? const SizedBox(),
          ),
        );
      },
      localizationsDelegates: [
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      supportedLocales: [
        const Locale('id', 'ID'),
        const Locale('en', 'US'),
      ],
      locale: const Locale('id', 'ID'),
    );
  }
}
