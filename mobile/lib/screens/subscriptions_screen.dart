import 'package:flutter/material.dart';
import '../theme/app_colors.dart';
import '../models/subscription_plan.dart';
import '../services/subscription_service.dart';
import '../services/payment_service.dart';
import 'payment_webview_screen.dart';

class SubscriptionsScreen extends StatefulWidget {
  const SubscriptionsScreen({super.key});

  @override
  State<SubscriptionsScreen> createState() => _SubscriptionsScreenState();
}

class _SubscriptionsScreenState extends State<SubscriptionsScreen> {
  final SubscriptionService _service = SubscriptionService();
  final PaymentService _paymentService = PaymentService();

  List<SubscriptionPlan> _plans = [];
  ClientSubscription? _activeSubscription;
  bool _loading = true;
  bool _subscribing = false;
  String? _selectedPaymentMethod;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _loading = true);

    final results = await Future.wait([
      _service.getPlans(),
      _service.getMySubscription(),
    ]);

    setState(() {
      _plans = results[0] as List<SubscriptionPlan>;
      _activeSubscription = results[1] as ClientSubscription?;
      _loading = false;
    });
  }

  Future<void> _subscribe(SubscriptionPlan plan) async {
    if (_selectedPaymentMethod == null) {
      _showPaymentMethodDialog(plan);
      return;
    }

    setState(() => _subscribing = true);

    final result = await _service.subscribe(
      planId: plan.id,
      paymentMethod: _selectedPaymentMethod!,
    );

    setState(() => _subscribing = false);

    if (!mounted) return;

    if (!result.success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result.message ?? 'Erreur'),
          backgroundColor: AppColors.danger,
        ),
      );
      return;
    }

    // Paiement en ligne (Orange Money ou Wave) → rediriger vers Paytech
    final method = _selectedPaymentMethod;
    final subscriptionId = result.subscription?.id;

    if (subscriptionId != null &&
        (method == 'orange_money' || method == 'wave')) {
      final redirectUrl = await _paymentService.initiateSubscriptionPayment(subscriptionId);

      if (!mounted) return;

      if (redirectUrl == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(result.message ?? 'Abonnement souscrit ! Le paiement en ligne est temporairement indisponible.'),
            backgroundColor: AppColors.warning,
          ),
        );
        _loadData();
        return;
      }

      final paymentResult = await Navigator.of(context).push<PaymentResult>(
        MaterialPageRoute(
          builder: (_) => PaymentWebViewScreen(
            redirectUrl: redirectUrl,
            title: 'Payer ${plan.formattedPrice}',
          ),
        ),
      );

      if (!mounted) return;

      if (paymentResult == PaymentResult.success) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Paiement effectué avec succès !'),
            backgroundColor: AppColors.success,
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Paiement annulé. Votre abonnement est enregistré, vous pourrez payer plus tard.'),
            backgroundColor: AppColors.warning,
            duration: Duration(seconds: 4),
          ),
        );
      }

      _loadData();
      return;
    }

    // Paiement en espèces
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(result.message ?? 'Abonnement souscrit !'),
        backgroundColor: AppColors.success,
      ),
    );
    _loadData();
  }

  void _showPaymentMethodDialog(SubscriptionPlan plan) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) => Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(
              'Mode de paiement',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: AppColors.secondary,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Choisissez comment payer votre forfait "${plan.name}"',
              style: TextStyle(color: AppColors.textMuted),
            ),
            const SizedBox(height: 20),
            _PaymentMethodTile(
              icon: Icons.money,
              label: 'Especes',
              value: 'cash',
              onTap: () => _selectPaymentAndSubscribe(plan, 'cash'),
            ),
            _PaymentMethodTile(
              icon: Icons.phone_android,
              label: 'Orange Money',
              value: 'orange_money',
              onTap: () => _selectPaymentAndSubscribe(plan, 'orange_money'),
            ),
            _PaymentMethodTile(
              icon: Icons.waves,
              label: 'Wave',
              value: 'wave',
              onTap: () => _selectPaymentAndSubscribe(plan, 'wave'),
            ),
            _PaymentMethodTile(
              icon: Icons.credit_card,
              label: 'Carte bancaire',
              value: 'card',
              onTap: () => _selectPaymentAndSubscribe(plan, 'card'),
            ),
            const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }

  void _selectPaymentAndSubscribe(SubscriptionPlan plan, String method) {
    Navigator.pop(context);
    setState(() => _selectedPaymentMethod = method);
    _subscribe(plan);
  }

  Future<void> _cancelSubscription() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Annuler l\'abonnement?'),
        content: const Text(
          'Vous pourrez continuer a utiliser les interventions restantes jusqu\'a la fin de la periode.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Non'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: AppColors.danger),
            child: const Text('Oui, annuler'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    final result = await _service.cancel();

    if (!mounted) return;

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(result.message ?? (result.success ? 'Annule' : 'Erreur')),
        backgroundColor: result.success ? AppColors.success : AppColors.danger,
      ),
    );

    if (result.success) {
      _loadData();
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
          icon: const Icon(Icons.arrow_back, color: AppColors.secondary),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Forfaits',
          style: TextStyle(
            color: AppColors.secondary,
            fontWeight: FontWeight.w600,
          ),
        ),
        centerTitle: true,
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: ListView(
                padding: const EdgeInsets.all(20),
                children: [
                  if (_activeSubscription != null) ...[
                    _ActiveSubscriptionCard(
                      subscription: _activeSubscription!,
                      onCancel: _cancelSubscription,
                    ),
                    const SizedBox(height: 24),
                    const Divider(),
                    const SizedBox(height: 16),
                  ],
                  Text(
                    _activeSubscription != null
                        ? 'Autres forfaits disponibles'
                        : 'Choisissez votre forfait',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: AppColors.secondary,
                    ),
                  ),
                  const SizedBox(height: 16),
                  ..._plans.map((plan) => Padding(
                        padding: const EdgeInsets.only(bottom: 16),
                        child: _PlanCard(
                          plan: plan,
                          isCurrentPlan: _activeSubscription?.plan.id == plan.id,
                          onSubscribe: _activeSubscription == null
                              ? () => _showPaymentMethodDialog(plan)
                              : null,
                          isLoading: _subscribing,
                        ),
                      )),
                ],
              ),
            ),
    );
  }
}

