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
          body: Container(
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [Color(0xFFEAF8F2), Color(0xFFF4F8F6)],
              ),
            ),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          'Pertanyaan ${_index + 1} dari ${statements.length}',
                          style: const TextStyle(fontWeight: FontWeight.w600),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: const Color(0xFFD8F3E8),
                          borderRadius: BorderRadius.circular(999),
                        ),
                        child: Text('${_answers.length}/${statements.length} terjawab'),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  LinearProgressIndicator(
                    value: (_index + 1) / statements.length,
                    minHeight: 9,
                    borderRadius: BorderRadius.circular(99),
                  ),
                  const SizedBox(height: 12),
                  Expanded(
                    child: SingleChildScrollView(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          Card(
                            elevation: 0,
                            child: Padding(
                              padding: const EdgeInsets.all(18),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'Seberapa kamu menyukai aktivitas ini?',
                                    style: TextStyle(fontWeight: FontWeight.w600),
                                  ),
                                  const SizedBox(height: 10),
                                  Text(
                                    current.content,
                                    style: const TextStyle(fontSize: 19, height: 1.4),
                                  ),
                                ],
                              ),
                            ),
                          ),
                          const SizedBox(height: 14),
                          const Text(
                            'Pilih tingkat ketertarikanmu',
                            style: TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF315A4D)),
                          ),
                          const SizedBox(height: 8),
                          ...List.generate(
                            5,
                            (i) {
                              final value = i + 1;
                              final isSelected = answered == value;
                              return Padding(
                                padding: const EdgeInsets.only(bottom: 8),
                                child: InkWell(
                                  borderRadius: BorderRadius.circular(16),
                                  onTap: () {
                                    setState(() {
                                      _answers[current.answerKey] = value;
                                    });
                                  },
                                  child: AnimatedContainer(
                                    duration: const Duration(milliseconds: 220),
                                    curve: Curves.easeOutCubic,
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                                    decoration: BoxDecoration(
                                      color: isSelected ? _bgColorForValue(value) : Colors.white,
                                      borderRadius: BorderRadius.circular(16),
                                      border: Border.all(
                                        color: isSelected ? _borderColorForValue(value) : const Color(0xFFDDE9E3),
                                        width: isSelected ? 1.5 : 1,
                                      ),
                                      boxShadow: [
                                        if (isSelected)
                                          const BoxShadow(
                                            color: Color(0x190B8B6A),
                                            blurRadius: 14,
                                            offset: Offset(0, 6),
                                          ),
                                      ],
                                    ),
                                    child: Row(
                                      children: [
                                        Container(
                                          width: 42,
                                          height: 42,
                                          decoration: BoxDecoration(
                                            color: Colors.white,
                                            shape: BoxShape.circle,
                                            border: Border.all(color: _borderColorForValue(value)),
                                          ),
                                          alignment: Alignment.center,
                                          child: Text(_emojiForValue(value), style: const TextStyle(fontSize: 20)),
                                        ),
                                        const SizedBox(width: 12),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                _labelForValue(value),
                                                style: TextStyle(
                                                  fontWeight: FontWeight.w700,
                                                  color: isSelected ? const Color(0xFF0D3D31) : const Color(0xFF2B3D37),
                                                ),
                                              ),
                                              const SizedBox(height: 2),
                                              Text(
                                                _detailForValue(value),
                                                style: const TextStyle(fontSize: 12, color: Color(0xFF5B756B)),
                                              ),
                                            ],
                                          ),
                                        ),
                                        AnimatedOpacity(
                                          duration: const Duration(milliseconds: 180),
                                          opacity: isSelected ? 1 : 0,
                                          child: const Icon(Icons.check_circle, color: Color(0xFF0B8B6A)),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              );
                            },
                          ),
                          if (state.errorMessage != null)
                            Padding(
                              padding: const EdgeInsets.only(top: 8),
                              child: Text(
                                state.errorMessage!,
                                style: const TextStyle(color: Color(0xFFB3261E), fontWeight: FontWeight.w600),
                              ),
                            ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),
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
                                  child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                                )
                              : Text(_index == statements.length - 1 ? 'Lihat Hasil' : 'Berikutnya'),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
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

  String _emojiForValue(int value) {
    switch (value) {
      case 1:
        return '😫';
      case 2:
        return '🙁';
      case 3:
        return '😐';
      case 4:
        return '🙂';
      default:
        return '😁';
    }
  }

  String _detailForValue(int value) {
    switch (value) {
      case 1:
        return 'Aktivitas ini sangat tidak cocok untukku';
      case 2:
        return 'Aku cenderung menghindari aktivitas ini';
      case 3:
        return 'Netral, bisa iya bisa tidak';
      case 4:
        return 'Aktivitas ini cukup menarik bagiku';
      default:
        return 'Aktivitas ini sangat cocok untukku';
    }
  }

  Color _bgColorForValue(int value) {
    switch (value) {
      case 1:
        return const Color(0xFFFFF0F0);
      case 2:
        return const Color(0xFFFFF7F0);
      case 3:
        return const Color(0xFFFFFCF0);
      case 4:
        return const Color(0xFFF1FAF1);
      default:
        return const Color(0xFFECFDF4);
    }
  }

  Color _borderColorForValue(int value) {
    switch (value) {
      case 1:
        return const Color(0xFFE69B9B);
      case 2:
        return const Color(0xFFE7B58B);
      case 3:
        return const Color(0xFFE5CC80);
      case 4:
        return const Color(0xFF8CC99A);
      default:
        return const Color(0xFF58B780);
    }
  }
}
