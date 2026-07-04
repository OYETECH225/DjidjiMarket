import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../models/order.dart';
import '../../services/vendor_portal_service.dart';
import '../../theme/app_theme.dart';

class VendorOrdersScreen extends StatefulWidget {
  const VendorOrdersScreen({super.key});

  @override
  State<VendorOrdersScreen> createState() => _VendorOrdersScreenState();
}

class _VendorOrdersScreenState extends State<VendorOrdersScreen> {
  late Future<List<Order>> _future;
  final _currencyFormat = NumberFormat.decimalPattern('fr');

  @override
  void initState() {
    super.initState();
    _future = context.read<VendorPortalService>().myOrders();
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

          final orders = snapshot.data ?? [];

          if (orders.isEmpty) {
            return const Center(child: Text('Aucune commande reçue pour le moment.'));
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
                  title: Text('Commande #${order.id} — ${order.clientName ?? "Client"}'),
                  subtitle: Text('${_currencyFormat.format(order.totalAmount)} XOF'),
                  trailing: Chip(
                    label: Text(order.statusLabel, style: const TextStyle(fontSize: 12)),
                    backgroundColor: AppColors.green.withValues(alpha: 0.1),
                    labelStyle: const TextStyle(color: AppColors.green, fontWeight: FontWeight.w600),
                    side: BorderSide.none,
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
