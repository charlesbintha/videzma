import 'logger_service.dart';

class LocationStore {
  static double? latitude;
  static double? longitude;
  static int? lastLocationId;

  static bool get hasLocation {
    final has = latitude != null && longitude != null;
    logger.debug('LocationStore', 'hasLocation: $has (lat=$latitude, lng=$longitude)');
    return has;
  }

  static void set(double lat, double lng) {
    latitude = lat;
    longitude = lng;
    logger.info('LocationStore', 'Position enregistree: lat=$lat, lng=$lng');
  }

  static void setLocationId(int? id) {
    lastLocationId = id;
    logger.info('LocationStore', 'Location ID enregistre: $id');
  }

  static void clear() {
    latitude = null;
    longitude = null;
    lastLocationId = null;
    logger.info('LocationStore', 'Position effacee');
  }
}
