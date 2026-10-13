import '../widgets/service_scene.dart';
import "package:flutter/material.dart";
import "../theme/app_colors.dart";
import "../models/service_request.dart";
import "../services/service_request_service.dart";
import "../services/notification_service.dart";
import "../services/session_store.dart";
import "../services/logger_service.dart";
import "new_request_screen.dart";
import "map_screen.dart";
import "profile_screen.dart";
import "notifications_screen.dart";
import "task_screen.dart";
import "service_request_detail_screen.dart";

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final ServiceRequestService _serviceRequestService = ServiceRequestService();
  final NotificationService _notificationService = NotificationService();

  List<ServiceRequest> _requests = [];
  int _unreadNotifications = 0;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    logger.info('HomeScreen', 'Debut chargement donnees...');
    setState(() => _loading = true);

    try {
      logger.debug('HomeScreen', 'Chargement demandes...');
      final requests = await _serviceRequestService.list();
      logger.info('HomeScreen', '${requests.length} demandes chargees');

      logger.debug('HomeScreen', 'Chargement notifications...');
      final unreadCount = await _notificationService.getUnreadCount();
      logger.info('HomeScreen', '$unreadCount notifications non lues');

      setState(() {
        _requests = requests;
        _unreadNotifications = unreadCount;
        _loading = false;
      });
      logger.info('HomeScreen', 'Chargement termine avec succes');
    } catch (e) {
      logger.error('HomeScreen', 'Erreur lors du chargement', e);
      setState(() => _loading = false);
    }
  }

  Future<void> _open(Widget screen) async {
    await Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
    if (mounted) _loadData();
  }

  @override
  Widget build(BuildContext context) {
    final name = (SessionStore.user?.name ?? 'Bienvenue').split(' ').first;
    return Scaffold(
        backgroundColor: const Color(0xFFF5F7FB),
        body: SafeArea(
          child: RefreshIndicator(
              onRefresh: _loadData,
              child: ListView(padding: const EdgeInsets.all(20), children: [
                Row(children: [
                  const Icon(Icons.water_drop_rounded,
                      color: AppColors.primary, size: 26),
                  const SizedBox(width: 8),
                  const Text('videzma',
                      style: TextStyle(
                          fontWeight: FontWeight.w800,
                          fontSize: 23,
                          letterSpacing: -1)),
                  const Spacer(),
                  IconButton(
                      tooltip: 'Notifications',
                      onPressed: () => _open(const NotificationsScreen()),
                      icon: Badge(
                          isLabelVisible: _unreadNotifications > 0,
                          child: const Icon(Icons.notifications_none_rounded))),
                  IconButton(
                      tooltip: 'Mon profil',
                      onPressed: () => _open(const ProfileScreen()),
                      icon: const CircleAvatar(
                          radius: 18,
                          backgroundColor: Color(0xFFE3EBFF),
                          child: Icon(Icons.person_outline,
                              color: AppColors.primary, size: 20))),
                ]),
                const SizedBox(height: 24),
                Text('Bonjour, $name',
                    style: const TextStyle(
                        color: AppColors.textMuted, fontSize: 13)),
                const SizedBox(height: 8),
                const Text('Une maison sereine.\nOn s’occupe du reste.',
                    style: TextStyle(
                        fontSize: 28,
                        height: 1.15,
                        fontWeight: FontWeight.w800,
                        letterSpacing: -.9)),
                const SizedBox(height: 20),
                Container(
                    clipBehavior: Clip.antiAlias,
                    decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(24)),
                    child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const SizedBox(
                              height: 180,
                              width: double.infinity,
                              child: ServiceScene()),
                          Padding(
                              padding: const EdgeInsets.fromLTRB(20, 8, 20, 20),
                              child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text('VIDANGE À DOMICILE',
                                        style: TextStyle(
                                            color: AppColors.primary,
                                            fontSize: 10,
                                            fontWeight: FontWeight.w700,
                                            letterSpacing: 1.7)),
                                    const SizedBox(height: 8),
                                    const Text(
                                        'Votre intervention,\nen 3 étapes.',
                                        style: TextStyle(
                                            fontSize: 22,
                                            fontWeight: FontWeight.w700,
                                            height: 1.2)),
                                    const SizedBox(height: 12),
                                    const Text(
                                        'Le lieu, votre besoin, puis la confirmation.',
                                        style: TextStyle(
                                            fontSize: 12,
                                            color: AppColors.textMuted)),
                                    const SizedBox(height: 20),
                                    SizedBox(
                                        width: double.infinity,
                                        child: FilledButton(
                                            onPressed: () =>
                                                _open(const NewRequestScreen()),
                                            child: const Row(
                                                mainAxisAlignment:
                                                    MainAxisAlignment
                                                        .spaceBetween,
                                                children: [
                                                  Text('Commander une vidange'),
                                                  Icon(
                                                      Icons
                                                          .arrow_forward_rounded,
                                                      size: 20)
                                                ]))),
                                  ])),
                        ])),
                const SizedBox(height: 16),
                Row(children: [
                  Expanded(
                      child: _QuickPanel(
                          title: 'Mon adresse',
                          subtitle: 'Voir ma position',
                          icon: Icons.near_me_outlined,
                          onTap: () => _open(const MapScreen()))),
                  const SizedBox(width: 12),
                  Expanded(
                      child: _QuickPanel(
                          title: 'Mes demandes',
                          subtitle: 'Consulter le suivi',
                          icon: Icons.receipt_long_outlined,
                          onTap: () => _open(const RequestsListScreen()))),
                ]),
                const SizedBox(height: 28),
                Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Text('Vos interventions',
                          style: TextStyle(
                              fontSize: 19, fontWeight: FontWeight.w700)),
                      TextButton(
                          onPressed: () => _open(const RequestsListScreen()),
                          child: const Text('Tout voir')),
                    ]),
                if (_loading)
                  const Center(
                      child: Padding(
                          padding: EdgeInsets.all(24),
                          child: CircularProgressIndicator()))
                else if (_requests.isEmpty)
                  _EmptyState(
                      onCreateRequest: () => _open(const NewRequestScreen()))
                else
                  ..._requests.take(3).map((request) => Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: _RecentRequestTile(
                          request: request,
                          onTap: () => _open(ServiceRequestDetailScreen(
                              requestId: request.id))))),
              ])),
        ));
  }
}

