import 'package:flutter/material.dart';

/// Decorative illustration: no live GPS or service availability is implied.
class ServiceScene extends StatelessWidget {
  const ServiceScene({super.key});
  @override
  Widget build(BuildContext context) => Semantics(
        label: 'Illustration d’un camion de vidange',
        image: true,
        child: CustomPaint(
            painter: _ScenePainter(), size: const Size(double.infinity, 180)),
      );
}

class _ScenePainter extends CustomPainter {
  @override
  void paint(Canvas c, Size size) {
    c.save();
    c.scale(size.width / 340, size.height / 180);
    final p = Paint();
    c.drawCircle(const Offset(270, 50), 78, p..color = const Color(0xFFDBEAFE));
    for (var i = 0; i < 5; i++) {
      final x = 10.0 + i * 69;
      c.drawRRect(
          RRect.fromRectAndRadius(
              Rect.fromLTWH(x, 65 - (i % 2) * 24, 47, 77 + (i % 2) * 24),
              const Radius.circular(5)),
          p..color = const Color(0xFFE9EFF8));
      c.drawRect(Rect.fromLTWH(x + 10, 78 - (i % 2) * 24, 9, 12),
          p..color = Colors.white);
      c.drawRect(Rect.fromLTWH(x + 28, 78 - (i % 2) * 24, 9, 12), p);
    }
    c.drawRRect(
        RRect.fromRectAndRadius(
            const Rect.fromLTWH(0, 146, 340, 5), const Radius.circular(3)),
        p..color = const Color(0xFFC7D5EC));
    c.drawOval(const Rect.fromLTWH(55, 146, 230, 16),
        p..color = const Color(0xFFD6E1F4));
    c.drawRRect(
        RRect.fromRectAndRadius(
            const Rect.fromLTWH(63, 70, 141, 67), const Radius.circular(28)),
        p..color = const Color(0xFF2563EB));
    c.drawRRect(
        RRect.fromRectAndRadius(
            const Rect.fromLTWH(73, 77, 124, 15), const Radius.circular(8)),
        p..color = const Color(0xFF60A5FA));
    c.drawRect(const Rect.fromLTWH(61, 128, 207, 13),
        p..color = const Color(0xFF172554));
    final cab = Path()
      ..moveTo(210, 87)
      ..lineTo(241, 87)
      ..lineTo(268, 117)
      ..lineTo(268, 132)
      ..lineTo(210, 132)
      ..close();
    c.drawPath(cab, p..color = const Color(0xFF3B82F6));
    final window = Path()
      ..moveTo(219, 94)
      ..lineTo(238, 94)
      ..lineTo(252, 112)
      ..lineTo(219, 112)
      ..close();
    c.drawPath(window, p..color = const Color(0xFFDBF4FF));
    for (final x in [91.0, 241.0]) {
      c.drawCircle(Offset(x, 141), 17, p..color = const Color(0xFF172554));
      c.drawCircle(Offset(x, 141), 8, p..color = const Color(0xFFCBD5E1));
      c.drawCircle(Offset(x, 141), 3, p..color = Colors.white);
    }
    c.drawCircle(const Offset(293, 48), 16, p..color = const Color(0xFF2563EB));
    c.drawPath(
        Path()
          ..moveTo(284, 59)
          ..lineTo(293, 73)
          ..lineTo(302, 59)
          ..close(),
        p);
    c.drawCircle(const Offset(293, 47), 6, p..color = Colors.white);
    c.restore();
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
