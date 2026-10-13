import '../widgets/portal_header.dart';
import "package:flutter/material.dart";
import "../theme/app_colors.dart";
import "../models/service_request.dart";
import "../services/service_request_service.dart";
import "service_request_detail_screen.dart";

class InterventionsListScreen extends StatefulWidget {
  const InterventionsListScreen({super.key});

  @override
  State<InterventionsListScreen> createState() =>
      _InterventionsListScreenState();
}

class _InterventionsListScreenState extends State<InterventionsListScreen> {
  final ServiceRequestService _service = ServiceRequestService();

  List<ServiceRequest> _interventions = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    final all = await _service.list();
    // Filtrer uniquement les interventions terminees
    final completed = all.where((r) => r.status == 'completed').toList();
    setState(() {
      _interventions = completed;
      _loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.sand,
      body: SafeArea(
        child: Column(
          children: [
            PortalHeader(
                title: 'Interventions',
                subtitle: 'Votre planning, en un coup d’œil.',
                trailing: IconButton(
                    tooltip: 'Actualiser',
                    icon: const Icon(Icons.refresh),
                    onPressed: _loadData)),
            const SizedBox(height: 16),
            Expanded(
              child: RefreshIndicator(
                onRefresh: _loadData,
                child: _loading
                    ? const Center(child: CircularProgressIndicator())
                    : _interventions.isEmpty
                        ? _buildEmptyState()
                        : ListView.separated(
                            padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
                            itemCount: _interventions.length,
                            separatorBuilder: (_, __) =>
                                const SizedBox(height: 16),
                            itemBuilder: (context, index) {
                              final intervention = _interventions[index];
                              return _InterventionCard(
                                intervention: intervention,
                                onTap: () {
                                  Navigator.of(context).push(
                                    MaterialPageRoute(
                                      builder: (_) =>
                                          ServiceRequestDetailScreen(
                                        requestId: intervention.id,
                                      ),
                                    ),
                                  );
                                },
                              );
                            },
                          ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return ListView(
      children: [
        const SizedBox(height: 80),
        Icon(
          Icons.history,
          size: 64,
          color: AppColors.textMuted.withOpacity(0.4),
        ),
        const SizedBox(height: 16),
        const Text(
          "Aucune intervention",
          textAlign: TextAlign.center,
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 8),
        const Text(
          "Vos interventions terminees apparaitront ici.",
          textAlign: TextAlign.center,
          style: TextStyle(
            color: AppColors.textMuted,
          ),
        ),
      ],
    );
  }
}

class _InterventionCard extends StatelessWidget {
  final ServiceRequest intervention;
  final VoidCallback onTap;

  const _InterventionCard({
    required this.intervention,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(22),
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
            Row(
              children: [
                Container(
                  height: 48,
                  width: 48,
                  decoration: BoxDecoration(
                    color: AppColors.success.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: const Icon(
                    Icons.check_circle_outline,
                    color: AppColors.success,
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        "Intervention #${intervention.id}",
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        intervention.address,
                        style: const TextStyle(
                          color: AppColors.textMuted,
                          fontSize: 13,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                const Icon(
                  Icons.chevron_right,
                  color: AppColors.textMuted,
                ),
              ],
            ),
            const SizedBox(height: 14),
            const Divider(height: 1),
            const SizedBox(height: 14),
            Row(
              children: [
                _InfoChip(
                  icon: Icons.water_drop_outlined,
                  label:
                      "${intervention.actualVolume ?? intervention.estimatedVolume} m³",
                ),
                const SizedBox(width: 12),
                _InfoChip(
                  icon: Icons.payments_outlined,
                  label:
                      "${(intervention.finalPrice ?? intervention.estimatedPrice).toStringAsFixed(0)} F",
                ),
                const Spacer(),
                if (intervention.completedAt != null)
                  Text(
                    _formatDate(intervention.completedAt!),
                    style: const TextStyle(
                      color: AppColors.textMuted,
                      fontSize: 12,
                    ),
                  ),
              ],
            ),
            if (intervention.rating != null) ...[
              const SizedBox(height: 12),
              Row(
                children: [
                  ...List.generate(5, (index) {
                    return Icon(
                      index < intervention.rating!
                          ? Icons.star
                          : Icons.star_border,
                      color: Colors.amber,
                      size: 18,
                    );
                  }),
                  const SizedBox(width: 8),
                  Text(
                    "${intervention.rating}/5",
                    style: const TextStyle(
                      color: AppColors.textMuted,
                      fontSize: 12,
                    ),
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  String _formatDate(DateTime date) {
    return "${date.day}/${date.month}/${date.year}";
  }
}

class _InfoChip extends StatelessWidget {
  final IconData icon;
  final String label;

  const _InfoChip({
    required this.icon,
    required this.label,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: AppColors.primary.withOpacity(0.08),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: AppColors.primary),
          const SizedBox(width: 6),
          Text(
            label,
            style: TextStyle(
              color: AppColors.primary,
              fontSize: 12,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}
