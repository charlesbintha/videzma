class SubscriptionPlan {
  final int id;
  final String name;
  final String slug;
  final String? description;
  final String periodicity;
  final String periodicityLabel;
  final int periodDays;
  final int interventionsPerPeriod;
  final double maxVolumePerIntervention;
  final int price;
  final String formattedPrice;
  final int extraVolumePrice;
  final int discountPercent;
  final bool isFeatured;

  const SubscriptionPlan({
    required this.id,
    required this.name,
    required this.slug,
    this.description,
    required this.periodicity,
    required this.periodicityLabel,
    required this.periodDays,
    required this.interventionsPerPeriod,
    required this.maxVolumePerIntervention,
    required this.price,
    required this.formattedPrice,
    required this.extraVolumePrice,
    required this.discountPercent,
    required this.isFeatured,
  });

  factory SubscriptionPlan.fromJson(Map<String, dynamic> json) {
    return SubscriptionPlan(
      id: json['id'] as int,
      name: json['name'] as String,
      slug: json['slug'] as String,
      description: json['description'] as String?,
      periodicity: json['periodicity'] as String,
      periodicityLabel: json['periodicity_label'] as String? ?? json['periodicity'] as String,
      periodDays: json['period_days'] as int? ?? 30,
      interventionsPerPeriod: json['interventions_per_period'] as int? ?? 1,
      maxVolumePerIntervention: _parseDouble(json['max_volume_per_intervention']),
      price: json['price'] as int,
      formattedPrice: json['formatted_price'] as String? ?? '${json['price']} FCFA',
      extraVolumePrice: json['extra_volume_price'] as int? ?? 5000,
      discountPercent: json['discount_percent'] as int? ?? 0,
      isFeatured: json['is_featured'] as bool? ?? false,
    );
  }

  static double _parseDouble(dynamic value) {
    if (value == null) return 10.0;
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 10.0;
    return 10.0;
  }
}

class ClientSubscription {
  final int id;
  final String status;
  final SubscriptionPlan plan;
  final String currentPeriodStart;
  final String currentPeriodEnd;
  final int interventionsUsed;
  final int remainingInterventions;
  final double volumeUsed;
  final int remainingDays;
  final String? paymentMethod;
  final String paymentStatus;
  final bool autoRenew;
  final String createdAt;

  const ClientSubscription({
    required this.id,
    required this.status,
    required this.plan,
    required this.currentPeriodStart,
    required this.currentPeriodEnd,
    required this.interventionsUsed,
    required this.remainingInterventions,
    required this.volumeUsed,
    required this.remainingDays,
    this.paymentMethod,
    required this.paymentStatus,
    required this.autoRenew,
    required this.createdAt,
  });

  factory ClientSubscription.fromJson(Map<String, dynamic> json) {
    return ClientSubscription(
      id: json['id'] as int,
      status: json['status'] as String,
      plan: SubscriptionPlan.fromJson(json['plan'] as Map<String, dynamic>),
      currentPeriodStart: json['current_period_start'] as String,
      currentPeriodEnd: json['current_period_end'] as String,
      interventionsUsed: json['interventions_used'] as int? ?? 0,
      remainingInterventions: json['remaining_interventions'] as int? ?? 0,
      volumeUsed: (json['volume_used'] as num?)?.toDouble() ?? 0,
      remainingDays: json['remaining_days'] as int? ?? 0,
      paymentMethod: json['payment_method'] as String?,
      paymentStatus: json['payment_status'] as String? ?? 'pending',
      autoRenew: json['auto_renew'] as bool? ?? true,
      createdAt: json['created_at'] as String,
    );
  }

  bool get isActive => status == 'active';

  String get statusLabel {
    switch (status) {
      case 'active':
        return 'Actif';
      case 'paused':
        return 'En pause';
      case 'cancelled':
        return 'Annule';
      case 'expired':
        return 'Expire';
      default:
        return status;
    }
  }
}
