import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../models/listing.dart';
import '../../services/vendor_portal_service.dart';
import '../../theme/app_theme.dart';
import 'vendor_listing_form_screen.dart';

class VendorListingsScreen extends StatefulWidget {
  const VendorListingsScreen({super.key});

  @override
  State<VendorListingsScreen> createState() => _VendorListingsScreenState();
}

class _VendorListingsScreenState extends State<VendorListingsScreen> {
  late Future<List<Listing>> _future;
  final _currencyFormat = NumberFormat.decimalPattern('fr');

  @override
  void initState() {
    super.initState();
    _reload();
  }

  void _reload() {
    setState(() => _future = context.read<VendorPortalService>().myListings());
  }

  Future<void> _toggleActive(Listing listing) async {
    await context.read<VendorPortalService>().updateListing(listing.id, {'is_active': !listing.isActive});
    _reload();
  }

  Future<void> _delete(Listing listing) async {
    await context.read<VendorPortalService>().deleteListing(listing.id);
    _reload();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Mon catalogue'),
        actions: [
          IconButton(
            icon: const Icon(Icons.add),
            onPressed: () async {
              await Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const VendorListingFormScreen()),
              );
              _reload();
            },
          ),
        ],
      ),
      body: FutureBuilder<List<Listing>>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }

          final listings = snapshot.data ?? [];

          if (listings.isEmpty) {
            return const Center(child: Text('Aucun article pour le moment.'));
          }

          return ListView.separated(
            padding: const EdgeInsets.all(16),
            itemCount: listings.length,
            separatorBuilder: (_, _) => const SizedBox(height: 8),
            itemBuilder: (context, index) {
              final listing = listings[index];

              return Card(
                child: ListTile(
                  contentPadding: const EdgeInsets.all(12),
                  title: Text(listing.name, style: const TextStyle(fontWeight: FontWeight.w600)),
                  subtitle: Text('${_currencyFormat.format(listing.price)} ${listing.currency}'),
                  trailing: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      TextButton(
                        onPressed: () => _toggleActive(listing),
                        child: Text(
                          listing.isActive ? 'Actif' : 'Inactif',
                          style: TextStyle(color: listing.isActive ? AppColors.green : AppColors.error),
                        ),
                      ),
                      IconButton(
                        icon: const Icon(Icons.edit_outlined),
                        onPressed: () async {
                          await Navigator.of(context).push(
                            MaterialPageRoute(builder: (_) => VendorListingFormScreen(listing: listing)),
                          );
                          _reload();
                        },
                      ),
                      IconButton(
                        icon: const Icon(Icons.delete_outline),
                        onPressed: () => _delete(listing),
                      ),
                    ],
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
