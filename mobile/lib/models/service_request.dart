class ServiceRequest {
  final int id;
  final String status;
  final String address;
  final String? fosseType;
  final double? estimatedVolume;
  final double? actualVolume;
  final String urgencyLevel;
  final double? distanceKm;
  final int? priceAmount;
  final String? priceFormatted;
  final String? paymentMethod;
  final String? paymentStatus;
  final String? clientNotes;
  final String? driverNotes;
  final DateTime? requestedAt;
  final DateTime? assignedAt;
  final DateTime? acceptedAt;
  final DateTime? startedAt;
  final DateTime? completedAt;
  final DateTime? canceledAt;
  final String? photoBefore;
  final String? photoAfter;
  final int? rating;
  final String? ratingComment;
  final Driver? driver;
  final ServiceLocation? location;
  final InterventionSummary? intervention;

  const ServiceRequest({
    required this.id,
    required this.status,
    required this.address,
    this.fosseType,
    this.estimatedVolume,
    this.actualVolume,
    this.urgencyLevel = 'normal',
    this.distanceKm,
    this.priceAmount,
    this.priceFormatted,
    this.paymentMethod,
    this.paymentStatus,
    this.clientNotes,
    this.driverNotes,
    this.requestedAt,
    this.assignedAt,
    this.acceptedAt,
    this.startedAt,
    this.completedAt,
    this.canceledAt,
    this.photoBefore,
    this.photoAfter,
    this.rating,
    this.ratingComment,
    this.driver,
    this.location,
    this.intervention,
  });

  factory ServiceRequest.fromJson(Map<String, dynamic> json) {
    return ServiceRequest(
      id: json['id'] as int,
      status: json['status'] as String,
      address: json['address'] as String? ?? '',
      fosseType: json['fosse_type'] as String?,
      estimatedVolume: _parseDouble(json['estimated_volume']),
      actualVolume: _parseDouble(json['actual_volume']),
      urgencyLevel: json['urgency_level'] as String? ?? 'normal',
      distanceKm: _parseDouble(json['distance_km']),
      priceAmount: json['price_amount'] as int?,
      priceFormatted: json['price_formatted'] as String?,
      paymentMethod: json['payment_method'] as String?,
      paymentStatus: json['payment_status'] as String?,
      clientNotes: json['client_notes'] as String?,
      driverNotes: json['driver_notes'] as String?,
      requestedAt: _parseDateTime(json['requested_at']),
      assignedAt: _parseDateTime(json['assigned_at']),
      acceptedAt: _parseDateTime(json['accepted_at']),
      startedAt: _parseDateTime(json['started_at']),
      completedAt: _parseDateTime(json['completed_at']),
      canceledAt: _parseDateTime(json['canceled_at']),
      photoBefore: json['photo_before'] as String?,
      photoAfter: json['photo_after'] as String?,
      rating: json['rating'] as int?,
      ratingComment: json['rating_comment'] as String?,
      driver: json['driver'] != null ? Driver.fromJson(json['driver']) : null,
      location: json['location'] != null
          ? ServiceLocation.fromJson(json['location'])
          : null,
      intervention: json['intervention'] != null
          ? InterventionSummary.fromJson(json['intervention'])
          : null,
    );
  }

  static DateTime? _parseDateTime(dynamic value) {
    if (value == null) return null;
    return DateTime.tryParse(value as String);
  }

  static double? _parseDouble(dynamic value) {
    if (value == null) return null;
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value);
    return null;
  }

  String get statusLabel {
    switch (status) {
      case 'pending':
        return 'En attente';
      case 'assigned':
        return 'Assignée';
      case 'accepted':
        return 'Acceptée';
      case 'rejected':
        return 'Refusée';
      case 'in_progress':
        return 'En cours';
      case 'completed':
        return 'Terminée';
      case 'canceled':
        return 'Annulée';
      default:
        return status;
    }
  }

  String get fosseTypeLabel {
    switch (fosseType) {
      case 'traditionnelle':
        return 'Fosse traditionnelle';
      case 'septique':
        return 'Fosse septique';
      case 'toutes_eaux':
        return 'Fosse toutes eaux';
      default:
        return fosseType ?? 'Non spécifié';
    }
  }

  String get urgencyLabel {
    switch (urgencyLevel) {
      case 'normal':
        return 'Normal';
      case 'urgent':
        return 'Urgent';
      case 'emergency':
        return 'Urgence';
      default:
        return urgencyLevel;
    }
  }

  bool get canCancel =>
      status == 'pending' || status == 'assigned' || status == 'accepted';

  bool get canRate => status == 'completed' && rating == null;

  bool get isActive =>
      status == 'pending' || status == 'assigned' ||
      status == 'accepted' || status == 'in_progress';

  /// Alias for requestedAt for easier usage
  DateTime? get createdAt => requestedAt;

  /// Driver name if assigned
  String? get driverName => driver?.name;

  /// Notes from client
  String? get notes => clientNotes;

  /// Calculated estimated price (BASE_PRICE + volume * PRICE_PER_M3)
  double get estimatedPrice {
    if (priceAmount != null) return priceAmount!.toDouble();
    const basePrice = 15000;
    const pricePerM3 = 5000;
    return basePrice + (estimatedVolume ?? 0) * pricePerM3;
  }

  /// Final price after intervention
  double? get finalPrice {
    if (status != 'completed') return null;
    if (priceAmount != null) return priceAmount!.toDouble();
    const basePrice = 15000;
    const pricePerM3 = 5000;
    return basePrice + (actualVolume ?? estimatedVolume ?? 0) * pricePerM3;
  }
}

class Driver {
  final int id;
  final String name;
  final String? email;
  final String? phone;

  const Driver({
    required this.id,
    required this.name,
    this.email,
    this.phone,
  });

  factory Driver.fromJson(Map<String, dynamic> json) {
    return Driver(
      id: json['id'] as int,
      name: json['name'] as String,
      email: json['email'] as String?,
      phone: json['phone'] as String?,
    );
  }
}

class ServiceLocation {
  final int id;
  final double latitude;
  final double longitude;
  final String? address;
  final DateTime? capturedAt;

  const ServiceLocation({
    required this.id,
    required this.latitude,
    required this.longitude,
    this.address,
    this.capturedAt,
  });

  factory ServiceLocation.fromJson(Map<String, dynamic> json) {
    return ServiceLocation(
      id: json['id'] as int,
      latitude: _parseDouble(json['latitude']),
      longitude: _parseDouble(json['longitude']),
      address: json['address'] as String?,
      capturedAt: json['captured_at'] != null
          ? DateTime.tryParse(json['captured_at'] as String)
          : null,
    );
  }

  static double _parseDouble(dynamic value) {
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }
}

class InterventionSummary {
  final int id;
  final DateTime? scheduledAt;
  final String? status;
  final DateTime? startedAt;
  final DateTime? completedAt;

  const InterventionSummary({
    required this.id,
    this.scheduledAt,
    this.status,
    this.startedAt,
    this.completedAt,
  });

  factory InterventionSummary.fromJson(Map<String, dynamic> json) {
    return InterventionSummary(
      id: json['id'] as int,
      scheduledAt: json['scheduled_at'] != null
          ? DateTime.tryParse(json['scheduled_at'] as String)
          : null,
      status: json['status'] as String?,
      startedAt: json['started_at'] != null
          ? DateTime.tryParse(json['started_at'] as String)
          : null,
      completedAt: json['completed_at'] != null
          ? DateTime.tryParse(json['completed_at'] as String)
          : null,
    );
  }
}
