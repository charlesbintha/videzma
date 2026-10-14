# Navigation chauffeur

> Version 0.1.2 : voir [OPEN_SOURCE_MAPS.md](OPEN_SOURCE_MAPS.md) pour la migration OpenStreetMap/openrouteservice. Les détails ci-dessous décrivent la version Google 0.1.1.

La version 0.1.1 utilise la carte Google existante et les itinéraires calculés par
Laravel via Google Directions. Il s'agit d'un guidage visuel dans Videzma, sans
Navigation SDK ni annonces vocales. Le bouton de carte externe ouvre Google Maps
en mode navigation pour le guidage vocal.

## Comportement

- GPS Android haute précision, intervalle demandé de 2 secondes. Un service de
  premier plan avec notification maintient la capture pendant la navigation.
- La caméra et les instructions suivent le GPS sans attendre le serveur.
  Un geste sur la carte suspend le centrage ; le bouton de position le réactive.
- Positions de plus de 15 secondes ou de précision supérieure à 50 m ignorées.
  Un avertissement apparaît après 20 secondes sans position utilisable.
- Envoi serveur au plus toutes les 5 secondes, requêtes sérialisées et conservation
  de la dernière position en attente. Pas de rejeu de l'historique hors ligne.
- Distance restante mesurée sur la géométrie routière Google ; durée estimée à
  partir du dernier calcul Google et de la progression sur ce trajet.
- Recalcul après deux positions à plus de 60 m du trajet, avec délai minimal
  de 20 secondes entre tentatives. Actualisation aussi après 60 secondes et 50 m
  parcourus. Les recalculs nécessitent Internet et consomment des appels Directions.
- Alerte d'arrivée automatique unique : GPS précis à 30 m, client à moins de 40 m,
  distance routière restante inférieure à 80 m et position proche de la route.
- Une panne réseau conserve le dernier itinéraire et affiche un avertissement.
  La sortie de l'écran de navigation arrête la capture.

## Vérification sur téléphone (encore nécessaire)

1. Installer l'APK en mise à jour, activer le GPS précis et ouvrir une mission
   attribuée au chauffeur, acceptée ou en cours.
2. Démarrer en extérieur. Vérifier la position, les instructions et la distance
   pendant un déplacement comportant au moins un virage.
3. Quitter volontairement le trajet : vérifier le recalcul et la nouvelle ligne.
4. Couper Internet temporairement : le GPS continue, la synchronisation est
   signalée indisponible ; rétablir Internet pour vérifier la reprise.
5. Désactiver le GPS, puis le réactiver ; vérifier l'avertissement et la reprise
   (selon Android, un retour à la mission peut être nécessaire).
6. Verrouiller l'écran brièvement pendant la mission et vérifier la notification
   et la reprise au déverrouillage. Les restrictions batterie du constructeur
   peuvent affecter le suivi et doivent être vérifiées sur l'appareil cible.
7. À destination, vérifier qu'une seule alerte automatique apparaît, puis terminer
   et vérifier la disparition du service de localisation.

Tests automatisés : `flutter test test/navigation_progress_test.dart
 test/navigation_service_test.dart test/location_service_test.dart
 test/tracking_test.dart` (réunir la commande sur une ligne).

## APK

Compilation : définir `GOOGLE_MAPS_API_KEY` dans l'environnement, puis exécuter
`flutter build apk --release --dart-define=API_BASE_URL=https://videzma.emmaluxury.store`.
La configuration actuelle conserve la signature Android de développement pour
permettre la mise à jour de l'APK précédent. Une publication Play Store nécessite
une configuration de signature de distribution dédiée.
