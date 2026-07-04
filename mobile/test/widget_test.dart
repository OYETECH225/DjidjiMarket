import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:djidjimarket/main.dart';

void main() {
  testWidgets('App boots to the home screen', (WidgetTester tester) async {
    await tester.pumpWidget(const DjidjiMarketApp());

    expect(find.byType(RichText).evaluate().any((e) => (e.widget as RichText).text.toPlainText() == 'djidjimarket'), isTrue);
    expect(find.byType(CircularProgressIndicator), findsOneWidget);
  });
}
