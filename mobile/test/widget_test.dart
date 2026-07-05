import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';

import 'package:djidjimarket/main.dart';
import 'package:djidjimarket/services/api_client.dart';
import 'package:djidjimarket/services/auth_service.dart';
import 'package:djidjimarket/services/cart_service.dart';
import 'package:djidjimarket/services/courier_portal_service.dart';
import 'package:djidjimarket/services/order_service.dart';
import 'package:djidjimarket/services/payment_service.dart';
import 'package:djidjimarket/services/vendor_portal_service.dart';
import 'package:djidjimarket/services/vendor_service.dart';

void main() {
  testWidgets('App boots to the home screen', (WidgetTester tester) async {
    await tester.pumpWidget(const DjidjiMarketApp());

    expect(find.byType(RichText).evaluate().any((e) => (e.widget as RichText).text.toPlainText() == 'djidjimarket'), isTrue);
    expect(find.byType(CircularProgressIndicator), findsOneWidget);
  });

  testWidgets('Every service is registered as a provider', (WidgetTester tester) async {
    // Regression test: VendorPortalService was built and used by every
    // vendor screen but never added to MultiProvider in main.dart, so those
    // screens would throw ProviderNotFoundException at runtime despite
    // `flutter analyze`/`flutter test` (on HomeScreen alone) reporting no
    // issues. Assert each service resolves from the real app widget tree.
    await tester.pumpWidget(const DjidjiMarketApp());

    final context = tester.element(find.byType(MaterialApp));

    expect(() => context.read<ApiClient>(), returnsNormally);
    expect(() => context.read<AuthService>(), returnsNormally);
    expect(() => context.read<CartService>(), returnsNormally);
    expect(() => context.read<VendorService>(), returnsNormally);
    expect(() => context.read<VendorPortalService>(), returnsNormally);
    expect(() => context.read<CourierPortalService>(), returnsNormally);
    expect(() => context.read<OrderService>(), returnsNormally);
    expect(() => context.read<PaymentService>(), returnsNormally);
  });

  testWidgets('Bottom nav Panier tab opens the cart screen', (WidgetTester tester) async {
    await tester.pumpWidget(const DjidjiMarketApp());
    await tester.tap(find.text('Panier'));
    await tester.pumpAndSettle();

    expect(find.text('Mon panier'), findsOneWidget);
  });

  testWidgets('Bottom nav Commandes tab redirects to login when logged out', (WidgetTester tester) async {
    await tester.pumpWidget(const DjidjiMarketApp());
    await tester.tap(find.text('Commandes'));
    await tester.pumpAndSettle();

    expect(find.text('Connexion'), findsWidgets);
  });

  testWidgets('Bottom nav Profil tab redirects to login when logged out', (WidgetTester tester) async {
    await tester.pumpWidget(const DjidjiMarketApp());
    await tester.tap(find.text('Profil'));
    await tester.pumpAndSettle();

    expect(find.text('Connexion'), findsWidgets);
  });
}
