import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../services/api_client.dart';
import '../../services/vendor_portal_service.dart';
import '../../theme/app_theme.dart';
import 'vendor_dashboard_screen.dart';

class VendorOnboardingScreen extends StatefulWidget {
  const VendorOnboardingScreen({super.key});

  @override
  State<VendorOnboardingScreen> createState() => _VendorOnboardingScreenState();
}

class _VendorOnboardingScreenState extends State<VendorOnboardingScreen> {
  final _businessNameController = TextEditingController();
  final _slugController = TextEditingController();
  final _addressController = TextEditingController();
  final _descriptionController = TextEditingController();
  String _vendorType = 'boutique';
  bool _isLoading = false;
  String? _errorMessage;

  Future<void> _submit() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      await context.read<VendorPortalService>().createProfile(
            businessName: _businessNameController.text,
            vendorType: _vendorType,
            slug: _slugController.text,
            addressText: _addressController.text,
            description: _descriptionController.text,
          );

      if (!mounted) return;
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const VendorDashboardScreen()),
      );
    } on ApiException catch (e) {
      setState(() => _errorMessage = e.errorFor('business_name') ?? e.errorFor('slug') ?? e.message);
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Créer ma boutique')),
      body: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          Text(
            'Ces informations seront visibles publiquement sur votre page boutique.',
            style: TextStyle(color: Colors.grey[600]),
          ),
          const SizedBox(height: 24),
          TextField(
            controller: _businessNameController,
            decoration: const InputDecoration(labelText: 'Nom de la boutique'),
          ),
          const SizedBox(height: 16),
          DropdownButtonFormField<String>(
            initialValue: _vendorType,
            decoration: const InputDecoration(labelText: "Type d'activité"),
            items: const [
              DropdownMenuItem(value: 'boutique', child: Text('Boutique')),
              DropdownMenuItem(value: 'street_food', child: Text('Street food')),
              DropdownMenuItem(value: 'restaurant', child: Text('Restaurant')),
            ],
            onChanged: (value) => setState(() => _vendorType = value!),
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _slugController,
            decoration: const InputDecoration(labelText: 'Lien personnalisé', hintText: 'ma-boutique'),
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _addressController,
            decoration: const InputDecoration(labelText: 'Adresse', hintText: 'Quartier, ville'),
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _descriptionController,
            maxLines: 3,
            decoration: const InputDecoration(labelText: 'Description'),
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
                : const Text('Créer ma boutique'),
          ),
        ],
      ),
    );
  }
}
