import '../widgets/portal_header.dart';
import "package:flutter/material.dart";
import "package:url_launcher/url_launcher.dart";
import "../theme/app_colors.dart";
import "../models/service_request.dart";
import "../services/service_request_service.dart";
import "../services/driver_tracking_service.dart";
import "driver_tracking_screen.dart";

class ServiceRequestDetailScreen extends StatefulWidget {
  final int requestId;

  const ServiceRequestDetailScreen({super.key, required this.requestId});

  @override
  State<ServiceRequestDetailScreen> createState() =>
      _ServiceRequestDetailScreenState();
}

class _ServiceRequestDetailScreenState
    extends State<ServiceRequestDetailScreen> {
  final ServiceRequestService _service = ServiceRequestService();

  ServiceRequest? _request;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    final request = await _service.getById(widget.requestId);
    setState(() {
      _request = request;
      _loading = false;
    });
  }

  Future<void> _cancelRequest() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text("Annuler la demande"),
        content: const Text(
          "Voulez-vous vraiment annuler cette demande de vidange ?",
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text("Non"),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: AppColors.danger),
            child: const Text("Oui, annuler"),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    final success = await _service.cancel(widget.requestId);
    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text("Demande annulee")),
      );
      Navigator.pop(context);
    }
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
          Icon(
            Icons.error_outline,
            size: 48,
            color: AppColors.danger.withOpacity(0.7),
          ),
          const SizedBox(height: 16),
          const Text(
            "Demande introuvable",
            style: TextStyle(color: AppColors.textMuted),
          ),
          const SizedBox(height: 20),
          ElevatedButton.icon(
            onPressed: () => Navigator.pop(context),
            icon: const Icon(Icons.arrow_back),
            label: const Text("Retour"),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              foregroundColor: Colors.white,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildContent() {
    final request = _request!;
    return RefreshIndicator(
      onRefresh: _loadData,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
        children: [
          PortalHeader(
              title: 'Demande #${request.id}',
              subtitle: 'Les détails de votre intervention.',
              back: true,
              trailing: IconButton(
                  tooltip: 'Actualiser',
                  icon: const Icon(Icons.refresh),
                  onPressed: _loadData)),
          const SizedBox(height: 12),
          _SummaryCard(request: request),
          const SizedBox(height: 20),
          _InfoCard(
            title: "Adresse",
            value: request.address,
            icon: Icons.location_on_outlined,
          ),
          const SizedBox(height: 12),
          _InfoCard(
            title: "Type de fosse",
            value: request.fosseTypeLabel,
            icon: Icons.plumbing_outlined,
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: _InfoCard(
                  title: "Volume estime",
                  value: "${request.estimatedVolume} m³",
                  icon: Icons.water_drop_outlined,
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
          _InfoCard(
            title: "Prix estime",
            value: "${request.estimatedPrice.toStringAsFixed(0)} FCFA",
            icon: Icons.payments_outlined,
          ),
          if (request.driverName != null) ...[
            const SizedBox(height: 12),
            _InfoCard(
              title: "Vidangeur",
              value: request.driverName!,
              icon: Icons.person_outline,
            ),
          ],
          if (request.notes != null && request.notes!.isNotEmpty) ...[
            const SizedBox(height: 12),
            _InfoCard(
              title: "Notes",
              value: request.notes!,
              icon: Icons.note_outlined,
            ),
          ],
          const SizedBox(height: 24),
          // Track driver button - shown when driver is en route
          if (_canTrackDriver(request)) ...[
            SizedBox(
              height: 56,
              child: ElevatedButton.icon(
                onPressed: () => _openTracking(request),
                icon: const Icon(Icons.location_searching),
                label: const Text("Suivre le vidangeur"),
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.secondary,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(18),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 12),
          ],
          if (_canCancel(request.status)) ...[
            SizedBox(
              height: 56,
              child: OutlinedButton.icon(
                onPressed: _cancelRequest,
                icon: const Icon(Icons.cancel_outlined),
                label: const Text("Annuler la demande"),
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppColors.danger,
                  side: const BorderSide(color: AppColors.danger),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(18),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 12),
          ],
          SizedBox(
            height: 56,
            child: ElevatedButton.icon(
              onPressed: () async {
                final uri = Uri.parse("tel:+221770000000");
                if (await canLaunchUrl(uri)) {
                  await launchUrl(uri);
                }
              },
              icon: const Icon(Icons.phone),
              label: const Text("Contacter le support"),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.primary,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(18),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  bool _canCancel(String status) {
    return status == 'pending' || status == 'assigned';
  }

  bool _canTrackDriver(ServiceRequest request) {
    // Can track when status is in_progress or accepted and driver is assigned
    final trackableStatuses = ['accepted', 'in_progress'];
    return trackableStatuses.contains(request.status) &&
        request.driver != null &&
        request.location != null;
  }

  Future<void> _openTracking(ServiceRequest request) async {
    // First check if tracking is available
    final trackingService = DriverTrackingService();
    final status = await trackingService.checkTrackingStatus(request.id);
    trackingService.dispose();

    if (!mounted) return;

    if (!status.available) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text("Le vidangeur n'a pas encore demarre la navigation"),
        ),
      );
      return;
    }

    // Navigate to tracking screen
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => DriverTrackingScreen(
          serviceRequestId: request.id,
          clientLatitude: request.location!.latitude,
          clientLongitude: request.location!.longitude,
          clientAddress: request.address,
        ),
      ),
    );
  }
}

class _SummaryCard extends StatelessWidget {
  final ServiceRequest request;

  const _SummaryCard({required this.request});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.06),
            blurRadius: 18,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            height: 56,
            width: 56,
            decoration: BoxDecoration(
              color: AppColors.primary.withOpacity(0.1),
              borderRadius: BorderRadius.circular(18),
            ),
            child: const Icon(
              Icons.local_shipping,
              color: AppColors.primary,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  "Demande #${request.id}",
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  request.createdAt != null
                      ? _formatDate(request.createdAt!)
                      : "Date non disponible",
                  style: const TextStyle(
                    color: AppColors.textMuted,
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
          _StatusBadge(status: request.status),
        ],
      ),
    );
  }

  String _formatDate(DateTime date) {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final yesterday = today.subtract(const Duration(days: 1));
    final dateOnly = DateTime(date.year, date.month, date.day);

    final time =
        "${date.hour.toString().padLeft(2, '0')}:${date.minute.toString().padLeft(2, '0')}";

    if (dateOnly == today) {
      return "Aujourd'hui, $time";
    } else if (dateOnly == yesterday) {
      return "Hier, $time";
    } else {
      return "${date.day}/${date.month}/${date.year} $time";
    }
  }
}

class _StatusBadge extends StatelessWidget {
  final String status;

  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    Color background;
    Color textColor;
    String label;

    switch (status) {
      case 'completed':
        background = const Color(0xFFE2F7EF);
        textColor = const Color(0xFF2E9C6A);
        label = "Terminee";
        break;
      case 'in_progress':
        background = const Color(0xFFFFE9D6);
        textColor = const Color(0xFFEB7A2E);
        label = "En cours";
        break;
      case 'accepted':
        background = const Color(0xFFE3F2FD);
        textColor = const Color(0xFF1976D2);
        label = "Acceptee";
        break;
      case 'assigned':
        background = AppColors.primary.withOpacity(0.1);
        textColor = AppColors.primary;
        label = "Assignee";
        break;
      case 'pending':
        background = const Color(0xFFFFF3E0);
        textColor = const Color(0xFFFF9800);
        label = "En attente";
        break;
      case 'rejected':
        background = const Color(0xFFFFE2E2);
        textColor = const Color(0xFFD64545);
        label = "Refusee";
        break;
      case 'cancelled':
        background = const Color(0xFFF5F5F5);
        textColor = const Color(0xFF9E9E9E);
        label = "Annulee";
        break;
      default:
        background = const Color(0xFFF5F5F5);
        textColor = const Color(0xFF9E9E9E);
        label = status;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: background,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: textColor,
          fontSize: 11,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }
}

class _InfoCard extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;

  const _InfoCard({
    required this.title,
    required this.value,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
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
      child: Row(
        children: [
          Container(
            height: 40,
            width: 40,
            decoration: BoxDecoration(
              color: AppColors.primary.withOpacity(0.1),
              borderRadius: BorderRadius.circular(12),
            ),
            child: Icon(icon, color: AppColors.primary, size: 20),
          ),
          const SizedBox(width: 14),
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
                const SizedBox(height: 4),
                Text(
                  value,
                  style: const TextStyle(fontWeight: FontWeight.w600),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
