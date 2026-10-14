// Compile with dart compile js, then execute with Node to test web arithmetic.
import '../lib/services/navigation_progress.dart';
import '../test/fixtures/dakar_polyline.dart';

void main() {
  final standard =
      NavigationProgress.decodePolyline('_p~iF~ps|U_ulLnnqC_mqNvxq`@');
  if (standard.length != 3 || standard.last.longitude != -126.453) {
    throw StateError('Negative longitude decoding failed');
  }
  final dakar = NavigationProgress.decodePolyline(dakarPolyline);
  if (dakar.length < 80 ||
      dakar.any((p) =>
          p.latitude < 14.68 ||
          p.latitude > 14.73 ||
          p.longitude < -17.49 ||
          p.longitude > -17.43)) {
    throw StateError('Dakar route escaped its geographic bounds');
  }
  if (NavigationProgress.decodePolyline('_p~iF~ps|U_').isNotEmpty ||
      NavigationProgress.decodePolyline('_gjaR?').isNotEmpty) {
    throw StateError('Invalid coordinates accepted');
  }
  print('Web polyline checks passed: ${dakar.length} Dakar points');
}
