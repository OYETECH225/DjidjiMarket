import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../services/courier_portal_service.dart';
import 'courier_dashboard_screen.dart';

class CourierOnboardingScreen extends StatefulWidget {
  const CourierOnboardingScreen({super.key});

  @override
  State<CourierOnboardingScreen> createState() => _CourierOnboardingScreenState();
}

class _CourierOnboardingScreenState extends State<CourierOnboardingScreen> {
  String _vehicleType = 'moto';
  bool _isLoading = false;

  Future<void> _submit() async {
    setState(() => _isLoading = true);

    try {
      await context.read<CourierPortalService>().createProfile(vehicleType: _vehicleType);

      if (!mounted) return;
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const CourierDashboardScreen()),
      );
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Devenir livreur')),
      body: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          Text(
            'Renseignez votre moyen de transport pour commencer à recevoir des commandes.',
            style: TextStyle(color: Colors.grey[600]),
          ),
          const SizedBox(height: 24),
          DropdownButtonFormField<String>(
            initialValue: _vehicleType,
            decoration: const InputDecoration(labelText: 'Moyen de transport'),
            items: const [
              DropdownMenuItem(value: 'moto', child: Text('Moto')),
              DropdownMenuItem(value: 'tricycle', child: Text('Tricycle')),
              DropdownMenuItem(value: 'velo', child: Text('Vélo')),
              DropdownMenuItem(value: 'pied', child: Text('À pied')),
            ],
            onChanged: (value) => setState(() => _vehicleType = value!),
          ),
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: _isLoading ? null : _submit,
            child: _isLoading
                ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                : const Text('Devenir livreur'),
          ),
        ],
      ),
    );
  }
}
