import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../services/api_client.dart';
import '../../services/auth_service.dart';
import '../../theme/app_theme.dart';
import '../home_screen.dart';

class VerifyOtpScreen extends StatefulWidget {
  final String phone;
  final String role;
  final bool isNewUser;

  const VerifyOtpScreen({super.key, required this.phone, required this.role, required this.isNewUser});

  @override
  State<VerifyOtpScreen> createState() => _VerifyOtpScreenState();
}

class _VerifyOtpScreenState extends State<VerifyOtpScreen> {
  final _nameController = TextEditingController();
  final List<TextEditingController> _digitControllers = List.generate(6, (_) => TextEditingController());
  final List<FocusNode> _digitFocusNodes = List.generate(6, (_) => FocusNode());
  bool _isLoading = false;
  String? _errorMessage;
  String? _infoMessage;

  @override
  void dispose() {
    _nameController.dispose();
    for (final c in _digitControllers) {
      c.dispose();
    }
    for (final f in _digitFocusNodes) {
      f.dispose();
    }
    super.dispose();
  }

  String get _code => _digitControllers.map((c) => c.text).join();

  void _onDigitChanged(int index, String value) {
    if (value.isNotEmpty && index < 5) {
      _digitFocusNodes[index + 1].requestFocus();
    }
  }

  Future<void> _submit() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      await context.read<AuthService>().verifyOtp(
            phone: widget.phone,
            code: _code,
            name: widget.isNewUser ? _nameController.text : null,
            role: widget.isNewUser ? widget.role : null,
          );

      if (!mounted) return;
      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const HomeScreen()),
        (route) => false,
      );
    } on ApiException catch (e) {
      setState(() => _errorMessage = e.errorFor('code') ?? e.errorFor('name') ?? e.message);
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _resend() async {
    await context.read<AuthService>().requestOtp(widget.phone);
    setState(() => _infoMessage = 'Un nouveau code a été envoyé.');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Vérification')),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text('Nous avons envoyé un code de 6 chiffres au ${widget.phone}.',
                textAlign: TextAlign.center, style: const TextStyle(color: AppColors.onSurfaceVariant)),
            const SizedBox(height: 24),
            if (widget.isNewUser) ...[
              TextField(
                controller: _nameController,
                decoration: const InputDecoration(labelText: 'Votre nom'),
              ),
              const SizedBox(height: 16),
            ],
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: List.generate(6, (index) {
                return SizedBox(
                  width: 44,
                  height: 56,
                  child: TextField(
                    controller: _digitControllers[index],
                    focusNode: _digitFocusNodes[index],
                    keyboardType: TextInputType.number,
                    maxLength: 1,
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
                    decoration: const InputDecoration(counterText: ''),
                    onChanged: (value) => _onDigitChanged(index, value),
                  ),
                );
              }),
            ),
            if (_errorMessage != null) ...[
              const SizedBox(height: 12),
              Text(_errorMessage!, style: const TextStyle(color: AppColors.error), textAlign: TextAlign.center),
            ],
            const SizedBox(height: 20),
            ElevatedButton(
              onPressed: _isLoading ? null : _submit,
              child: _isLoading
                  ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                  : const Text('Vérifier'),
            ),
            const SizedBox(height: 12),
            TextButton(
              onPressed: _resend,
              child: Text(_infoMessage ?? 'Renvoyer le code'),
            ),
          ],
        ),
      ),
    );
  }
}
