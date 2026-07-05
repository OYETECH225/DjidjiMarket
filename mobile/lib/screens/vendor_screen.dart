import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../models/listing.dart';
import '../models/vendor.dart';
import '../services/cart_service.dart';
import '../services/vendor_service.dart';
import '../theme/app_theme.dart';
import '../widgets/app_bottom_nav.dart';
import 'cart_screen.dart';

class VendorScreen extends StatefulWidget {
  final String slug;

  const VendorScreen({super.key, required this.slug});

  @override
  State<VendorScreen> createState() => _VendorScreenState();
}

class _VendorScreenState extends State<VendorScreen> {
  late Future<(Vendor, List<Listing>)> _future;
  final _currencyFormat = NumberFormat.decimalPattern('fr');

  @override
  void initState() {
    super.initState();
    _future = _load();
  }

  Future<(Vendor, List<Listing>)> _load() async {
    final vendorService = context.read<VendorService>();
    final vendor = await vendorService.show(widget.slug);
    final listings = await vendorService.listings(vendor.id);

    return (vendor, listings);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Boutique')),
      bottomNavigationBar: const AppBottomNav(),
      body: FutureBuilder<(Vendor, List<Listing>)>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }

          if (snapshot.hasError || !snapshot.hasData) {
            return const Center(child: Text('Boutique introuvable.'));
          }

          final (vendor, listings) = snapshot.data!;

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Row(
                    children: [
                      CircleAvatar(
                        radius: 28,
                        backgroundColor: AppColors.background,
                        backgroundImage: vendor.logoUrl != null ? NetworkImage(vendor.logoUrl!) : null,
                        child: vendor.logoUrl == null
                            ? const Icon(Icons.storefront, color: AppColors.green)
                            : null,
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(vendor.businessName,
                                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                            if (vendor.addressText != null)
                              Text(vendor.addressText!, style: TextStyle(color: Colors.grey[600])),
                            if (vendor.isVerified)
                              const Padding(
                                padding: EdgeInsets.only(top: 4),
                                child: Text('✓ Vérifié', style: TextStyle(color: AppColors.green)),
                              ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
              if (listings.isEmpty)
                const Padding(
                  padding: EdgeInsets.only(top: 40),
                  child: Center(child: Text('Cette boutique n\'a pas encore de produits.')),
                )
              else
                ...listings.map((listing) => Card(
                      margin: const EdgeInsets.only(bottom: 12),
                      child: ListTile(
                        contentPadding: const EdgeInsets.all(12),
                        title: Text(listing.name, style: const TextStyle(fontWeight: FontWeight.w600)),
                        subtitle: listing.isOnFlashSale
                            ? Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Text(
                                    _currencyFormat.format(listing.price),
                                    style: const TextStyle(decoration: TextDecoration.lineThrough),
                                  ),
                                  const SizedBox(width: 6),
                                  Text(
                                    '${_currencyFormat.format(listing.salePrice)} ${listing.currency}',
                                    style: const TextStyle(color: AppColors.orange, fontWeight: FontWeight.w600),
                                  ),
                                ],
                              )
                            : Text(
                                '${_currencyFormat.format(listing.price)} ${listing.currency}',
                                style: const TextStyle(color: AppColors.orange, fontWeight: FontWeight.w600),
                              ),
                        trailing: ElevatedButton(
                          onPressed: () {
                            context.read<CartService>().add(listing);
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text('"${listing.name}" ajouté au panier.'),
                                action: SnackBarAction(
                                  label: 'Voir le panier',
                                  onPressed: () => Navigator.of(context).push(
                                    MaterialPageRoute(builder: (_) => const CartScreen()),
                                  ),
                                ),
                              ),
                            );
                          },
                          child: const Text('Ajouter'),
                        ),
                      ),
                    )),
            ],
          );
        },
      ),
    );
  }
}
