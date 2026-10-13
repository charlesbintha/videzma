import "package:flutter/material.dart";
import "package:flutter/services.dart";
import "../services/logger_service.dart";
import "../theme/app_colors.dart";

class LogsScreen extends StatefulWidget {
  const LogsScreen({super.key});

  @override
  State<LogsScreen> createState() => _LogsScreenState();
}

class _LogsScreenState extends State<LogsScreen> {
  LogLevel? _filterLevel;

  List<LogEntry> get _filteredLogs {
    final logs = logger.logs.reversed.toList();
    if (_filterLevel == null) return logs;
    return logs.where((l) => l.level == _filterLevel).toList();
  }

  Color _levelColor(LogLevel level) {
    switch (level) {
      case LogLevel.debug:
        return Colors.grey;
      case LogLevel.info:
        return AppColors.blue;
      case LogLevel.warning:
        return AppColors.warning;
      case LogLevel.error:
        return AppColors.danger;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.sand,
      appBar: AppBar(
        title: const Text("Logs"),
        backgroundColor: AppColors.surface,
        actions: [
          IconButton(
            icon: const Icon(Icons.copy),
            tooltip: "Copier les logs",
            onPressed: () {
              final text = logger.exportLogs();
              Clipboard.setData(ClipboardData(text: text));
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text("Logs copies dans le presse-papier")),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.delete_outline),
            tooltip: "Effacer les logs",
            onPressed: () {
              logger.clear();
              setState(() {});
            },
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            tooltip: "Rafraichir",
            onPressed: () => setState(() {}),
          ),
        ],
      ),
      body: Column(
        children: [
          // Filter chips
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            color: AppColors.surface,
            child: Row(
              children: [
                const Text("Filtre: ", style: TextStyle(fontWeight: FontWeight.w600)),
                const SizedBox(width: 8),
                _FilterChip(
                  label: "Tous",
                  selected: _filterLevel == null,
                  onTap: () => setState(() => _filterLevel = null),
                ),
                _FilterChip(
                  label: "Debug",
                  selected: _filterLevel == LogLevel.debug,
                  color: Colors.grey,
                  onTap: () => setState(() => _filterLevel = LogLevel.debug),
                ),
                _FilterChip(
                  label: "Info",
                  selected: _filterLevel == LogLevel.info,
                  color: AppColors.blue,
                  onTap: () => setState(() => _filterLevel = LogLevel.info),
                ),
                _FilterChip(
                  label: "Warn",
                  selected: _filterLevel == LogLevel.warning,
                  color: AppColors.warning,
                  onTap: () => setState(() => _filterLevel = LogLevel.warning),
                ),
                _FilterChip(
                  label: "Error",
                  selected: _filterLevel == LogLevel.error,
                  color: AppColors.danger,
                  onTap: () => setState(() => _filterLevel = LogLevel.error),
                ),
              ],
            ),
          ),
          // Logs list
          Expanded(
            child: _filteredLogs.isEmpty
                ? const Center(child: Text("Aucun log"))
                : ListView.builder(
                    padding: const EdgeInsets.all(8),
                    itemCount: _filteredLogs.length,
                    itemBuilder: (context, index) {
                      final log = _filteredLogs[index];
                      return Card(
                        margin: const EdgeInsets.only(bottom: 6),
                        child: Padding(
                          padding: const EdgeInsets.all(10),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 6,
                                      vertical: 2,
                                    ),
                                    decoration: BoxDecoration(
                                      color: _levelColor(log.level).withOpacity(0.15),
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                    child: Text(
                                      log.level.name.toUpperCase(),
                                      style: TextStyle(
                                        fontSize: 10,
                                        fontWeight: FontWeight.w700,
                                        color: _levelColor(log.level),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    "[${log.tag}]",
                                    style: const TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600,
                                      color: AppColors.textMuted,
                                    ),
                                  ),
                                  const Spacer(),
                                  Text(
                                    "${log.timestamp.hour.toString().padLeft(2, '0')}:"
                                    "${log.timestamp.minute.toString().padLeft(2, '0')}:"
                                    "${log.timestamp.second.toString().padLeft(2, '0')}",
                                    style: const TextStyle(
                                      fontSize: 10,
                                      color: AppColors.textMuted,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 6),
                              Text(
                                log.message,
                                style: const TextStyle(fontSize: 13),
                              ),
                              if (log.error != null) ...[
                                const SizedBox(height: 4),
                                Text(
                                  log.error!,
                                  style: const TextStyle(
                                    fontSize: 11,
                                    color: AppColors.danger,
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }
}

class _FilterChip extends StatelessWidget {
  final String label;
  final bool selected;
  final Color? color;
  final VoidCallback onTap;

  const _FilterChip({
    required this.label,
    required this.selected,
    required this.onTap,
    this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
          decoration: BoxDecoration(
            color: selected ? (color ?? AppColors.blue) : Colors.transparent,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: color ?? AppColors.blue,
              width: 1,
            ),
          ),
          child: Text(
            label,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w500,
              color: selected ? Colors.white : (color ?? AppColors.blue),
            ),
          ),
        ),
      ),
    );
  }
}
