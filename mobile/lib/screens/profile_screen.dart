import "package:flutter/material.dart";
import "package:url_launcher/url_launcher.dart";
import "../theme/app_colors.dart";
import "../models/auth_user.dart";
import "../services/profile_service.dart";
import "../services/session_store.dart";
import "edit_profile_screen.dart";
import "login_screen.dart";
import "location_history_screen.dart";
import "logs_screen.dart";
import "subscriptions_screen.dart";

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final ProfileService _profileService = ProfileService();

  AuthUser? _user;
  bool _loading = true;
  bool _loggingOut = false;

  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  Future<void> _loadProfile() async {
    setState(() => _loading = true);

    final user = await _profileService.getProfile();

    setState(() {
      _user = user ?? SessionStore.user;
      _loading = false;
    });
  }

  Future<void> _logout() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text("Deconnexion"),
        content: const Text("Voulez-vous vraiment vous deconnecter ?"),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text("Annuler"),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: AppColors.danger),
            child: const Text("Deconnecter"),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    setState(() => _loggingOut = true);

    await _profileService.logout();

    if (mounted) {
      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
        (route) => false,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.sand,
      appBar: AppBar(
        backgroundColor: AppColors.primary,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Colors.white),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          "Mon profil",
          style: TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.w600,
          ),
        ),
        centerTitle: true,
        actions: [
          if (_user != null)
            IconButton(
              icon: const Icon(Icons.edit_outlined, color: Colors.white),
              onPressed: () async {
                final updated = await Navigator.of(context).push<bool>(
                  MaterialPageRoute(
                    builder: (_) => EditProfileScreen(user: _user!),
                  ),
                );
                if (updated == true) {
                  _loadProfile();
                }
              },
            ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _user == null
              ? _ErrorState(onRetry: _loadProfile)
              : RefreshIndicator(
                  onRefresh: _loadProfile,
                  child: ListView(
                    padding: const EdgeInsets.all(20),
                    children: [
                      _ProfileHeader(user: _user!),
                      const SizedBox(height: 24),
                      _InfoSection(user: _user!),
                      const SizedBox(height: 24),
                      _MenuSection(
                        onLocationHistory: () {
                          Navigator.of(context).push(
                            MaterialPageRoute(
                              builder: (_) => const LocationHistoryScreen(),
                            ),
                          );
                        },
                        onSubscriptions: () {
                          Navigator.of(context).push(
                            MaterialPageRoute(
                              builder: (_) => const SubscriptionsScreen(),
                            ),
                          );
                        },
                      ),
                      const SizedBox(height: 24),
                      _LogoutButton(
                        onLogout: _logout,
                        loading: _loggingOut,
                      ),
                      const SizedBox(height: 32),
                    ],
                  ),
                ),
    );
  }
}

class _ProfileHeader extends StatelessWidget {
  final AuthUser user;

  const _ProfileHeader({required this.user});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [AppColors.blue, AppColors.blue.withOpacity(0.8)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: AppColors.blue.withOpacity(0.3),
            blurRadius: 20,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Column(
        children: [
          Container(
            height: 80,
            width: 80,
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.2),
              shape: BoxShape.circle,
            ),
            child: Center(
              child: Text(
                _getInitials(user.name),
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 28,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ),
          const SizedBox(height: 16),
          Text(
            user.name,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 22,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            user.email,
            style: TextStyle(
              color: Colors.white.withOpacity(0.8),
              fontSize: 14,
            ),
          ),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.2),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text(
              user.roleLabel,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 12,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
        ],
      ),
    );
  }

  String _getInitials(String name) {
    final parts = name.trim().split(' ');
    if (parts.isEmpty) return '?';
    if (parts.length == 1) return parts[0][0].toUpperCase();
    return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
  }
}

class _InfoSection extends StatelessWidget {
  final AuthUser user;

