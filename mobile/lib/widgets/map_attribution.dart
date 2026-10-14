import 'dart:async';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

/// Show the credit initially, then keep the licence accessible via an info button.
class MapAttribution extends StatefulWidget {
  const MapAttribution({super.key});

  @override
  State<MapAttribution> createState() => _MapAttributionState();
}

class _MapAttributionState extends State<MapAttribution> {
  bool _expanded = true;
  Timer? _collapseTimer;

  @override
  void initState() {
    super.initState();
    _collapseTimer = Timer(const Duration(seconds: 6), () {
      if (mounted) setState(() => _expanded = false);
    });
  }

  @override
  void dispose() {
    _collapseTimer?.cancel();
    super.dispose();
  }

  void _toggle() {
    _collapseTimer?.cancel();
    setState(() => _expanded = !_expanded);
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(left: 8),
      child: Material(
        color: Colors.white.withValues(alpha: .94),
        borderRadius: BorderRadius.circular(12),
        clipBehavior: Clip.antiAlias,
        child: AnimatedSize(
          duration: MediaQuery.disableAnimationsOf(context)
              ? Duration.zero
              : const Duration(milliseconds: 200),
          alignment: Alignment.centerLeft,
          curve: Curves.easeInOut,
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              if (_expanded)
                Flexible(
                  child: InkWell(
                    onTap: () => launchUrl(
                        Uri.parse('https://www.openstreetmap.org/copyright')),
                    child: const Padding(
                      padding:
                          EdgeInsets.symmetric(horizontal: 8, vertical: 12),
                      child: Text('© OpenStreetMap contributors',
                          style:
                              TextStyle(fontSize: 12, color: Colors.black87)),
                    ),
                  ),
                ),
              IconButton(
                onPressed: _toggle,
                tooltip: _expanded
                    ? 'Replier les crédits de la carte'
                    : 'Crédits et licence de la carte',
                constraints: const BoxConstraints(minWidth: 44, minHeight: 44),
                iconSize: 20,
                icon: Icon(_expanded ? Icons.close : Icons.info_outline),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
