class OrderItem {
  final int id;
  final int listingId;
  final String? listingName;
  final int quantity;
  final double unitPrice;

  OrderItem({
    required this.id,
    required this.listingId,
    required this.quantity,
    required this.unitPrice,
    this.listingName,
  });

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    return OrderItem(
      id: json['id'],
      listingId: json['listing_id'],
      listingName: json['listing_name'],
      quantity: json['quantity'],
      unitPrice: double.parse(json['unit_price'].toString()),
    );
  }
}

/// Mirrors Order::STATUS_LABELS on the backend.
const Map<String, String> orderStatusLabels = {
  'en_attente_paiement': 'En attente de paiement',
  'paiement_sequestre': 'Paiement séquestré',
  'confirmee': 'Confirmée',
  'en_preparation': 'En préparation',
  'cherche_livreur': 'Recherche livreur',
  'livreur_assigne': 'Livreur assigné',
  'recuperee': 'Récupérée',
  'en_livraison': 'En livraison',
  'livree': 'Livrée',
  'paiement_libere': 'Paiement libéré',
  'litige_ouvert': 'Litige ouvert',
  'annulee': 'Annulée',
};

class Order {
  final int id;
  final int clientId;
  final int vendorId;
  final int? courierId;
  final String status;
  final String deliveryAddressText;
  final double totalAmount;
  final double deliveryFee;
  final String source;
  final String? vendorBusinessName;
  final String? vendorAddressText;
  final String? clientName;
  final DateTime? createdAt;
  final List<OrderItem> items;

  Order({
    required this.id,
    required this.clientId,
    required this.vendorId,
    required this.status,
    required this.deliveryAddressText,
    required this.totalAmount,
    required this.deliveryFee,
    required this.source,
    this.courierId,
    this.vendorBusinessName,
    this.vendorAddressText,
    this.clientName,
    this.createdAt,
    this.items = const [],
  });

  String get statusLabel => orderStatusLabels[status] ?? status;

  factory Order.fromJson(Map<String, dynamic> json) {
    return Order(
      id: json['id'],
      clientId: json['client_id'],
      vendorId: json['vendor_id'],
      courierId: json['courier_id'],
      status: json['status'],
      deliveryAddressText: json['delivery_address_text'] ?? '',
      totalAmount: double.parse(json['total_amount'].toString()),
      deliveryFee: double.parse((json['delivery_fee'] ?? 0).toString()),
      source: json['source'] ?? 'app',
      vendorBusinessName: json['vendor_business_name'],
      vendorAddressText: json['vendor_address_text'],
      clientName: json['client_name'],
      createdAt: json['created_at'] != null ? DateTime.tryParse(json['created_at']) : null,
      items: (json['items'] as List<dynamic>? ?? [])
          .map((item) => OrderItem.fromJson(item))
          .toList(),
    );
  }
}
