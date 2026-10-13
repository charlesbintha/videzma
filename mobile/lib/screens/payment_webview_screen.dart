import "package:flutter/material.dart";
import "package:webview_flutter/webview_flutter.dart";
import "../theme/app_colors.dart";

/// Résultat retourné par le screen de paiement.
enum PaymentResult { success, canceled, error }

/// Écran WebView affichant le checkout Paytech.
/// Pop avec un [PaymentResult] quand le paiement est terminé.
class PaymentWebViewScreen extends StatefulWidget {
  final String redirectUrl;
  final String title;

  const PaymentWebViewScreen({
    super.key,
    required this.redirectUrl,
    required this.title,
  });

  @override
  State<PaymentWebViewScreen> createState() => _PaymentWebViewScreenState();
}

class _PaymentWebViewScreenState extends State<PaymentWebViewScreen> {
  late final WebViewController _controller;
  bool _isLoading = true;
  bool _hasError = false;

  @override
  void initState() {
    super.initState();

    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(Colors.white)
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageStarted: (_) {
            if (mounted) setState(() => _isLoading = true);
          },
          onPageFinished: (_) {
            if (mounted) setState(() => _isLoading = false);
          },
          onWebResourceError: (_) {
            if (mounted) setState(() { _isLoading = false; _hasError = true; });
          },
          onNavigationRequest: (NavigationRequest request) {
            final url = request.url;

            // Détecter la page de succès retournée par notre backend
            if (url.contains("/payment/success")) {
              Navigator.of(context).pop(PaymentResult.success);
              return NavigationDecision.prevent;
            }

            // Détecter la page d'annulation
            if (url.contains("/payment/cancel")) {
              Navigator.of(context).pop(PaymentResult.canceled);
              return NavigationDecision.prevent;
            }

            return NavigationDecision.navigate;
          },
        ),
      )
      ..loadRequest(Uri.parse(widget.redirectUrl));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      appBar: AppBar(
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        elevation: 0,
        title: Text(
          widget.title,
          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
        ),
        leading: IconButton(
          icon: const Icon(Icons.close),
          tooltip: "Annuler le paiement",
          onPressed: () => Navigator.of(context).pop(PaymentResult.canceled),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            tooltip: "Actualiser",
            onPressed: () => _controller.reload(),
          ),
        ],
      ),
      body: Stack(
        children: [
          if (!_hasError)
            WebViewWidget(controller: _controller)
          else
            _ErrorView(onRetry: () {
              setState(() { _hasError = false; _isLoading = true; });
              _controller.loadRequest(Uri.parse(widget.redirectUrl));
            }),
          if (_isLoading && !_hasError)
            Container(
              color: Colors.white,
              child: Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    CircularProgressIndicator(color: AppColors.primary),
                    const SizedBox(height: 16),
                    const Text(
                      "Chargement du paiement…",
                      style: TextStyle(color: AppColors.textMuted, fontSize: 14),
                    ),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _ErrorView extends StatelessWidget {
  final VoidCallback onRetry;

  const _ErrorView({required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.wifi_off_rounded, size: 64, color: Colors.grey.shade400),
            const SizedBox(height: 16),
            const Text(
              "Impossible de charger la page de paiement",
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
            ),
            const SizedBox(height: 8),
            const Text(
              "Vérifiez votre connexion internet et réessayez.",
              textAlign: TextAlign.center,
              style: TextStyle(color: AppColors.textMuted),
            ),
            const SizedBox(height: 24),
            ElevatedButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh),
              label: const Text("Réessayer"),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
