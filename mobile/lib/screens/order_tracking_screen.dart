import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../models/order.dart';
import '../services/order_service.dart';
import '../theme/app_theme.dart';

class OrderTrackingScreen extends StatefulWidget {
  final int orderId;

  const OrderTrackingScreen({super.key, required this.orderId});

  @override
  State<OrderTrackingScreen> createState() => _OrderTrackingScreenState();
}

class _OrderTrackingScreenState extends State<OrderTrackingScreen> {
  late Future<Order> _future;
  bool _isConfirming = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _future = context.read<OrderService>().show(widget.orderId);
  }

  Future<void> _confirmReceipt() async {
    setState(() {
      _isConfirming = true;
      _errorMessage = null;
    });

    try {
      final order = await context.read<OrderService>().confirmReceipt(widget.orderId);
      setState(() => _future = Future.value(order));
    } catch (e) {
      setState(() => _errorMessage = e.toString());
    } finally {
      if (mounted) setState(() => _isConfirming = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final currencyFormat = NumberFormat.decimalPattern('fr');

    return Scaffold(
      appBar: AppBar(title: Text('Commande #${widget.orderId}')),
      body: FutureBuilder<Order>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }

          if (snapshot.hasError || !snapshot.hasData) {
            return const Center(child: Text('Commande introuvable.'));
          }

          final order = snapshot.data!;

          return Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Chip(
                          label: Text(order.statusLabel),
                          backgroundColor: AppColors.green.withValues(alpha: 0.1),
                          labelStyle: const TextStyle(color: AppColors.green, fontWeight: FontWeight.w600),
                        ),
                        const SizedBox(height: 12),
                        ...order.items.map((item) => Padding(
                              padding: const EdgeInsets.symmetric(vertical: 2),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text('${item.quantity} × ${item.listingName ?? "Article"}'),
                                  Text('${currencyFormat.format(item.unitPrice * item.quantity)} XOF'),
                                ],
                              ),
                            )),
                        const Divider(),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('Total', style: TextStyle(fontWeight: FontWeight.bold)),
                            Text('${currencyFormat.format(order.totalAmount)} XOF',
                                style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.green)),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Text('Livraison : ${order.deliveryAddressText}',
                            style: TextStyle(color: Colors.grey[600])),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                if (_errorMessage != null)
                  Padding(
                    padding: const EdgeInsets.only(bottom: 8),
                    child: Text(_errorMessage!, style: const TextStyle(color: AppColors.error)),
                  ),
                if (order.status == 'livree')
                  ElevatedButton(
                    onPressed: _isConfirming ? null : _confirmReceipt,
                    child: _isConfirming
                        ? const SizedBox(
                            height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                        : const Text('J\'ai reçu ma commande'),
                  )
                else if (order.status == 'paiement_libere')
                  const Center(
                    child: Text('Merci ! Réception confirmée.',
                        style: TextStyle(color: AppColors.green, fontWeight: FontWeight.w600)),
                  ),
              ],
            ),
          );
        },
      ),
    );
  }
}
