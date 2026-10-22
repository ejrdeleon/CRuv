class UserModel {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String role;
  final String? barangay;
  final String? address;
  final bool isActive;
  final DateTime? createdAt;

  const UserModel({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    required this.role,
    this.barangay,
    this.address,
    this.isActive = true,
    this.createdAt,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: (json['id'] as num).toInt(),
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
      phone: json['phone'] as String?,
      role: json['role'] as String? ?? 'resident',
      barangay: json['barangay'] as String?,
      address: json['address'] as String?,
      isActive: json['is_active'] as bool? ?? true,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'].toString())
          : null,
    );
  }
}

class UserStats {
  final int total;
  final int active;
  final int resolved;
  final int unreadNotifications;
  final int safetyAlerts;

  const UserStats({
    this.total = 0,
    this.active = 0,
    this.resolved = 0,
    this.unreadNotifications = 0,
    this.safetyAlerts = 0,
  });

  factory UserStats.fromJson(Map<String, dynamic>? json) {
    if (json == null) {
      return const UserStats();
    }
    return UserStats(
      total: (json['total'] as num?)?.toInt() ?? 0,
      active: (json['active'] as num?)?.toInt() ?? 0,
      resolved: (json['resolved'] as num?)?.toInt() ?? 0,
      unreadNotifications: (json['unread_notifications'] as num?)?.toInt() ?? 0,
      safetyAlerts: (json['safety_alerts'] as num?)?.toInt() ?? 0,
    );
  }
}
