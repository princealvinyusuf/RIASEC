// This is a basic Flutter widget test.
//
// To perform an interaction with a widget in your test, use the WidgetTester
// utility in the flutter_test package. For example, you can send tap and scroll
// gestures. You can also use WidgetTester to find child widgets in the widget
// tree, read text, and verify that the values of widget properties are correct.

import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';

import 'package:riasec_mobile/main.dart';
import 'package:riasec_mobile/core/config/app_config.dart';
import 'package:riasec_mobile/core/network/api_client.dart';
import 'package:riasec_mobile/core/storage/session_store.dart';
import 'package:riasec_mobile/features/app_state.dart';
import 'package:riasec_mobile/features/riasec_repository.dart';

void main() {
  testWidgets('Shows onboarding title', (WidgetTester tester) async {
    final appState = AppState(
      repository: RiasecRepository(ApiClient(baseUrl: AppConfig.apiBaseUrl)),
      sessionStore: SessionStore(),
    );
    appState.initializing = false;

    await tester.pumpWidget(
      ChangeNotifierProvider.value(
        value: appState,
        child: const RiasecMobileApp(),
      ),
    );
    expect(find.text('Temukan Arah Karier yang Tepat'), findsOneWidget);
  });
}
