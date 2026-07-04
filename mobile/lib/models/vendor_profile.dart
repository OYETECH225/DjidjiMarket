/// Owner's view of their own vendor profile (GET/PATCH /api/vendor/me) —
/// includes fields the public VendorResource deliberately omits.
class VendorProfile {
  final int id;
  final String businessName;
  final String vendorType;
  final String slug;
  final String? description;
  final String? logoUrl;
  final String? addressText;
  final String verificationLevel;
  final double commissionRate;
  final bool isActive;

  VendorProfile({
    required this.id,
    required this.businessName,
    required this.vendorType,
    required this.slug,
    required this.verificationLevel,
    required this.commissionRate,
    required this.isActive,
    this.description,
    this.logoUrl,
    this.addressText,
  });

  factory VendorProfile.fromJson(Map<String, dynamic> json) {
    return VendorProfile(
      id: json['id'],
      businessName: json['business_name'],
      vendorType: json['vendor_type'],
      slug: json['slug'],
      description: json['description'],
      logoUrl: json['logo_url'],
      addressText: json['address_text'],
      verificationLevel: json['verification_level'] ?? 'non_verifie',
      commissionRate: double.parse((json['commission_rate'] ?? 0).toString()),
      isActive: json['is_active'] ?? false,
    );
  }
}

const Map<String, String> vendorVerificationLabels = {
  'non_verifie': 'Non vérifié',
  'identite_confirmee': 'Identité confirmée',
  'verifie': 'Vérifié',
};