class _ActiveSubscriptionCard extends StatelessWidget {
  final ClientSubscription subscription;
  final VoidCallback onCancel;

  const _ActiveSubscriptionCard({
    required this.subscription,
    required this.onCancel,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [AppColors.primary, AppColors.primary.withOpacity(0.8)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withOpacity(0.3),
            blurRadius: 20,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: const Text(
                  'Abonnement actif',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ),
              const Spacer(),
              Text(
                subscription.plan.periodicityLabel,
                style: TextStyle(
                  color: Colors.white.withOpacity(0.8),
                  fontSize: 12,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(
            subscription.plan.name,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 24,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            subscription.plan.formattedPrice,
            style: TextStyle(
              color: Colors.white.withOpacity(0.9),
              fontSize: 18,
            ),
          ),
          const SizedBox(height: 20),
          Row(
            children: [
              _StatItem(
                label: 'Interventions',
                value: '${subscription.remainingInterventions}/${subscription.plan.interventionsPerPeriod}',
              ),
              const SizedBox(width: 24),
              _StatItem(
                label: 'Jours restants',
                value: '${subscription.remainingDays}',
              ),
            ],
          ),
          const SizedBox(height: 16),
          LinearProgressIndicator(
            value: subscription.interventionsUsed / subscription.plan.interventionsPerPeriod,
            backgroundColor: Colors.white.withOpacity(0.2),
            valueColor: const AlwaysStoppedAnimation<Color>(Colors.white),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Text(
                'Expire le ${subscription.currentPeriodEnd}',
                style: TextStyle(
                  color: Colors.white.withOpacity(0.8),
                  fontSize: 12,
                ),
              ),
              const Spacer(),
              TextButton(
                onPressed: onCancel,
                style: TextButton.styleFrom(
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                ),
                child: const Text('Annuler'),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _StatItem extends StatelessWidget {
  final String label;
  final String value;

  const _StatItem({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            color: Colors.white.withOpacity(0.7),
            fontSize: 12,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          value,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 20,
            fontWeight: FontWeight.bold,
          ),
        ),
      ],
    );
  }
}

class _PlanCard extends StatelessWidget {
  final SubscriptionPlan plan;
  final bool isCurrentPlan;
  final VoidCallback? onSubscribe;
  final bool isLoading;

  const _PlanCard({
    required this.plan,
    required this.isCurrentPlan,
    this.onSubscribe,
    this.isLoading = false,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: plan.isFeatured
            ? Border.all(color: AppColors.primary, width: 2)
            : null,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 15,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Text(
                          plan.name,
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: AppColors.secondary,
                          ),
                        ),
                        if (plan.isFeatured) ...[
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 2,
                            ),
                            decoration: BoxDecoration(
                              color: AppColors.primary,
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: const Text(
                              'Populaire',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ],
                        if (isCurrentPlan) ...[
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 2,
                            ),
                            decoration: BoxDecoration(
                              color: AppColors.success,
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: const Text(
                              'Actuel',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      plan.periodicityLabel,
                      style: TextStyle(
                        color: AppColors.textMuted,
                        fontSize: 13,
                      ),
                    ),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    plan.formattedPrice,
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: AppColors.primary,
                    ),
                  ),
                  if (plan.discountPercent > 0)
                    Text(
                      '-${plan.discountPercent}%',
                      style: TextStyle(
                        color: AppColors.success,
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                ],
              ),
            ],
          ),
          if (plan.description != null) ...[
            const SizedBox(height: 12),
            Text(
              plan.description!,
              style: TextStyle(
                color: AppColors.textMuted,
                fontSize: 13,
              ),
            ),
          ],
          const SizedBox(height: 16),
          Row(
            children: [
              _FeatureChip(
                icon: Icons.replay,
                label: '${plan.interventionsPerPeriod} vidange(s)',
              ),
              const SizedBox(width: 12),
              _FeatureChip(
                icon: Icons.water_drop,
                label: '${plan.maxVolumePerIntervention} m³ max',
              ),
            ],
          ),
          if (onSubscribe != null) ...[
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: isLoading ? null : onSubscribe,
                style: ElevatedButton.styleFrom(
                  backgroundColor: plan.isFeatured
                      ? AppColors.primary
                      : AppColors.secondary,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: isLoading
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      )
                    : const Text('Souscrire'),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _FeatureChip extends StatelessWidget {
  final IconData icon;
  final String label;

  const _FeatureChip({required this.icon, required this.label});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: AppColors.primary.withOpacity(0.1),
        borderRadius: BorderRadius.circular(8),
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
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }
}

class _PaymentMethodTile extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  final VoidCallback onTap;

  const _PaymentMethodTile({
    required this.icon,
    required this.label,
    required this.value,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return ListTile(
      leading: Container(
        height: 44,
        width: 44,
        decoration: BoxDecoration(
          color: AppColors.primary.withOpacity(0.1),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Icon(icon, color: AppColors.primary),
      ),
      title: Text(label),
      trailing: const Icon(Icons.chevron_right),
      onTap: onTap,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
    );
  }
}
