import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../models/order.dart';
import '../services/order_service.dart';
import '../theme/app_theme.dart';
import 'order_tracking_screen.dart';

class MyOrdersScreen extends StatefulWidget {
  const MyOrdersScreen({super.key});

  @override
  State<MyOrdersScreen> createState() => _MyOrdersScreenState();
}

class _MyOrdersScreenState extends State<MyOrdersScreen> {
  late Future<List<Order>> _future;
  final _currencyFormat = NumberFormat.decimalPattern('fr');

  @override
  void initState() {
    super.initState();
    _future = context.read<OrderService>().myOrders();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Mes commandes')),
      body: FutureBuilder<List<Order>>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }

          if (snapshot.hasError) {
            return const Center(child: Text('Impossible de charger vos commandes.'));
          }

          final orders = snapshot.data ?? [];

          if (orders.isEmpty) {
            return const Center(child: Text('Vous n\'avez pas encore passé de commande.'));
          }

          return ListView.separated(
            padding: const EdgeInsets.all(16),
            itemCount: orders.length,
            separatorBuilder: (_, _) => const SizedBox(height: 8),
            itemBuilder: (context, index) {
              final order = orders[index];

              return Card(
                child: ListTile(
                  contentPadding: const EdgeInsets.all(12),
                  title: Text(order.vendorBusinessName ?? 'Boutique', style: const TextStyle(fontWeight: FontWeight.w600)),
                  subtitle: Text('${_currencyFormat.format(order.totalAmount)} XOF'),
                  trailing: Chip(
                    label: Text(order.statusLabel, style: const TextStyle(fontSize: 12)),
                    backgroundColor: AppColors.green.withValues(alpha: 0.1),
                    labelStyle: const TextStyle(color: AppColors.green, fontWeight: FontWeight.w600),
                    side: BorderSide.none,
                  ),
                  onTap: () => Navigator.of(context).push(
                    MaterialPageRoute(builder: (_) => OrderTrackingScreen(orderId: order.id)),
                  ),
                ),
              );
            },
          );
        },
      ),
    );
  }
}
