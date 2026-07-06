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

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  Future<void> _openEditDialog(BuildContext context) async {
    final user = context.read<AuthService>().currentUser!;
    final nameController = TextEditingController(text: user.name);
    final emailController = TextEditingController(text: user.email ?? '');
    String? errorMessage;
    bool isSaving = false;

    await showDialog<void>(
      context: context,
      builder: (dialogContext) {
        return StatefulBuilder(
          builder: (dialogContext, setState) {
            return AlertDialog(
              title: const Text('Modifier mon profil'),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  TextField(
                    controller: nameController,
                    decoration: const InputDecoration(labelText: 'Nom complet'),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: emailController,
                    keyboardType: TextInputType.emailAddress,
                    decoration: const InputDecoration(labelText: 'Adresse email', hintText: 'vous@exemple.ci'),
                  ),
                  if (errorMessage != null) ...[
                    const SizedBox(height: 8),
                    Text(errorMessage!, style: const TextStyle(color: AppColors.error)),
                  ],
                ],
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.of(dialogContext).pop(),
                  child: const Text('Annuler'),
                ),
                FilledButton(
                  onPressed: isSaving
                      ? null
                      : () async {
                          setState(() {
                            isSaving = true;
                            errorMessage = null;
                          });

                          try {
                            await context.read<AuthService>().updateProfile(
                                  name: nameController.text,
                                  email: emailController.text.isEmpty ? null : emailController.text,
                                );

                            if (dialogContext.mounted) Navigator.of(dialogContext).pop();
                          } on ApiException catch (e) {
                            setState(() {
                              errorMessage = e.errorFor('name') ?? e.errorFor('email') ?? e.message;
                              isSaving = false;
                            });
                          }
                        },
                  child: isSaving
                      ? const SizedBox(height: 18, width: 18, child: CircularProgressIndicator(strokeWidth: 2))
                      : const Text('Enregistrer'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = context.watch<AuthService>().currentUser!;
    final roleLabel = _roleLabels[user.role] ?? user.role;

    return Scaffold(
      appBar: AppBar(title: const Text('Profil')),
      body: ListView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
        children: [
          Center(
            child: Stack(
              clipBehavior: Clip.none,
              children: [
                CircleAvatar(
                  radius: 44,
                  backgroundColor: AppColors.green.withValues(alpha: 0.1),
                  child: const Icon(Icons.person, color: AppColors.green, size: 48),
                ),
                Positioned(
                  right: -2,
                  bottom: -2,
                  child: GestureDetector(
                    onTap: () => _openEditDialog(context),
                    child: Container(
                      padding: const EdgeInsets.all(6),
                      decoration: const BoxDecoration(color: AppColors.green, shape: BoxShape.circle),
                      child: const Icon(Icons.edit, color: Colors.white, size: 16),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          Text(user.name, textAlign: TextAlign.center, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
          const SizedBox(height: 4),
          Text(user.phone, textAlign: TextAlign.center, style: TextStyle(color: Colors.grey[600])),
          const SizedBox(height: 8),
          Center(
            child: Chip(
              label: Text(roleLabel, style: const TextStyle(fontSize: 12)),
              backgroundColor: AppColors.green.withValues(alpha: 0.1),
              labelStyle: const TextStyle(color: AppColors.green, fontWeight: FontWeight.w600),
              side: BorderSide.none,
            ),
          ),
          const SizedBox(height: 24),
          if (user.role == 'client')
            Material(
              color: AppColors.orange,
              borderRadius: BorderRadius.circular(24),
              child: InkWell(
                borderRadius: BorderRadius.circular(24),
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const VendorOnboardingScreen()),
                ),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Row(
                    children: [
                      const Icon(Icons.storefront_outlined, color: Colors.white),
                      const SizedBox(width: 12),
                      const Expanded(
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
                      const Icon(Icons.chevron_right, color: Colors.white),
                    ],
                  ),
                ),
              ),
            ),
          const SizedBox(height: 20),
          Container(
            decoration: BoxDecoration(
              color: AppColors.background,
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: AppColors.outlineVariant),
            ),
            child: Column(
              children: [
                if (user.role == 'vendor')
                  _MenuRow(
                    icon: Icons.storefront_outlined,
                    label: 'Mon espace vendeur',
                    onTap: () => Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => const VendorDashboardScreen()),
                    ),
                  )
                else if (user.role == 'courier')
                  _MenuRow(
                    icon: Icons.two_wheeler_outlined,
                    label: 'Mon espace livreur',
                    onTap: () => Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => const CourierDashboardScreen()),
                    ),
                  ),
                _MenuRow(
                  icon: Icons.receipt_long_outlined,
                  label: 'Mes commandes',
                  showDivider: false,
                  onTap: () => Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const MyOrdersScreen()),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          Center(
            child: TextButton.icon(
              onPressed: () {
                context.read<AuthService>().logout();
                Navigator.of(context).popUntil((route) => route.isFirst);
              },
              icon: const Icon(Icons.logout, color: AppColors.error, size: 18),
              label: const Text('Se déconnecter', style: TextStyle(color: AppColors.error, fontWeight: FontWeight.w600)),
            ),
          ),
        ],
      ),
    );
  }
}

class _MenuRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;
  final bool showDivider;

  const _MenuRow({required this.icon, required this.label, required this.onTap, this.showDivider = true});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        ListTile(
          leading: Icon(icon, color: AppColors.green),
          title: Text(label, style: const TextStyle(fontWeight: FontWeight.w500)),
          trailing: const Icon(Icons.chevron_right, color: AppColors.onSurfaceVariant),
          onTap: onTap,
        ),
        if (showDivider) const Divider(height: 1, color: AppColors.outlineVariant),
      ],
    );
  }
}
