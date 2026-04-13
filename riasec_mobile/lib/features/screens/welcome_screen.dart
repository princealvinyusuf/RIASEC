import 'package:flutter/material.dart';

class WelcomeScreen extends StatelessWidget {
  const WelcomeScreen({super.key, required this.onStart});

  final VoidCallback onStart;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('RIASEC Mobile')),
      body: Stack(
        children: [
          Container(
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [Color(0xFFE8F7F1), Color(0xFFF4F8F6)],
              ),
            ),
          ),
          Positioned(
            top: -36,
            right: -24,
            child: _bubble(120, const Color(0x443BC38B)),
          ),
          Positioned(
            top: 68,
            left: -28,
            child: _bubble(82, const Color(0x2A1A9C70)),
          ),
          SafeArea(
            child: LayoutBuilder(
              builder: (context, constraints) {
                return SingleChildScrollView(
                  padding: const EdgeInsets.all(20),
                  child: ConstrainedBox(
                    constraints: BoxConstraints(minHeight: constraints.maxHeight - 40),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.88),
                            borderRadius: BorderRadius.circular(24),
                            border: Border.all(color: Colors.white),
                            boxShadow: const [
                              BoxShadow(
                                color: Color(0x1A0B8B6A),
                                blurRadius: 24,
                                offset: Offset(0, 12),
                              ),
                            ],
                          ),
                          padding: const EdgeInsets.all(18),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: const [
                              Row(
                                children: [
                                  CircleAvatar(
                                    radius: 16,
                                    backgroundColor: Color(0xFFD8F3E8),
                                    child: Icon(Icons.psychology_alt_outlined, color: Color(0xFF0B8B6A), size: 18),
                                  ),
                                  SizedBox(width: 10),
                                  Text(
                                    'Profiler Minat',
                                    style: TextStyle(
                                      color: Color(0xFF0B8B6A),
                                      fontWeight: FontWeight.w700,
                                    ),
                                  ),
                                ],
                              ),
                              SizedBox(height: 12),
                              Text(
                                'Asesmen Minat Kerja',
                                style: TextStyle(fontSize: 30, height: 1.1, fontWeight: FontWeight.w800),
                              ),
                              SizedBox(height: 10),
                              Text(
                                'Temukan 3 profil minat dominanmu (RIASEC), lalu lihat rekomendasi karier dan pelatihan yang paling relevan.',
                                style: TextStyle(height: 1.45, color: Color(0xFF334F45)),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 14),
                        _infoTile(
                          icon: Icons.verified_user_outlined,
                          title: 'Khusus peserta asesmen',
                          subtitle: 'Fitur admin tidak tersedia di aplikasi ini.',
                        ),
                        const SizedBox(height: 10),
                        _infoTile(
                          icon: Icons.timer_outlined,
                          title: 'Durasi cepat',
                          subtitle: 'Rata-rata pengerjaan sekitar 7-12 menit.',
                        ),
                        const SizedBox(height: 10),
                        _infoTile(
                          icon: Icons.insights_outlined,
                          title: 'Hasil langsung',
                          subtitle: 'Lihat profil RIASEC, rekomendasi karier, dan pelatihan.',
                        ),
                        SizedBox(height: constraints.maxHeight > 760 ? 28 : 16),
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
                            child: ElevatedButton.icon(
                              onPressed: onStart,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.transparent,
                                shadowColor: Colors.transparent,
                                padding: const EdgeInsets.symmetric(vertical: 15),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                              ),
                              icon: const Icon(Icons.play_circle_outline),
                              label: const Text('Mulai Asesmen'),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  static Widget _bubble(double size, Color color) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: color,
      ),
    );
  }

  static Widget _infoTile({
    required IconData icon,
    required String title,
    required String subtitle,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.9),
        borderRadius: BorderRadius.circular(16),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          CircleAvatar(
            radius: 14,
            backgroundColor: const Color(0xFFD8F3E8),
            child: Icon(icon, color: const Color(0xFF0B8B6A), size: 16),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: const TextStyle(fontWeight: FontWeight.w700)),
                const SizedBox(height: 2),
                Text(subtitle, style: const TextStyle(fontSize: 13, color: Color(0xFF4C675E))),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
