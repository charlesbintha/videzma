import 'package:flutter/material.dart';

/// Slides the whole panel, including safe-area spacing, beyond the map edge.
class NavigationPanel extends StatelessWidget {
  final bool visible;
  final bool fromTop;
  final Widget child;

  const NavigationPanel({
    super.key,
    required this.visible,
    required this.fromTop,
    required this.child,
  });

  @override
  Widget build(BuildContext context) {
    return IgnorePointer(
      ignoring: !visible,
      child: ExcludeSemantics(
        excluding: !visible,
        child: AnimatedSlide(
          offset: visible ? Offset.zero : Offset(0, fromTop ? -1 : 1),
          duration: MediaQuery.disableAnimationsOf(context)
              ? Duration.zero
              : const Duration(milliseconds: 240),
          curve: Curves.easeInOutCubic,
          child: child,
        ),
      ),
    );
  }
}
