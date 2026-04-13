import 'package:shared_preferences/shared_preferences.dart';

class SessionStore {
  static const _tokenKey = 'access_token';
  static const _participantIdKey = 'participant_id';
  static const _lastScoreIdKey = 'last_score_id';

  Future<void> saveSession({
    required String accessToken,
    required int participantId,
  }) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, accessToken);
    await prefs.setInt(_participantIdKey, participantId);
  }

  Future<void> saveLastScoreId(int scoreId) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt(_lastScoreIdKey, scoreId);
  }

  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_tokenKey);
  }

  Future<int?> getParticipantId() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getInt(_participantIdKey);
  }

  Future<int?> getLastScoreId() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getInt(_lastScoreIdKey);
  }
}
