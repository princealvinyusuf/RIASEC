import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../app_state.dart';

class PersonalInfoScreen extends StatefulWidget {
  const PersonalInfoScreen({super.key, required this.onSuccess});

  final VoidCallback onSuccess;

  @override
  State<PersonalInfoScreen> createState() => _PersonalInfoScreenState();
}

class _PersonalInfoScreenState extends State<PersonalInfoScreen> {
  final _formKey = GlobalKey<FormState>();
  final _fullName = TextEditingController();
  final _birthDate = TextEditingController();
  final _phone = TextEditingController();
  final _email = TextEditingController();
  final _schoolName = TextEditingController();
  final _extracurricular = TextEditingController();
  final _organization = TextEditingController();
  String _classLevel = '10';

  @override
  void dispose() {
    _fullName.dispose();
    _birthDate.dispose();
    _phone.dispose();
    _email.dispose();
    _schoolName.dispose();
    _extracurricular.dispose();
    _organization.dispose();
    super.dispose();
  }

  Future<void> _pickDate() async {
    final now = DateTime.now();
    final initialDate = DateTime(now.year - 16, now.month, now.day);
    final selected = await showDatePicker(
      context: context,
      firstDate: DateTime(1950),
      lastDate: now,
      initialDate: initialDate,
    );
    if (selected != null) {
      _birthDate.text = selected.toIso8601String().split('T').first;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<AppState>(
      builder: (context, state, _) {
        return Scaffold(
          appBar: AppBar(title: const Text('Data Peserta')),
          body: Container(
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [Color(0xFFEFFAF5), Color(0xFFF4F8F6)],
              ),
            ),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Form(
                key: _formKey,
                child: ListView(
                  children: [
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: const [
                            CircleAvatar(
                              radius: 20,
                              backgroundColor: Color(0xFFD8F3E8),
                              child: Icon(Icons.assignment_ind_outlined, color: Color(0xFF0B8B6A)),
                            ),
                            SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Lengkapi Data Awal',
                                    style: TextStyle(fontSize: 22, fontWeight: FontWeight.w700),
                                  ),
                                  SizedBox(height: 8),
                                  Text('Data ini digunakan untuk memulai asesmen minat kerja RIASEC.'),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 14),
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(14),
                        child: Column(
                          children: [
                            TextFormField(
                              controller: _fullName,
                              decoration: const InputDecoration(
                                labelText: 'Nama lengkap',
                                prefixIcon: Icon(Icons.person_outline),
                              ),
                              validator: (value) =>
                                  (value == null || value.trim().length < 3) ? 'Minimal 3 karakter' : null,
                            ),
                            const SizedBox(height: 12),
                            TextFormField(
                              controller: _birthDate,
                              readOnly: true,
                              decoration: const InputDecoration(
                                labelText: 'Tanggal lahir',
                                prefixIcon: Icon(Icons.cake_outlined),
                                suffixIcon: Icon(Icons.calendar_today_outlined),
                              ),
                              onTap: _pickDate,
                              validator: (value) => (value == null || value.isEmpty) ? 'Wajib diisi' : null,
                            ),
                            const SizedBox(height: 12),
                            TextFormField(
                              controller: _phone,
                              keyboardType: TextInputType.phone,
                              decoration: const InputDecoration(
                                labelText: 'Nomor HP',
                                prefixIcon: Icon(Icons.phone_outlined),
                              ),
                              validator: (value) => (value == null || value.trim().length < 10) ? 'Minimal 10 digit' : null,
                            ),
                            const SizedBox(height: 12),
                            TextFormField(
                              controller: _email,
                              keyboardType: TextInputType.emailAddress,
                              decoration: const InputDecoration(
                                labelText: 'Email',
                                prefixIcon: Icon(Icons.mail_outline),
                              ),
                              validator: (value) => (value == null || !value.contains('@')) ? 'Email tidak valid' : null,
                            ),
                            const SizedBox(height: 12),
                            DropdownButtonFormField<String>(
                              initialValue: _classLevel,
                              decoration: const InputDecoration(
                                labelText: 'Kelas',
                                prefixIcon: Icon(Icons.school_outlined),
                              ),
                              items: const [
                                DropdownMenuItem(value: '10', child: Text('10')),
                                DropdownMenuItem(value: '11', child: Text('11')),
                                DropdownMenuItem(value: '12', child: Text('12')),
                              ],
                              onChanged: (value) => setState(() => _classLevel = value ?? '10'),
                            ),
                            const SizedBox(height: 12),
                            TextFormField(
                              controller: _schoolName,
                              decoration: const InputDecoration(
                                labelText: 'Nama sekolah',
                                prefixIcon: Icon(Icons.location_city_outlined),
                              ),
                              validator: (value) =>
                                  (value == null || value.trim().length < 3) ? 'Minimal 3 karakter' : null,
                            ),
                            const SizedBox(height: 12),
                            TextFormField(
                              controller: _extracurricular,
                              decoration: const InputDecoration(
                                labelText: 'Ekstrakurikuler',
                                prefixIcon: Icon(Icons.groups_outlined),
                              ),
                              validator: (value) =>
                                  (value == null || value.trim().length < 3) ? 'Minimal 3 karakter' : null,
                            ),
                            const SizedBox(height: 12),
                            TextFormField(
                              controller: _organization,
                              decoration: const InputDecoration(
                                labelText: 'Organisasi',
                                prefixIcon: Icon(Icons.diversity_3_outlined),
                              ),
                              validator: (value) =>
                                  (value == null || value.trim().length < 3) ? 'Minimal 3 karakter' : null,
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),
                    if (state.errorMessage != null)
                      Padding(
                        padding: const EdgeInsets.only(bottom: 8),
                        child: Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFFECEC),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFFFFCCCC)),
                          ),
                          child: Text(
                            state.errorMessage!,
                            style: const TextStyle(color: Color(0xFFB3261E), fontWeight: FontWeight.w600),
                          ),
                        ),
                      ),
                    DecoratedBox(
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
                        onPressed: state.loading
                            ? null
                            : () async {
                                if (!_formKey.currentState!.validate()) {
                                  return;
                                }
                                final success = await state.registerParticipant(
                                  fullName: _fullName.text.trim(),
                                  birthDate: _birthDate.text.trim(),
                                  phone: _phone.text.trim(),
                                  email: _email.text.trim(),
                                  classLevel: _classLevel,
                                  schoolName: _schoolName.text.trim(),
                                  extracurricular: _extracurricular.text.trim(),
                                  organization: _organization.text.trim(),
                                );
                                if (success && mounted) {
                                  widget.onSuccess();
                                }
                              },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.transparent,
                          shadowColor: Colors.transparent,
                        ),
                        icon: const Icon(Icons.arrow_forward_rounded),
                        label: state.loading
                            ? const SizedBox(
                                width: 18,
                                height: 18,
                                child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                              )
                            : const Text('Lanjut ke Pertanyaan'),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        );
      },
    );
  }
}
