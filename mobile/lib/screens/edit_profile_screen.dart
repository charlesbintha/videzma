import "package:flutter/material.dart";
import "../theme/app_colors.dart";
import "../models/auth_user.dart";
import "../services/profile_service.dart";

class EditProfileScreen extends StatefulWidget {
  final AuthUser user;

  const EditProfileScreen({super.key, required this.user});

  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> {
  final ProfileService _profileService = ProfileService();
  final _formKey = GlobalKey<FormState>();

  late TextEditingController _nameController;
  late TextEditingController _phoneController;
  String _selectedLocale = 'fr';

  bool _saving = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _nameController = TextEditingController(text: widget.user.name);
    _phoneController = TextEditingController(text: widget.user.phone ?? '');
    _selectedLocale = widget.user.locale ?? 'fr';
  }

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  bool get _hasChanges {
    return _nameController.text != widget.user.name ||
        _phoneController.text != (widget.user.phone ?? '') ||
        _selectedLocale != (widget.user.locale ?? 'fr');
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _saving = true;
      _error = null;
    });

    final result = await _profileService.updateProfile(
      name: _nameController.text.trim(),
      phone: _phoneController.text.trim(),
      locale: _selectedLocale,
    );

    setState(() => _saving = false);

    if (result.success) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text("Profil mis a jour avec succes"),
            backgroundColor: AppColors.success,
          ),
        );
        Navigator.pop(context, true);
      }
    } else {
      setState(() => _error = result.error);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.sand,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.close, color: AppColors.navy),
          onPressed: () => Navigator.pop(context, false),
        ),
        title: const Text(
          "Modifier le profil",
          style: TextStyle(
            color: AppColors.navy,
            fontWeight: FontWeight.w600,
          ),
        ),
        centerTitle: true,
        actions: [
          TextButton(
            onPressed: _saving || !_hasChanges ? null : _save,
            child: _saving
                ? const SizedBox(
                    height: 18,
                    width: 18,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : Text(
                    "Enregistrer",
                    style: TextStyle(
                      color: _hasChanges ? AppColors.blue : AppColors.textMuted,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Avatar
              Center(
                child: Stack(
                  children: [
                    Container(
                      height: 100,
                      width: 100,
                      decoration: BoxDecoration(
                        color: AppColors.blue.withOpacity(0.1),
                        shape: BoxShape.circle,
                      ),
                      child: Center(
                        child: Text(
                          _getInitials(_nameController.text),
                          style: const TextStyle(
                            color: AppColors.blue,
                            fontSize: 32,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 32),

              // Error message
              if (_error != null) ...[
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppColors.danger.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Row(
                    children: [
                      const Icon(
                        Icons.error_outline,
                        color: AppColors.danger,
                        size: 20,
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          _error!,
                          style: const TextStyle(color: AppColors.danger),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),
              ],

              // Name field
              _buildLabel("Nom complet"),
              const SizedBox(height: 8),
              TextFormField(
                controller: _nameController,
                decoration: _inputDecoration(
                  hintText: "Votre nom",
                  prefixIcon: Icons.person_outline,
                ),
                textCapitalization: TextCapitalization.words,
                validator: (value) {
                  if (value == null || value.trim().isEmpty) {
                    return "Le nom est requis";
                  }
                  if (value.trim().length < 2) {
                    return "Le nom doit contenir au moins 2 caracteres";
                  }
                  return null;
                },
                onChanged: (_) => setState(() {}),
              ),
              const SizedBox(height: 20),

              // Email field (read-only)
              _buildLabel("Email"),
              const SizedBox(height: 8),
              TextFormField(
                initialValue: widget.user.email,
                decoration: _inputDecoration(
                  hintText: "Votre email",
                  prefixIcon: Icons.email_outlined,
                ).copyWith(
                  filled: true,
                  fillColor: AppColors.textMuted.withOpacity(0.1),
                ),
                enabled: false,
              ),
              const SizedBox(height: 4),
              const Text(
                "L'email ne peut pas etre modifie",
                style: TextStyle(
                  color: AppColors.textMuted,
                  fontSize: 12,
                ),
              ),
              const SizedBox(height: 20),

              // Phone field
              _buildLabel("Telephone"),
              const SizedBox(height: 8),
              TextFormField(
                controller: _phoneController,
                decoration: _inputDecoration(
                  hintText: "Votre numero de telephone",
                  prefixIcon: Icons.phone_outlined,
                ),
                keyboardType: TextInputType.phone,
                validator: (value) {
                  if (value != null && value.isNotEmpty && value.length < 8) {
                    return "Numero de telephone invalide";
                  }
                  return null;
                },
                onChanged: (_) => setState(() {}),
              ),
              const SizedBox(height: 20),

              // Locale selector
              _buildLabel("Langue"),
              const SizedBox(height: 8),
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.04),
                      blurRadius: 8,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Column(
                  children: [
                    _LocaleOption(
                      label: "Francais",
                      value: "fr",
                      selected: _selectedLocale == "fr",
                      onTap: () {
                        setState(() => _selectedLocale = "fr");
                      },
                    ),
                    const Divider(height: 1),
                    _LocaleOption(
                      label: "English",
                      value: "en",
                      selected: _selectedLocale == "en",
                      onTap: () {
                        setState(() => _selectedLocale = "en");
                      },
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildLabel(String text) {
    return Text(
      text,
      style: const TextStyle(
        fontWeight: FontWeight.w600,
        fontSize: 14,
      ),
    );
  }

  InputDecoration _inputDecoration({
    required String hintText,
    required IconData prefixIcon,
  }) {
    return InputDecoration(
      hintText: hintText,
      hintStyle: const TextStyle(color: AppColors.textMuted),
      prefixIcon: Icon(prefixIcon, color: AppColors.textMuted),
      filled: true,
      fillColor: Colors.white,
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: BorderSide.none,
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: BorderSide.none,
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: AppColors.blue, width: 2),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: AppColors.danger),
      ),
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
    );
  }

  String _getInitials(String name) {
    final trimmed = name.trim();
    if (trimmed.isEmpty) return '?';
    final parts = trimmed.split(' ');
    if (parts.length == 1) return parts[0][0].toUpperCase();
    return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
  }
}

class _LocaleOption extends StatelessWidget {
  final String label;
  final String value;
  final bool selected;
  final VoidCallback onTap;

  const _LocaleOption({
    required this.label,
    required this.value,
    required this.selected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        child: Row(
          children: [
            Icon(
              Icons.language,
              color: selected ? AppColors.blue : AppColors.textMuted,
              size: 22,
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                label,
                style: TextStyle(
                  fontWeight: selected ? FontWeight.w600 : FontWeight.normal,
                  color: selected ? AppColors.blue : AppColors.text,
                ),
              ),
            ),
            if (selected)
              const Icon(
                Icons.check_circle,
                color: AppColors.blue,
                size: 22,
              ),
          ],
        ),
      ),
    );
  }
}
