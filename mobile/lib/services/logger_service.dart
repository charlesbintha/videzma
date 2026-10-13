import 'dart:developer' as developer;

enum LogLevel { debug, info, warning, error }

class LoggerService {
  static final LoggerService _instance = LoggerService._internal();
  factory LoggerService() => _instance;
  LoggerService._internal();

  final List<LogEntry> _logs = [];
  List<LogEntry> get logs => List.unmodifiable(_logs);

  void _log(LogLevel level, String tag, String message, [Object? error]) {
    final entry = LogEntry(
      timestamp: DateTime.now(),
      level: level,
      tag: tag,
      message: message,
      error: error?.toString(),
    );
    _logs.add(entry);

    // Keep only last 500 logs
    if (_logs.length > 500) {
      _logs.removeAt(0);
    }

    // Print to console
    final prefix = '[${level.name.toUpperCase()}][$tag]';
    developer.log('$prefix $message', name: 'VIDEZMA', error: error);
    print('$prefix $message${error != null ? ' | Error: $error' : ''}');
  }

  void debug(String tag, String message) => _log(LogLevel.debug, tag, message);
  void info(String tag, String message) => _log(LogLevel.info, tag, message);
  void warning(String tag, String message) => _log(LogLevel.warning, tag, message);
  void error(String tag, String message, [Object? error]) =>
      _log(LogLevel.error, tag, message, error);

  void clear() => _logs.clear();

  String exportLogs() {
    final buffer = StringBuffer();
    for (final log in _logs) {
      buffer.writeln(log.toString());
    }
    return buffer.toString();
  }
}

class LogEntry {
  final DateTime timestamp;
  final LogLevel level;
  final String tag;
  final String message;
  final String? error;

  LogEntry({
    required this.timestamp,
    required this.level,
    required this.tag,
    required this.message,
    this.error,
  });

  @override
  String toString() {
    final time = '${timestamp.hour.toString().padLeft(2, '0')}:'
        '${timestamp.minute.toString().padLeft(2, '0')}:'
        '${timestamp.second.toString().padLeft(2, '0')}';
    return '[$time][${level.name.toUpperCase()}][$tag] $message${error != null ? ' | $error' : ''}';
  }
}

// Global logger instance
final logger = LoggerService();
