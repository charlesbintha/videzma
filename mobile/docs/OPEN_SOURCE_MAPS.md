# Cartes OpenStreetMap et itinéraires openrouteservice

Les quatre écrans cartographiques mobiles utilisent maintenant `flutter_map` :
carte du client, choix du lieu d'intervention, suivi du chauffeur, navigation.
Aucune clé Google ni script JavaScript Google n'est nécessaire sur le mobile.
Le fournisseur de tuiles par défaut est OpenStreetMap. Le cache natif intégré
respecte les en-têtes HTTP ; le navigateur utilise son propre cache. L'attribution
reste visible et aucun téléchargement de zone hors ligne n'est implémenté.
Le service public de tuiles n'offre pas de disponibilité garantie ; prévoir un
fournisseur adapté si le trafic augmente. `MAP_TILE_URL` est configurable au build.

## Activation des trajets

1. Créer un compte gratuit sur https://account.heigit.org/ et obtenir une clé.
2. Ajouter `OPENROUTESERVICE_API_KEY` au `.env` serveur (jamais dans Flutter).
3. Définir `ROUTING_PROVIDER=openrouteservice`, puis régénérer le cache Laravel
   avec `php artisan config:cache` si le déploiement l'utilise.
4. Vérifier un trajet public à Dakar, puis une mission de test attribuée.

La configuration reste sur `google` tant que la clé openrouteservice n'est pas
fournie et que le basculement n'a pas été validé. Aucun repli automatique vers
Google payant ni vers un serveur public de démonstration OSRM n'est effectué.
Activation effectuée sur videzma.emmaluxury.store le 14 octobre 2026. Un trajet public
à Dakar a été vérifié depuis la production : 4,12 km, 7 minutes estimées, 13 instructions.
Les tests automatisés utilisent des réponses simulées ; le suivi en déplacement réel
sur téléphone reste à vérifier.

## Gratuité et limites

L'offre hébergée gratuite openrouteservice impose des quotas. Vérifier les limites
actuelles dans le tableau de bord du compte ; chaque recalcul consomme une requête.
Le suivi du GPS et l'envoi à Laravel n'utilisent pas de requête Directions.
Recalcul hors trajet après deux fixes à plus de 60 m, avec un intervalle minimal
de 30 secondes. Actualisation périodique toutes les 5 minutes si au moins 50 m ont
été parcourus. Cache serveur de 20 secondes sur un trajet strictement identique.
Une erreur de quota est affichée explicitement ; la dernière route est conservée.

La durée est une estimation routière, sans trafic en temps réel. Le profil actuel
est `driving-car` : il ne garantit pas la prise en compte du gabarit d'un camion.
La navigation vocale n'est pas intégrée ; le bouton externe ouvre Google Maps.
Un moteur auto-hébergé supprime les quotas du fournisseur, mais nécessite de la
capacité serveur et de la maintenance. Un hébergement PHP mutualisé ne suffit pas
à lui seul pour exécuter un moteur comme OSRM.

## Compatibilité et tests

Laravel conserve le contrat d'itinéraire historique (polylines, étapes, distances,
durées) pour les versions mobiles existantes. Le fournisseur est identifié dans
`route_data.provider`. Les clés Google du serveur sont conservées pour les vues
administratives existantes, qui ne font pas partie de cette migration mobile.

- PHP : `php artisan test --filter='OpenRouteServiceDirectionsTest|GoogleMapsDirectionsTest|LocationTrackingTest|VidezmaRegressionTest'`
- Flutter : `flutter test test/navigation_progress_test.dart test/navigation_service_test.dart test/location_service_test.dart test/tracking_test.dart`
- APK : `flutter build apk --release --dart-define=API_BASE_URL=https://videzma.emmaluxury.store`

La version 0.1.2 conserve la signature de développement des APK précédents.
