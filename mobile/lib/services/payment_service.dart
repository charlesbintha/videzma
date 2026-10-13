import "dart:convert";
import "api_client.dart";

class PaymentService {
  final ApiClient _client = ApiClient();

  /// Initier le paiement Paytech pour une demande de service.
  /// Retourne l'URL de redirection vers le checkout Paytech, ou null en cas d'erreur.
  Future<String?> initiateServicePayment(int serviceRequestId) async {
    try {
      final response = await _client.post(
        "/payments/service/$serviceRequestId/initiate",
        {},
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body) as Map<String, dynamic>;
        return data["redirect_url"] as String?;
      }
      return null;
    } catch (_) {
      return null;
    }
  }

  /// Initier le paiement Paytech pour une souscription.
  Future<String?> initiateSubscriptionPayment(int subscriptionId) async {
    try {
      final response = await _client.post(
        "/payments/subscription/$subscriptionId/initiate",
        {},
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body) as Map<String, dynamic>;
        return data["redirect_url"] as String?;
      }
      return null;
    } catch (_) {
      return null;
    }
  }

  /// Vérifier le statut de paiement d'une demande de service.
  Future<String?> getServicePaymentStatus(int serviceRequestId) async {
    try {
      final response = await _client.get(
        "/payments/service/$serviceRequestId/status",
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body) as Map<String, dynamic>;
        return data["payment_status"] as String?;
      }
      return null;
    } catch (_) {
      return null;
    }
  }
}
