import 'package:flutter/material.dart';

class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key, required this.onFinish});

  final VoidCallback onFinish;

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen> {
  final PageController _controller = PageController();
  int _currentIndex = 0;

  final List<_OnboardingItem> _items = const [
    _OnboardingItem(
      imagePath: 'lib/study.png',
      title: 'Temukan Arah Karier yang Tepat',
      subtitle:
          'Kenali minat dan kepribadianmu untuk menemukan pekerjaan yang paling sesuai dengan dirimu.',
      cta: 'Mulai Sekarang',
    ),
    _OnboardingItem(
      imagePath: 'lib/puzzle.png',
      title: 'Pahami Dirimu Lebih Dalam',
      subtitle:
          'Ikuti tes singkat berbasis RIASEC untuk mengetahui tipe kepribadian kariermu secara akurat.',
      cta: 'Lanjutkan',
    ),
    _OnboardingItem(
      imagePath: 'lib/chat.png',
      title: 'Dapatkan Rekomendasi Karier',
      subtitle:
          'Temukan pekerjaan yang cocok, peluang karier, dan pengembangan diri berdasarkan hasil tesmu.',
      cta: 'Mulai Tes',
    ),
  ];

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _handleCta() {
    if (_currentIndex == _items.length - 1) {
      widget.onFinish();
      return;
    }
    _controller.nextPage(
      duration: const Duration(milliseconds: 300),
      curve: Curves.easeOutCubic,
    );
  }

  @override
  Widget build(BuildContext context) {
    final item = _items[_currentIndex];

    return Scaffold(
      appBar: AppBar(
        title: const Text('RIASEC Mobile'),
        actions: [
          TextButton(
            onPressed: widget.onFinish,
            child: const Text('Lewati'),
          ),
        ],
      ),
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Color(0xFFEAF8F2), Color(0xFFF4F8F6)],
          ),
        ),
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                Expanded(
                  child: PageView.builder(
                    controller: _controller,
                    itemCount: _items.length,
                    onPageChanged: (index) => setState(() => _currentIndex = index),
                    itemBuilder: (context, index) {
                      final page = _items[index];
                      return SingleChildScrollView(
                        child: Column(
                          children: [
                            const SizedBox(height: 10),
                            ClipRRect(
                              borderRadius: BorderRadius.circular(24),
                              child: Container(
                                color: Colors.white,
                                child: Image.asset(
                                  page.imagePath,
                                  fit: BoxFit.contain,
                                  height: 280,
                                  width: double.infinity,
                                ),
                              ),
                            ),
                            const SizedBox(height: 24),
                            Text(
                              page.title,
                              textAlign: TextAlign.center,
                              style: const TextStyle(
                                fontSize: 28,
                                fontWeight: FontWeight.w800,
                                height: 1.15,
                              ),
                            ),
                            const SizedBox(height: 12),
                            Text(
                              page.subtitle,
                              textAlign: TextAlign.center,
                              style: const TextStyle(
                                fontSize: 15,
                                color: Color(0xFF4A665D),
                                height: 1.45,
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                ),
                const SizedBox(height: 10),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: List.generate(
                    _items.length,
                    (index) => AnimatedContainer(
                      duration: const Duration(milliseconds: 220),
                      width: _currentIndex == index ? 24 : 8,
                      height: 8,
                      margin: const EdgeInsets.symmetric(horizontal: 4),
                      decoration: BoxDecoration(
                        color: _currentIndex == index
                            ? const Color(0xFF0B8B6A)
                            : const Color(0xFFBEDFD2),
                        borderRadius: BorderRadius.circular(99),
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 14),
                SizedBox(
                  width: double.infinity,
                  child: DecoratedBox(
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF109D78), Color(0xFF0B8B6A)],
                      ),
                      borderRadius: BorderRadius.circular(14),
                      boxShadow: const [
                        BoxShadow(
                          color: Color(0x330B8B6A),
                          blurRadius: 16,
                          offset: Offset(0, 8),
                        ),
                      ],
                    ),
                    child: ElevatedButton(
                      onPressed: _handleCta,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.transparent,
                        shadowColor: Colors.transparent,
                        padding: const EdgeInsets.symmetric(vertical: 15),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                      ),
                      child: Text(item.cta),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _OnboardingItem {
  const _OnboardingItem({
    required this.imagePath,
    required this.title,
    required this.subtitle,
    required this.cta,
  });

  final String imagePath;
  final String title;
  final String subtitle;
  final String cta;
}
