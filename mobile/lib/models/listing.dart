class Listing {
  final int id;
  final int vendorId;
  final String type;
  final String name;
  final String? description;
  final double price;
  final String currency;
  final int? stockQuantity;

  Listing({
    required this.id,
    required this.vendorId,
    required this.type,
    required this.name,
    required this.price,
    required this.currency,
    this.description,
    this.stockQuantity,
  });

  factory Listing.fromJson(Map<String, dynamic> json) {
    return Listing(
      id: json['id'],
      vendorId: json['vendor_id'],
      type: json['type'],
      name: json['name'],
      description: json['description'],
      price: double.parse(json['price'].toString()),
      currency: json['currency'] ?? 'XOF',
      stockQuantity: json['stock_quantity'],
    );
  }
}
