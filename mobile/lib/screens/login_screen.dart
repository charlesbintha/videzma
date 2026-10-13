import "../widgets/portal_header.dart";
import "package:flutter/material.dart";
import "../theme/app_colors.dart";
import "../widgets/primary_button.dart";
import "../services/auth_service.dart";
import "../services/session_store.dart";
import "consent_location_screen.dart";
import "main_navigation_screen.dart";
import "driver_main_navigation_screen.dart";
import "signup_screen.dart";

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  bool rememberMe = true;
  bool isLoading = false;
  String? errorMessage;
  final emailController = TextEditingController();
  final passwordController = TextEditingController();
  final authService = AuthService();

  @override
  void dispose() {
    emailController.dispose();
    passwordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() {
      isLoading = true;
      errorMessage = null;
    });

    final result = await authService.login(
      email: emailController.text.trim(),
      password: passwordController.text,
      deviceName: "mobile",
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

    // Rediriger selon le role
    final user = SessionStore.user;
    if (user != null && user.isDriver) {
      // Les vidangeurs ont leur propre interface
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const DriverMainNavigationScreen()),
      );
    } else {
      // Pour les clients, verifier la permission de localisation
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
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
        backgroundColor: AppColors.sand,
        body: SafeArea(
            child: SingleChildScrollView(
          child: Column(children: [
            const PortalHeader(
                title: 'Connexion',
                subtitle: 'Heureux de vous retrouver chez Videzma.'),
            Padding(
                padding: const EdgeInsets.all(24),
                child: Column(children: [
                  const SizedBox(height: 16),
                  const Icon(Icons.local_shipping_outlined,
                      size: 48, color: AppColors.primary),
                  const SizedBox(height: 16),
                  const Text('Votre espace Videzma',
                      style:
                          TextStyle(fontSize: 20, fontWeight: FontWeight.w700)),
                  const SizedBox(height: 8),
                  const Text('Connectez-vous pour suivre vos interventions.',
                      textAlign: TextAlign.center,
                      style:
                          TextStyle(color: AppColors.textMuted, fontSize: 12)),
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
                          const Icon(Icons.error_outline,
                              color: AppColors.danger),
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
                  const SizedBox(height: 24),
                  TextField(
                    keyboardType: TextInputType.emailAddress,
                    controller: emailController,
                    decoration: const InputDecoration(
                      labelText: "Email",
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    obscureText: true,
                    controller: passwordController,
                    decoration: const InputDecoration(
                      labelText: "Mot de passe",
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Checkbox(
                        value: rememberMe,
                        visualDensity: VisualDensity.compact,
                        onChanged: (value) {
                          setState(() {
                            rememberMe = value ?? false;
                          });
                        },
                        activeColor: AppColors.blue,
                      ),
                      const Text(
                        "Se souvenir de moi",
                        style: TextStyle(fontSize: 13),
                      ),
                    ],
                  ),
                  Align(
                    alignment: Alignment.centerLeft,
                    child: TextButton(
                      onPressed: () {},
                      style: TextButton.styleFrom(
                        padding: EdgeInsets.zero,
                        minimumSize: Size.zero,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                      ),
                      child: const Text("Mot de passe oublie?"),
                    ),
                  ),
                  const SizedBox(height: 16),
                  PrimaryButton(
                    label: "Se connecter",
                    isLoading: isLoading,
                    onPressed: isLoading ? null : _submit,
                  ),
                  const SizedBox(height: 8),
                  TextButton(
                    onPressed: () {
                      Navigator.of(context).pushReplacement(
                        MaterialPageRoute(builder: (_) => const SignupScreen()),
                      );
                    },
                    child: const Text("Creer un compte"),
                  ),
                ])),
          ]),
        )));
  }
}
