import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../models/vendor.dart';
import '../services/auth_service.dart';
import '../services/cart_service.dart';
import '../services/vendor_service.dart';
import '../theme/app_theme.dart';
import 'auth/login_screen.dart';
import 'cart_screen.dart';
import 'vendor/vendor_dashboard_screen.dart';
import 'vendor_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  late Future<List<Vendor>> _vendorsFuture;

  @override
  void initState() {
    super.initState();
    _vendorsFuture = context.read<VendorService>().list();
  }

  Future<void> _refresh() async {
    setState(() {
      _vendorsFuture = context.read<VendorService>().list();
    });
    await _vendorsFuture;
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthService>();
    final cartCount = context.watch<CartService>().count;

    return Scaffold(
      appBar: AppBar(
        title: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Image.asset('assets/images/DjidjiMarket-icone-seule.png', height: 32),
            const SizedBox(width: 8),
            RichText(
              text: const TextSpan(
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: AppColors.green),
                children: [
                  TextSpan(text: 'djidji'),
                  TextSpan(text: 'market', style: TextStyle(color: AppColors.orange)),
                ],
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: Badge(
              label: Text('$cartCount'),
              isLabelVisible: cartCount > 0,
              backgroundColor: AppColors.orange,
              child: const Icon(Icons.shopping_bag_outlined),
            ),
            onPressed: () => Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => const CartScreen()),
            ),
          ),
          if (!auth.isAuthenticated)
            IconButton(
              icon: const Icon(Icons.person_outline),
              onPressed: () => Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const LoginScreen()),
              ),
            )
          else ...[
            if (auth.currentUser?.role == 'vendor')
              IconButton(
                icon: const Icon(Icons.storefront_outlined),
                tooltip: 'Mon espace vendeur',
                onPressed: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const VendorDashboardScreen()),
                ),
              ),
            IconButton(
              icon: const Icon(Icons.logout),
              onPressed: () => context.read<AuthService>().logout(),
            ),
          ],
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _refresh,
        child: FutureBuilder<List<Vendor>>(
          future: _vendorsFuture,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }

            if (snapshot.hasError) {
              return ListView(
                children: const [
                  SizedBox(height: 120),
                  Center(child: Text('Impossible de charger les boutiques.')),
                ],
              );
            }

            final vendors = snapshot.data ?? [];

            if (vendors.isEmpty) {
              return ListView(
                children: const [
                  SizedBox(height: 120),
                  Center(child: Text('Aucune boutique disponible pour le moment.')),
                ],
              );
            }

            return ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: vendors.length,
              separatorBuilder: (_, _) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                final vendor = vendors[index];

                return Card(
                  child: ListTile(
                    contentPadding: const EdgeInsets.all(12),
                    leading: CircleAvatar(
                      radius: 24,
                      backgroundColor: AppColors.background,
                      backgroundImage: vendor.logoUrl != null ? NetworkImage(vendor.logoUrl!) : null,
                      child: vendor.logoUrl == null
                          ? const Icon(Icons.storefront, color: AppColors.green)
                          : null,
                    ),
                    title: Text(
                      vendor.businessName,
                      style: const TextStyle(fontWeight: FontWeight.w600),
                    ),
                    subtitle: Text(vendor.vendorType),
                    trailing: vendor.isVerified
                        ? const Icon(Icons.verified, color: AppColors.green, size: 20)
                        : null,
                    onTap: () => Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => VendorScreen(slug: vendor.slug)),
                    ),
                  ),
                );
              },
            );
          },
        ),
      ),
    );
  }
}
