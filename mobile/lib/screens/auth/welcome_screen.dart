import 'package:flutter/material.dart';

import '../../theme/app_theme.dart';
import 'phone_screen.dart';

class WelcomeScreen extends StatelessWidget {
  const WelcomeScreen({super.key});

  void _continueAs(BuildContext context, String role) {
    Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => PhoneScreen(role: role)),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            children: [
              const Spacer(flex: 2),
              Image.asset('assets/images/DjidjiMarket-icone-seule.png', height: 64),
              const SizedBox(height: 16),
              RichText(
                text: const TextSpan(
                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 24, color: AppColors.green),
                  children: [
                    TextSpan(text: 'djidji'),
                    TextSpan(text: 'market', style: TextStyle(color: AppColors.orange)),
                  ],
                ),
              ),
              const SizedBox(height: 24),
              const Text('Bienvenue', style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              const Text(
                'Le vrai marché, en toute confiance',
                textAlign: TextAlign.center,
                style: TextStyle(color: AppColors.onSurfaceVariant),
              ),
              const Spacer(flex: 3),
              ElevatedButton(
                onPressed: () => _continueAs(context, 'client'),
                child: const Text('Continuer en tant que client'),
              ),
              const SizedBox(height: 12),
              OutlinedButton(
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppColors.green,
                  side: const BorderSide(color: AppColors.green),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(999)),
                ),
                onPressed: () => _continueAs(context, 'vendor'),
                child: const Text('Je suis vendeur'),
              ),
              const SizedBox(height: 16),
              TextButton(
                onPressed: () => _continueAs(context, 'courier'),
                child: const Text('Je suis livreur'),
              ),
              const Spacer(),
            ],
          ),
        ),
      ),
    );
  }
}
