import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/listing.dart';
import '../../services/api_client.dart';
import '../../services/vendor_portal_service.dart';
import '../../theme/app_theme.dart';

class VendorListingFormScreen extends StatefulWidget {
  final Listing? listing;

  const VendorListingFormScreen({super.key, this.listing});

  @override
  State<VendorListingFormScreen> createState() => _VendorListingFormScreenState();
}

class _VendorListingFormScreenState extends State<VendorListingFormScreen> {
  late final TextEditingController _nameController;
  late final TextEditingController _descriptionController;
  late final TextEditingController _priceController;
  late final TextEditingController _stockController;
  String _type = 'produit';
  bool _isLoading = false;
  String? _errorMessage;

  bool get _isEditing => widget.listing != null;

  @override
  void initState() {
    super.initState();
    final listing = widget.listing;
    _nameController = TextEditingController(text: listing?.name ?? '');
    _descriptionController = TextEditingController(text: listing?.description ?? '');
    _priceController = TextEditingController(text: listing != null ? listing.price.toStringAsFixed(0) : '');
    _stockController = TextEditingController(text: listing?.stockQuantity?.toString() ?? '');
    _type = listing?.type ?? 'produit';
  }

  Future<void> _submit() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final portal = context.read<VendorPortalService>();
    final price = double.tryParse(_priceController.text) ?? 0;
    final stock = _stockController.text.isEmpty ? null : int.tryParse(_stockController.text);

    try {
      if (_isEditing) {
        await portal.updateListing(widget.listing!.id, {
          'type': _type,
          'name': _nameController.text,
          'description': _descriptionController.text,
          'price': price,
          'stock_quantity': stock,
        });
      } else {
        await portal.createListing(
          type: _type,
          name: _nameController.text,
          price: price,
          description: _descriptionController.text,
          stockQuantity: stock,
        );
      }

      if (!mounted) return;
      Navigator.of(context).pop();
    } on ApiException catch (e) {
      setState(() => _errorMessage = e.errorFor('name') ?? e.errorFor('price') ?? e.message);
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(_isEditing ? "Modifier l'article" : 'Ajouter un article')),
      body: ListView(
        padding: const EdgeInsets.all(24),
        children: [
          DropdownButtonFormField<String>(
            initialValue: _type,
            decoration: const InputDecoration(labelText: 'Type'),
            items: const [
              DropdownMenuItem(value: 'produit', child: Text('Produit')),
              DropdownMenuItem(value: 'plat_du_jour', child: Text('Plat du jour')),
              DropdownMenuItem(value: 'menu_item', child: Text('Menu item')),
            ],
            onChanged: (value) => setState(() => _type = value!),
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _nameController,
            decoration: const InputDecoration(labelText: "Nom de l'article"),
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _descriptionController,
            maxLines: 3,
            decoration: const InputDecoration(labelText: 'Description'),
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _priceController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(labelText: 'Prix (XOF)'),
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _stockController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(labelText: 'Stock (laisser vide si non applicable)'),
          ),
          if (_errorMessage != null) ...[
            const SizedBox(height: 12),
            Text(_errorMessage!, style: const TextStyle(color: AppColors.error)),
          ],
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: _isLoading ? null : _submit,
            child: _isLoading
                ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                : const Text('Enregistrer'),
          ),
        ],
      ),
    );
  }
}
