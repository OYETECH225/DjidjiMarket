import 'api_client.dart';

class PaymentService {
  final ApiClient _client;

  PaymentService(this._client);

  Future<void> initiate({required int orderId, required String provider}) async {
    await _client.post('/payments/initiate', body: {
      'order_id': orderId,
      'provider': provider,
    });
  }
}
