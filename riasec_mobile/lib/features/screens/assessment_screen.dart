import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../app_state.dart';
import '../riasec_repository.dart';

class AssessmentScreen extends StatefulWidget {
  const AssessmentScreen({super.key, required this.onSubmitted});

  final VoidCallback onSubmitted;

  @override
  State<AssessmentScreen> createState() => _AssessmentScreenState();
}

class _AssessmentScreenState extends State<AssessmentScreen> {
  int _index = 0;
  final Map<String, int> _answers = {};

  void _next(List<RiasecStatement> statements) {
    if (_index < statements.length - 1) {
      setState(() => _index += 1);
    }
  }

  void _prev() {
    if (_index > 0) {
      setState(() => _index -= 1);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<AppState>(
      builder: (context, state, _) {
        final statements = state.statements;
        if (statements.isEmpty) {
          return const Scaffold(body: Center(child: Text('Pertanyaan belum tersedia.')));
        }
        final current = statements[_index];
        final answered = _answers[current.answerKey];
        final complete = _answers.length == statements.length;

        return Scaffold(
          appBar: AppBar(title: const Text('Pertanyaan RIASEC')),
          body: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Text('Pertanyaan ${_index + 1} dari ${statements.length}'),
                const SizedBox(height: 8),
                LinearProgressIndicator(value: (_index + 1) / statements.length),
                const SizedBox(height: 24),
                Text(current.content, style: const TextStyle(fontSize: 18)),
                const SizedBox(height: 16),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: List.generate(
                    5,
                    (i) {
                      final value = i + 1;
                      final isSelected = answered == value;
                      return ChoiceChip(
                        label: Text(_labelForValue(value)),
                        selected: isSelected,
                        onSelected: (_) {
                          setState(() {
                            _answers[current.answerKey] = value;
                          });
                        },
                      );
                    },
                  ),
                ),
                if (state.errorMessage != null)
                  Padding(
                    padding: const EdgeInsets.only(top: 8),
                    child: Text(state.errorMessage!, style: const TextStyle(color: Colors.red)),
                  ),
                const Spacer(),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: _index == 0 ? null : _prev,
                        child: const Text('Sebelumnya'),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: ElevatedButton(
                        onPressed: answered == null
                            ? null
                            : (_index == statements.length - 1
                                ? (complete && !state.loading
                                    ? () async {
                                        final success = await state.submitAnswers(_answers);
                                        if (success && mounted) {
                                          widget.onSubmitted();
                                        }
                                      }
                                    : null)
                                : () => _next(statements)),
                        child: state.loading
                            ? const SizedBox(
                                width: 18,
                                height: 18,
                                child: CircularProgressIndicator(strokeWidth: 2),
                              )
                            : Text(_index == statements.length - 1 ? 'Lihat Hasil' : 'Berikutnya'),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  String _labelForValue(int value) {
    switch (value) {
      case 1:
        return 'Sangat Tidak Suka';
      case 2:
        return 'Tidak Suka';
      case 3:
        return 'Ragu-ragu';
      case 4:
        return 'Suka';
      default:
        return 'Sangat Suka';
    }
  }
}
