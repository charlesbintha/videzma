import 'dart:async';
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:web_socket_channel/web_socket_channel.dart';
import '../config/app_config.dart';
import 'session_store.dart';
import 'logger_service.dart';

/// Configuration for Reverb WebSocket connection
class ReverbConfig {
  final String host;
  final int port;
  final String scheme;
  final String appKey;

  const ReverbConfig({
    required this.host,
    required this.port,
    required this.scheme,
    required this.appKey,
  });

  String get wsScheme => scheme == 'https' ? 'wss' : 'ws';

  String get wsUrl => '$wsScheme://$host:$port/app/$appKey';
}

/// Event received from WebSocket
class WebSocketEvent {
  final String event;
  final String channel;
  final Map<String, dynamic> data;

  WebSocketEvent({
    required this.event,
    required this.channel,
    required this.data,
  });

  factory WebSocketEvent.fromJson(Map<String, dynamic> json) {
    return WebSocketEvent(
      event: json['event'] as String? ?? '',
      channel: json['channel'] as String? ?? '',
      data: json['data'] is String
          ? jsonDecode(json['data'] as String) as Map<String, dynamic>
          : (json['data'] as Map<String, dynamic>?) ?? {},
    );
  }
}

/// WebSocket service for real-time communication with Laravel Reverb
class WebSocketService {
  static WebSocketService? _instance;

  final ReverbConfig config;

  WebSocketChannel? _channel;
  StreamSubscription? _subscription;
  String? _socketId;
  bool _connected = false;
  Timer? _pingTimer;

  final _eventController = StreamController<WebSocketEvent>.broadcast();
  final _connectionController = StreamController<bool>.broadcast();
  final Set<String> _subscribedChannels = {};

  // Callbacks for channel events
  final Map<String, List<void Function(WebSocketEvent)>> _eventCallbacks = {};

  WebSocketService._({required this.config});

  /// Get singleton instance with default config from AppConfig
  static WebSocketService get instance {
    _instance ??= WebSocketService._(
      config: const ReverbConfig(
        host: AppConfig.reverbHost,
        port: AppConfig.reverbPort,
        scheme: AppConfig.reverbScheme,
        appKey: AppConfig.reverbAppKey,
      ),
    );
    return _instance!;
  }

  /// Stream of WebSocket events
  Stream<WebSocketEvent> get events => _eventController.stream;

  /// Stream of connection status
  Stream<bool> get connectionStatus => _connectionController.stream;

  /// Whether connected to WebSocket
  bool get isConnected => _connected;

  /// Socket ID for authentication
  String? get socketId => _socketId;

  /// Connect to WebSocket server
  Future<void> connect() async {
    if (_connected) {
      logger.debug('WebSocket', 'Already connected');
      return;
    }

    try {
      logger.info('WebSocket', 'Connecting to ${config.wsUrl}');

      final uri = Uri.parse(config.wsUrl);
      _channel = WebSocketChannel.connect(uri);

      _subscription = _channel!.stream.listen(
        _onMessage,
        onError: _onError,
        onDone: _onDone,
      );

      // Start ping timer to keep connection alive
      _pingTimer?.cancel();
      _pingTimer = Timer.periodic(const Duration(seconds: 30), (_) {
        _sendPing();
      });

      logger.info('WebSocket', 'WebSocket connected');
    } catch (e) {
      logger.error('WebSocket', 'Connection failed: $e');
      _connectionController.add(false);
      rethrow;
    }
  }

  /// Disconnect from WebSocket server
  void disconnect() {
    logger.info('WebSocket', 'Disconnecting...');

    _pingTimer?.cancel();
    _pingTimer = null;

    _subscription?.cancel();
    _subscription = null;

    _channel?.sink.close();
    _channel = null;

    _connected = false;
    _socketId = null;
    _subscribedChannels.clear();

    _connectionController.add(false);
  }

  /// Subscribe to a private channel
  Future<void> subscribePrivate(String channelName) async {
    final fullChannelName =
        channelName.startsWith('private-') ? channelName : 'private-$channelName';

    if (_subscribedChannels.contains(fullChannelName)) {
      logger.debug('WebSocket', 'Already subscribed to $fullChannelName');
      return;
    }

    if (_socketId == null) {
      logger.warning('WebSocket', 'Cannot subscribe: not connected');
      return;
    }

    try {
      // Get auth signature from backend
      final auth = await _authenticate(fullChannelName);

      // Send subscribe message
      _send({
        'event': 'pusher:subscribe',
        'data': {
          'channel': fullChannelName,
          'auth': auth,
        },
      });

      _subscribedChannels.add(fullChannelName);
      logger.info('WebSocket', 'Subscribed to $fullChannelName');
    } catch (e) {
      logger.error('WebSocket', 'Failed to subscribe to $fullChannelName: $e');
    }
  }

