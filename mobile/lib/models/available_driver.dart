class AvailableDriver {
  final int id;
  final String name;
  final String? phone;
  final String? email;
  final String vehicleType;
  final int? tankCapacity;
  final String? zoneCoverage;
  final DriverLocation? location;
  final double distanceKm;
  final String distanceFormatted;

  AvailableDriver({
    required this.id,
    required this.name,
    this.phone,
    this.email,
    required this.vehicleType,
    this.tankCapacity,
    this.zoneCoverage,
    this.location,
    required this.distanceKm,
    required this.distanceFormatted,
  });

  factory AvailableDriver.fromJson(Map<String, dynamic> json) {
    return AvailableDriver(
      id: json['id'] as int,
      name: json['name'] as String,
      phone: json['phone'] as String?,
      email: json['email'] as String?,
      vehicleType: json['vehicle_type'] as String? ?? 'Camion citerne',
      tankCapacity: json['tank_capacity'] as int?,
      zoneCoverage: json['zone_coverage'] as String?,
      location: json['location'] != null
          ? DriverLocation.fromJson(json['location'] as Map<String, dynamic>)
          : null,
      distanceKm: (json['distance_km'] as num).toDouble(),
      distanceFormatted: json['distance_formatted'] as String? ?? '',
    );
  }

  String get capacityLabel {
    if (tankCapacity != null) {
      return '$tankCapacity m³';
    }
    return 'Capacité non spécifiée';
  }
}

class DriverLocation {
  final double latitude;
  final double longitude;

  DriverLocation({
    required this.latitude,
    required this.longitude,
  });

  factory DriverLocation.fromJson(Map<String, dynamic> json) {
    return DriverLocation(
      latitude: (json['latitude'] as num).toDouble(),
      longitude: (json['longitude'] as num).toDouble(),
    );
  }
}
