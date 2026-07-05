import '../models/listing.dart';
import '../models/vendor.dart';
import 'api_client.dart';

class VendorService {
  final ApiClient _client;

  VendorService(this._client);

  Future<List<Vendor>> list({String? type}) async {
    final query = type != null ? '?type=$type' : '';
    final json = await _client.get('/vendors$query', auth: false);

    return (json['data'] as List).map((v) => Vendor.fromJson(v)).toList();
  }

  Future<Vendor> show(String slug) async {
    final json = await _client.get('/vendors/$slug', auth: false);

    return Vendor.fromJson(json['data']);
  }

  Future<List<Listing>> listings(int vendorId) async {
    final json = await _client.get('/vendors/$vendorId/listings', auth: false);

    return (json['data'] as List).map((l) => Listing.fromJson(l)).toList();
  }

  Future<List<Listing>> dishesOfTheDay() async {
    final json = await _client.get('/dishes-of-the-day', auth: false);

    return (json['data'] as List).map((l) => Listing.fromJson(l)).toList();
  }

  Future<List<Listing>> flashSales() async {
    final json = await _client.get('/flash-sales', auth: false);

    return (json['data'] as List).map((l) => Listing.fromJson(l)).toList();
  }
}
