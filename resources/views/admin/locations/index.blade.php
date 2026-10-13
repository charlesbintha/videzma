@extends('layouts.master')
@section('title', 'Localisations')
@section('main_content')
<div class="container-fluid vz-locations">
    <div class="vz-location-heading"><div><span class="vz-location-eyebrow">OPÉRATIONS SUR LE TERRAIN</span><h3>Localisations</h3><p>Les lieux d’intervention et le suivi de vos vidangeurs, sur une seule carte.</p></div><button class="btn btn-primary" id="refresh-locations" aria-label="Actualiser les positions" type="button"><i class="fa fa-refresh" aria-hidden="true"></i> Actualiser</button></div>
    <div class="vz-location-stats"><div><strong id="mission-count">{{ $mapData['total'] }}</strong><span>Demandes actives</span></div><div><strong id="live-count">—</strong><span>Vidangeurs · GPS récent</span></div><div><strong id="missing-count">—</strong><span>Lieux à préciser</span></div></div>
    <form class="vz-location-filters" method="GET" action="{{ route('admin.locations.index') }}">
        <div><label for="location-search">Client ou adresse</label><input id="location-search" class="form-control" name="search" maxlength="100" value="{{ $search }}" placeholder="Rechercher une intervention…"></div>
        <div><label for="location-driver">Vidangeur</label><select class="form-select" id="location-driver" name="driver_id"><option value="">Tous les vidangeurs</option>@foreach($drivers as $driver)<option value="{{ $driver->id }}" @selected((string)$selectedDriver === (string)$driver->id)>{{ $driver->name }}</option>@endforeach</select></div>
        <button class="btn btn-primary" type="submit">Filtrer</button><a class="btn btn-light" href="{{ route('admin.locations.index') }}">Réinitialiser</a>
    </form>
    <div class="vz-location-workspace">
        <section class="vz-location-map-panel" aria-label="Carte des interventions">
            <div class="vz-location-map-toolbar"><span><i class="fa fa-map-marker" aria-hidden="true"></i> Vue terrain</span><button id="fit-locations" type="button" class="btn btn-light">Tout voir</button></div>
            <div id="locations-map" class="vz-location-map"><p class="vz-map-placeholder">{{ $mapsKey ? 'Chargement de la carte…' : 'La carte est indisponible pour le moment. Vous pouvez consulter les interventions dans la liste.' }}</p></div>
            <div class="vz-map-legend"><span><b class="vz-map-dot destination"></b> Lieu d’intervention</span><span><b class="vz-map-dot driver"></b> Vidangeur</span><span><b class="vz-map-dot stale"></b> Position ancienne</span></div>
            <p id="map-notice" class="vz-location-notice" role="status">Sélectionnez une demande pour consulter son trajet.</p>
        </section>
        <section class="vz-location-list-panel" aria-label="Demandes actives"><div class="vz-location-list-heading"><h4>Interventions à suivre</h4><span id="sync-state" role="status">Mise à jour toutes les 15 s</span></div><div id="location-missions" class="vz-location-missions"></div></section>
    </div>
    <section id="location-detail" class="vz-location-detail" aria-label="Intervention sélectionnée" hidden></section>
</div>
@endsection
@section('scripts')
<script id="locations-config" type="application/json">{!! json_encode(['data' => $mapData, 'endpoint' => route('admin.locations.data', request()->only('driver_id','search')), 'hasMap' => (bool)$mapsKey], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script src="{{ asset('assets/js/videzma-locations.js') }}?v={{ filemtime(public_path('assets/js/videzma-locations.js')) }}"></script>
@if($mapsKey)<script async src="https://maps.googleapis.com/maps/api/js?key={{ $mapsKey }}&amp;callback=initLocationsMap&amp;loading=async" onerror="locationsMapFailed()"></script>@endif
@endsection
