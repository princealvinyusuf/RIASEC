import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';

import '../app_state.dart';

class ResultScreen extends StatelessWidget {
  const ResultScreen({super.key, required this.onRestart});

  final VoidCallback onRestart;

  @override
  Widget build(BuildContext context) {
    return Consumer<AppState>(
      builder: (context, state, _) {
        final result = state.assessmentResult;
        final recommendation = state.recommendationResult;
        if (result == null) {
          return const Scaffold(body: Center(child: Text('Belum ada hasil asesmen.')));
        }

        final scores = result.percentages.entries.toList()
          ..sort((a, b) => b.value.compareTo(a.value));

        return Scaffold(
          appBar: AppBar(title: const Text('Hasil RIASEC')),
          body: Container(
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [Color(0xFFE9F8F2), Color(0xFFF4F8F6)],
              ),
            ),
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(18),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Profil Minat Dominan', style: TextStyle(fontWeight: FontWeight.w600)),
                        const SizedBox(height: 8),
                        Text(
                          result.resultPersonality,
                          style: const TextStyle(fontSize: 34, fontWeight: FontWeight.w800, letterSpacing: 2),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Distribusi Skor', style: TextStyle(fontWeight: FontWeight.w600)),
                        const SizedBox(height: 8),
                        ...scores.map(
                          (entry) => Padding(
                            padding: const EdgeInsets.only(bottom: 10),
                            child: Column(
                              children: [
                                Row(
                                  children: [
                                    Text(entry.key, style: const TextStyle(fontWeight: FontWeight.w700)),
                                    const Spacer(),
                                    Text('${entry.value.toStringAsFixed(2)}%'),
                                  ],
                                ),
                                const SizedBox(height: 5),
                                LinearProgressIndicator(
                                  value: (entry.value / 100).clamp(0, 1),
                                  minHeight: 7,
                                  borderRadius: BorderRadius.circular(99),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                if (recommendation != null) ...[
                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 6),
                    child: Text(
                      'Rekomendasi Karier',
                      style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
                    ),
                  ),
                  ...recommendation.careerRecommendations.take(5).map(
                        (item) => Padding(
                          padding: const EdgeInsets.only(bottom: 10),
                          child: Card(
                            child: ListTile(
                              onTap: () => _openExternalLink(
                                context,
                                _buildKarirhubSearchUrl(_getPrimaryKeyword(item)),
                              ),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                              minVerticalPadding: 10,
                              leading: const CircleAvatar(
                                backgroundColor: Color(0xFFD8F3E8),
                                child: Icon(Icons.work_outline, color: Color(0xFF0B8B6A)),
                              ),
                              title: Text(
                                (item['title'] ?? '-').toString(),
                                style: const TextStyle(fontWeight: FontWeight.w700),
                              ),
                              subtitle: Padding(
                                padding: const EdgeInsets.only(top: 4),
                                child: Text(
                                  '${(item['why'] ?? '').toString()}\nTap untuk lihat lowongan.',
                                  style: const TextStyle(height: 1.4),
                                ),
                              ),
                              isThreeLine: true,
                            ),
                          ),
                        ),
                      ),
                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 6),
                    child: Text(
                      'Rekomendasi Pelatihan',
                      style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
                    ),
                  ),
                  ...recommendation.trainingRecommendations.take(5).map(
                        (item) => Padding(
                          padding: const EdgeInsets.only(bottom: 10),
                          child: Card(
                            child: ListTile(
                              onTap: () => _openExternalLink(
                                context,
                                _buildSkillhubSearchUrl(_getPrimaryKeyword(item)),
                              ),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                              minVerticalPadding: 10,
                              leading: const CircleAvatar(
                                backgroundColor: Color(0xFFD8F3E8),
                                child: Icon(Icons.school_outlined, color: Color(0xFF0B8B6A)),
                              ),
                              title: Text(
                                (item['title'] ?? '-').toString(),
                                style: const TextStyle(fontWeight: FontWeight.w700),
                              ),
                              subtitle: Padding(
                                padding: const EdgeInsets.only(top: 4),
                                child: Text(
                                  '${(item['reason'] ?? '').toString()}\nTap untuk cari pelatihan.',
                                  style: const TextStyle(height: 1.4),
                                ),
                              ),
                              isThreeLine: true,
                            ),
                          ),
                        ),
                      ),
                ],
                const SizedBox(height: 20),
                ElevatedButton.icon(
                  onPressed: onRestart,
                  icon: const Icon(Icons.refresh),
                  label: const Text('Ulangi Asesmen'),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  static String _getPrimaryKeyword(Map<String, dynamic> item) {
    final rawKeyword = (item['keyword'] ?? '').toString().trim();
    if (rawKeyword.isNotEmpty) {
      return rawKeyword;
    }
    return (item['title'] ?? '').toString().trim();
  }

  static String _buildKarirhubSearchUrl(String keyword) {
    if (keyword.isEmpty) {
      return 'https://karirhub.kemnaker.go.id/lowongan-dalam-negeri/lowongan';
    }
    return 'https://karirhub.kemnaker.go.id/lowongan-dalam-negeri/lowongan?keyword=${Uri.encodeComponent(keyword)}';
  }

  static String _buildSkillhubSearchUrl(String keyword) {
    if (keyword.isEmpty) {
      return 'https://skillhub.kemnaker.go.id/pelatihan/vokasi-nasional/jadwal';
    }
    final filtersValue = 'keyword:$keyword#$keyword';
    return 'https://skillhub.kemnaker.go.id/pelatihan/vokasi-nasional/jadwal?keyword=${Uri.encodeComponent(keyword)}&filters=${Uri.encodeComponent(filtersValue)}';
  }

  static Future<void> _openExternalLink(BuildContext context, String url) async {
    final uri = Uri.tryParse(url);
    if (uri == null) {
      _showLinkError(context);
      return;
    }

    final launched = await launchUrl(uri, mode: LaunchMode.externalApplication);
    if (!launched && context.mounted) {
      _showLinkError(context);
    }
  }

  static void _showLinkError(BuildContext context) {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Gagal membuka link. Coba lagi beberapa saat.'),
      ),
    );
  }
}
