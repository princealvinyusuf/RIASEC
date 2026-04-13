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
          body: Padding(
            padding: const EdgeInsets.all(16),
            child: Form(
              key: _formKey,
              child: ListView(
                children: [
                  TextFormField(
                    controller: _fullName,
                    decoration: const InputDecoration(labelText: 'Nama lengkap'),
                    validator: (value) =>
                        (value == null || value.trim().length < 3) ? 'Minimal 3 karakter' : null,
                  ),
                  TextFormField(
                    controller: _birthDate,
                    readOnly: true,
                    decoration: const InputDecoration(labelText: 'Tanggal lahir'),
                    onTap: _pickDate,
                    validator: (value) => (value == null || value.isEmpty) ? 'Wajib diisi' : null,
                  ),
                  TextFormField(
                    controller: _phone,
                    keyboardType: TextInputType.phone,
                    decoration: const InputDecoration(labelText: 'Nomor HP'),
                    validator: (value) => (value == null || value.trim().length < 10) ? 'Minimal 10 digit' : null,
                  ),
                  TextFormField(
                    controller: _email,
                    keyboardType: TextInputType.emailAddress,
                    decoration: const InputDecoration(labelText: 'Email'),
                    validator: (value) => (value == null || !value.contains('@')) ? 'Email tidak valid' : null,
                  ),
                  DropdownButtonFormField<String>(
                    initialValue: _classLevel,
                    decoration: const InputDecoration(labelText: 'Kelas'),
                    items: const [
                      DropdownMenuItem(value: '10', child: Text('10')),
                      DropdownMenuItem(value: '11', child: Text('11')),
                      DropdownMenuItem(value: '12', child: Text('12')),
                    ],
                    onChanged: (value) => setState(() => _classLevel = value ?? '10'),
                  ),
                  TextFormField(
                    controller: _schoolName,
                    decoration: const InputDecoration(labelText: 'Nama sekolah'),
                    validator: (value) =>
                        (value == null || value.trim().length < 3) ? 'Minimal 3 karakter' : null,
                  ),
                  TextFormField(
                    controller: _extracurricular,
                    decoration: const InputDecoration(labelText: 'Ekstrakurikuler'),
                    validator: (value) =>
                        (value == null || value.trim().length < 3) ? 'Minimal 3 karakter' : null,
                  ),
                  TextFormField(
                    controller: _organization,
                    decoration: const InputDecoration(labelText: 'Organisasi'),
                    validator: (value) =>
                        (value == null || value.trim().length < 3) ? 'Minimal 3 karakter' : null,
                  ),
                  const SizedBox(height: 16),
                  if (state.errorMessage != null)
                    Text(state.errorMessage!, style: const TextStyle(color: Colors.red)),
                  const SizedBox(height: 8),
                  ElevatedButton(
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
                    child: state.loading
                        ? const CircularProgressIndicator()
                        : const Text('Lanjut ke Pertanyaan'),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }
}
