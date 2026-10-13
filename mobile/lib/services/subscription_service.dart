import 'dart:convert';
import '../models/subscription_plan.dart';
import 'api_client.dart';
import 'logger_service.dart';

class SubscriptionService {
  static const _tag = 'SubscriptionService';
  final ApiClient _client;

  SubscriptionService({ApiClient? client}) : _client = client ?? ApiClient();

  /// Liste tous les forfaits disponibles.
  Future<List<SubscriptionPlan>> getPlans() async {
    logger.info(_tag, 'Chargement des forfaits...');

    try {
      final response = await _client.get('/api/subscriptions/plans');

      if (response.statusCode < 200 || response.statusCode >= 300) {
        logger.warning(_tag, 'Erreur liste forfaits: ${response.statusCode}');
        return [];
      }

      final payload = jsonDecode(response.body) as Map<String, dynamic>;
      final data = payload['data'] as List<dynamic>? ?? [];
      logger.info(_tag, '${data.length} forfaits charges');

      return data
          .map((e) => SubscriptionPlan.fromJson(e as Map<String, dynamic>))
          .toList();
    } catch (e) {
      logger.error(_tag, 'Exception chargement forfaits', e);
      return [];
    }
  }

  /// Recupere l'abonnement actif du client.
  Future<ClientSubscription?> getMySubscription() async {
    logger.info(_tag, 'Chargement abonnement actif...');

    try {
      final response = await _client.get('/api/subscriptions/my');

      if (response.statusCode < 200 || response.statusCode >= 300) {
        logger.warning(_tag, 'Erreur abonnement: ${response.statusCode}');
        return null;
      }

      final payload = jsonDecode(response.body) as Map<String, dynamic>;
      final data = payload['data'] as Map<String, dynamic>?;

      if (data == null) return null;
      return ClientSubscription.fromJson(data);
    } catch (e) {
      logger.error(_tag, 'Exception chargement abonnement', e);
      return null;
    }
  }

  /// Historique des abonnements.
  Future<List<ClientSubscription>> getHistory() async {
    logger.info(_tag, 'Chargement historique abonnements...');

    try {
      final response = await _client.get('/api/subscriptions/history');

      if (response.statusCode < 200 || response.statusCode >= 300) {
        logger.warning(_tag, 'Erreur historique: ${response.statusCode}');
        return [];
      }

      final payload = jsonDecode(response.body) as Map<String, dynamic>;
      final data = payload['data'] as List<dynamic>? ?? [];

      return data
          .map((e) => ClientSubscription.fromJson(e as Map<String, dynamic>))
          .toList();
    } catch (e) {
      logger.error(_tag, 'Exception historique', e);
      return [];
    }
  }

  /// Souscrire a un forfait.
  Future<SubscriptionResult> subscribe({
    required int planId,
    required String paymentMethod,
  }) async {
    logger.info(_tag, 'Souscription au forfait #$planId...');

    try {
      final response = await _client.post('/api/subscriptions/subscribe', {
        'plan_id': planId,
        'payment_method': paymentMethod,
      });

      final payload = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode < 200 || response.statusCode >= 300) {
        return SubscriptionResult(
          success: false,
          message: payload['message'] as String? ?? 'Erreur lors de la souscription',
        );
      }

      final data = payload['data'] as Map<String, dynamic>?;
      return SubscriptionResult(
        success: true,
        message: payload['message'] as String?,
        subscription: data != null ? ClientSubscription.fromJson(data) : null,
      );
    } catch (e) {
      logger.error(_tag, 'Exception souscription', e);
      return SubscriptionResult(
        success: false,
        message: 'Erreur: $e',
      );
    }
  }

  /// Annuler l'abonnement.
  Future<SubscriptionResult> cancel() async {
    logger.info(_tag, 'Annulation abonnement...');

    try {
      final response = await _client.post('/api/subscriptions/cancel', {});
      final payload = jsonDecode(response.body) as Map<String, dynamic>;

      return SubscriptionResult(
        success: response.statusCode >= 200 && response.statusCode < 300,
        message: payload['message'] as String?,
      );
    } catch (e) {
      logger.error(_tag, 'Exception annulation', e);
      return SubscriptionResult(
        success: false,
        message: 'Erreur: $e',
      );
    }
  }

  /// Estimer le prix d'un forfait personnalise.
  Future<PriceEstimate?> estimatePrice({
    required String periodicity,
    required int interventionsPerPeriod,
    double? volume,
  }) async {
    logger.info(_tag, 'Estimation prix...');

    try {
      final response = await _client.post('/api/subscriptions/estimate', {
        'periodicity': periodicity,
        'interventions_per_period': interventionsPerPeriod,
        if (volume != null) 'volume': volume,
      });

      if (response.statusCode < 200 || response.statusCode >= 300) {
        return null;
      }

      final payload = jsonDecode(response.body) as Map<String, dynamic>;
      final data = payload['data'] as Map<String, dynamic>?;

      if (data == null) return null;
      return PriceEstimate.fromJson(data);
    } catch (e) {
      logger.error(_tag, 'Exception estimation', e);
      return null;
    }
  }
}

class SubscriptionResult {
  final bool success;
  final String? message;
  final ClientSubscription? subscription;

  SubscriptionResult({
    required this.success,
    this.message,
    this.subscription,
  });
}

class PriceEstimate {
  final String periodicity;
  final String periodicityLabel;
  final int interventionsPerPeriod;
  final double volumePerIntervention;
  final int pricePerIntervention;
  final int totalWithoutDiscount;
  final int discountPercent;
  final int discountAmount;
  final int finalPrice;
  final String formattedPrice;

  const PriceEstimate({
    required this.periodicity,
    required this.periodicityLabel,
    required this.interventionsPerPeriod,
    required this.volumePerIntervention,
    required this.pricePerIntervention,
    required this.totalWithoutDiscount,
    required this.discountPercent,
    required this.discountAmount,
    required this.finalPrice,
    required this.formattedPrice,
  });

  factory PriceEstimate.fromJson(Map<String, dynamic> json) {
    return PriceEstimate(
      periodicity: json['periodicity'] as String,
      periodicityLabel: json['periodicity_label'] as String,
      interventionsPerPeriod: json['interventions_per_period'] as int,
      volumePerIntervention: (json['volume_per_intervention'] as num).toDouble(),
      pricePerIntervention: json['price_per_intervention'] as int,
      totalWithoutDiscount: json['total_without_discount'] as int,
      discountPercent: json['discount_percent'] as int,
      discountAmount: json['discount_amount'] as int,
      finalPrice: json['final_price'] as int,
      formattedPrice: json['formatted_price'] as String,
    );
  }
}
