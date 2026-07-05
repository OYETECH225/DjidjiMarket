import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/order.dart';
import '../../services/api_client.dart';
import '../../services/courier_portal_service.dart';
import 'courier_my_deliveries_screen.dart';

class CourierAvailableOrdersScreen extends StatefulWidget {
  const CourierAvailableOrdersScreen({super.key});

  @override
  State<CourierAvailableOrdersScreen> createState() => _CourierAvailableOrdersScreenState();
}

class _CourierAvailableOrdersScreenState extends State<CourierAvailableOrdersScreen> {
  late Future<List<Order>> _future;

  @override
  void initState() {
    super.initState();
    _future = context.read<CourierPortalService>().availableOrders();
  }

  Future<void> _accept(Order order) async {
    try {
      await context.read<CourierPortalService>().acceptOrder(order.id);

      if (!mounted) return;
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const CourierMyDeliveriesScreen()),
      );
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
      setState(() => _future = context.read<CourierPortalService>().availableOrders());
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Commandes disponibles')),
      body: FutureBuilder<List<Order>>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }

          if (snapshot.hasError) {
            final error = snapshot.error;
            final message = error is ApiException ? error.message : 'Impossible de charger les commandes.';
            return Center(child: Text(message));
          }

          final orders = snapshot.data ?? [];

          if (orders.isEmpty) {
            return const Center(child: Text('Aucune commande en attente pour le moment.'));
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
                  title: Text(order.vendorBusinessName ?? 'Boutique'),
                  subtitle: Text(
                    '${order.vendorAddressText ?? ""}\nLivraison : ${order.deliveryAddressText}',
                  ),
                  isThreeLine: true,
                  trailing: ElevatedButton(
                    onPressed: () => _accept(order),
                    child: const Text('Accepter'),
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
