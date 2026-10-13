import "package:flutter/material.dart";
import "../theme/app_colors.dart";
import "../widgets/primary_button.dart";
import "../services/location_service.dart";
import "../services/session_store.dart";
import "main_navigation_screen.dart";

class ConsentLocationScreen extends StatefulWidget {
  const ConsentLocationScreen({super.key});

  @override
  State<ConsentLocationScreen> createState() => _ConsentLocationScreenState();
}

class _ConsentLocationScreenState extends State<ConsentLocationScreen> {
  bool isLoading = false;
  String? errorMessage;
  final locationService = LocationService();

  Future<void> _activate() async {
    setState(() {
      isLoading = true;
      errorMessage = null;
    });

    final ok = await locationService.captureAndSend();

    if (!mounted) return;

    if (!ok) {
      setState(() {
        isLoading = false;
        errorMessage = "Localisation indisponible.";
      });
      return;
    }

    // Sauvegarder que la permission a ete accordee
    await SessionStore.setLocationPermissionGranted(true);

    setState(() {
      isLoading = false;
    });

    Navigator.of(context).pushReplacement(
      MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
    );
  }

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
          child: Padding(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  children: [
                    IconButton(
                      icon: const Icon(Icons.arrow_back_ios_new, size: 20),
                      onPressed: () => Navigator.of(context).pop(),
                    ),
                    const Expanded(
                      child: Text(
                        "Localisation",
                        textAlign: TextAlign.center,
                        style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700),
                      ),
                    ),
                    const SizedBox(width: 40),
                  ],
                ),
                const SizedBox(height: 20),
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(24),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.06),
                        blurRadius: 18,
                        offset: const Offset(0, 12),
                      ),
                    ],
                  ),
                  child: Column(
                    children: [
                      Container(
                        height: 120,
                        width: double.infinity,
                        decoration: BoxDecoration(
                          color: AppColors.primary.withOpacity(0.1),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: const Icon(
                          Icons.location_on_outlined,
                          size: 72,
                          color: AppColors.primary,
                        ),
                      ),
                      const SizedBox(height: 20),
                      const Text(
                        "Autoriser la localisation",
                        style: TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.w700,
                          color: AppColors.navy,
                        ),
                      ),
                      const SizedBox(height: 10),
                      const Text(
                        "Videzma utilise votre position pour localiser le vidangeur le plus proche et suivre l'intervention.",
                        textAlign: TextAlign.center,
                        style: TextStyle(fontSize: 14, color: AppColors.textMuted, height: 1.5),
                      ),
                      if (errorMessage != null) ...[
                        const SizedBox(height: 16),
                        Text(
                          errorMessage ?? "",
                          style: const TextStyle(color: AppColors.danger),
                        ),
                      ],
                      const SizedBox(height: 24),
                      PrimaryButton(
                        label: "Activer",
                        isLoading: isLoading,
                        onPressed: isLoading ? null : _activate,
                      ),
                      TextButton(
                        onPressed: () {
                          Navigator.of(context).pushReplacement(
                            MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
                          );
                        },
                        child: const Text("Plus tard"),
                      ),
                    ],
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
