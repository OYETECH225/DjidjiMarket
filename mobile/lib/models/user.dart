class AppUser {
  final int id;
  final String name;
  final String phone;
  final bool phoneVerified;
  final String? email;
  final String role;

  AppUser({
    required this.id,
    required this.name,
    required this.phone,
    required this.phoneVerified,
    required this.role,
    this.email,
  });

  factory AppUser.fromJson(Map<String, dynamic> json) {
    return AppUser(
      id: json['id'],
      name: json['name'],
      phone: json['phone'],
      phoneVerified: json['phone_verified'] ?? false,
      email: json['email'],
      role: json['role'],
    );
  }
}
