class AppConfig {
  // Videzma API Configuration
  // Local development
  // static const String apiBaseUrl = "http://127.0.0.1:8000";
  // static const String apiBaseUrl = "http://192.168.1.4:8000";

  // Production
  static const String apiBaseUrl = String.fromEnvironment(
    "API_BASE_URL",
    defaultValue: "https://videzma.emmaluxury.store",
  );

  // Laravel Reverb WebSocket Configuration
  // Local development
  // static const String reverbHost = "127.0.0.1";
  // static const int reverbPort = 8080;
  // static const String reverbScheme = "http";

  // Production (WebSocket server on same domain)
  static const String reverbHost = "videzma.emmaluxury.store";
  static const int reverbPort = 6001; // Port WebSocket production
  static const String reverbScheme = "https";
  static const String reverbAppKey = "scoodwox4hbllzjzcnq6khxv";

  // App Info
  static const String appName = "Videzma";
  static const String appVersion = "0.1.0";
}
