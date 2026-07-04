import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../services/api_client.dart';
import '../services/cart_service.dart';
import '../services/order_service.dart';
import '../services/payment_service.dart';
import '../theme/app_theme.dart';
import 'order_tracking_screen.dart';

const _paymentOptions = {
  'cash_on_delivery': 'Paiement à la livraison',
  'orange_money': 'Orange Money',
  'mtn_money': 'MTN Money',
  'moov_money': 'Moov Money',
  'wave': 'Wave',
};

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final _addressController = TextEditingController();
  String _provider = 'cash_on_delivery';
  bool _isLoading = false;
  String? _errorMessage;

  Future<void> _placeOrder() async {
    if (_addressController.text.isEmpty) {
      setState(() => _errorMessage = 'Adresse de livraison requise.');
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final cart = context.read<CartService>();
    final orderService = context.read<OrderService>();
    final paymentService = context.read<PaymentService>();

    try {
      final order = await orderService.create(
            vendorId: cart.vendorId!,
            items: cart.toOrderItems(),
            deliveryAddressText: _addressController.text,
          );

      await paymentService.initiate(orderId: order.id, provider: _provider);

      cart.clear();

      if (!mounted) return;
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => OrderTrackingScreen(orderId: order.id)),
      );
    } on ApiException catch (e) {
      setState(() => _errorMessage = e.errorFor('items') ?? e.message);
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartService>();
    final currencyFormat = NumberFormat.decimalPattern('fr');

    return Scaffold(
      appBar: AppBar(title: const Text('Finaliser la commande')),
      body: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  ...cart.lines.map((line) => Padding(
                        padding: const EdgeInsets.symmetric(vertical: 2),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text('${line.quantity} × ${line.listing.name}'),
                            Text('${currencyFormat.format(line.subtotal)} XOF'),
                          ],
                        ),
                      )),
                  const Divider(),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Total', style: TextStyle(fontWeight: FontWeight.bold)),
                      Text('${currencyFormat.format(cart.total)} XOF',
                          style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.green)),
                    ],
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 24),
          TextField(
            controller: _addressController,
            decoration: const InputDecoration(labelText: 'Adresse de livraison'),
          ),
          const SizedBox(height: 16),
          const Text('Mode de paiement', style: TextStyle(fontWeight: FontWeight.w600)),
          ..._paymentOptions.entries.map((entry) => RadioListTile<String>(
                value: entry.key,
                groupValue: _provider,
                title: Text(entry.value),
                activeColor: AppColors.green,
                onChanged: (value) => setState(() => _provider = value!),
              )),
          if (_errorMessage != null) ...[
            const SizedBox(height: 8),
            Text(_errorMessage!, style: const TextStyle(color: Colors.red)),
          ],
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: _isLoading ? null : _placeOrder,
            child: _isLoading
                ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                : const Text('Confirmer la commande'),
          ),
        ],
      ),
    );
  }
}
