import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'core/config/app_config.dart';
import 'core/network/api_client.dart';
import 'core/storage/session_store.dart';
import 'features/app_state.dart';
import 'features/riasec_repository.dart';
import 'features/screens/assessment_screen.dart';
import 'features/screens/onboarding_screen.dart';
import 'features/screens/personal_info_screen.dart';
import 'features/screens/result_screen.dart';
import 'features/screens/welcome_screen.dart';

void main() {
  final appState = AppState(
    repository: RiasecRepository(ApiClient(baseUrl: AppConfig.apiBaseUrl)),
    sessionStore: SessionStore(),
  );
  runApp(
    ChangeNotifierProvider.value(
      value: appState,
      child: const RiasecMobileApp(),
    ),
  );
  appState.initialize();
}

class RiasecMobileApp extends StatelessWidget {
  const RiasecMobileApp({super.key});

  @override
  Widget build(BuildContext context) {
    final base = ThemeData(
      useMaterial3: true,
      colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF0B8B6A)),
    );
    return MaterialApp(
      title: 'RIASEC Mobile',
      debugShowCheckedModeBanner: false,
      theme: base.copyWith(
        scaffoldBackgroundColor: const Color(0xFFF4F8F6),
        appBarTheme: const AppBarTheme(
          centerTitle: true,
          backgroundColor: Colors.transparent,
          elevation: 0,
          surfaceTintColor: Colors.transparent,
          foregroundColor: Color(0xFF0D3D31),
        ),
        inputDecorationTheme: InputDecorationTheme(
          filled: true,
          fillColor: Colors.white,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(14),
            borderSide: const BorderSide(color: Color(0xFFD9E6E0)),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(14),
            borderSide: const BorderSide(color: Color(0xFFD9E6E0)),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(14),
            borderSide: const BorderSide(color: Color(0xFF0B8B6A), width: 1.4),
          ),
          contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            elevation: 0,
            backgroundColor: const Color(0xFF0B8B6A),
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 14),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          ),
        ),
        cardTheme: CardThemeData(
          color: Colors.white,
          elevation: 0,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
          margin: EdgeInsets.zero,
        ),
      ),
      home: const AppNavigator(),
    );
  }
}

class AppNavigator extends StatefulWidget {
  const AppNavigator({super.key});

  @override
  State<AppNavigator> createState() => _AppNavigatorState();
}

class _AppNavigatorState extends State<AppNavigator> {
  AppPage _page = AppPage.onboarding;
  bool _didResolveInitialPage = false;

  Future<void> _finishOnboarding(AppState state) async {
    await state.markOnboardingSeen();
    if (!mounted) {
      return;
    }
    setState(() => _page = AppPage.welcome);
  }

  @override
  Widget build(BuildContext context) {
    final state = context.watch<AppState>();
    if (state.initializing) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    if (!_didResolveInitialPage) {
      _didResolveInitialPage = true;
      if (state.assessmentResult != null) {
        _page = AppPage.result;
      } else if (state.onboardingSeen) {
        _page = AppPage.welcome;
      } else {
        _page = AppPage.onboarding;
      }
    }

    final Widget page = switch (_page) {
      AppPage.onboarding =>
        OnboardingScreen(onFinish: () => _finishOnboarding(state)),
      AppPage.welcome =>
        WelcomeScreen(onStart: () => setState(() => _page = AppPage.personalInfo)),
      AppPage.personalInfo =>
        PersonalInfoScreen(onSuccess: () => setState(() => _page = AppPage.assessment)),
      AppPage.assessment =>
        AssessmentScreen(onSubmitted: () => setState(() => _page = AppPage.result)),
      AppPage.result =>
        ResultScreen(onRestart: () => setState(() => _page = AppPage.personalInfo)),
    };
    return AnimatedSwitcher(
      duration: const Duration(milliseconds: 320),
      child: KeyedSubtree(key: ValueKey(_page), child: page),
    );
  }
}

enum AppPage { onboarding, welcome, personalInfo, assessment, result }
