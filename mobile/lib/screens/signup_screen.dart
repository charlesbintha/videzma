import "package:flutter/material.dart";
import "../theme/app_colors.dart";
import "../widgets/primary_button.dart";
import "../services/auth_service.dart";
import "../services/session_store.dart";
import "consent_location_screen.dart";
import "main_navigation_screen.dart";
import "login_screen.dart";

class SignupScreen extends StatefulWidget {
  const SignupScreen({super.key});

  @override
  State<SignupScreen> createState() => _SignupScreenState();
}

class _SignupScreenState extends State<SignupScreen> {
  int _step = 0;
  bool isLoading = false;
  String? errorMessage;
  final authService = AuthService();

  final nameController = TextEditingController();
  final emailController = TextEditingController();
  final phoneController = TextEditingController();
  final passwordController = TextEditingController();
  final confirmController = TextEditingController();
  final emergencyController = TextEditingController();

  @override
  void dispose() {
    nameController.dispose();
    emailController.dispose();
    phoneController.dispose();
    passwordController.dispose();
    confirmController.dispose();
    emergencyController.dispose();
    super.dispose();
  }

  Future<void> _next() async {
    if (_step < 2) {
      setState(() {
        _step += 1;
      });
      return;
    }

    if (passwordController.text != confirmController.text) {
      setState(() {
        errorMessage = "Les mots de passe ne correspondent pas.";
      });
      return;
    }

    setState(() {
      isLoading = true;
      errorMessage = null;
    });

    final result = await authService.register(
      name: nameController.text.trim(),
      email: emailController.text.trim(),
      phone: phoneController.text.trim(),
      password: passwordController.text,
    );

    if (!mounted) return;

    if (!result.ok) {
      setState(() {
        isLoading = false;
        errorMessage = result.message;
      });
      return;
    }

    setState(() {
      isLoading = false;
    });

    // Si la permission de localisation est deja accordee, aller directement au Home
    if (SessionStore.locationPermissionGranted) {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const MainNavigationScreen()),
      );
    } else {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const ConsentLocationScreen()),
      );
    }
  }

  Widget _buildStepContent() {
    switch (_step) {
      case 0:
        return Column(
          key: const ValueKey('step_0'),
          children: [
            TextField(
              controller: nameController,
              decoration: const InputDecoration(labelText: "Nom complet"),
              textInputAction: TextInputAction.next,
            ),
            const SizedBox(height: 16),
            TextField(
              controller: emailController,
              keyboardType: TextInputType.emailAddress,
              decoration: const InputDecoration(labelText: "Email"),
              textInputAction: TextInputAction.done,
            ),
          ],
        );
      case 1:
        return Column(
          key: const ValueKey('step_1'),
          children: [
            TextField(
              controller: phoneController,
              keyboardType: TextInputType.phone,
              decoration: const InputDecoration(labelText: "Telephone"),
              textInputAction: TextInputAction.next,
            ),
            const SizedBox(height: 16),
            TextField(
              controller: passwordController,
              obscureText: true,
              decoration: const InputDecoration(labelText: "Mot de passe"),
              textInputAction: TextInputAction.done,
            ),
          ],
        );
      default:
        return Column(
          key: const ValueKey('step_2'),
          children: [
            TextField(
              controller: confirmController,
              obscureText: true,
              decoration: const InputDecoration(labelText: "Confirmer le mot de passe"),
              textInputAction: TextInputAction.next,
            ),
            const SizedBox(height: 16),
            TextField(
              controller: emergencyController,
              keyboardType: TextInputType.phone,
              decoration: const InputDecoration(labelText: "Numero d'urgence (optionnel)"),
              textInputAction: TextInputAction.done,
            ),
          ],
        );
    }
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
          child: SingleChildScrollView(
            padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
            child: Column(
              children: [
                Row(
                  children: [
                    IconButton(
                      icon: const Icon(Icons.arrow_back_ios_new, size: 20),
                      onPressed: () => Navigator.of(context).pop(),
                    ),
                    const Expanded(
                      child: Text(
                        "Inscription",
                        textAlign: TextAlign.center,
                        style: TextStyle(fontSize: 20, fontWeight: FontWeight.w700),
                      ),
                    ),
                    const SizedBox(width: 40),
                  ],
                ),
                const SizedBox(height: 16),
                Image.asset(
                  'assets/images/logo.png',
                  height: 80,
                  fit: BoxFit.contain,
                ),
                const SizedBox(height: 16),
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
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: List.generate(3, (index) {
                          final isActive = index <= _step;
                          return AnimatedContainer(
                            duration: const Duration(milliseconds: 200),
                            margin: const EdgeInsets.symmetric(horizontal: 6),
                            height: 10,
                            width: isActive ? 32 : 14,
                            decoration: BoxDecoration(
                              color: isActive ? AppColors.blue : AppColors.border,
                              borderRadius: BorderRadius.circular(999),
                            ),
                          );
                        }),
                      ),
                      const SizedBox(height: 16),
                      Text(
                        "Etape ${_step + 1} sur 3",
                        style: const TextStyle(color: AppColors.textMuted),
                      ),
                      const SizedBox(height: 12),
                      Align(
                        alignment: Alignment.centerLeft,
                        child: Text(
                          _step == 0
                              ? "Informations principales"
                              : _step == 1
                                  ? "Coordonnees"
                                  : "Finalisation",
                          style: const TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.w700,
                            color: AppColors.navy,
                          ),
                        ),
                      ),
                      if (errorMessage != null) ...[
                        const SizedBox(height: 16),
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: AppColors.danger.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.error_outline, color: AppColors.danger),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  errorMessage ?? "",
                                  style: const TextStyle(color: AppColors.danger),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                      const SizedBox(height: 20),
                      _buildStepContent(),
                      const SizedBox(height: 24),
                      PrimaryButton(
                        label: _step == 2 ? "Valider" : "Continuer",
                        isLoading: isLoading,
                        onPressed: isLoading ? null : _next,
                      ),
                      TextButton(
                        onPressed: () {
                          Navigator.of(context).pushReplacement(
                            MaterialPageRoute(builder: (_) => const LoginScreen()),
                          );
                        },
                        child: const Text("J'ai deja un compte"),
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
