import "package:flutter/material.dart";

/// Shared blue palette for the Videzma mobile and administration interfaces.
class AppColors {
  static const Color primary = Color(0xFF2563EB);
  static const Color primary600 = Color(0xFF1D4ED8);
  static const Color primary400 = Color(0xFF60A5FA);
  static const Color primary100 = Color(0xFFEFF6FF);

  static const Color secondary = Color(0xFF0891B2);
  static const Color secondary600 = Color(0xFF0E7490);
  static const Color secondary400 = Color(0xFF38BDF8);
  static const Color secondary100 = Color(0xFFECFEFF);

  static const Color accent = Color(0xFF38BDF8);
  static const Color accent600 = Color(0xFF0284C7);
  static const Color accent400 = Color(0xFF7DD3FC);
  static const Color accent100 = Color(0xFFE0F2FE);

  // Neutral colors
  static const Color sand = Color(0xFFF3F6FC);
  static const Color surface = Color(0xFFFFFFFF);
  static const Color surface2 = Color(0xFFEFF6FF);
  static const Color text = Color(0xFF172554);
  static const Color textMuted = Color(0xFF64748B);
  static const Color border = Color(0xFFE2E8F0);

  // Semantic colors
  static const Color success = Color(0xFF4CB050); // Green Videzma
  static const Color warning = Color(0xFFF7941D); // Yellow Videzma
  static const Color danger = Color(0xFFE53935);
  static const Color info = Color(0xFF2196F3);

  // Dark mode colors
  static const Color darkPrimary = Color(0xFF60A5FA);
  static const Color darkPrimary600 = Color(0xFF2563EB);
  static const Color darkSecondary = Color(0xFF38BDF8);
  static const Color darkSecondary600 = Color(0xFF0891B2);
  static const Color darkAccent = Color(0xFF7DD3FC);
  static const Color darkAccent600 = Color(0xFF0284C7);
  static const Color darkBackground = Color(0xFF121212);
  static const Color darkSurface = Color(0xFF1E1E1E);
  static const Color darkSurface2 = Color(0xFF2C2C2C);
  static const Color darkText = Color(0xFFE0E0E0);
  static const Color darkTextMuted = Color(0xFF9E9E9E);
  static const Color darkBorder = Color(0xFF424242);
  static const Color darkSuccess = Color(0xFF6EC071);
  static const Color darkWarning = Color(0xFFFAB24D);
  static const Color darkDanger = Color(0xFFEF5350);

  // Gradient colors for splash/branding
  static const List<Color> brandGradient = [primary600, primary, secondary];

  // Status colors for service requests
  static const Color statusPending = Color(0xFFF7941D); // Yellow/Gold
  static const Color statusAssigned = Color(0xFF2196F3); // Blue
  static const Color statusAccepted = Color(0xFF4CB050); // Green
  static const Color statusInProgress = Color(0xFF2563EB);
  static const Color statusCompleted = Color(0xFF4CB050); // Green
  static const Color statusRejected = Color(0xFFE53935); // Red
  static const Color statusCancelled = Color(0xFF9E9E9E); // Grey

  // Aliases for backwards compatibility
  static const Color navy = text;
  static const Color blue = info;
  static const Color blue600 = Color(0xFF1976D2);
  static const Color orange = primary;
  static const Color orange600 = primary600;
  static const Color green = secondary;
  static const Color green600 = secondary600;
}
