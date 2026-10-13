import "package:flutter/material.dart";
import "../theme/app_colors.dart";

class AddProjectPage extends StatefulWidget {
  const AddProjectPage({super.key});

  @override
  State<AddProjectPage> createState() => _AddProjectPageState();
}

class _AddProjectPageState extends State<AddProjectPage> {
  final formKey = GlobalKey<FormState>();
  final projectController = TextEditingController(text: "Vidange fosse septique");
  final descriptionController = TextEditingController(
    text:
        "Decrivez rapidement votre besoin pour aider le vidangeur\n"
        "a preparer l'intervention.",
  );

  String taskGroup = "Domicile";
  DateTime startDate = DateTime(2022, 5, 1);
  DateTime endDate = DateTime(2022, 6, 30);

  @override
  void dispose() {
    projectController.dispose();
    descriptionController.dispose();
    super.dispose();
  }

  String _formatDate(DateTime date) {
    const months = [
      "Jan",
      "Fev",
      "Mar",
      "Avr",
      "Mai",
      "Juin",
      "Juil",
      "Aou",
      "Sep",
      "Oct",
      "Nov",
      "Dec",
    ];
    final month = months[date.month - 1];
    final day = date.day.toString().padLeft(2, "0");
    return "$day $month, ${date.year}";
  }

  Future<void> _pickDate({
    required DateTime initialDate,
    required ValueChanged<DateTime> onSelected,
  }) async {
    final picked = await showDatePicker(
      context: context,
      initialDate: initialDate,
      firstDate: DateTime(2021),
      lastDate: DateTime(2024),
    );
    if (picked != null) {
      onSelected(picked);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF7F5FB),
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
          child: Form(
            key: formKey,
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
                      "Nouvelle demande",
                      textAlign: TextAlign.center,
                      style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700),
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.notifications_none),
                    onPressed: () {},
                  ),
                ],
              ),
              const SizedBox(height: 16),
              _FormCard(
                child: DropdownButtonFormField<String>(
                  value: taskGroup,
                  decoration: const InputDecoration(
                    labelText: "Type de service",
                    border: InputBorder.none,
                  ),
                  icon: const Icon(Icons.keyboard_arrow_down_rounded),
                  items: const [
                    DropdownMenuItem(value: "Domicile", child: Text("Vidange a domicile")),
                    DropdownMenuItem(
                      value: "Entretien",
                      child: Text("Entretien regulier"),
                    ),
                    DropdownMenuItem(value: "Urgence", child: Text("Intervention urgente")),
                  ],
                  onChanged: (value) {
                    if (value == null) return;
                    setState(() {
                      taskGroup = value;
                    });
                  },
                ),
              ),
              const SizedBox(height: 16),
              _FormCard(
                child: TextFormField(
                  controller: projectController,
                  decoration: const InputDecoration(
                    labelText: "Motif",
                    border: InputBorder.none,
                  ),
                ),
              ),
              const SizedBox(height: 16),
              _FormCard(
                child: TextFormField(
                  controller: descriptionController,
                  maxLines: 5,
                  decoration: const InputDecoration(
                    labelText: "Description",
                    border: InputBorder.none,
                  ),
                ),
              ),
              const SizedBox(height: 16),
              _DateCard(
                label: "Date souhaitee",
                value: _formatDate(startDate),
                onTap: () {
                  _pickDate(
                    initialDate: startDate,
                    onSelected: (date) => setState(() => startDate = date),
                  );
                },
              ),
              const SizedBox(height: 16),
              _DateCard(
                label: "Heure souhaitee",
                value: _formatDate(endDate),
                onTap: () {
                  _pickDate(
                    initialDate: endDate,
                    onSelected: (date) => setState(() => endDate = date),
                  );
                },
              ),
              const SizedBox(height: 16),
              _FormCard(
                child: Row(
                  children: [
                    CircleAvatar(
                      radius: 24,
                      backgroundColor: const Color(0xFFEDE7FF),
                      child: const Icon(Icons.image_outlined, color: AppColors.navy),
                    ),
                    const SizedBox(width: 12),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: const [
                        Text(
                        "Videzma",
                          style: TextStyle(
                            color: Color(0xFF4CB050),
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        Text(
                        "Services",
                          style: TextStyle(
                            color: Color(0xFFF15A22),
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                    const Spacer(),
                    TextButton(
                      onPressed: () {},
                      style: TextButton.styleFrom(
                        backgroundColor: const Color(0xFFF1EEF9),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(18),
                        ),
                      ),
                      child: const Text(
                        "Ajouter piece",
                        style: TextStyle(color: AppColors.navy),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 28),
              SizedBox(
                width: double.infinity,
                height: 58,
                child: ElevatedButton(
                  onPressed: () {},
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF7F62FF),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(30),
                    ),
                  ),
                  child: const Text(
                    "Envoyer la demande",
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

class _FormCard extends StatelessWidget {
  final Widget child;

  const _FormCard({required this.child});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
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
      child: child,
    );
  }
}

class _DateCard extends StatelessWidget {
  final String label;
  final String value;
  final VoidCallback onTap;

  const _DateCard({
    required this.label,
    required this.value,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return _FormCard(
      child: InkWell(
        onTap: onTap,
        child: Row(
          children: [
            const Icon(Icons.calendar_today, size: 18, color: AppColors.textMuted),
            const SizedBox(width: 12),
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
                  const SizedBox(height: 4),
                  Text(
                    value,
                    style: const TextStyle(
                      fontWeight: FontWeight.w600,
                      color: AppColors.navy,
                    ),
                  ),
                ],
              ),
            ),
            const Icon(Icons.keyboard_arrow_down_rounded, color: AppColors.textMuted),
          ],
        ),
      ),
    );
  }
}
