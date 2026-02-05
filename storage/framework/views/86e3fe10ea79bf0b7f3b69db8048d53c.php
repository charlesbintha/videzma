<?php $__env->startSection('title', 'Localisation'); ?>

<?php $__env->startSection('css'); ?>
    <style>
        .wadnou-map {
            height: 520px;
            border-radius: 12px;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('main_content'); ?>
    <div class="container-fluid">
        <div class="page-title">
            <div class="row">
                <div class="col-sm-6">
                    <h3>Localisation & navigation</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">
                                <svg class="stroke-icon">
                                    <use href="<?php echo e(asset('assets/svg/icon-sprite.svg#stroke-home')); ?>"></use>
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
                        <h5>Patients suivis</h5>
                    </div>
                    <div class="card-body">
                        <form method="get" class="row g-2 align-items-end">
                            <div class="col-12">
                                <label class="form-label" for="doctor_id">Filtrer par medecin</label>
                                <select class="form-select" id="doctor_id" name="doctor_id">
                                    <option value="">Tous les medecins</option>
                                    <?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($doctor->id); ?>" <?php if((string) $selectedDoctor === (string) $doctor->id): echo 'selected'; endif; ?>>
                                            <?php echo e($doctor->name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="search">Recherche patient</label>
                                <input class="form-control" id="search" name="search" value="<?php echo e($search); ?>" placeholder="Nom ou email">
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
                                        <th>Patient</th>
                                        <th>Position</th>
                                        <th>Capture</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <?php ($location = $patient->latestLocation); ?>
                                        <tr>
                                            <td>
                                                <div class="fw-semibold"><?php echo e($patient->name); ?></div>
                                                <small class="text-muted"><?php echo e($patient->email); ?></small>
                                            </td>
                                            <td>
                                                <?php if($location): ?>
                                                    <?php echo e($location->latitude); ?>, <?php echo e($location->longitude); ?>

                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($location): ?>
                                                    <?php echo e(($location->captured_at ?? $location->created_at)?->format('Y-m-d H:i')); ?>

                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <tr>
                                            <td colspan="3" class="text-center">Aucun patient.</td>
                                        </tr>
                                    <?php endif; ?>
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
                        <?php if(!$mapsKey): ?>
                            <div class="alert alert-warning mb-0">
                                Cle Google Maps manquante. Renseignez <code>GOOGLE_MAPS_API_KEY</code> dans le .env.
                            </div>
                        <?php else: ?>
                            <div id="wadnou-map" class="wadnou-map"></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <?php if($mapsKey): ?>
        <script>
            const wadnouPatients = <?php echo json_encode($mapPatients, 15, 512) ?>;
            const wadnouDoctor = <?php echo json_encode($doctorMap, 15, 512) ?>;
            const wadnouMapId = <?php echo json_encode($mapId, 15, 512) ?>;

            function escapeHtml(value) {
                const div = document.createElement('div');
                div.appendChild(document.createTextNode(value ?? ''));
                return div.innerHTML;
            }

            window.initWadnouMap = function () {
                const hasMarkers = Boolean(wadnouDoctor) || wadnouPatients.length > 0;
                const fallbackCenter = { lat: 0, lng: 0 };
                const first = wadnouDoctor || wadnouPatients[0];
                const center = first ? { lat: first.latitude, lng: first.longitude } : fallbackCenter;
                const mapOptions = {
                    center,
                    zoom: hasMarkers ? 12 : 2,
                };

                if (wadnouMapId) {
                    mapOptions.mapId = wadnouMapId;
                }

                const map = new google.maps.Map(document.getElementById('wadnou-map'), mapOptions);

                if (!hasMarkers) {
                    return;
                }

                let directionsRenderer = null;
                let directionsService = null;

                if (wadnouDoctor) {
                    new google.maps.Marker({
                        position: { lat: wadnouDoctor.latitude, lng: wadnouDoctor.longitude },
                        map,
                        title: wadnouDoctor.name,
                        label: 'D',
                    });

                    directionsRenderer = new google.maps.DirectionsRenderer({ map });
                    directionsService = new google.maps.DirectionsService();
                }

                wadnouPatients.forEach((patient) => {
                    const marker = new google.maps.Marker({
                        position: { lat: patient.latitude, lng: patient.longitude },
                        map,
                        title: patient.name,
                    });

                    const info = new google.maps.InfoWindow({
                        content: `<strong>${escapeHtml(patient.name)}</strong><br>${escapeHtml(patient.address || '')}<br>${escapeHtml(patient.captured_at || '')}`,
                    });

                    marker.addListener('click', () => {
                        info.open(map, marker);

                        if (wadnouDoctor && directionsService && directionsRenderer) {
                            directionsService.route({
                                origin: { lat: wadnouDoctor.latitude, lng: wadnouDoctor.longitude },
                                destination: { lat: patient.latitude, lng: patient.longitude },
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
        <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo e($mapsKey); ?>&callback=initWadnouMap&loading=async<?php echo e($mapId ? '&map_ids=' . $mapId : ''); ?>" async defer></script>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ORACLE\wadnou\resources\views/admin/locations/index.blade.php ENDPATH**/ ?>