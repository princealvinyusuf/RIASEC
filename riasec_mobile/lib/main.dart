import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'core/config/app_config.dart';
import 'core/network/api_client.dart';
import 'core/storage/session_store.dart';
import 'features/app_state.dart';
import 'features/riasec_repository.dart';
import 'features/screens/assessment_screen.dart';
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
    return MaterialApp(
      title: 'RIASEC Mobile',
      theme: ThemeData(colorScheme: ColorScheme.fromSeed(seedColor: Colors.green)),
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
  AppPage _page = AppPage.welcome;

  @override
  Widget build(BuildContext context) {
    final state = context.watch<AppState>();
    if (state.initializing) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }
    if (state.assessmentResult != null && _page == AppPage.welcome) {
      _page = AppPage.result;
    }

    switch (_page) {
      case AppPage.welcome:
        return WelcomeScreen(onStart: () => setState(() => _page = AppPage.personalInfo));
      case AppPage.personalInfo:
        return PersonalInfoScreen(onSuccess: () => setState(() => _page = AppPage.assessment));
      case AppPage.assessment:
        return AssessmentScreen(onSubmitted: () => setState(() => _page = AppPage.result));
      case AppPage.result:
        return ResultScreen(onRestart: () => setState(() => _page = AppPage.personalInfo));
    }
  }
}

enum AppPage { welcome, personalInfo, assessment, result }
