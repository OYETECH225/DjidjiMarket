import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../services/auth_service.dart';
import '../theme/app_theme.dart';
import 'courier/courier_dashboard_screen.dart';
import 'my_orders_screen.dart';
import 'vendor/vendor_dashboard_screen.dart';

const _roleLabels = {
  'client': 'Client',
  'vendor': 'Vendeur',
  'courier': 'Livreur',
  'admin': 'Administrateur',
  'partner_manager': 'Gestionnaire partenaire',
};

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

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
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(user.name, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                  Text(user.phone, style: TextStyle(color: Colors.grey[600])),
                  const SizedBox(height: 8),
                  Chip(
                    label: Text(roleLabel, style: const TextStyle(fontSize: 12)),
                    backgroundColor: AppColors.green.withValues(alpha: 0.1),
                    labelStyle: const TextStyle(color: AppColors.green, fontWeight: FontWeight.w600),
                    side: BorderSide.none,
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
