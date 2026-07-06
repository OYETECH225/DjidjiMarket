import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../services/api_client.dart';
import '../services/auth_service.dart';
import '../theme/app_theme.dart';
import 'courier/courier_dashboard_screen.dart';
import 'my_orders_screen.dart';
import 'vendor/vendor_dashboard_screen.dart';
import 'vendor/vendor_onboarding_screen.dart';

const _roleLabels = {
  'client': 'Client',
  'vendor': 'Vendeur',
  'courier': 'Livreur',
  'admin': 'Administrateur',
  'partner_manager': 'Gestionnaire partenaire',
};

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  late final TextEditingController _nameController;
  late final TextEditingController _emailController;
  bool _isSaving = false;
  String? _errorMessage;
  String? _savedMessage;

  @override
  void initState() {
    super.initState();
    final user = context.read<AuthService>().currentUser!;
    _nameController = TextEditingController(text: user.name);
    _emailController = TextEditingController(text: user.email ?? '');
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    setState(() {
      _isSaving = true;
      _errorMessage = null;
      _savedMessage = null;
    });

    try {
      await context.read<AuthService>().updateProfile(
            name: _nameController.text,
            email: _emailController.text.isEmpty ? null : _emailController.text,
          );

      if (!mounted) return;
      setState(() => _savedMessage = 'Profil mis à jour.');
    } on ApiException catch (e) {
      setState(() => _errorMessage = e.errorFor('name') ?? e.errorFor('email') ?? e.message);
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthService>().currentUser!;
    final roleLabel = _roleLabels[user.role] ?? user.role;

    return Scaffold(
      appBar: AppBar(title: const Text('Profil')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 28,
                    backgroundColor: AppColors.green.withValues(alpha: 0.1),
                    child: const Icon(Icons.person, color: AppColors.green, size: 32),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(user.name, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                        Text(user.phone, style: TextStyle(color: Colors.grey[600])),
                        const SizedBox(height: 6),
                        Chip(
                          label: Text(roleLabel, style: const TextStyle(fontSize: 12)),
                          backgroundColor: AppColors.green.withValues(alpha: 0.1),
                          labelStyle: const TextStyle(color: AppColors.green, fontWeight: FontWeight.w600),
                          side: BorderSide.none,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
          if (user.role == 'client') ...[
            const SizedBox(height: 16),
            Material(
              color: AppColors.orange,
              borderRadius: BorderRadius.circular(16),
              child: InkWell(
                borderRadius: BorderRadius.circular(16),
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const VendorOnboardingScreen()),
                ),
                child: const Padding(
                  padding: EdgeInsets.all(16),
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Devenir vendeur sur DjidjiMarket',
                                style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                            SizedBox(height: 2),
                            Text('Ouvrez votre boutique dès aujourd\'hui', style: TextStyle(color: Colors.white70)),
                          ],
                        ),
                      ),
                      Icon(Icons.chevron_right, color: Colors.white),
                    ],
                  ),
                ),
              ),
            ),
          ],
          const SizedBox(height: 16),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Informations personnelles', style: TextStyle(fontWeight: FontWeight.w600)),
                  const SizedBox(height: 12),
                  TextField(
                    controller: _nameController,
                    decoration: const InputDecoration(labelText: 'Nom complet'),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: _emailController,
                    keyboardType: TextInputType.emailAddress,
                    decoration: const InputDecoration(labelText: 'Adresse email', hintText: 'vous@exemple.ci'),
                  ),
                  if (_errorMessage != null) ...[
                    const SizedBox(height: 8),
                    Text(_errorMessage!, style: const TextStyle(color: AppColors.error)),
                  ],
                  if (_savedMessage != null) ...[
                    const SizedBox(height: 8),
                    Text(_savedMessage!, style: const TextStyle(color: AppColors.green)),
                  ],
                  const SizedBox(height: 12),
                  ElevatedButton(
                    onPressed: _isSaving ? null : _save,
                    child: _isSaving
                        ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                        : const Text('Enregistrer'),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 16),
          if (user.role == 'vendor')
            Card(
              child: ListTile(
                title: const Text('Mon espace vendeur'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const VendorDashboardScreen()),
                ),
              ),
            )
          else if (user.role == 'courier')
            Card(
              child: ListTile(
                title: const Text('Mon espace livreur'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const CourierDashboardScreen()),
                ),
              ),
            ),
          Card(
            child: ListTile(
              title: const Text('Mes commandes'),
              trailing: const Icon(Icons.chevron_right),
              onTap: () => Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const MyOrdersScreen()),
              ),
            ),
          ),
          const SizedBox(height: 24),
          OutlinedButton(
            style: OutlinedButton.styleFrom(
              foregroundColor: AppColors.error,
              side: const BorderSide(color: AppColors.outlineVariant),
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(999)),
            ),
            onPressed: () {
              context.read<AuthService>().logout();
              Navigator.of(context).popUntil((route) => route.isFirst);
            },
            child: const Text('Déconnexion'),
          ),
        ],
      ),
    );
  }
}
