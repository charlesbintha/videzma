(() => {
    'use strict';
    const config = JSON.parse(document.getElementById('locations-config').textContent);
    const el = id => document.getElementById(id);
    const escape = text => String(text ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    let data = config.data, selected = null, map, markers = [], directions, router, routeGeneration = 0, busy = false;
    const age = position => {
        if (!position?.captured_at) return 'Date inconnue';
        return new Intl.DateTimeFormat('fr-FR', {dateStyle:'short', timeStyle:'short'}).format(new Date(position.captured_at));
    };
    const fresh = mission => mission.position?.fresh && Date.now() - Date.parse(mission.position.captured_at) <= 120000;
    const state = mission => !mission.driver ? 'À attribuer' : !mission.tracking ? 'Suivi non démarré' : !mission.position ? 'En attente du GPS' : fresh(mission) ? 'GPS récent' : 'Position ancienne';
    function render() {
        const focusedMission = document.activeElement?.closest('.vz-mission')?.dataset.id;
        const focusedRoute = document.activeElement?.id === 'show-route';
        el('mission-count').textContent = data.total;
        el('live-count').textContent = new Set(data.missions.filter(fresh).map(m => m.driver.id)).size;
        el('missing-count').textContent = data.missions.filter(m => !m.destination).length;
        el('location-missions').innerHTML = data.missions.length ? data.missions.map(m => `<button class="vz-mission ${selected === m.id ? 'is-selected' : ''}" type="button" data-id="${m.id}" aria-pressed="${selected === m.id}"><span class="vz-mission-top"><strong>${escape(m.client)}</strong><span>#${m.id}</span></span><span class="vz-mission-address">${escape(m.address || 'Adresse à préciser')}</span><span class="vz-mission-meta">${escape(m.status)} · ${escape(m.driver?.name || 'Aucun vidangeur')}</span><span class="vz-gps-state ${fresh(m) ? 'is-fresh' : ''}">${escape(state(m))}</span>${!m.destination ? '<span class="vz-missing-location">Lieu non renseigné</span>' : ''}</button>`).join('') : '<div class="vz-location-empty"><i class="fa fa-map-o" aria-hidden="true"></i><h4>Aucune demande active</h4><p>Modifiez les filtres ou retrouvez les demandes terminées dans les interventions.</p></div>';
        if (data.total > data.missions.length) el('sync-state').textContent = '300 demandes affichées : affinez les filtres.';
        if (selected && !data.missions.some(m => m.id === selected)) { selected = null; directions?.setDirections({routes: []}); routeGeneration++; }
        renderDetail();
        if (map) drawMarkers();
        if (focusedMission) el('location-missions').querySelector(`[data-id="${focusedMission}"]`)?.focus({preventScroll:true});
        if (focusedRoute) el('show-route')?.focus({preventScroll:true});
    }
    function renderDetail() {
        const m = data.missions.find(m => m.id === selected);
        el('location-detail').hidden = !m;
        if (!m) return;
        el('location-detail').innerHTML = `<div><span class="vz-location-eyebrow">DEMANDE #${m.id} · ${escape(m.status)}</span><h4>${escape(m.client)}</h4><p>${escape(m.address || 'Adresse à préciser')}</p></div><div><strong>${escape(m.driver?.name || 'Vidangeur à attribuer')}</strong><p>${escape(state(m))}${m.position ? ' · ' + escape(age(m.position)) : ''}</p><small>Le lieu affiché est celui enregistré pour cette intervention.</small></div><div class="vz-location-actions"><button type="button" class="btn btn-primary" id="show-route" ${!fresh(m) || !m.destination || !map ? 'disabled' : ''}>Calculer le trajet</button><a class="btn btn-light" href="${escape(m.url)}">Voir la demande</a></div>`;
        el('show-route').addEventListener('click', route);
    }
    function select(id) {
        selected = id; routeGeneration++; directions?.setDirections({routes: []});
        el('map-notice').textContent = 'Le trajet est disponible lorsque le GPS du vidangeur est récent.';
        render();
        const m = data.missions.find(m => m.id === selected);
        if (map && m?.destination) { map.panTo(m.destination); map.setZoom(14); }
        el('location-detail').scrollIntoView({behavior:'smooth', block:'nearest'});
    }
    el('location-missions').addEventListener('click', event => { const button = event.target.closest('[data-id]'); if (button) select(Number(button.dataset.id)); });
    function drawMarkers() {
        markers.forEach(m => m.setMap(null)); markers = [];
        const drivers = new Set();
        data.missions.forEach(m => {
            if (m.destination) {
                const marker = new google.maps.Marker({map, position:m.destination, title:`#${m.id} · ${m.client}`, icon:{path:google.maps.SymbolPath.CIRCLE, scale:selected === m.id ? 11 : 8, fillColor:'#2563eb', fillOpacity:1, strokeColor:'#ffffff', strokeWeight:3}});
                marker.addListener('click', () => select(m.id)); markers.push(marker);
            }
            if (m.position && !drivers.has(m.driver.id)) {
                drivers.add(m.driver.id);
                const marker = new google.maps.Marker({map, position:m.position, title:`${m.driver.name} · ${state(m)}`, icon:{path:google.maps.SymbolPath.FORWARD_CLOSED_ARROW, scale:6, fillColor:fresh(m) ? '#0891b2' : '#64748b', fillOpacity:1, strokeColor:'#ffffff', strokeWeight:2}});
                marker.addListener('click', () => select(m.id)); markers.push(marker);
            }
        });
    }
    function fit() {
        if (!map || !markers.length) return;
        const bounds = new google.maps.LatLngBounds(); markers.forEach(m => bounds.extend(m.getPosition())); map.fitBounds(bounds, 48);
        if (markers.length === 1) map.setZoom(14);
    }
    async function route() {
        const m = data.missions.find(m => m.id === selected);
        if (!m || !fresh(m) || !m.destination || !router) return;
        const generation = ++routeGeneration;
        el('map-notice').textContent = 'Calcul du trajet routier…';
        router.route({origin:{lat:m.position.lat,lng:m.position.lng}, destination:m.destination, travelMode:google.maps.TravelMode.DRIVING}, (result, status) => {
            if (generation !== routeGeneration) return;
            if (status !== 'OK') { directions.setDirections({routes: []}); el('map-notice').textContent = 'Trajet indisponible. Vérifiez les points de départ et d’arrivée ou réessayez.'; return; }
            directions.setDirections(result);
            const leg = result.routes[0].legs[0]; el('map-notice').textContent = `Trajet calculé : ${leg.distance.text} · environ ${leg.duration.text}. Recalculez si le vidangeur se déplace.`;
        });
    }
    window.locationsMapFailed = () => { el('locations-map').innerHTML = '<p class="vz-map-placeholder">La carte n’a pas pu être chargée. La liste des demandes reste disponible.</p>'; el('map-notice').textContent = 'Carte indisponible. Réessayez en rechargeant la page.'; };
    window.gm_authFailure = window.locationsMapFailed;
    window.initLocationsMap = () => {
        map = new google.maps.Map(el('locations-map'), {center:{lat:14.6928,lng:-17.4467}, zoom:12, mapTypeControl:false, streetViewControl:false, fullscreenControl:true, gestureHandling:'cooperative'});
        router = new google.maps.DirectionsService();
        directions = new google.maps.DirectionsRenderer({map, suppressMarkers:true, polylineOptions:{strokeColor:'#2563eb',strokeWeight:5}});
        drawMarkers(); fit(); renderDetail();
    };
    async function refresh() {
        if (busy) return;
        busy = true; el('refresh-locations').disabled = true;
        try {
            const response = await fetch(config.endpoint, {headers:{Accept:'application/json'}, cache:'no-store', signal:AbortSignal.timeout(10000)});
            if (!response.ok) throw new Error();
            const previous = JSON.stringify(data.missions.find(m => m.id === selected));
            data = await response.json();
            const changed = previous !== JSON.stringify(data.missions.find(m => m.id === selected));
            if (changed) { routeGeneration++; directions?.setDirections({routes: []}); }
            el('sync-state').textContent = 'Actualisé à ' + new Date().toLocaleTimeString('fr-FR');
            render();
            if (selected && changed) el('map-notice').textContent = 'Positions actualisées. Vous pouvez recalculer le trajet.';
        } catch (_) { el('sync-state').textContent = 'Actualisation interrompue. Réessayez.'; render(); }
        finally { busy = false; el('refresh-locations').disabled = false; }
    }
    el('refresh-locations').addEventListener('click', refresh);
    el('fit-locations').addEventListener('click', fit);
    setInterval(() => { if (!document.hidden) refresh(); }, 15000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
    render();
})();
