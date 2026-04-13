import 'package:flutter/foundation.dart';

import '../core/storage/session_store.dart';
import 'riasec_repository.dart';

class AppState extends ChangeNotifier {
  AppState({
    required RiasecRepository repository,
    required SessionStore sessionStore,
  })  : _repository = repository,
        _sessionStore = sessionStore;

  final RiasecRepository _repository;
  final SessionStore _sessionStore;

  bool initializing = true;
  bool loading = false;
  bool onboardingSeen = false;
  String? errorMessage;

  String? accessToken;
  int? participantId;
  int? currentScoreId;

  List<RiasecStatement> statements = [];
  AssessmentResult? assessmentResult;
  RecommendationResult? recommendationResult;

  Future<void> initialize() async {
    accessToken = await _sessionStore.getToken();
    participantId = await _sessionStore.getParticipantId();
    currentScoreId = await _sessionStore.getLastScoreId();
    onboardingSeen = await _sessionStore.getOnboardingSeen();

    if (accessToken != null && currentScoreId != null) {
      try {
        assessmentResult = await _repository.getAssessment(
          accessToken: accessToken!,
          scoreId: currentScoreId!,
        );
        recommendationResult = await _repository.getRecommendations(
          accessToken: accessToken!,
          scoreId: currentScoreId!,
        );
      } catch (_) {
        // keep app usable even if history fetch fails
      }
    }
    initializing = false;
    notifyListeners();
  }

  Future<void> markOnboardingSeen() async {
    onboardingSeen = true;
    await _sessionStore.setOnboardingSeen();
    notifyListeners();
  }

  Future<bool> registerParticipant({
    required String fullName,
    required String birthDate,
    required String phone,
    required String email,
    required String classLevel,
    required String schoolName,
    required String extracurricular,
    required String organization,
  }) async {
    loading = true;
    errorMessage = null;
    notifyListeners();

    try {
      final session = await _repository.createParticipant(
        fullName: fullName,
        birthDate: birthDate,
        phone: phone,
        email: email,
        classLevel: classLevel,
        schoolName: schoolName,
        extracurricular: extracurricular,
        organization: organization,
      );
      accessToken = session.accessToken;
      participantId = session.participantId;
      await _sessionStore.saveSession(
        accessToken: session.accessToken,
        participantId: session.participantId,
      );

      statements = await _repository.getStatements();
      loading = false;
      notifyListeners();
      return true;
    } catch (e) {
      loading = false;
      errorMessage = e.toString().replaceFirst('Exception: ', '');
      notifyListeners();
      return false;
    }
  }

  Future<bool> submitAnswers(Map<String, int> answers) async {
    if (accessToken == null) {
      errorMessage = 'Sesi tidak tersedia. Silakan isi data peserta lagi.';
      notifyListeners();
      return false;
    }

    loading = true;
    errorMessage = null;
    notifyListeners();
    try {
      assessmentResult = await _repository.submitAssessment(
        accessToken: accessToken!,
        answers: answers,
      );
      currentScoreId = assessmentResult!.scoreId;
      await _sessionStore.saveLastScoreId(currentScoreId!);
      recommendationResult = await _repository.getRecommendations(
        accessToken: accessToken!,
        scoreId: currentScoreId!,
      );
      loading = false;
      notifyListeners();
      return true;
    } catch (e) {
      loading = false;
      errorMessage = e.toString().replaceFirst('Exception: ', '');
      notifyListeners();
      return false;
    }
  }
}
