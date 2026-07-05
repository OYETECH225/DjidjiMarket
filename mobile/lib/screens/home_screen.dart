import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../models/listing.dart';
import '../models/vendor.dart';
import '../services/auth_service.dart';
import '../services/cart_service.dart';
import '../services/vendor_service.dart';
import '../theme/app_theme.dart';
import '../widgets/app_bottom_nav.dart';
import 'auth/login_screen.dart';
import 'cart_screen.dart';
import 'courier/courier_dashboard_screen.dart';
import 'vendor/vendor_dashboard_screen.dart';
import 'vendor_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  late Future<List<Vendor>> _vendorsFuture;
  late Future<List<Listing>> _dishesFuture;
  late Future<List<Listing>> _flashSalesFuture;
  String? _selectedType;
  final _currencyFormat = NumberFormat.decimalPattern('fr');

  @override
  void initState() {
    super.initState();
    _vendorsFuture = context.read<VendorService>().list();
    _dishesFuture = context.read<VendorService>().dishesOfTheDay();
    _flashSalesFuture = context.read<VendorService>().flashSales();
  }

  Future<void> _refresh() async {
    setState(() {
      _vendorsFuture = context.read<VendorService>().list(type: _selectedType);
      _dishesFuture = context.read<VendorService>().dishesOfTheDay();
      _flashSalesFuture = context.read<VendorService>().flashSales();
    });
    await Future.wait([_vendorsFuture, _dishesFuture, _flashSalesFuture]);
  }

  void _filterBy(String? type) {
    setState(() {
      _selectedType = type;
      _vendorsFuture = context.read<VendorService>().list(type: type);
    });
  }

  void _addToCart(Listing listing) {
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
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthService>();
    final cartCount = context.watch<CartService>().count;

    return Scaffold(
      appBar: AppBar(
        title: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Image.asset('assets/images/DjidjiMarket-icone-seule.png', height: 32),
            const SizedBox(width: 8),
            RichText(
              text: const TextSpan(
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: AppColors.green),
                children: [
                  TextSpan(text: 'djidji'),
                  TextSpan(text: 'market', style: TextStyle(color: AppColors.orange)),
                ],
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: Badge(
              label: Text('$cartCount'),
              isLabelVisible: cartCount > 0,
              backgroundColor: AppColors.orange,
              child: const Icon(Icons.shopping_bag_outlined),
            ),
            onPressed: () => Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => const CartScreen()),
            ),
          ),
          if (!auth.isAuthenticated)
            IconButton(
              icon: const Icon(Icons.person_outline),
              onPressed: () => Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const LoginScreen()),
              ),
            )
          else ...[
            if (auth.currentUser?.role == 'vendor')
              IconButton(
                icon: const Icon(Icons.storefront_outlined),
                tooltip: 'Mon espace vendeur',
                onPressed: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const VendorDashboardScreen()),
                ),
              ),
            if (auth.currentUser?.role == 'courier')
              IconButton(
                icon: const Icon(Icons.two_wheeler_outlined),
                tooltip: 'Mon espace livreur',
                onPressed: () => Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const CourierDashboardScreen()),
                ),
              ),
            IconButton(
              icon: const Icon(Icons.logout),
              onPressed: () => context.read<AuthService>().logout(),
            ),
          ],
        ],
      ),
      bottomNavigationBar: const AppBottomNav(currentIndex: 0),
      body: RefreshIndicator(
        onRefresh: _refresh,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            _buildHero(),
            const SizedBox(height: 16),
            _buildTrustRow(),
            const SizedBox(height: 24),
            _buildFlashSales(),
            _buildCategoryGrid(),
            const SizedBox(height: 24),
            _buildDishesOfTheDay(),
            _buildVendorSection(),
          ],
        ),
      ),
    );
  }

  Widget _buildHero() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppColors.green,
        borderRadius: BorderRadius.circular(24),
      ),
      child: const Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Le vrai marché, en toute confiance',
            style: TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.w800, height: 1.2),
          ),
          SizedBox(height: 8),
          Text(
            'Commandez vos produits locaux préférés. Livraison sécurisée et vendeurs vérifiés en Côte d\'Ivoire.',
            style: TextStyle(color: Colors.white70),
          ),
        ],
      ),
    );
  }

  Widget _buildTrustRow() {
    final items = [
      (Icons.shield_outlined, 'Paiement protégé', 'Votre argent est reversé au vendeur qu\'après réception.'),
      (Icons.verified_outlined, 'Vendeurs vérifiés', 'Chaque boutique passe par une vérification.'),
      (Icons.local_shipping_outlined, 'Livraison rapide', 'Livraison garantie sous 24 à 48h.'),
    ];

    return Column(
      children: items
          .map((item) => Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    CircleAvatar(
                      radius: 20,
                      backgroundColor: AppColors.green.withValues(alpha: 0.1),
                      child: Icon(item.$1, color: AppColors.green, size: 20),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(item.$2, style: const TextStyle(fontWeight: FontWeight.w600)),
                          Text(item.$3, style: TextStyle(color: Colors.grey[600], fontSize: 13)),
                        ],
                      ),
                    ),
                  ],
                ),
              ))
          .toList(),
    );
  }

  Widget _buildCategoryGrid() {
    final categories = {
      'boutique': 'djidji-cat-boutique',
      'street_food': 'djidji-cat-food',
      'restaurant': 'djidji-cat-restaurant',
    };

    return SizedBox(
      height: 110,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: categories.length + 1,
        separatorBuilder: (_, _) => const SizedBox(width: 10),
        itemBuilder: (context, index) {
          if (index == 0) {
            return _categoryChip('Tous', null, selected: _selectedType == null);
          }
          final entry = categories.entries.elementAt(index - 1);
          return _categoryChip(vendorTypeLabels[entry.key] ?? entry.key, entry.key,
              selected: _selectedType == entry.key);
        },
      ),
    );
  }

  Widget _categoryChip(String label, String? type, {required bool selected}) {
    return GestureDetector(
      onTap: () => _filterBy(type),
      child: Container(
        width: 110,
        decoration: BoxDecoration(
          color: selected ? AppColors.green : Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: selected ? AppColors.green : AppColors.outlineVariant),
        ),
        alignment: Alignment.center,
        child: Text(
          label,
          style: TextStyle(
            color: selected ? Colors.white : AppColors.onSurface,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
    );
  }

  Widget _buildFlashSales() {
    return FutureBuilder<List<Listing>>(
      future: _flashSalesFuture,
      builder: (context, snapshot) {
        final items = snapshot.data ?? [];
        if (items.isEmpty) return const SizedBox.shrink();

        return Padding(
          padding: const EdgeInsets.only(bottom: 24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  const Icon(Icons.bolt, color: AppColors.orange),
                  const SizedBox(width: 6),
                  const Text('Vente flash',
                      style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.orange)),
                ],
              ),
              const SizedBox(height: 12),
              SizedBox(
                height: 160,
                child: ListView.separated(
                  scrollDirection: Axis.horizontal,
                  itemCount: items.length,
                  separatorBuilder: (_, _) => const SizedBox(width: 12),
                  itemBuilder: (context, index) => _flashSaleCard(items[index]),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _flashSaleCard(Listing item) {
    return Container(
      width: 160,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.outlineVariant),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(item.name, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w600)),
          if (item.vendorBusinessName != null)
            Text(item.vendorBusinessName!, maxLines: 1, overflow: TextOverflow.ellipsis, style: TextStyle(fontSize: 12, color: Colors.grey[600])),
          const Spacer(),
          Row(
            children: [
              Text(_currencyFormat.format(item.price), style: const TextStyle(decoration: TextDecoration.lineThrough, fontSize: 12)),
            ],
          ),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('${_currencyFormat.format(item.salePrice)} ${item.currency}',
                  style: const TextStyle(color: AppColors.orange, fontWeight: FontWeight.bold)),
              InkWell(
                onTap: () => _addToCart(item),
                child: const CircleAvatar(
                  radius: 14,
                  backgroundColor: AppColors.orange,
                  child: Icon(Icons.add, color: Colors.white, size: 16),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildDishesOfTheDay() {
    return FutureBuilder<List<Listing>>(
      future: _dishesFuture,
      builder: (context, snapshot) {
        final items = snapshot.data ?? [];
        if (items.isEmpty) return const SizedBox.shrink();

        return Padding(
          padding: const EdgeInsets.only(bottom: 24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Plats du jour', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.green)),
              const SizedBox(height: 12),
              SizedBox(
                height: 140,
                child: ListView.separated(
                  scrollDirection: Axis.horizontal,
                  itemCount: items.length,
                  separatorBuilder: (_, _) => const SizedBox(width: 12),
                  itemBuilder: (context, index) => _dishCard(items[index]),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _dishCard(Listing dish) {
    return Container(
      width: 160,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.outlineVariant),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(dish.name, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w600)),
          if (dish.vendorBusinessName != null)
            Text(dish.vendorBusinessName!, maxLines: 1, overflow: TextOverflow.ellipsis, style: TextStyle(fontSize: 12, color: Colors.grey[600])),
          const Spacer(),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('${_currencyFormat.format(dish.price)} ${dish.currency}',
                  style: const TextStyle(color: AppColors.orange, fontWeight: FontWeight.bold)),
              InkWell(
                onTap: () => _addToCart(dish),
                child: const CircleAvatar(
                  radius: 14,
                  backgroundColor: AppColors.green,
                  child: Icon(Icons.add, color: Colors.white, size: 16),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildVendorSection() {
    return FutureBuilder<List<Vendor>>(
      future: _vendorsFuture,
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return const Padding(
            padding: EdgeInsets.only(top: 40),
            child: Center(child: CircularProgressIndicator()),
          );
        }

        if (snapshot.hasError) {
          return const Padding(
            padding: EdgeInsets.only(top: 40),
            child: Center(child: Text('Impossible de charger les boutiques.')),
          );
        }

        final vendors = snapshot.data ?? [];

        if (vendors.isEmpty) {
          return const Padding(
            padding: EdgeInsets.only(top: 40),
            child: Center(child: Text('Aucune boutique disponible pour le moment.')),
          );
        }

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              _selectedType != null ? (vendorTypeLabels[_selectedType] ?? _selectedType!) : 'Toutes les boutiques',
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.green),
            ),
            const SizedBox(height: 12),
            ...vendors.map((vendor) => Card(
                  margin: const EdgeInsets.only(bottom: 12),
                  child: ListTile(
                    contentPadding: const EdgeInsets.all(12),
                    leading: CircleAvatar(
                      radius: 24,
                      backgroundColor: AppColors.background,
                      backgroundImage: vendor.logoUrl != null ? NetworkImage(vendor.logoUrl!) : null,
                      child: vendor.logoUrl == null
                          ? const Icon(Icons.storefront, color: AppColors.green)
                          : null,
                    ),
                    title: Text(
                      vendor.businessName,
                      style: const TextStyle(fontWeight: FontWeight.w600),
                    ),
                    subtitle: Text(vendorTypeLabels[vendor.vendorType] ?? vendor.vendorType),
                    trailing: vendor.isVerified
                        ? const Icon(Icons.verified, color: AppColors.green, size: 20)
                        : null,
                    onTap: () => Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => VendorScreen(slug: vendor.slug)),
                    ),
                  ),
                )),
          ],
        );
      },
    );
  }
}
