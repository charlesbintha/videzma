import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../theme/app_colors.dart';
import '../models/service_request.dart';
import '../services/driver_service.dart';
import 'driver_navigation_screen.dart';

/// Detail screen for drivers to view and manage a service request
class DriverServiceDetailScreen extends StatefulWidget {
  final int requestId;

  const DriverServiceDetailScreen({super.key, required this.requestId});

  @override
  State<DriverServiceDetailScreen> createState() =>
      _DriverServiceDetailScreenState();
}

class _DriverServiceDetailScreenState extends State<DriverServiceDetailScreen> {
  final DriverService _driverService = DriverService();

  ServiceRequest? _request;
  bool _loading = true;
  bool _actionLoading = false;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    final request = await _driverService.getById(widget.requestId);
    if (mounted) {
      setState(() {
        _request = request;
        _loading = false;
      });
    }
  }

  Future<void> _acceptRequest() async {
    final confirmed = await _showConfirmDialog(
      title: "Accepter la demande",
      message: "Voulez-vous accepter cette demande de vidange ?",
      confirmText: "Accepter",
      confirmColor: AppColors.secondary,
    );

    if (confirmed != true) return;

    setState(() => _actionLoading = true);
    final result = await _driverService.acceptRequest(widget.requestId);
    setState(() => _actionLoading = false);

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result.message),
          backgroundColor: result.success ? AppColors.secondary : AppColors.danger,
        ),
      );
      if (result.success) {
        _loadData();
      }
    }
  }

  Future<void> _rejectRequest() async {
    final reason = await _showRejectDialog();
    if (reason == null) return;

    setState(() => _actionLoading = true);
    final result = await _driverService.rejectRequest(
      widget.requestId,
      reason: reason.isNotEmpty ? reason : null,
    );
    setState(() => _actionLoading = false);

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result.message),
          backgroundColor: result.success ? AppColors.secondary : AppColors.danger,
        ),
      );
      if (result.success) {
        Navigator.pop(context);
      }
    }
  }

  Future<void> _startNavigation() async {
    final request = _request!;

    if (request.location == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("Position du client non disponible")),
      );
      return;
    }

    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => DriverNavigationScreen(
          serviceRequestId: request.id,
          clientId: request.driver?.id ?? 0, // This should be client ID
          clientName: request.address,
          clientPhone: null, // Would need client phone from API
          clientAddress: request.address,
          clientLatitude: request.location!.latitude,
          clientLongitude: request.location!.longitude,
        ),
      ),
    );
  }

  Future<void> _startIntervention() async {
    final confirmed = await _showConfirmDialog(
      title: "Démarrer l'intervention",
      message: "Confirmez que vous êtes arrivé chez le client et prêt à commencer l'intervention.",
      confirmText: "Démarrer",
      confirmColor: AppColors.primary,
    );

    if (confirmed != true) return;

    setState(() => _actionLoading = true);
    final result = await _driverService.startIntervention(widget.requestId);
    setState(() => _actionLoading = false);

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result.message),
          backgroundColor: result.success ? AppColors.secondary : AppColors.danger,
        ),
      );
      if (result.success) {
        _loadData();
      }
    }
  }

  Future<void> _completeIntervention() async {
    final volumeResult = await _showCompleteDialog();
    if (volumeResult == null) return;

    setState(() => _actionLoading = true);
    final result = await _driverService.completeIntervention(
      widget.requestId,
      actualVolume: volumeResult.volume,
      notes: volumeResult.notes,
    );
    setState(() => _actionLoading = false);

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result.message),
          backgroundColor: result.success ? AppColors.secondary : AppColors.danger,
        ),
      );
      if (result.success) {
        Navigator.pop(context);
      }
    }
  }

  Future<void> _callClient() async {
    // In a real app, we'd get the client phone from the API
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text("Numéro du client non disponible")),
    );
  }

  Future<void> _openInMaps() async {
    if (_request?.location == null) return;

    final url = 'https://www.google.com/maps/dir/?api=1'
        '&destination=${_request!.location!.latitude},${_request!.location!.longitude}'
        '&travelmode=driving';
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  Future<bool?> _showConfirmDialog({
    required String title,
    required String message,
    required String confirmText,
    required Color confirmColor,
  }) {
    return showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(title),
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text("Annuler"),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(backgroundColor: confirmColor),
            child: Text(confirmText),
          ),
        ],
      ),
    );
  }

  Future<String?> _showRejectDialog() {
    final controller = TextEditingController();
    return showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text("Refuser la demande"),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text("Indiquez la raison du refus (optionnel):"),
            const SizedBox(height: 12),
            TextField(
              controller: controller,
              decoration: const InputDecoration(
                hintText: "Raison...",
                border: OutlineInputBorder(),
              ),
              maxLines: 2,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text("Annuler"),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, controller.text),
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.danger),
            child: const Text("Refuser"),
          ),
        ],
      ),
    );
  }

  Future<_CompleteResult?> _showCompleteDialog() {
    final volumeController = TextEditingController(
      text: _request?.estimatedVolume?.toString() ?? "",
    );
    final notesController = TextEditingController();

    return showDialog<_CompleteResult>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text("Terminer l'intervention"),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text("Volume réel vidangé (m³):"),
            const SizedBox(height: 8),
            TextField(
              controller: volumeController,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(
                hintText: "Ex: 5.5",
                border: OutlineInputBorder(),
                suffixText: "m³",
              ),
            ),
            const SizedBox(height: 16),
            const Text("Notes (optionnel):"),
            const SizedBox(height: 8),
            TextField(
              controller: notesController,
              decoration: const InputDecoration(
                hintText: "Observations...",
                border: OutlineInputBorder(),
              ),
              maxLines: 2,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text("Annuler"),
          ),
          ElevatedButton(
            onPressed: () {
              final volume = double.tryParse(volumeController.text);
              if (volume == null || volume <= 0) {
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text("Veuillez entrer un volume valide")),
                );
                return;
              }
              Navigator.pop(
                context,
                _CompleteResult(volume: volume, notes: notesController.text),
              );
            },
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.secondary),
            child: const Text("Terminer"),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.sand,
      body: SafeArea(
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _request == null
                ? _buildError()
                : _buildContent(),
      ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.error_outline, size: 48, color: AppColors.danger.withOpacity(0.7)),
          const SizedBox(height: 16),
          const Text("Demande introuvable", style: TextStyle(color: AppColors.textMuted)),
          const SizedBox(height: 20),
          ElevatedButton.icon(
            onPressed: () => Navigator.pop(context),
            icon: const Icon(Icons.arrow_back),
            label: const Text("Retour"),
          ),
        ],
      ),
    );
  }

  Widget _buildContent() {
    final request = _request!;

    return Stack(
      children: [
        RefreshIndicator(
          onRefresh: _loadData,
          child: ListView(
            padding: const EdgeInsets.fromLTRB(20, 12, 20, 120),
            children: [
              // Header
              Row(
                children: [
                  IconButton(
                    icon: const Icon(Icons.arrow_back_ios_new, size: 20),
                    onPressed: () => Navigator.pop(context),
                  ),
                  const Expanded(
                    child: Text(
                      "Détail demande",
                      textAlign: TextAlign.center,
                      style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700),
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.refresh),
                    onPressed: _loadData,
                  ),
                ],
              ),
              const SizedBox(height: 12),

              // Status card
              _buildStatusCard(request),
              const SizedBox(height: 16),

              // Address card
              _InfoCard(
                title: "Adresse",
                value: request.address,
                icon: Icons.location_on_outlined,
                trailing: IconButton(
                  icon: const Icon(Icons.map_outlined, color: AppColors.secondary),
                  onPressed: _openInMaps,
                ),
              ),
              const SizedBox(height: 12),

              // Details row
              Row(
                children: [
                  Expanded(
                    child: _InfoCard(
                      title: "Type de fosse",
                      value: request.fosseTypeLabel,
                      icon: Icons.plumbing_outlined,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _InfoCard(
                      title: "Urgence",
                      value: request.urgencyLabel,
                      icon: Icons.priority_high,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),

              Row(
                children: [
                  Expanded(
                    child: _InfoCard(
                      title: "Volume estimé",
                      value: "${request.estimatedVolume ?? '-'} m³",
                      icon: Icons.water_drop_outlined,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _InfoCard(
                      title: "Prix",
                      value: request.priceFormatted ?? "${request.estimatedPrice.toInt()} FCFA",
                      icon: Icons.payments_outlined,
                    ),
                  ),
                ],
              ),

              if (request.clientNotes != null && request.clientNotes!.isNotEmpty) ...[
                const SizedBox(height: 12),
                _InfoCard(
                  title: "Notes du client",
                  value: request.clientNotes!,
                  icon: Icons.note_outlined,
                ),
              ],

              if (request.distanceKm != null) ...[
                const SizedBox(height: 12),
                _InfoCard(
                  title: "Distance",
                  value: "${request.distanceKm!.toStringAsFixed(1)} km",
                  icon: Icons.route,
                ),
              ],
            ],
          ),
        ),

        // Bottom action buttons
        Positioned(
          left: 0,
          right: 0,
          bottom: 0,
          child: Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.1),
                  blurRadius: 10,
                  offset: const Offset(0, -4),
                ),
              ],
            ),
            child: SafeArea(
              top: false,
              child: _actionLoading
                  ? const Center(child: CircularProgressIndicator())
                  : _buildActionButtons(request),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildStatusCard(ServiceRequest request) {
    Color statusColor;
    IconData statusIcon;
    String statusText;

    switch (request.status) {
      case 'pending':
      case 'assigned':
        statusColor = AppColors.warning;
        statusIcon = Icons.schedule;
        statusText = "En attente d'acceptation";
        break;
      case 'accepted':
        statusColor = AppColors.info;
        statusIcon = Icons.check_circle_outline;
        statusText = "Acceptée - Prêt à naviguer";
        break;
      case 'in_progress':
        statusColor = AppColors.primary;
        statusIcon = Icons.local_shipping;
        statusText = "Intervention en cours";
        break;
      case 'completed':
        statusColor = AppColors.secondary;
        statusIcon = Icons.check_circle;
        statusText = "Intervention terminée";
        break;
      default:
        statusColor = AppColors.textMuted;
        statusIcon = Icons.help_outline;
        statusText = request.statusLabel;
    }

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: statusColor.withOpacity(0.1),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: statusColor.withOpacity(0.3)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: statusColor.withOpacity(0.2),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(statusIcon, color: statusColor, size: 28),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  "Demande #${request.id}",
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 16,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  statusText,
                  style: TextStyle(
                    color: statusColor,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActionButtons(ServiceRequest request) {
    switch (request.status) {
      case 'pending':
      case 'assigned':
        return Row(
          children: [
            Expanded(
              child: OutlinedButton.icon(
                onPressed: _rejectRequest,
                icon: const Icon(Icons.close),
                label: const Text("Refuser"),
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppColors.danger,
                  side: const BorderSide(color: AppColors.danger),
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              flex: 2,
              child: ElevatedButton.icon(
                onPressed: _acceptRequest,
                icon: const Icon(Icons.check),
                label: const Text("Accepter"),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.secondary,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
            ),
          ],
        );

      case 'accepted':
        return Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _startNavigation,
                icon: const Icon(Icons.navigation),
                label: const Text("Naviguer vers le client"),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 10),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _startIntervention,
                icon: const Icon(Icons.play_arrow),
                label: const Text("Démarrer l'intervention"),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.secondary,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
            ),
          ],
        );

      case 'in_progress':
        return SizedBox(
          width: double.infinity,
          child: ElevatedButton.icon(
            onPressed: _completeIntervention,
            icon: const Icon(Icons.check_circle),
            label: const Text("Terminer l'intervention"),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.secondary,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 16),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
          ),
        );

      case 'completed':
        return SizedBox(
          width: double.infinity,
          child: OutlinedButton.icon(
            onPressed: () => Navigator.pop(context),
            icon: const Icon(Icons.arrow_back),
            label: const Text("Retour"),
            style: OutlinedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
          ),
        );

      default:
        return const SizedBox.shrink();
    }
  }
}

class _InfoCard extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;
  final Widget? trailing;

  const _InfoCard({
    required this.title,
    required this.value,
    required this.icon,
    this.trailing,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 8,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: AppColors.secondary.withOpacity(0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: AppColors.secondary, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    color: AppColors.textMuted,
                    fontSize: 12,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: const TextStyle(
                    fontWeight: FontWeight.w600,
                    fontSize: 14,
                  ),
                ),
              ],
            ),
          ),
          if (trailing != null) trailing!,
        ],
      ),
    );
  }
}

class _CompleteResult {
  final double volume;
  final String? notes;

  _CompleteResult({required this.volume, this.notes});
}
