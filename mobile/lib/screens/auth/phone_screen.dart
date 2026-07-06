import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../services/api_client.dart';
import '../../services/auth_service.dart';
import '../../theme/app_theme.dart';
import 'verify_otp_screen.dart';

/// Entry point for both login and registration — there's no password, so
/// the only difference between the two is whether [AuthService.requestOtp]
/// reports the phone as new (see [VerifyOtpScreen]).
class PhoneScreen extends StatefulWidget {
  final String role;

  const PhoneScreen({super.key, required this.role});

  @override
  State<PhoneScreen> createState() => _PhoneScreenState();
}

class _PhoneScreenState extends State<PhoneScreen> {
  final _phoneController = TextEditingController();
  bool _isLoading = false;
  String? _errorMessage;

  Future<void> _submit() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final isNew = await context.read<AuthService>().requestOtp(_phoneController.text);

      if (!mounted) return;
      Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => VerifyOtpScreen(phone: _phoneController.text, role: widget.role, isNewUser: isNew),
        ),
      );
    } on ApiException catch (e) {
      setState(() => _errorMessage = e.errorFor('phone') ?? e.message);
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Votre numéro')),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text(
              'Entrez votre numéro pour recevoir un code de confirmation par SMS.',
              style: TextStyle(color: AppColors.onSurfaceVariant),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              decoration: const InputDecoration(labelText: 'Téléphone', hintText: '+225 07 00 00 00 00'),
            ),
            if (_errorMessage != null) ...[
              const SizedBox(height: 12),
              Text(_errorMessage!, style: const TextStyle(color: AppColors.error)),
            ],
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: _isLoading ? null : _submit,
              child: _isLoading
                  ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                  : const Text('Recevoir le code'),
            ),
          ],
        ),
      ),
    );
  }
}
