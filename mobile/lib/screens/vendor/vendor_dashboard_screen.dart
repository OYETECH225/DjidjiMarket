import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/vendor_profile.dart';
import '../../services/api_client.dart';
import '../../services/vendor_portal_service.dart';
import '../../theme/app_theme.dart';
import 'vendor_listings_screen.dart';
import 'vendor_onboarding_screen.dart';
import 'vendor_orders_screen.dart';

class VendorDashboardScreen extends StatefulWidget {
  const VendorDashboardScreen({super.key});

  @override
  State<VendorDashboardScreen> createState() => _VendorDashboardScreenState();
}

class _VendorDashboardScreenState extends State<VendorDashboardScreen> {
  late Future<VendorProfile> _future;

  @override
  void initState() {
    super.initState();
    _future = context.read<VendorPortalService>().me();
  }

  Future<void> _toggleActive(VendorProfile profile) async {
    final updated = await context.read<VendorPortalService>().setActive(!profile.isActive);
    setState(() => _future = Future.value(updated));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Ma boutique')),
      body: FutureBuilder<VendorProfile>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }

          if (snapshot.hasError || !snapshot.hasData) {
            final error = snapshot.error;
            if (error is ApiException && error.statusCode == 404) {
              return Center(
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Text('Vous n\'avez pas encore de boutique.'),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: () => Navigator.of(context).pushReplacement(
                          MaterialPageRoute(builder: (_) => const VendorOnboardingScreen()),
                        ),
                        child: const Text('Créer ma boutique'),
                      ),
                    ],
                  ),
                ),
              );
            }

            return const Center(child: Text('Profil vendeur introuvable.'));
          }

          final profile = snapshot.data!;

          return ListView(
            padding: const EdgeInsets.all(24),
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(profile.businessName,
                            style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
                        const SizedBox(height: 4),
                        Chip(
                          label: Text(vendorVerificationLabels[profile.verificationLevel] ?? ''),
                          backgroundColor: AppColors.green.withValues(alpha: 0.1),
                          labelStyle: const TextStyle(color: AppColors.green, fontWeight: FontWeight.w600),
                          side: BorderSide.none,
                        ),
                      ],
                    ),
                  ),
                  OutlinedButton(
                    onPressed: () => _toggleActive(profile),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: profile.isActive ? AppColors.green : AppColors.error,
                      side: BorderSide(color: profile.isActive ? AppColors.green : AppColors.error),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(999)),
                    ),
                    child: Text(profile.isActive ? 'Boutique visible' : 'Boutique masquée'),
                  ),
                ],
              ),
              const SizedBox(height: 24),
              Row(
                children: [
                  Expanded(
                    child: Card(
                      child: ListTile(
                        title: const Text('Catalogue'),
                        subtitle: const Text('Gérer mes articles'),
                        trailing: const Icon(Icons.chevron_right),
                        onTap: () => Navigator.of(context).push(
                          MaterialPageRoute(builder: (_) => const VendorListingsScreen()),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: Card(
                      child: ListTile(
                        title: const Text('Commandes'),
                        subtitle: const Text('Voir les commandes reçues'),
                        trailing: const Icon(Icons.chevron_right),
                        onTap: () => Navigator.of(context).push(
                          MaterialPageRoute(builder: (_) => const VendorOrdersScreen()),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),
              Text(
                'Votre page publique : djidjimarket.ci/boutique/${profile.slug}',
                style: TextStyle(color: Colors.grey[600], fontSize: 13),
              ),
            ],
          );
        },
      ),
    );
  }
}
