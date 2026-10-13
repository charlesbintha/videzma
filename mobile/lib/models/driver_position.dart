/// Represents the real-time position of a driver during navigation
class DriverPosition {
  final double latitude;
  final double longitude;
  final int timestamp;
  final int? distanceMeters;
  final int? etaMinutes;
  final String driverName;
  final String? driverPhone;
  final bool serverFresh;

  DriverPosition({
    required this.latitude,
    required this.longitude,
    required this.timestamp,
    this.distanceMeters,
    this.etaMinutes,
    required this.driverName,
    this.driverPhone,
    this.serverFresh = true,
  });

  /// Parse from optimized API response
  /// API returns: { tracking: true, p: {lat, lng, t}, d, eta, driver: {name, phone} }
  factory DriverPosition.fromJson(Map<String, dynamic> json) {
    final position = json['p'] as Map<String, dynamic>;
    final driver = json['driver'] as Map<String, dynamic>?;

    return DriverPosition(
      latitude: (position['lat'] as num).toDouble(),
      longitude: (position['lng'] as num).toDouble(),
      timestamp: position['t'] as int,
      distanceMeters: json['d'] as int?,
      etaMinutes: json['eta'] as int?,
      driverName: driver?['name'] as String? ?? 'Vidangeur',
      driverPhone: driver?['phone'] as String?,
      serverFresh: json['is_fresh'] as bool? ?? true,
    );
  }

  bool get isFresh {
    final age = DateTime.now().millisecondsSinceEpoch ~/ 1000 - timestamp;
    return serverFresh && age >= -30 && age <= 120;
  }

  /// Format distance for display
  String get formattedDistance {
    if (distanceMeters == null) return '--';
    if (distanceMeters! < 1000) {
      return '$distanceMeters m';
    }
    return '${(distanceMeters! / 1000).toStringAsFixed(1)} km';
  }

  /// Format ETA for display
  String get formattedEta {
    if (!isFresh || etaMinutes == null) return '--';
    if (etaMinutes! < 1) {
      return 'Arrivee imminente';
    }
    if (etaMinutes! < 60) {
      return '$etaMinutes min';
    }
    final hours = etaMinutes! ~/ 60;
    final mins = etaMinutes! % 60;
    if (mins == 0) {
      return '${hours}h';
    }
    return '${hours}h ${mins}min';
  }

  /// Check if driver is very close (within 100m)
  bool get isArriving =>
      isFresh && distanceMeters != null && distanceMeters! < 100;

  /// Check if driver is nearby (within 500m)
  bool get isNearby =>
      isFresh && distanceMeters != null && distanceMeters! < 500;

  @override
  String toString() {
    return 'DriverPosition(lat: $latitude, lng: $longitude, distance: $formattedDistance, eta: $formattedEta)';
  }
}

/// Result of checking tracking status
class TrackingStatus {
  final bool available;
  final String? status;
  final DateTime? navigationStartedAt;
  final String? reason;

  TrackingStatus({
    required this.available,
    this.status,
    this.navigationStartedAt,
    this.reason,
  });

  factory TrackingStatus.fromJson(Map<String, dynamic> json) {
    return TrackingStatus(
      available: json['available'] as bool? ?? false,
      status: json['status'] as String?,
      navigationStartedAt: json['navigation_started_at'] != null
          ? DateTime.parse(json['navigation_started_at'] as String)
          : null,
      reason: json['reason'] as String?,
    );
  }
}
