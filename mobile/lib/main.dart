import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'screens/home_screen.dart';
import 'services/api_client.dart';
import 'services/auth_service.dart';
import 'services/cart_service.dart';
import 'services/courier_portal_service.dart';
import 'services/order_service.dart';
import 'services/payment_service.dart';
import 'services/vendor_portal_service.dart';
import 'services/vendor_service.dart';
import 'theme/app_theme.dart';

void main() {
  runApp(const DjidjiMarketApp());
}

class DjidjiMarketApp extends StatelessWidget {
  const DjidjiMarketApp({super.key});

  @override
  Widget build(BuildContext context) {
    final apiClient = ApiClient();

    return MultiProvider(
      providers: [
        Provider.value(value: apiClient),
        ChangeNotifierProvider(create: (_) => AuthService(apiClient)),
        ChangeNotifierProvider(create: (_) => CartService()),
        Provider(create: (_) => VendorService(apiClient)),
        Provider(create: (_) => VendorPortalService(apiClient)),
        Provider(create: (_) => CourierPortalService(apiClient)),
        Provider(create: (_) => OrderService(apiClient)),
        Provider(create: (_) => PaymentService(apiClient)),
      ],
      child: MaterialApp(
        title: 'DjidjiMarket',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light,
        home: const HomeScreen(),
      ),
    );
  }
}
