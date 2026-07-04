import '../models/listing.dart';
import '../models/order.dart';
import '../models/vendor_profile.dart';
import 'api_client.dart';

/// The authenticated vendor's own data — profile, catalog, orders.
/// Distinct from [VendorService], which is the public read-only catalog.
class VendorPortalService {
  final ApiClient _client;

  VendorPortalService(this._client);

  Future<VendorProfile> createProfile({
    required String businessName,
    required String vendorType,
    required String slug,
    String? description,
    String? addressText,
  }) async {
    final json = await _client.post('/vendor/profile', body: {
      'business_name': businessName,
      'vendor_type': vendorType,
      'slug': slug,
      if (description != null && description.isNotEmpty) 'description': description,
      if (addressText != null && addressText.isNotEmpty) 'address_text': addressText,
    });

    return VendorProfile.fromJson(json['vendor']);
  }

  Future<VendorProfile> me() async {
    final json = await _client.get('/vendor/me');

    return VendorProfile.fromJson(json['data']);
  }

  Future<VendorProfile> setActive(bool isActive) async {
    final json = await _client.patch('/vendor/me', body: {'is_active': isActive});

    return VendorProfile.fromJson(json['data']);
  }

  Future<List<Listing>> myListings() async {
    final json = await _client.get('/vendor/listings');

    return (json['data'] as List).map((l) => Listing.fromJson(l)).toList();
  }

  Future<Listing> createListing({
    required String type,
    required String name,
    required double price,
    String? description,
    int? stockQuantity,
  }) async {
    final json = await _client.post('/vendor/listings', body: {
      'type': type,
      'name': name,
      'price': price,
      if (description != null && description.isNotEmpty) 'description': description,
      if (stockQuantity != null) 'stock_quantity': stockQuantity,
    });

    return Listing.fromJson(json['listing']);
  }

  Future<Listing> updateListing(int listingId, Map<String, dynamic> changes) async {
    final json = await _client.put('/vendor/listings/$listingId', body: changes);

    return Listing.fromJson(json['data']);
  }

  Future<void> deleteListing(int listingId) => _client.delete('/vendor/listings/$listingId');

  Future<List<Order>> myOrders() async {
    final json = await _client.get('/vendor/orders');

    return (json['data'] as List).map((o) => Order.fromJson(o)).toList();
  }
}
