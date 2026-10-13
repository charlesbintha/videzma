import "package:flutter/material.dart";
import "theme/app_theme.dart";
import "screens/splash_screen.dart";

void main() {
  runApp(const VidezmaApp());
}

class VidezmaApp extends StatelessWidget {
  const VidezmaApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: "Videzma",
      builder: (context, child) => ColoredBox(
        color: const Color(0xFFEFF6FF),
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 480),
            child: child ?? const SizedBox.shrink(),
          ),
        ),
      ),
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light(),
      darkTheme: AppTheme.dark(),
      themeMode: ThemeMode.light,
      home: const SplashScreen(),
    );
  }
}