  /// Unsubscribe from a channel
  void unsubscribe(String channelName) {
    final fullChannelName =
        channelName.startsWith('private-') ? channelName : 'private-$channelName';

    if (!_subscribedChannels.contains(fullChannelName)) {
      return;
    }

    _send({
      'event': 'pusher:unsubscribe',
      'data': {'channel': fullChannelName},
    });

    _subscribedChannels.remove(fullChannelName);
    _eventCallbacks.remove(fullChannelName);
    logger.info('WebSocket', 'Unsubscribed from $fullChannelName');
  }

  /// Listen to events on a channel
  void on(String channelName, String eventName, void Function(WebSocketEvent) callback) {
    final fullChannelName =
        channelName.startsWith('private-') ? channelName : 'private-$channelName';
    final key = '$fullChannelName:$eventName';

    _eventCallbacks[key] ??= [];
    _eventCallbacks[key]!.add(callback);
  }

  /// Remove event listener
  void off(String channelName, String eventName, void Function(WebSocketEvent) callback) {
    final fullChannelName =
        channelName.startsWith('private-') ? channelName : 'private-$channelName';
    final key = '$fullChannelName:$eventName';

    _eventCallbacks[key]?.remove(callback);
  }

  void _onMessage(dynamic message) {
    try {
      final data = jsonDecode(message as String) as Map<String, dynamic>;
      final event = data['event'] as String?;

      logger.debug('WebSocket', 'Received: $event');

      switch (event) {
        case 'pusher:connection_established':
          _onConnectionEstablished(data);
          break;
        case 'pusher:ping':
          _sendPong();
          break;
        case 'pusher:pong':
          // Received pong, connection is alive
          break;
        case 'pusher_internal:subscription_succeeded':
          logger.info('WebSocket', 'Subscription succeeded: ${data['channel']}');
          break;
        case 'pusher:error':
          logger.error('WebSocket', 'Pusher error: ${data['data']}');
          break;
        default:
          if (event != null && !event.startsWith('pusher')) {
            _handleEvent(data);
          }
      }
    } catch (e) {
      logger.error('WebSocket', 'Error parsing message: $e');
    }
  }

  void _onConnectionEstablished(Map<String, dynamic> data) {
    final connectionData = data['data'] is String
        ? jsonDecode(data['data'] as String) as Map<String, dynamic>
        : data['data'] as Map<String, dynamic>;

    _socketId = connectionData['socket_id'] as String?;
    _connected = true;

    logger.info('WebSocket', 'Connection established, socket_id: $_socketId');
    _connectionController.add(true);
  }

  void _handleEvent(Map<String, dynamic> data) {
    final event = WebSocketEvent.fromJson(data);
    _eventController.add(event);

    // Call registered callbacks
    final key = '${event.channel}:${event.event}';
    final callbacks = _eventCallbacks[key];
    if (callbacks != null) {
      for (final callback in callbacks) {
        callback(event);
      }
    }
  }

  void _onError(dynamic error) {
    logger.error('WebSocket', 'WebSocket error: $error');
    _connected = false;
    _connectionController.add(false);
  }

  void _onDone() {
    logger.info('WebSocket', 'WebSocket closed');
    _connected = false;
    _socketId = null;
    _connectionController.add(false);

    // Auto-reconnect after 3 seconds
    Future.delayed(const Duration(seconds: 3), () {
      if (!_connected) {
        logger.info('WebSocket', 'Attempting to reconnect...');
        connect();
      }
    });
  }

  void _send(Map<String, dynamic> data) {
    if (_channel == null) {
      logger.warning('WebSocket', 'Cannot send: not connected');
      return;
    }

    final message = jsonEncode(data);
    _channel!.sink.add(message);
  }

  void _sendPing() {
    _send({'event': 'pusher:ping', 'data': {}});
  }

  void _sendPong() {
    _send({'event': 'pusher:pong', 'data': {}});
  }

  Future<String> _authenticate(String channelName) async {
    final token = SessionStore.token;
    if (token == null) {
      throw Exception('Not authenticated');
    }

    final response = await http.post(
      Uri.parse('${AppConfig.apiBaseUrl}/api/broadcasting/auth'),
      headers: {
        'Authorization': 'Bearer $token',
        'Content-Type': 'application/x-www-form-urlencoded',
        'Accept': 'application/json',
      },
      body: {
        'socket_id': _socketId,
        'channel_name': channelName,
      },
    );

    if (response.statusCode != 200) {
      throw Exception('Auth failed: ${response.statusCode}');
    }

    final data = jsonDecode(response.body) as Map<String, dynamic>;
    return data['auth'] as String;
  }

  /// Dispose resources
  void dispose() {
    disconnect();
    _eventController.close();
    _connectionController.close();
  }
}
