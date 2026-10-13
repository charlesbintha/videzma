import "dart:convert";
import "package:http/http.dart" as http;
import "../config/app_config.dart";
import "session_store.dart";
import "logger_service.dart";

class ApiClient {
  static const _tag = 'ApiClient';

  ApiClient({http.Client? client}) : _client = client ?? http.Client();

  final http.Client _client;

  Future<http.Response> post(String path, Map<String, dynamic> body) async {
    final url = "${AppConfig.apiBaseUrl}$path";
    logger.info(_tag, 'POST $url');

    try {
      final response = await _client.post(
        Uri.parse(url),
        headers: _headers(),
        body: jsonEncode(body),
      );
      logger.info(_tag, 'POST $path -> ${response.statusCode}');
      if (response.statusCode >= 400) {
        logger.warning(_tag, 'Reponse erreur: ${response.body}');
      }
      return response;
    } catch (e) {
      logger.error(_tag, 'POST $path echoue', e);
      rethrow;
    }
  }

  Future<http.Response> get(String path, {Map<String, String>? query}) async {
    final uri = Uri.parse("${AppConfig.apiBaseUrl}$path").replace(
      queryParameters: query,
    );
    logger.info(_tag, 'GET $uri');

    try {
      final response = await _client.get(
        uri,
        headers: _headers(),
      );
      logger.info(_tag, 'GET $path -> ${response.statusCode}');
      if (response.statusCode >= 400) {
        logger.warning(_tag, 'Reponse erreur: ${response.body}');
      }
      return response;
    } catch (e) {
      logger.error(_tag, 'GET $path echoue', e);
      rethrow;
    }
  }

  /// GET request with ETag support for conditional requests
  /// Returns 304 Not Modified if content hasn't changed
  Future<http.Response> getWithEtag(String path, {String? etag}) async {
    final uri = Uri.parse("${AppConfig.apiBaseUrl}$path");

    try {
      final headers = _headers();
      if (etag != null) {
        headers['If-None-Match'] = etag;
      }

      final response = await _client.get(uri, headers: headers);

      // Don't log 304 as it's expected for unchanged content
      if (response.statusCode != 304) {
        logger.debug(_tag, 'GET $path -> ${response.statusCode}');
      }

      return response;
    } catch (e) {
      logger.error(_tag, 'GET $path echoue', e);
      rethrow;
    }
  }

  Future<http.Response> patch(String path, Map<String, dynamic> body) async {
    final url = "${AppConfig.apiBaseUrl}$path";
    logger.info(_tag, 'PATCH $url');

    try {
      final response = await _client.patch(
        Uri.parse(url),
        headers: _headers(),
        body: jsonEncode(body),
      );
      logger.info(_tag, 'PATCH $path -> ${response.statusCode}');
      if (response.statusCode >= 400) {
        logger.warning(_tag, 'Reponse erreur: ${response.body}');
      }
      return response;
    } catch (e) {
      logger.error(_tag, 'PATCH $path echoue', e);
      rethrow;
    }
  }

  Future<http.Response> delete(String path) async {
    final url = "${AppConfig.apiBaseUrl}$path";
    logger.info(_tag, 'DELETE $url');

    try {
      final response = await _client.delete(
        Uri.parse(url),
        headers: _headers(),
      );
      logger.info(_tag, 'DELETE $path -> ${response.statusCode}');
      if (response.statusCode >= 400) {
        logger.warning(_tag, 'Reponse erreur: ${response.body}');
      }
      return response;
    } catch (e) {
      logger.error(_tag, 'DELETE $path echoue', e);
      rethrow;
    }
  }

  Map<String, String> _headers() {
    final headers = <String, String>{
      "Content-Type": "application/json",
      "Accept": "application/json",
    };

    final token = SessionStore.token;
    if (token != null) {
      headers["Authorization"] = "Bearer $token";
    }

    return headers;
  }
}
