import "package:flutter/material.dart";
import "package:url_launcher/url_launcher.dart";
import "../theme/app_colors.dart";
import "../models/request_item.dart";

class RequestDetailScreen extends StatelessWidget {
  final RequestItem item;

  const RequestDetailScreen({super.key, required this.item});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.sand,
      body: SafeArea(
        child: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [
                Color(0xFFF8F5FF),
                Color(0xFFFDFBF6),
              ],
              begin: Alignment.topCenter,
              end: Alignment.bottomCenter,
            ),
          ),
          child: ListView(
            padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
            children: [
              Row(
                children: [
                  IconButton(
                    icon: const Icon(Icons.arrow_back_ios_new, size: 20),
                    onPressed: () => Navigator.of(context).pop(),
                  ),
                  const Expanded(
                    child: Text(
                      "Detail demande",
                      textAlign: TextAlign.center,
                      style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700),
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.more_horiz),
                    onPressed: () {},
                  ),
                ],
              ),
              const SizedBox(height: 12),
              _SummaryCard(item: item),
              const SizedBox(height: 20),
              _InfoCard(
                title: "Motif",
                value: item.title,
              ),
              const SizedBox(height: 12),
              _InfoCard(
                title: "Categorie",
                value: item.category,
              ),
              const SizedBox(height: 12),
              _InfoCard(
                title: "Date",
                value: "25 Mai 2026 - ${item.time}",
              ),
              const SizedBox(height: 12),
              _InfoCard(
                title: "Medecin",
                value: "Dr. Ndiaye (affectation en cours)",
              ),
              const SizedBox(height: 12),
              _InfoCard(
                title: "Adresse",
                value: "Dakar, Plateau",
              ),
              const SizedBox(height: 12),
              _InfoCard(
                title: "Description",
                value:
                    "Je ressens une douleur persistante depuis 2 jours. "
                    "Merci de verifier si une consultation est possible.",
              ),
              const SizedBox(height: 22),
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
                    backgroundColor: AppColors.blue,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(18),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _SummaryCard extends StatelessWidget {
  final RequestItem item;

  const _SummaryCard({required this.item});

  @override
  Widget build(BuildContext context) {
    final statusStyle = _statusStyle(item.status);
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
              color: const Color(0xFFEDE7FF),
              borderRadius: BorderRadius.circular(18),
            ),
            child: const Icon(Icons.medical_services_outlined, color: AppColors.blue),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.title,
                  style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 6),
                Text(
                  "Reference ${item.reference}",
                  style: const TextStyle(color: AppColors.textMuted, fontSize: 12),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: statusStyle.background,
              borderRadius: BorderRadius.circular(12),
            ),
            child: Text(
              statusStyle.label,
              style: TextStyle(
                color: statusStyle.textColor,
                fontSize: 11,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _InfoCard extends StatelessWidget {
  final String title;
  final String value;

  const _InfoCard({required this.title, required this.value});

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
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: const TextStyle(color: AppColors.textMuted, fontSize: 12),
          ),
          const SizedBox(height: 6),
          Text(
            value,
            style: const TextStyle(fontWeight: FontWeight.w600),
          ),
        ],
      ),
    );
  }
}

class _StatusStyle {
  final Color background;
  final Color textColor;
  final String label;

  const _StatusStyle(this.background, this.textColor, this.label);
}

_StatusStyle _statusStyle(RequestStatus status) {
  switch (status) {
    case RequestStatus.done:
      return const _StatusStyle(Color(0xFFE2F7EF), Color(0xFF2E9C6A), "Terminee");
    case RequestStatus.inProgress:
      return const _StatusStyle(Color(0xFFFFE9D6), Color(0xFFEB7A2E), "En cours");
    case RequestStatus.pending:
      return const _StatusStyle(Color(0xFFE8E2FF), Color(0xFF7F62FF), "En attente");
  }
}
