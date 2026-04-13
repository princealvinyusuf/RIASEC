import '../core/network/api_client.dart';

class ParticipantSession {
  ParticipantSession({required this.participantId, required this.accessToken});
  final int participantId;
  final String accessToken;
}

class RiasecStatement {
  RiasecStatement({
    required this.id,
    required this.content,
    required this.category,
  });

  final int id;
  final String content;
  final String category;

  String get answerKey => '$category$id';

  factory RiasecStatement.fromJson(Map<String, dynamic> json) {
    return RiasecStatement(
      id: (json['statement_id'] as num?)?.toInt() ?? 0,
      content: (json['statement_content'] ?? '').toString(),
      category: (json['statement_category'] ?? '').toString(),
    );
  }
}

class AssessmentResult {
  AssessmentResult({
    required this.scoreId,
    required this.resultPersonality,
    required this.percentages,
  });

  final int scoreId;
  final String resultPersonality;
  final Map<String, double> percentages;

  factory AssessmentResult.fromJson(Map<String, dynamic> json) {
    final rawScores = (json['score_percentage_list'] as Map<String, dynamic>? ?? {});
    return AssessmentResult(
      scoreId: (json['score_id'] as num?)?.toInt() ?? 0,
      resultPersonality: (json['result_personality'] ?? '').toString(),
      percentages: rawScores.map(
        (key, value) => MapEntry(key, (value as num?)?.toDouble() ?? 0),
      ),
    );
  }
}

class RecommendationResult {
  RecommendationResult({
    required this.topCodes,
    required this.careerRecommendations,
    required this.trainingRecommendations,
  });

  final List<String> topCodes;
  final List<Map<String, dynamic>> careerRecommendations;
  final List<Map<String, dynamic>> trainingRecommendations;

  factory RecommendationResult.fromJson(Map<String, dynamic> json) {
    return RecommendationResult(
      topCodes: (json['top_codes'] as List<dynamic>? ?? [])
          .map((e) => e.toString())
          .toList(),
      careerRecommendations:
          (json['career_recommendations'] as List<dynamic>? ?? [])
              .whereType<Map<String, dynamic>>()
              .toList(),
      trainingRecommendations:
          (json['training_recommendations'] as List<dynamic>? ?? [])
              .whereType<Map<String, dynamic>>()
              .toList(),
    );
  }
}

class RiasecRepository {
  RiasecRepository(this._apiClient);
  final ApiClient _apiClient;

  Future<ParticipantSession> createParticipant({
    required String fullName,
    required String birthDate,
    required String phone,
    required String email,
    required String classLevel,
    required String schoolName,
    required String extracurricular,
    required String organization,
  }) async {
    final response = await _apiClient.post(
      '/participants',
      body: {
        'full_name': fullName,
        'birth_date': birthDate,
        'phone': phone,
        'email': email,
        'class_level': classLevel,
        'school_name': schoolName,
        'extracurricular': extracurricular,
        'organization': organization,
      },
    );
    final data = response['data'] as Map<String, dynamic>;
    return ParticipantSession(
      participantId: (data['participant_id'] as num?)?.toInt() ?? 0,
      accessToken: (data['access_token'] ?? '').toString(),
    );
  }

  Future<List<RiasecStatement>> getStatements() async {
    final response = await _apiClient.get('/riasec/statements');
    final items = (response['data'] as Map<String, dynamic>)['items'] as List<dynamic>? ?? [];
    return items
        .whereType<Map<String, dynamic>>()
        .map(RiasecStatement.fromJson)
        .toList();
  }

  Future<AssessmentResult> submitAssessment({
    required String accessToken,
    required Map<String, int> answers,
  }) async {
    final response = await _apiClient.post(
      '/riasec/assessments',
      accessToken: accessToken,
      body: {'answers': answers, 'can_save_data': true},
    );
    return AssessmentResult.fromJson(response['data'] as Map<String, dynamic>);
  }

  Future<AssessmentResult> getAssessment({
    required String accessToken,
    required int scoreId,
  }) async {
    final response = await _apiClient.get(
      '/riasec/assessments/$scoreId',
      accessToken: accessToken,
    );
    return AssessmentResult.fromJson(response['data'] as Map<String, dynamic>);
  }

  Future<RecommendationResult> getRecommendations({
    required String accessToken,
    required int scoreId,
  }) async {
    final response = await _apiClient.get(
      '/riasec/assessments/$scoreId/recommendations',
      accessToken: accessToken,
    );
    return RecommendationResult.fromJson(response['data'] as Map<String, dynamic>);
  }
}
