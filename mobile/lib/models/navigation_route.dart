class NavigationRoute {
  final int sessionId;
  final int? serviceRequestId;
  final double distanceMeters;
  final int etaMinutes;
  final String? formattedDistanceText;
  final String? formattedEtaText;
  final ClientNavInfo? client;
  final String? polyline;
  final RouteData? routeData;

  NavigationRoute({
    required this.sessionId,
    this.serviceRequestId,
    required this.distanceMeters,
    required this.etaMinutes,
    this.formattedDistanceText,
    this.formattedEtaText,
    this.client,
    this.polyline,
    this.routeData,
  });

  factory NavigationRoute.fromJson(Map<String, dynamic> json) {
    return NavigationRoute(
      sessionId: json['session_id'] as int? ?? 0,
      serviceRequestId: json['service_request_id'] as int?,
      distanceMeters: (json['distance_meters'] as num?)?.toDouble() ?? 0,
      etaMinutes: json['eta_minutes'] as int? ?? 0,
      formattedDistanceText: json['formatted_distance'] as String?,
      formattedEtaText: json['formatted_eta'] as String?,
      client: json['client'] != null
          ? ClientNavInfo.fromJson(json['client'] as Map<String, dynamic>)
          : null,
      polyline: json['polyline'] as String?,
      routeData: json['route_data'] != null
          ? RouteData.fromJson(json['route_data'] as Map<String, dynamic>)
          : null,
    );
  }

  double get distanceKm => distanceMeters / 1000;

  String get formattedDistance {
    if (formattedDistanceText != null) return formattedDistanceText!;
    if (distanceKm < 1) {
      return '${(distanceKm * 1000).toInt()} m';
    }
    return '${distanceKm.toStringAsFixed(1)} km';
  }

  String get formattedEta {
    if (formattedEtaText != null) return formattedEtaText!;
    if (etaMinutes < 60) {
      return '$etaMinutes min';
    }
    final hours = etaMinutes ~/ 60;
    final mins = etaMinutes % 60;
    return '${hours}h ${mins}min';
  }

  LatLng? get destination {
    return client?.location;
  }
}

class ClientNavInfo {
  final int id;
  final String name;
  final LatLng? location;

  ClientNavInfo({
    required this.id,
    required this.name,
    this.location,
  });

  factory ClientNavInfo.fromJson(Map<String, dynamic> json) {
    LatLng? loc;
    if (json['location'] != null) {
      final locJson = json['location'] as Map<String, dynamic>;
      loc = LatLng(
        latitude: _parseDouble(locJson['latitude']),
        longitude: _parseDouble(locJson['longitude']),
      );
    }
    return ClientNavInfo(
      id: json['id'] as int? ?? 0,
      name: json['name'] as String? ?? 'Client',
      location: loc,
    );
  }

  static double _parseDouble(dynamic value) {
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }
}

class LatLng {
  final double latitude;
  final double longitude;

  LatLng({required this.latitude, required this.longitude});

  factory LatLng.fromJson(Map<String, dynamic> json) {
    return LatLng(
      latitude: (json['latitude'] as num).toDouble(),
      longitude: (json['longitude'] as num).toDouble(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'latitude': latitude,
      'longitude': longitude,
    };
  }
}

class RouteData {
  final List<RouteLeg> legs;
  final String? overviewPolyline;

  RouteData({
    required this.legs,
    this.overviewPolyline,
  });

  factory RouteData.fromJson(Map<String, dynamic> json) {
    final routes = json['routes'] as List<dynamic>?;
    if (routes == null || routes.isEmpty) {
      return RouteData(legs: []);
    }

    final firstRoute = routes[0] as Map<String, dynamic>;
    final legsJson = firstRoute['legs'] as List<dynamic>? ?? [];
    final polyline = firstRoute['overview_polyline'] as Map<String, dynamic>?;

    return RouteData(
      legs: legsJson
          .map((e) => RouteLeg.fromJson(e as Map<String, dynamic>))
          .toList(),
      overviewPolyline: polyline?['points'] as String?,
    );
  }
}

class RouteLeg {
  final String startAddress;
  final String endAddress;
  final int distanceMeters;
  final int durationSeconds;
  final int? durationInTrafficSeconds;
  final List<RouteStep> steps;

  RouteLeg({
    required this.startAddress,
    required this.endAddress,
    required this.distanceMeters,
    required this.durationSeconds,
    this.durationInTrafficSeconds,
    required this.steps,
  });

  factory RouteLeg.fromJson(Map<String, dynamic> json) {
    final stepsJson = json['steps'] as List<dynamic>? ?? [];
    return RouteLeg(
      startAddress: json['start_address'] as String? ?? '',
      endAddress: json['end_address'] as String? ?? '',
      distanceMeters: (json['distance']?['value'] as num?)?.toInt() ?? 0,
      durationSeconds: (json['duration']?['value'] as num?)?.toInt() ?? 0,
      durationInTrafficSeconds:
          (json['duration_in_traffic']?['value'] as num?)?.toInt(),
      steps: stepsJson
          .map((e) => RouteStep.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class RouteStep {
  final String instruction;
  final int distanceMeters;
  final int durationSeconds;
  final LatLng startLocation;
  final LatLng endLocation;
  final String? maneuver;
  final String? polyline;

  RouteStep({
    required this.instruction,
    required this.distanceMeters,
    required this.durationSeconds,
    required this.startLocation,
    required this.endLocation,
    this.maneuver,
    this.polyline,
  });

  factory RouteStep.fromJson(Map<String, dynamic> json) {
    return RouteStep(
      instruction: _stripHtml(json['html_instructions'] as String? ?? ''),
      distanceMeters: (json['distance']?['value'] as num?)?.toInt() ?? 0,
      durationSeconds: (json['duration']?['value'] as num?)?.toInt() ?? 0,
      startLocation: LatLng(
        latitude: (json['start_location']?['lat'] as num?)?.toDouble() ?? 0,
        longitude: (json['start_location']?['lng'] as num?)?.toDouble() ?? 0,
      ),
      endLocation: LatLng(
        latitude: (json['end_location']?['lat'] as num?)?.toDouble() ?? 0,
        longitude: (json['end_location']?['lng'] as num?)?.toDouble() ?? 0,
      ),
      maneuver: json['maneuver'] as String?,
      polyline: json['polyline']?['points'] as String?,
    );
  }

  static String _stripHtml(String htmlString) {
    return htmlString
        .replaceAll(RegExp(r'<[^>]*>'), ' ')
        .replaceAll(RegExp(r'\s+'), ' ')
        .trim();
  }
}
