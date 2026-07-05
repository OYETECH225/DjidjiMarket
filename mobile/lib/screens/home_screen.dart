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

  final _searchController = TextEditingController();
  String _searchQuery = '';
  Future<({List<Vendor> vendors, List<Listing> listings})>? _searchFuture;

  @override
  void initState() {
    super.initState();
    _vendorsFuture = context.read<VendorService>().list();
    _dishesFuture = context.read<VendorService>().dishesOfTheDay();
    _flashSalesFuture = context.read<VendorService>().flashSales();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
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

  void _onSearchChanged(String value) {
    setState(() {
      _searchQuery = value.trim();
      _searchFuture = _searchQuery.isEmpty ? null : context.read<VendorService>().search(_searchQuery);
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
            _buildSearchBar(),
            const SizedBox(height: 16),
            if (_searchQuery.isNotEmpty) ...[
              _buildSearchResults(),
              const SizedBox(height: 24),
            ] else ...[
              _buildCategoryPills(),
              const SizedBox(height: 16),
              _buildTrustBanner(),
              const SizedBox(height: 24),
              _buildFeaturedVendors(),
              _buildFlashSales(),
              _buildDishesOfTheDay(),
              _buildVendorSection(),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildSearchBar() {
    return TextField(
      controller: _searchController,
      onChanged: _onSearchChanged,
      decoration: InputDecoration(
        hintText: 'Rechercher une boutique, un article...',
        prefixIcon: const Icon(Icons.search, color: AppColors.onSurfaceVariant),
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(999), borderSide: const BorderSide(color: AppColors.outlineVariant)),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(999), borderSide: const BorderSide(color: AppColors.outlineVariant)),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(999), borderSide: const BorderSide(color: AppColors.green, width: 2)),
      ),
    );
  }

  Widget _buildSearchResults() {
    return FutureBuilder<({List<Vendor> vendors, List<Listing> listings})>(
      future: _searchFuture,
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return const Padding(
            padding: EdgeInsets.only(top: 24),
            child: Center(child: CircularProgressIndicator()),
          );
        }

        final vendors = snapshot.data?.vendors ?? [];
        final listings = snapshot.data?.listings ?? [];

        if (vendors.isEmpty && listings.isEmpty) {
          return const Padding(
            padding: EdgeInsets.only(top: 24),
            child: Center(child: Text('Aucun résultat.')),
          );
        }

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('Résultats pour "$_searchQuery"',
                style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.green)),
            const SizedBox(height: 12),
            ...vendors.map((vendor) => Card(
                  margin: const EdgeInsets.only(bottom: 8),
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: AppColors.background,
                      backgroundImage: vendor.logoUrl != null ? NetworkImage(vendor.logoUrl!) : null,
                      child: vendor.logoUrl == null ? const Icon(Icons.storefront, color: AppColors.green) : null,
                    ),
                    title: Text(vendor.businessName, style: const TextStyle(fontWeight: FontWeight.w600)),
                    subtitle: Text(vendorTypeLabels[vendor.vendorType] ?? vendor.vendorType),
                    onTap: () => Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => VendorScreen(slug: vendor.slug)),
                    ),
                  ),
                )),
            ...listings.map((listing) => Card(
                  margin: const EdgeInsets.only(bottom: 8),
                  child: ListTile(
                    title: Text(listing.name, style: const TextStyle(fontWeight: FontWeight.w600)),
                    subtitle: Text(listing.vendorBusinessName ?? ''),
                    trailing: Text('${_currencyFormat.format(listing.effectivePrice)} ${listing.currency}',
                        style: const TextStyle(color: AppColors.orange, fontWeight: FontWeight.bold)),
                  ),
                )),
          ],
        );
      },
    );
  }

  Widget _buildCategoryPills() {
    final categories = vendorTypeLabels;

    return SizedBox(
      height: 44,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: categories.length + 1,
        separatorBuilder: (_, _) => const SizedBox(width: 8),
        itemBuilder: (context, index) {
          if (index == 0) {
            return _categoryPill('Tous', null, selected: _selectedType == null);
          }
          final entry = categories.entries.elementAt(index - 1);
          return _categoryPill(entry.value, entry.key, selected: _selectedType == entry.key);
        },
      ),
    );
  }

  Widget _categoryPill(String label, String? type, {required bool selected}) {
    return GestureDetector(
      onTap: () => _filterBy(type),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 20),
        decoration: BoxDecoration(
          color: selected ? AppColors.green : Colors.white,
          borderRadius: BorderRadius.circular(999),
          border: Border.all(color: selected ? AppColors.green : AppColors.outlineVariant),
        ),
        alignment: Alignment.center,
        child: Text(
          label,
          style: TextStyle(color: selected ? Colors.white : AppColors.onSurface, fontWeight: FontWeight.w600),
        ),
      ),
    );
  }

  Widget _buildTrustBanner() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.green.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Row(
        children: [
          const Icon(Icons.shield_outlined, color: AppColors.green),
          const SizedBox(width: 12),
          const Expanded(
            child: Text(
              'Paiement protégé jusqu\'à réception de votre commande',
              style: TextStyle(color: AppColors.green, fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFeaturedVendors() {
    return FutureBuilder<List<Vendor>>(
      future: _vendorsFuture,
      builder: (context, snapshot) {
        final vendors = (snapshot.data ?? []).where((v) => v.isVerified).take(4).toList();
        if (vendors.isEmpty) return const SizedBox.shrink();

        return Padding(
          padding: const EdgeInsets.only(bottom: 24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('Vendeurs en vedette', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.green)),
                ],
              ),
              const SizedBox(height: 12),
              SizedBox(
                height: 170,
                child: ListView.separated(
                  scrollDirection: Axis.horizontal,
                  itemCount: vendors.length,
                  separatorBuilder: (_, _) => const SizedBox(width: 12),
                  itemBuilder: (context, index) => _featuredVendorCard(vendors[index]),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _featuredVendorCard(Vendor vendor) {
    return GestureDetector(
      onTap: () => Navigator.of(context).push(
        MaterialPageRoute(builder: (_) => VendorScreen(slug: vendor.slug)),
      ),
      child: Container(
        width: 150,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppColors.outlineVariant),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Stack(
              children: [
                Container(
                  height: 100,
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: AppColors.green.withValues(alpha: 0.1),
                    borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                    image: vendor.logoUrl != null
                        ? DecorationImage(image: NetworkImage(vendor.logoUrl!), fit: BoxFit.cover)
                        : null,
                  ),
                  child: vendor.logoUrl == null
                      ? const Icon(Icons.storefront, color: AppColors.green, size: 32)
                      : null,
                ),
                Positioned(
                  right: 6,
                  top: 6,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(999)),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.verified, color: AppColors.green, size: 12),
                        SizedBox(width: 2),
                        Text('VÉRIFIÉ', style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: AppColors.green)),
                      ],
                    ),
                  ),
                ),
              ],
            ),
            Padding(
              padding: const EdgeInsets.all(10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(vendor.businessName, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w600)),
                  Text(vendorTypeLabels[vendor.vendorType] ?? vendor.vendorType, style: TextStyle(fontSize: 12, color: Colors.grey[600])),
                ],
              ),
            ),
          ],
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
                height: 190,
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
    final discount = item.salePrice != null ? (100 - (item.salePrice! / item.price * 100)).round() : 0;
    final remaining = item.saleEndsAt != null ? item.saleEndsAt!.difference(DateTime.now()) : Duration.zero;

    return Container(
      width: 160,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.outlineVariant),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Stack(
            children: [
              Container(
                height: 80,
                width: double.infinity,
                decoration: const BoxDecoration(
                  color: AppColors.background,
                  borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
                ),
              ),
              Positioned(
                left: 6,
                top: 6,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                  decoration: BoxDecoration(color: AppColors.orange, borderRadius: BorderRadius.circular(999)),
                  child: Text('-$discount%', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.white)),
                ),
              ),
            ],
          ),
          Padding(
            padding: const EdgeInsets.all(10),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(item.name, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w600)),
                if (item.vendorBusinessName != null)
                  Text(item.vendorBusinessName!, maxLines: 1, overflow: TextOverflow.ellipsis, style: TextStyle(fontSize: 11, color: Colors.grey[600])),
                Text('Se termine dans ${remaining.inHours}h${(remaining.inMinutes % 60).toString().padLeft(2, '0')}',
                    style: const TextStyle(fontSize: 11, color: AppColors.orange, fontWeight: FontWeight.w600)),
                const SizedBox(height: 4),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(_currencyFormat.format(item.price), style: const TextStyle(decoration: TextDecoration.lineThrough, fontSize: 11)),
                        Text('${_currencyFormat.format(item.salePrice)} ${item.currency}',
                            style: const TextStyle(color: AppColors.orange, fontWeight: FontWeight.bold)),
                      ],
                    ),
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
