class AuthUser {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String role;
  final String status;
  final String? locale;

  AuthUser({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    required this.role,
    required this.status,
    this.locale,
  });

  factory AuthUser.fromJson(Map<String, dynamic> json) {
    return AuthUser(
      id: json["id"] as int,
      name: json["name"] as String? ?? "",
      email: json["email"] as String? ?? "",
      phone: json["phone"] as String?,
      role: json["role"] as String? ?? "",
      status: json["status"] as String? ?? "",
      locale: json["locale"] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      "id": id,
      "name": name,
      "email": email,
      "phone": phone,
      "role": role,
      "status": status,
      "locale": locale,
    };
  }

  AuthUser copyWith({
    String? name,
    String? phone,
    String? locale,
  }) {
    return AuthUser(
      id: id,
      name: name ?? this.name,
      email: email,
      phone: phone ?? this.phone,
      role: role,
      status: status,
      locale: locale ?? this.locale,
    );
  }

  String get roleLabel {
    switch (role) {
      case 'client':
        return 'Client';
      case 'driver':
        return 'Vidangeur';
      case 'admin':
        return 'Administrateur';
      default:
        return role;
    }
  }

  String get statusLabel {
    switch (status) {
      case 'active':
        return 'Actif';
      case 'pending':
        return 'En attente';
      case 'suspended':
        return 'Suspendu';
      case 'inactive':
        return 'Inactif';
      default:
        return status;
    }
  }

  bool get isDriver => role == 'driver';
  bool get isClient => role == 'client';
  bool get isAdmin => role == 'admin';
}
