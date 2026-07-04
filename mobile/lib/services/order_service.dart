import '../models/order.dart';
import 'api_client.dart';

class OrderService {
  final ApiClient _client;

  OrderService(this._client);

  Future<Order> create({
    required int vendorId,
    required List<Map<String, dynamic>> items,
    required String deliveryAddressText,
    double? deliveryLatitude,
    double? deliveryLongitude,
  }) async {
    final json = await _client.post('/orders', body: {
      'vendor_id': vendorId,
      'items': items,
      'delivery_address_text': deliveryAddressText,
      if (deliveryLatitude != null) 'delivery_latitude': deliveryLatitude,
      if (deliveryLongitude != null) 'delivery_longitude': deliveryLongitude,
      'source': 'app',
    });

    return Order.fromJson(json['order']);
  }

  Future<Order> show(int orderId) async {
    final json = await _client.get('/orders/$orderId');

    return Order.fromJson(json['data']);
  }

  Future<Order> confirmReceipt(int orderId) async {
    final json = await _client.post('/orders/$orderId/confirm-receipt');

    return Order.fromJson(json['data']);
  }
}
