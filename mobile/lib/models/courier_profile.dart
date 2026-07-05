class CourierProfile {
  final int id;
  final String vehicleType;
  final String verificationStatus;
  final bool isAvailable;
  final double? ratingAverage;

  CourierProfile({
    required this.id,
    required this.vehicleType,
    required this.verificationStatus,
    required this.isAvailable,
    this.ratingAverage,
  });

  factory CourierProfile.fromJson(Map<String, dynamic> json) {
    return CourierProfile(
      id: json['id'],
      vehicleType: json['vehicle_type'],
      verificationStatus: json['verification_status'] ?? 'en_attente',
      isAvailable: json['is_available'] ?? false,
      ratingAverage: json['rating_average'] != null ? double.tryParse(json['rating_average'].toString()) : null,
    );
  }
}

const Map<String, String> vehicleTypeLabels = {
  'moto': 'Moto',
  'tricycle': 'Tricycle',
  'velo': 'Vélo',
  'pied': 'À pied',
};

const Map<String, String> courierVerificationLabels = {
  'en_attente': 'En attente',
  'verifie': 'Vérifié',
  'rejete': 'Rejeté',
};
