class Vendor {
  final int id;
  final String businessName;
  final String vendorType;
  final String slug;
  final String? description;
  final String? logoUrl;
  final String? addressText;
  final String verificationLevel;

  Vendor({
    required this.id,
    required this.businessName,
    required this.vendorType,
    required this.slug,
    required this.verificationLevel,
    this.description,
    this.logoUrl,
    this.addressText,
  });

  bool get isVerified => verificationLevel == 'verifie';

  factory Vendor.fromJson(Map<String, dynamic> json) {
    return Vendor(
      id: json['id'],
      businessName: json['business_name'],
      vendorType: json['vendor_type'],
      slug: json['slug'],
      description: json['description'],
      logoUrl: json['logo_url'],
      addressText: json['address_text'],
      verificationLevel: json['verification_level'] ?? 'non_verifie',
    );
  }
}
