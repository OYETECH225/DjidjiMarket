import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/courier_profile.dart';
import '../../services/api_client.dart';
import '../../services/courier_portal_service.dart';
import '../../theme/app_theme.dart';
import 'courier_available_orders_screen.dart';
import 'courier_my_deliveries_screen.dart';
import 'courier_onboarding_screen.dart';

class CourierDashboardScreen extends StatefulWidget {
  const CourierDashboardScreen({super.key});

  @override
  State<CourierDashboardScreen> createState() => _CourierDashboardScreenState();
}

class _CourierDashboardScreenState extends State<CourierDashboardScreen> {
  late Future<CourierProfile> _future;

  @override
  void initState() {
    super.initState();
    _future = context.read<CourierPortalService>().me();
  }

  Future<void> _toggleAvailability(CourierProfile profile) async {
    final updated = await context.read<CourierPortalService>().setAvailability(!profile.isAvailable);
    setState(() => _future = Future.value(updated));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Mon espace livreur')),
      body: FutureBuilder<CourierProfile>(
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
                      const Text('Vous n\'êtes pas encore livreur.'),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: () => Navigator.of(context).pushReplacement(
                          MaterialPageRoute(builder: (_) => const CourierOnboardingScreen()),
                        ),
                        child: const Text('Devenir livreur'),
                      ),
                    ],
                  ),
                ),
              );
            }

            return const Center(child: Text('Profil livreur introuvable.'));
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
                    child: Chip(
                      label: Text(
                        '${vehicleTypeLabels[profile.vehicleType]} · ${courierVerificationLabels[profile.verificationStatus]}',
                      ),
                      backgroundColor: AppColors.green.withValues(alpha: 0.1),
                      labelStyle: const TextStyle(color: AppColors.green, fontWeight: FontWeight.w600),
                      side: BorderSide.none,
                    ),
                  ),
                  OutlinedButton(
                    onPressed: () => _toggleAvailability(profile),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: profile.isAvailable ? AppColors.green : AppColors.error,
                      side: BorderSide(color: profile.isAvailable ? AppColors.green : AppColors.error),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(999)),
                    ),
                    child: Text(profile.isAvailable ? 'Disponible' : 'Indisponible'),
                  ),
                ],
              ),
              const SizedBox(height: 24),
              Card(
                child: ListTile(
                  title: const Text('Commandes disponibles'),
                  subtitle: const Text("Voir la liste d'attente"),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const CourierAvailableOrdersScreen()),
                  ),
                ),
              ),
              const SizedBox(height: 12),
              Card(
                child: ListTile(
                  title: const Text('Mes livraisons'),
                  subtitle: const Text('Livraisons en cours'),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => const CourierMyDeliveriesScreen()),
                  ),
                ),
              ),
              if (!profile.isAvailable) ...[
                const SizedBox(height: 24),
                Text(
                  "Passez-vous disponible pour voir les commandes en attente d'un livreur.",
                  textAlign: TextAlign.center,
                  style: TextStyle(color: Colors.grey[600]),
                ),
              ],
            ],
          );
        },
      ),
    );
  }
}
