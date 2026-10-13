class LocationPoint {
  final double latitude;
  final double longitude;
  final String? address;
  final DateTime? capturedAt;

  LocationPoint({
    required this.latitude,
    required this.longitude,
    this.address,
    this.capturedAt,
  });

  factory LocationPoint.fromJson(Map<String, dynamic> json) {
    return LocationPoint(
      latitude: (json["latitude"] as num?)?.toDouble() ?? 0,
      longitude: (json["longitude"] as num?)?.toDouble() ?? 0,
      address: json["address"] as String?,
      capturedAt: json["captured_at"] != null
          ? DateTime.tryParse(json["captured_at"] as String)
          : null,
    );
  }
}