class _QuickPanel extends StatelessWidget {
  final String title, subtitle;
  final IconData icon;
  final VoidCallback onTap;
  const _QuickPanel(
      {required this.title,
      required this.subtitle,
      required this.icon,
      required this.onTap});
  @override
  Widget build(BuildContext context) => Material(
      color: const Color(0xFFE9EFFB),
      borderRadius: BorderRadius.circular(18),
      child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(18),
          child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(icon, color: AppColors.primary, size: 23),
                    const SizedBox(height: 16),
                    Text(title,
                        style: const TextStyle(
                            fontWeight: FontWeight.w700, fontSize: 13)),
                    const SizedBox(height: 4),
                    Text(subtitle,
                        style: const TextStyle(
                            color: AppColors.textMuted, fontSize: 10)),
                  ]))));
}

class _RecentRequestTile extends StatelessWidget {
  final ServiceRequest request;
  final VoidCallback onTap;

  const _RecentRequestTile({
    required this.request,
    required this.onTap,
  });

  Color get _statusColor {
    switch (request.status) {
      case 'pending':
      case 'assigned':
        return AppColors.warning;
      case 'accepted':
      case 'in_progress':
        return AppColors.primary;
      case 'completed':
        return AppColors.success;
      case 'canceled':
      case 'rejected':
        return AppColors.danger;
      default:
        return AppColors.textMuted;
    }
  }

  IconData get _statusIcon {
    switch (request.status) {
      case 'pending':
      case 'assigned':
        return Icons.schedule;
      case 'accepted':
        return Icons.check_circle_outline;
      case 'in_progress':
        return Icons.local_shipping;
      case 'completed':
        return Icons.check_circle;
      case 'canceled':
      case 'rejected':
        return Icons.cancel;
      default:
        return Icons.help_outline;
    }
  }

  @override
  Widget build(BuildContext context) => Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(20),
      child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(20),
          child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(children: [
                      Icon(_statusIcon, size: 18, color: _statusColor),
                      const SizedBox(width: 8),
                      Text(request.statusLabel,
                          style: TextStyle(
                              color: _statusColor,
                              fontWeight: FontWeight.w600,
                              fontSize: 12)),
                      const Spacer(),
                      Text('#${request.id}',
                          style: const TextStyle(
                              color: AppColors.textMuted, fontSize: 12)),
                    ]),
                    const SizedBox(height: 16),
                    Text(request.address,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                            fontSize: 16,
                            height: 1.4,
                            fontWeight: FontWeight.w600)),
                    const SizedBox(height: 16),
                    const Divider(height: 1),
                    const SizedBox(height: 14),
                    Row(children: [
                      if (request.estimatedVolume != null)
                        Text('${request.estimatedVolume!.toInt()} m³',
                            style: const TextStyle(
                                color: AppColors.textMuted, fontSize: 12)),
                      const Spacer(),
                      Text(request.priceFormatted ?? 'À estimer',
                          style: const TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w700,
                              color: AppColors.primary)),
                      const SizedBox(width: 12),
                      const Icon(Icons.arrow_forward,
                          size: 18, color: AppColors.primary),
                    ]),
                  ]))));
}

class _EmptyState extends StatelessWidget {
  final VoidCallback onCreateRequest;
  const _EmptyState({required this.onCreateRequest});
  @override
  Widget build(BuildContext context) => Container(
        width: double.infinity,
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
            color: Colors.white, borderRadius: BorderRadius.circular(18)),
        child: Column(children: [
          const Icon(Icons.receipt_long_outlined,
              color: AppColors.primary, size: 32),
          const SizedBox(height: 12),
          const Text('Votre première intervention',
              style: TextStyle(fontWeight: FontWeight.w600)),
          const SizedBox(height: 8),
          const Text('Vos demandes et leur suivi apparaîtront ici.',
              textAlign: TextAlign.center,
              style: TextStyle(color: AppColors.textMuted, fontSize: 12)),
          const SizedBox(height: 16),
          FilledButton(
              onPressed: onCreateRequest,
              child: const Text('Créer une demande')),
        ]),
      );
}
