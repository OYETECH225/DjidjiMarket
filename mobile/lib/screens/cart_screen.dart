import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../services/auth_service.dart';
import '../services/cart_service.dart';
import '../theme/app_theme.dart';
import 'auth/login_screen.dart';
import 'checkout_screen.dart';

class CartScreen extends StatelessWidget {
  const CartScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartService>();
    final currencyFormat = NumberFormat.decimalPattern('fr');

    return Scaffold(
      appBar: AppBar(title: const Text('Mon panier')),
      body: cart.isEmpty
          ? const Center(child: Text('Votre panier est vide.'))
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                ...cart.lines.map((line) => Card(
                      margin: const EdgeInsets.only(bottom: 8),
                      child: ListTile(
                        title: Text(line.listing.name),
                        subtitle: line.listing.isOnFlashSale
                            ? Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Text(
                                    currencyFormat.format(line.listing.price),
                                    style: const TextStyle(decoration: TextDecoration.lineThrough),
                                  ),
                                  const SizedBox(width: 6),
                                  Text(
                                    '${currencyFormat.format(line.listing.salePrice)} XOF',
                                    style: const TextStyle(color: AppColors.orange, fontWeight: FontWeight.w600),
                                  ),
                                ],
                              )
                            : Text('${currencyFormat.format(line.listing.price)} XOF'),
                        leading: SizedBox(
                          width: 90,
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              IconButton(
                                icon: const Icon(Icons.remove_circle_outline),
                                onPressed: () => context
                                    .read<CartService>()
                                    .updateQuantity(line.listing.id, line.quantity - 1),
                              ),
                              Text('${line.quantity}'),
                              IconButton(
                                icon: const Icon(Icons.add_circle_outline),
                                onPressed: () => context
                                    .read<CartService>()
                                    .updateQuantity(line.listing.id, line.quantity + 1),
                              ),
                            ],
                          ),
                        ),
                        trailing: IconButton(
                          icon: const Icon(Icons.delete_outline),
                          onPressed: () => context.read<CartService>().remove(line.listing.id),
                        ),
                      ),
                    )),
                const Divider(),
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Total', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                      Text(
                        '${currencyFormat.format(cart.total)} XOF',
                        style: const TextStyle(
                            fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.green),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),
                ElevatedButton(
                  onPressed: () {
                    final auth = context.read<AuthService>();
                    if (!auth.isAuthenticated) {
                      Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => const LoginScreen()),
                      );
                      return;
                    }
                    Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => const CheckoutScreen()),
                    );
                  },
                  child: const Text('Passer la commande'),
                ),
              ],
            ),
    );
  }
}
