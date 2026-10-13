import '../widgets/portal_header.dart';
import "package:flutter/material.dart";
import "../theme/app_colors.dart";
import "../models/service_request.dart";
import "../services/service_request_service.dart";
import "new_request_screen.dart";
import "service_request_detail_screen.dart";

class RequestsListScreen extends StatefulWidget {
  const RequestsListScreen({super.key});

  @override
  State<RequestsListScreen> createState() => _RequestsListScreenState();
}

class _RequestsListScreenState extends State<RequestsListScreen> {
  final ServiceRequestService _service = ServiceRequestService();

  List<ServiceRequest> _requests = [];
  bool _loading = true;
  int _selectedFilterIndex = 0;

  static const List<String> _filters = [
    "Toutes",
    "En attente",
    "En cours",
    "Terminees",
  ];

  static const Map<String, List<String>> _filterStatuses = {
    "Toutes": [],
    "En attente": ["pending", "assigned"],
    "En cours": ["accepted", "in_progress"],
    "Terminees": ["completed", "rejected", "cancelled"],
  };

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);
    final requests = await _service.list();
    setState(() {
      _requests = requests;
      _loading = false;
    });
  }

  List<ServiceRequest> get _filteredRequests {
    final statuses = _filterStatuses[_filters[_selectedFilterIndex]] ?? [];
    if (statuses.isEmpty) return _requests;
    return _requests.where((r) => statuses.contains(r.status)).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.sand,
      body: SafeArea(
        child: Column(
          children: [
            PortalHeader(
                title: 'Mes demandes',
                subtitle: 'Retrouvez et suivez vos interventions.',
                trailing: IconButton(
                    tooltip: 'Actualiser',
                    icon: const Icon(Icons.refresh),
                    onPressed: _loadData)),
            const SizedBox(height: 12),
            SizedBox(
              height: 44,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                padding: const EdgeInsets.symmetric(horizontal: 20),
                itemCount: _filters.length,
                separatorBuilder: (_, __) => const SizedBox(width: 10),
                itemBuilder: (context, index) {
                  final isSelected = _selectedFilterIndex == index;
                  return _FilterChipPill(
                    label: _filters[index],
                    isSelected: isSelected,
                    onTap: () {
                      setState(() => _selectedFilterIndex = index);
                    },
                  );
                },
              ),
            ),
            const SizedBox(height: 18),
            Expanded(
              child: RefreshIndicator(
                onRefresh: _loadData,
                child: _loading
                    ? const Center(child: CircularProgressIndicator())
                    : _filteredRequests.isEmpty
                        ? _EmptyState(
                            filter: _filters[_selectedFilterIndex],
                          )
                        : ListView.separated(
                            padding: const EdgeInsets.fromLTRB(20, 0, 20, 24),
                            itemCount: _filteredRequests.length,
                            separatorBuilder: (_, __) =>
                                const SizedBox(height: 16),
                            itemBuilder: (context, index) {
                              final request = _filteredRequests[index];
                              return _ServiceRequestCard(
                                request: request,
                                onTap: () async {
                                  await Navigator.of(context).push(
                                    MaterialPageRoute(
                                      builder: (_) =>
                                          ServiceRequestDetailScreen(
                                        requestId: request.id,
                                      ),
                                    ),
                                  );
                                  _loadData();
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
}

class _FilterChipPill extends StatelessWidget {
  final String label;
  final bool isSelected;
  final VoidCallback onTap;

  const _FilterChipPill({
    required this.label,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final background =
        isSelected ? AppColors.primary : AppColors.primary.withOpacity(0.1);
    final textColor = isSelected ? Colors.white : AppColors.primary;
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
        decoration: BoxDecoration(
          color: background,
          borderRadius: BorderRadius.circular(18),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: textColor,
            fontWeight: FontWeight.w600,
            fontSize: 12,
          ),
        ),
      ),
    );
  }
}

class _ServiceRequestCard extends StatelessWidget {
  final ServiceRequest request;
  final VoidCallback onTap;

  const _ServiceRequestCard({
    required this.request,
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
                Expanded(
                  child: Text(
                    request.fosseTypeLabel,
                    style: const TextStyle(
                      color: AppColors.textMuted,
                      fontSize: 12,
                    ),
                  ),
                ),
                Container(
                  height: 28,
                  width: 28,
                  decoration: BoxDecoration(
                    color: AppColors.primary.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(
                    Icons.local_shipping_outlined,
                    size: 16,
                    color: AppColors.primary,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              request.address,
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
            const SizedBox(height: 6),
            Text(
              "Ref #${request.id} - ${request.estimatedVolume} m³",
              style: const TextStyle(color: AppColors.textMuted, fontSize: 12),
            ),
            if (request.driverName != null) ...[
              const SizedBox(height: 4),
              Row(
                children: [
                  const Icon(
                    Icons.person_outline,
                    size: 14,
                    color: AppColors.textMuted,
                  ),
                  const SizedBox(width: 4),
                  Text(
                    request.driverName!,
                    style: const TextStyle(
                      color: AppColors.textMuted,
                      fontSize: 12,
                    ),
                  ),
                ],
              ),
            ],
            const SizedBox(height: 12),
            Row(
              children: [
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: _urgencyColor(request.urgencyLevel).withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    request.urgencyLabel,
                    style: TextStyle(
                      color: _urgencyColor(request.urgencyLevel),
                      fontSize: 10,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
                const Spacer(),
                _StatusChip(status: request.status),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Color _urgencyColor(String urgency) {
    switch (urgency) {
      case 'urgent':
        return AppColors.danger;
      case 'high':
        return AppColors.warning;
      default:
        return AppColors.success;
    }
  }
}

class _StatusChip extends StatelessWidget {
  final String status;

  const _StatusChip({required this.status});

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

class _EmptyState extends StatelessWidget {
  final String filter;

  const _EmptyState({required this.filter});

  @override
  Widget build(BuildContext context) {
    return ListView(
      children: [
        const SizedBox(height: 80),
        Icon(
          Icons.inbox_outlined,
          size: 64,
          color: AppColors.textMuted.withOpacity(0.4),
        ),
        const SizedBox(height: 16),
        Text(
          filter == "Toutes"
              ? "Aucune demande"
              : "Aucune demande ${filter.toLowerCase()}",
          textAlign: TextAlign.center,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 8),
        Text(
          filter == "Toutes"
              ? "Creez votre premiere demande de vidange."
              : "Aucune demande ne correspond a ce filtre.",
          textAlign: TextAlign.center,
          style: const TextStyle(
            color: AppColors.textMuted,
          ),
        ),
      ],
    );
  }
}
