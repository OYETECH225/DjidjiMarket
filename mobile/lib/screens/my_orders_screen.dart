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
  bool _showFinal = false;
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
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Container(
              decoration: BoxDecoration(
                color: AppColors.outlineVariant.withValues(alpha: 0.2),
                borderRadius: BorderRadius.circular(999),
              ),
              padding: const EdgeInsets.all(4),
              child: Row(
                children: [
                  _tab('En cours', selected: !_showFinal, onTap: () => setState(() => _showFinal = false)),
                  _tab('Terminées', selected: _showFinal, onTap: () => setState(() => _showFinal = true)),
                ],
              ),
            ),
          ),
          Expanded(
            child: FutureBuilder<List<Order>>(
              future: _future,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (snapshot.hasError) {
                  return const Center(child: Text('Impossible de charger vos commandes.'));
                }

                final orders = (snapshot.data ?? []).where((o) => o.isFinal == _showFinal).toList();

                if (orders.isEmpty) {
                  return Center(
                    child: Text(_showFinal ? 'Aucune commande terminée pour le moment.' : 'Aucune commande en cours.'),
                  );
                }

                return ListView.separated(
                  padding: const EdgeInsets.all(16),
                  itemCount: orders.length,
                  separatorBuilder: (_, _) => const SizedBox(height: 8),
                  itemBuilder: (context, index) => _orderCard(orders[index]),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _tab(String label, {required bool selected, required VoidCallback onTap}) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 10),
          decoration: BoxDecoration(
            color: selected ? AppColors.green : Colors.transparent,
            borderRadius: BorderRadius.circular(999),
          ),
          alignment: Alignment.center,
          child: Text(
            label,
            style: TextStyle(
              color: selected ? Colors.white : AppColors.onSurfaceVariant,
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
      ),
    );
  }

  Widget _orderCard(Order order) {
    final firstItem = order.items.isNotEmpty ? order.items.first : null;

    return Card(
      child: InkWell(
        onTap: () => Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => OrderTrackingScreen(orderId: order.id)),
        ),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            children: [
              Container(
                height: 56,
                width: 56,
                decoration: BoxDecoration(
                  color: AppColors.green.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                  image: firstItem?.listingPhotoUrl != null
                      ? DecorationImage(image: NetworkImage(firstItem!.listingPhotoUrl!), fit: BoxFit.cover)
                      : null,
                ),
                child: firstItem?.listingPhotoUrl == null
                    ? const Icon(Icons.receipt_long_outlined, color: AppColors.green)
                    : null,
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      firstItem?.listingName ?? order.vendorBusinessName ?? 'Boutique',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(fontWeight: FontWeight.w600),
                    ),
                    Text('Vendu par : ${order.vendorBusinessName ?? ''}',
                        maxLines: 1, overflow: TextOverflow.ellipsis, style: TextStyle(fontSize: 12, color: Colors.grey[600])),
                    if (order.createdAt != null)
                      Text(_formatDate(order.createdAt!), style: TextStyle(fontSize: 11, color: Colors.grey[500])),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Chip(
                    label: Text(order.statusLabel, style: const TextStyle(fontSize: 11)),
                    backgroundColor: AppColors.orange.withValues(alpha: 0.1),
                    labelStyle: const TextStyle(color: AppColors.orange, fontWeight: FontWeight.w600),
                    side: BorderSide.none,
                    visualDensity: VisualDensity.compact,
                    materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  ),
                  const SizedBox(height: 4),
                  Text('${_currencyFormat.format(order.totalAmount)} XOF', style: const TextStyle(fontWeight: FontWeight.w600)),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  /// Manual formatting instead of intl's DateFormat with a locale pattern —
  /// month-name patterns need initializeDateFormatting() first, which this
  /// app never calls, so DateFormat('d MMM y', 'fr') would throw at runtime.
  String _formatDate(DateTime date) {
    final day = date.day.toString().padLeft(2, '0');
    final month = date.month.toString().padLeft(2, '0');
    final hour = date.hour.toString().padLeft(2, '0');
    final minute = date.minute.toString().padLeft(2, '0');

    return '$day/$month/${date.year} · $hour:$minute';
  }
}
