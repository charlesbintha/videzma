@extends('layouts.master')

@section('title', 'Localisation')

@section('css')
    <style>
        .videzma-map {
            height: 520px;
            border-radius: 12px;
        }
    </style>
@endsection

@section('main_content')
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Localisation & navigation</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">
                                <svg class="stroke-icon">
                                    <use href="{{ asset('assets/svg/icon-sprite.svg#stroke-home') }}"></use>
                                </svg></a></li>
                        <li class="breadcrumb-item active">Localisation</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Clients suivis</h5>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row g-2 align-items-end">
                            <div class="col-12">
                                <label class="form-label" for="driver_id">Filtrer par vidangeur</label>
                                <select class="form-select" id="driver_id" name="driver_id">
                                    <option value="">Tous les vidangeurs</option>
                                    @foreach ($drivers ?? $doctors ?? [] as $driver)
                                        <option value="{{ $driver->id }}" @selected((string) ($selectedDriver ?? $selectedDoctor ?? '') === (string) $driver->id)>
                                            {{ $driver->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="search">Recherche client</label>
                                <input class="form-control" id="search" name="search" value="{{ $search }}" placeholder="Nom ou email">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary w-100" type="submit">Appliquer</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Dernieres positions</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Position</th>
                                        <th>Capture</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($clients ?? $patients ?? [] as $client)
                                        @php($location = $client->latestLocation)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $client->name }}</div>
                                                <small class="text-muted">{{ $client->email }}</small>
                                            </td>
                                            <td>
                                                @if ($location)
                                                    {{ $location->latitude }}, {{ $location->longitude }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if ($location)
                                                    {{ ($location->captured_at ?? $location->created_at)?->format('Y-m-d H:i') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">Aucun client.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Carte</h5>
                    </div>
                    <div class="card-body">
                        @if (!$mapsKey)
                            <div class="alert alert-warning mb-0">
                                Cle Google Maps manquante. Renseignez <code>GOOGLE_MAPS_API_KEY</code> dans le .env.
                            </div>
                        @else
                            <div id="videzma-map" class="videzma-map"></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if ($mapsKey)
        <script>
            const videzmaClients = @json($mapClients ?? $mapPatients ?? []);
            const videzmaDriver = @json($driverMap ?? $doctorMap ?? null);
            const videzmaMapId = @json($mapId);

            function escapeHtml(value) {
                const div = document.createElement('div');
                div.appendChild(document.createTextNode(value ?? ''));
                return div.innerHTML;
            }

            window.initVidezmaMap = function () {
                const hasMarkers = Boolean(videzmaDriver) || videzmaClients.length > 0;
                const fallbackCenter = { lat: 14.6928, lng: -17.4467 }; // Dakar
                const first = videzmaDriver || videzmaClients[0];
                const center = first ? { lat: first.latitude, lng: first.longitude } : fallbackCenter;
                const mapOptions = {
                    center,
                    zoom: hasMarkers ? 12 : 10,
                };

                if (videzmaMapId) {
                    mapOptions.mapId = videzmaMapId;
                }

                const map = new google.maps.Map(document.getElementById('videzma-map'), mapOptions);

                if (!hasMarkers) {
                    return;
                }

                let directionsRenderer = null;
                let directionsService = null;

                if (videzmaDriver) {
                    new google.maps.Marker({
                        position: { lat: videzmaDriver.latitude, lng: videzmaDriver.longitude },
                        map,
                        title: videzmaDriver.name,
                        label: 'V', // V for Vidangeur
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 12,
                            fillColor: '#4CB050',
                            fillOpacity: 1,
                            strokeColor: '#fff',
                            strokeWeight: 2,
                        },
                    });

                    directionsRenderer = new google.maps.DirectionsRenderer({
                        map,
                        polylineOptions: {
                            strokeColor: '#F15A22',
                            strokeWeight: 4,
                        },
                    });
                    directionsService = new google.maps.DirectionsService();
                }

                videzmaClients.forEach((client) => {
                    const marker = new google.maps.Marker({
                        position: { lat: client.latitude, lng: client.longitude },
                        map,
                        title: client.name,
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 8,
                            fillColor: '#F15A22',
                            fillOpacity: 1,
                            strokeColor: '#fff',
                            strokeWeight: 2,
                        },
                    });

                    const info = new google.maps.InfoWindow({
                        content: `<strong>${escapeHtml(client.name)}</strong><br>${escapeHtml(client.address || '')}<br>${escapeHtml(client.captured_at || '')}`,
                    });

                    marker.addListener('click', () => {
                        info.open(map, marker);

                        if (videzmaDriver && directionsService && directionsRenderer) {
                            directionsService.route({
                                origin: { lat: videzmaDriver.latitude, lng: videzmaDriver.longitude },
                                destination: { lat: client.latitude, lng: client.longitude },
                                travelMode: google.maps.TravelMode.DRIVING,
                                drivingOptions: {
                                    departureTime: new Date(),
                                    trafficModel: 'bestguess',
                                },
                            }, (result, status) => {
                                if (status === 'OK') {
                                    directionsRenderer.setDirections(result);
                                }
                            });
                        }
                    });
                });
            };
        </script>
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $mapsKey }}&callback=initVidezmaMap&loading=async{{ $mapId ? '&map_ids=' . $mapId : '' }}" async defer></script>
    @endif
@endsection
