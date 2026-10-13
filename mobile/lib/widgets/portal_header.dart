import 'package:flutter/material.dart';
import '../theme/app_colors.dart';

/// Shared patterned header inspired by the portal's reference screens.
class PortalHeader extends StatelessWidget {
  final String title;
  final String subtitle;
  final Widget? trailing;
  final bool back;
  final Widget? child;
  const PortalHeader(
      {super.key,
      required this.title,
      required this.subtitle,
      this.trailing,
      this.back = false,
      this.child});

  @override
  Widget build(BuildContext context) {
    return ClipRRect(
      borderRadius: const BorderRadius.vertical(bottom: Radius.circular(28)),
      child: CustomPaint(
        painter: _HeaderPattern(),
        child: Padding(
          padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
          child:
              Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Row(children: [
              if (back) ...[
                IconButton.filledTonal(
                    tooltip: 'Retour',
                    onPressed: () => Navigator.of(context).pop(),
                    style: IconButton.styleFrom(
                        backgroundColor: Colors.white.withValues(alpha: .16),
                        foregroundColor: Colors.white),
                    icon: const Icon(Icons.arrow_back, size: 20)),
                const SizedBox(width: 12),
              ],
              Expanded(
                  child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                    Text(title,
                        style: const TextStyle(
                            color: Colors.white,
                            fontSize: 22,
                            fontWeight: FontWeight.w700)),
                    const SizedBox(height: 6),
                    Text(subtitle,
                        style: const TextStyle(
                            color: Color(0xFFDCEAFF),
                            fontSize: 12,
                            height: 1.5)),
                  ])),
              if (trailing != null)
                IconTheme(
                    data: const IconThemeData(color: Colors.white),
                    child: trailing!),
            ]),
            if (child != null) ...[const SizedBox(height: 20), child!],
          ]),
        ),
      ),
    );
  }
}

class _HeaderPattern extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    canvas.drawRect(
        Offset.zero & size,
        Paint()
          ..shader = const LinearGradient(
            colors: [Color(0xFF4278F5), AppColors.primary600],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ).createShader(Offset.zero & size));
    final paint = Paint()
      ..color = Colors.white.withValues(alpha: .045)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 22;
    for (double radius = 40; radius < 380; radius += 46) {
      canvas.drawCircle(
          Offset(size.width + 20, size.height + 42), radius, paint);
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
