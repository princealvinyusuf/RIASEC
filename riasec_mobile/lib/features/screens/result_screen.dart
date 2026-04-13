import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

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
          body: ListView(
            padding: const EdgeInsets.all(16),
            children: [
              Text(
                'Kode RIASEC: ${result.resultPersonality}',
                style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 12),
              ...scores.map(
                (entry) => ListTile(
                  contentPadding: EdgeInsets.zero,
                  title: Text(entry.key),
                  trailing: Text('${entry.value.toStringAsFixed(2)}%'),
                ),
              ),
              const SizedBox(height: 12),
              if (recommendation != null) ...[
                const Text(
                  'Rekomendasi Karier',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
                ),
                const SizedBox(height: 8),
                ...recommendation.careerRecommendations.take(5).map(
                      (item) => Card(
                        child: ListTile(
                          title: Text((item['title'] ?? '-').toString()),
                          subtitle: Text((item['why'] ?? '').toString()),
                        ),
                      ),
                    ),
                const SizedBox(height: 12),
                const Text(
                  'Rekomendasi Pelatihan',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
                ),
                const SizedBox(height: 8),
                ...recommendation.trainingRecommendations.take(5).map(
                      (item) => Card(
                        child: ListTile(
                          title: Text((item['title'] ?? '-').toString()),
                          subtitle: Text((item['reason'] ?? '').toString()),
                        ),
                      ),
                    ),
              ],
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: onRestart,
                child: const Text('Ulangi Asesmen'),
              ),
            ],
          ),
        );
      },
    );
  }
}
