import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/order.dart';
import '../../services/api_client.dart';
import '../../services/courier_portal_service.dart';
import '../../theme/app_theme.dart';

class CourierMyDeliveriesScreen extends StatefulWidget {
  const CourierMyDeliveriesScreen({super.key});

  @override
  State<CourierMyDeliveriesScreen> createState() => _CourierMyDeliveriesScreenState();
}

class _CourierMyDeliveriesScreenState extends State<CourierMyDeliveriesScreen> {
  late Future<List<Order>> _future;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _reload();
  }

  void _reload() {
    setState(() {
      _future = context.read<CourierPortalService>().myOrders().then(
            (orders) => orders
                .where((o) => !['livree', 'paiement_libere', 'annulee'].contains(o.status))
                .toList(),
          );
    });
  }

  Future<void> _advance(Order order) async {
    final next = deliveryTransitions[order.status];
    if (next == null) return;

    try {
      await context.read<CourierPortalService>().updateStatus(order.id, next);
      setState(() => _errorMessage = null);
      _reload();
    } on ApiException catch (e) {
      setState(() => _errorMessage = e.message);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Mes livraisons')),
      body: Column(
        children: [
          if (_errorMessage != null)
            Padding(
              padding: const EdgeInsets.all(12),
              child: Text(_errorMessage!, style: const TextStyle(color: AppColors.error)),
            ),
          Expanded(
            child: FutureBuilder<List<Order>>(
              future: _future,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) {
                  return const Center(child: CircularProgressIndicator());
                }

                final orders = snapshot.data ?? [];

                if (orders.isEmpty) {
                  return const Center(child: Text('Aucune livraison en cours.'));
                }

                return ListView.separated(
                  padding: const EdgeInsets.all(16),
                  itemCount: orders.length,
                  separatorBuilder: (_, _) => const SizedBox(height: 8),
                  itemBuilder: (context, index) {
                    final order = orders[index];
                    final next = deliveryTransitions[order.status];

                    return Card(
                      child: Padding(
                        padding: const EdgeInsets.all(12),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(order.vendorBusinessName ?? 'Boutique',
                                          style: const TextStyle(fontWeight: FontWeight.w600)),
                                      Text(order.vendorAddressText ?? '',
                                          style: TextStyle(color: Colors.grey[600])),
                                      Text('Livraison : ${order.deliveryAddressText}',
                                          style: TextStyle(color: Colors.grey[600])),
                                    ],
                                  ),
                                ),
                                Chip(
                                  label: Text(order.statusLabel, style: const TextStyle(fontSize: 12)),
                                  backgroundColor: AppColors.green.withValues(alpha: 0.1),
                                  labelStyle: const TextStyle(color: AppColors.green, fontWeight: FontWeight.w600),
                                  side: BorderSide.none,
                                ),
                              ],
                            ),
                            if (next != null) ...[
                              const SizedBox(height: 8),
                              ElevatedButton(
                                onPressed: () => _advance(order),
                                child: Text(orderStatusLabels[next] ?? next),
                              ),
                            ],
                          ],
                        ),
                      ),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
