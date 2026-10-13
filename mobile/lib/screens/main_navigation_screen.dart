import "package:flutter/material.dart";
import "../theme/app_colors.dart";
import "home_screen.dart";
import "task_screen.dart";
import "new_request_screen.dart";
import "interventions_list_screen.dart";
import "profile_screen.dart";

class MainNavigationScreen extends StatefulWidget {
  final int initialIndex;

  const MainNavigationScreen({super.key, this.initialIndex = 0});

  @override
  State<MainNavigationScreen> createState() => _MainNavigationScreenState();
}

class _MainNavigationScreenState extends State<MainNavigationScreen> {
  late int _currentIndex;
  final List<Widget> _screens = [
    const HomeScreen(),
    const RequestsListScreen(),
    const SizedBox(), // Placeholder pour le FAB central
    const InterventionsListScreen(),
    const ProfileScreen(),
  ];

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;
  }

  void _onTabTapped(int index) {
    if (index == 2) {
      // Bouton central - ouvrir nouvelle demande
      _openNewRequest();
      return;
    }

    setState(() {
      _currentIndex = index;
    });
  }

  Future<void> _openNewRequest() async {
    await Navigator.of(context).push(
      PageRouteBuilder(
        pageBuilder: (context, animation, secondaryAnimation) =>
            const NewRequestScreen(),
        transitionsBuilder: (context, animation, secondaryAnimation, child) {
          return SlideTransition(
            position: Tween<Offset>(
              begin: const Offset(0, 1),
              end: Offset.zero,
            ).animate(CurvedAnimation(
              parent: animation,
              curve: Curves.easeOutCubic,
            )),
            child: child,
          );
        },
      ),
    );
    // Rafraichir la page d'accueil apres creation
    if (_currentIndex == 0) {
      setState(() {});
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(
        index: _currentIndex == 2 ? 0 : _currentIndex,
        children: _screens,
      ),
      bottomNavigationBar: NavigationBar(
        height: 72,
        backgroundColor: Colors.white,
        elevation: 0,
        indicatorColor: AppColors.primary100,
        selectedIndex: _currentIndex == 2 ? 0 : _currentIndex,
        onDestinationSelected: _onTabTapped,
        labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
        destinations: const [
          NavigationDestination(
              icon: Icon(Icons.home_outlined),
              selectedIcon: Icon(Icons.home, color: AppColors.primary),
              label: 'Accueil'),
          NavigationDestination(
              icon: Icon(Icons.receipt_long_outlined), label: 'Demandes'),
          NavigationDestination(
              icon: Icon(Icons.add_circle_outline, color: AppColors.primary),
              label: 'Commander'),
          NavigationDestination(
              icon: Icon(Icons.event_outlined), label: 'Planning'),
          NavigationDestination(
              icon: Icon(Icons.person_outline), label: 'Profil'),
        ],
      ),
    );
  }
}
