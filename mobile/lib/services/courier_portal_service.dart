import '../models/courier_profile.dart';
import '../models/order.dart';
import 'api_client.dart';

/// The authenticated courier's own data and dispatch actions.
class CourierPortalService {
  final ApiClient _client;

  CourierPortalService(this._client);

  Future<CourierProfile> createProfile({required String vehicleType}) async {
    final json = await _client.post('/courier/profile', body: {'vehicle_type': vehicleType});

    return CourierProfile.fromJson(json['courier']);
  }

  Future<CourierProfile> me() async {
    final json = await _client.get('/courier/me');

    return CourierProfile.fromJson(json['data']);
  }

  Future<CourierProfile> setAvailability(bool isAvailable) async {
    final json = await _client.post('/courier/availability', body: {'is_available': isAvailable});

    return CourierProfile.fromJson(json['data']);
  }

  Future<List<Order>> availableOrders() async {
    final json = await _client.get('/courier/orders/available');

    return (json['data'] as List).map((o) => Order.fromJson(o)).toList();
  }

  Future<List<Order>> myOrders() async {
    final json = await _client.get('/courier/orders');

    return (json['data'] as List).map((o) => Order.fromJson(o)).toList();
  }

  Future<Order> acceptOrder(int orderId) async {
    final json = await _client.post('/courier/orders/$orderId/accept');

    return Order.fromJson(json['data']);
  }

  Future<Order> updateStatus(int orderId, String status) async {
    final json = await _client.post('/courier/orders/$orderId/status', body: {'status': status});

    return Order.fromJson(json['data']);
  }
}

/// Mirrors CourierDispatchService::ALLOWED_TRANSITIONS on the backend.
const Map<String, String> deliveryTransitions = {
  'livreur_assigne': 'recuperee',
  'recuperee': 'en_livraison',
  'en_livraison': 'livree',
};