  const _InfoSection({required this.user});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 12,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            "Informations",
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 16),
          _InfoRow(
            icon: Icons.email_outlined,
            label: "Email",
            value: user.email,
          ),
          if (user.phone != null && user.phone!.isNotEmpty) ...[
            const Divider(height: 24),
            _InfoRow(
              icon: Icons.phone_outlined,
              label: "Telephone",
              value: user.phone!,
            ),
          ],
          const Divider(height: 24),
          _InfoRow(
            icon: Icons.badge_outlined,
            label: "Statut",
            value: user.statusLabel,
            valueColor:
                user.status == 'active' ? AppColors.success : AppColors.warning,
          ),
          const Divider(height: 24),
          _InfoRow(
            icon: Icons.language_outlined,
            label: "Langue",
            value: user.locale == 'en' ? 'English' : 'Francais',
          ),
        ],
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  final Color? valueColor;

  const _InfoRow({
    required this.icon,
    required this.label,
    required this.value,
    this.valueColor,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          height: 40,
          width: 40,
          decoration: BoxDecoration(
            color: AppColors.blue.withOpacity(0.1),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Icon(icon, color: AppColors.blue, size: 20),
        ),
        const SizedBox(width: 14),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: const TextStyle(
                  color: AppColors.textMuted,
                  fontSize: 12,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                value,
                style: TextStyle(
                  fontWeight: FontWeight.w500,
                  color: valueColor,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

class _MenuSection extends StatelessWidget {
  final VoidCallback onLocationHistory;
  final VoidCallback onSubscriptions;

  const _MenuSection({
    required this.onLocationHistory,
    required this.onSubscriptions,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 12,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        children: [
          _MenuItem(
            icon: Icons.history,
            label: "Historique des positions",
            onTap: onLocationHistory,
          ),
          const Divider(height: 1, indent: 56),
          _MenuItem(
            icon: Icons.card_membership,
            label: "Mes forfaits",
            onTap: onSubscriptions,
          ),
          const Divider(height: 1, indent: 56),
          _MenuItem(
            icon: Icons.bug_report_outlined,
            label: "Logs (debug)",
            onTap: () {
              Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const LogsScreen()),
              );
            },
          ),
          const Divider(height: 1, indent: 56),
          _MenuItem(
            icon: Icons.help_outline,
            label: "Aide et support",
            onTap: () async {
              final uri = Uri.parse("tel:+221770000000");
              if (await canLaunchUrl(uri)) {
                await launchUrl(uri);
              }
            },
          ),
          const Divider(height: 1, indent: 56),
          _MenuItem(
            icon: Icons.info_outline,
            label: "A propos",
            onTap: () {
              showAboutDialog(
                context: context,
                applicationName: 'Videzma',
                applicationVersion: '0.1.0',
                applicationLegalese: '2025 Videzma. Tous droits reserves.',
              );
            },
          ),
        ],
      ),
    );
  }
}

class _MenuItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  const _MenuItem({
    required this.icon,
    required this.label,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        child: Row(
          children: [
            Icon(icon, color: AppColors.textMuted, size: 22),
            const SizedBox(width: 16),
            Expanded(
              child: Text(
                label,
                style: const TextStyle(fontWeight: FontWeight.w500),
              ),
            ),
            const Icon(
              Icons.chevron_right,
              color: AppColors.textMuted,
            ),
          ],
        ),
      ),
    );
  }
}

class _LogoutButton extends StatelessWidget {
  final VoidCallback onLogout;
  final bool loading;

  const _LogoutButton({
    required this.onLogout,
    required this.loading,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: double.infinity,
      child: OutlinedButton.icon(
        onPressed: loading ? null : onLogout,
        icon: loading
            ? const SizedBox(
                height: 18,
                width: 18,
                child: CircularProgressIndicator(strokeWidth: 2),
              )
            : const Icon(Icons.logout),
        label: Text(loading ? "Deconnexion..." : "Se deconnecter"),
        style: OutlinedButton.styleFrom(
          foregroundColor: AppColors.danger,
          side: const BorderSide(color: AppColors.danger),
          padding: const EdgeInsets.symmetric(vertical: 14),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
      ),
    );
  }
}

class _ErrorState extends StatelessWidget {
  final VoidCallback onRetry;

  const _ErrorState({required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.error_outline,
              size: 48,
              color: AppColors.danger.withOpacity(0.7),
            ),
            const SizedBox(height: 16),
            const Text(
              "Impossible de charger le profil",
              textAlign: TextAlign.center,
              style: TextStyle(color: AppColors.textMuted),
            ),
            const SizedBox(height: 20),
            ElevatedButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh),
              label: const Text("Reessayer"),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.blue,
                foregroundColor: Colors.white,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
