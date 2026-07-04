import 'package:flutter/foundation.dart';
import '../models/listing.dart';

class CartLine {
  final Listing listing;
  int quantity;

  CartLine(this.listing, this.quantity);

  double get subtotal => listing.price * quantity;
}

/// In-memory cart, scoped to a single vendor at a time — mirrors the web
/// CartService, since an Order belongs to exactly one vendor.
class CartService extends ChangeNotifier {
  final Map<int, CartLine> _lines = {};
  int? _vendorId;

  int? get vendorId => _vendorId;

  List<CartLine> get lines => _lines.values.toList();

  bool get isEmpty => _lines.isEmpty;

  int get count => _lines.values.fold(0, (sum, line) => sum + line.quantity);

  double get total => _lines.values.fold(0, (sum, line) => sum + line.subtotal);

  void add(Listing listing, {int quantity = 1}) {
    if (_vendorId != null && _vendorId != listing.vendorId) {
      _lines.clear();
    }
    _vendorId = listing.vendorId;

    if (_lines.containsKey(listing.id)) {
      _lines[listing.id]!.quantity += quantity;
    } else {
      _lines[listing.id] = CartLine(listing, quantity);
    }

    notifyListeners();
  }

  void updateQuantity(int listingId, int quantity) {
    if (quantity < 1) {
      remove(listingId);
      return;
    }

    _lines[listingId]?.quantity = quantity;
    notifyListeners();
  }

  void remove(int listingId) {
    _lines.remove(listingId);
    if (_lines.isEmpty) {
      _vendorId = null;
    }
    notifyListeners();
  }

  void clear() {
    _lines.clear();
    _vendorId = null;
    notifyListeners();
  }

  List<Map<String, dynamic>> toOrderItems() {
    return _lines.values
        .map((line) => {'listing_id': line.listing.id, 'quantity': line.quantity})
        .toList();
  }
}
