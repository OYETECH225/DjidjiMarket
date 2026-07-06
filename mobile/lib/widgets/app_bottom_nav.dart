import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../screens/auth/welcome_screen.dart';
import '../screens/cart_screen.dart';
import '../screens/my_orders_screen.dart';
import '../screens/profile_screen.dart';
import '../services/auth_service.dart';
import '../services/cart_service.dart';
import '../theme/app_theme.dart';

/// Bottom navigation shown only on "destination" screens (Accueil, Boutique),
/// mirroring the PWA: hidden on Panier/Checkout/Suivi de commande, which are
/// transactional screens. `currentIndex` is null on screens that aren't one
/// of the 4 nav destinations themselves (e.g. Boutique), so nothing is
/// highlighted — same behavior as the PWA's route-based active state.
class AppBottomNav extends StatelessWidget {
  final int? currentIndex;

  const AppBottomNav({super.key, this.currentIndex});

  void _onTap(BuildContext context, int index) {
    if (index == currentIndex) return;

    switch (index) {
      case 0:
        Navigator.of(context).popUntil((route) => route.isFirst);
        break;
      case 1:
        Navigator.of(context).push(MaterialPageRoute(builder: (_) => const CartScreen()));
        break;
      case 2:
        final isAuthenticated = context.read<AuthService>().isAuthenticated;
        Navigator.of(context).push(MaterialPageRoute(
          builder: (_) => isAuthenticated ? const MyOrdersScreen() : const WelcomeScreen(),
        ));
        break;
      case 3:
        final isAuthenticated = context.read<AuthService>().isAuthenticated;
        Navigator.of(context).push(MaterialPageRoute(
          builder: (_) => isAuthenticated ? const ProfileScreen() : const WelcomeScreen(),
        ));
        break;
    }
  }

  Widget _item(BuildContext context, {required int index, required IconData icon, required String label, Widget? badge}) {
    final active = index == currentIndex;
    final color = active ? AppColors.green : AppColors.onSurfaceVariant;

    return Expanded(
      child: InkWell(
        onTap: () => _onTap(context, index),
        child: Container(
          margin: const EdgeInsets.symmetric(vertical: 6, horizontal: 4),
          padding: const EdgeInsets.symmetric(vertical: 6),
          decoration: BoxDecoration(
            color: active ? AppColors.green.withValues(alpha: 0.1) : null,
            borderRadius: BorderRadius.circular(12),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              badge ?? Icon(icon, color: color, size: 22),
              const SizedBox(height: 2),
              Text(label, style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.w600)),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final cartCount = context.watch<CartService>().count;

    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(top: BorderSide(color: AppColors.outlineVariant)),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 8),
      child: SafeArea(
        top: false,
        child: Row(
          children: [
            _item(context, index: 0, icon: Icons.home_outlined, label: 'Accueil'),
            _item(
              context,
              index: 1,
              icon: Icons.shopping_bag_outlined,
              label: 'Panier',
              badge: Badge(
                label: Text('$cartCount'),
                isLabelVisible: cartCount > 0,
                backgroundColor: AppColors.orange,
                child: Icon(Icons.shopping_bag_outlined, color: currentIndex == 1 ? AppColors.green : AppColors.onSurfaceVariant, size: 22),
              ),
            ),
            _item(context, index: 2, icon: Icons.receipt_long_outlined, label: 'Commandes'),
            _item(context, index: 3, icon: Icons.person_outline, label: 'Profil'),
          ],
        ),
      ),
    );
  }
}
