<?php $__env->startSection('title', 'Vidangeur - ' . $driver->name); ?>

<?php $__env->startSection('main_content'); ?>
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Details du vidangeur</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.drivers.index')); ?>">Vidangeurs</a></li>
                    <li class="breadcrumb-item active"><?php echo e($driver->name); ?></li>
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
                    <h5>Informations personnelles</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <span class="text-white fs-1"><?php echo e(strtoupper(substr($driver->name, 0, 1))); ?></span>
                            </div>
                        </div>
                        <h5 class="mb-1"><?php echo e($driver->name); ?></h5>
                        <p class="text-muted mb-0"><?php echo e($driver->email); ?></p>
                    </div>

                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Telephone</span>
                            <strong><?php echo e($driver->phone ?? '-'); ?></strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Statut compte</span>
                            <?php if($driver->status === 'active'): ?>
                                <span class="badge bg-success">Actif</span>
                            <?php elseif($driver->status === 'inactive'): ?>
                                <span class="badge bg-secondary">Inactif</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Suspendu</span>
                            <?php endif; ?>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span class="text-muted">Inscription</span>
                            <strong><?php echo e($driver->created_at->format('d/m/Y')); ?></strong>
                        </li>
                    </ul>

                    <div class="mt-4 d-flex gap-2">
                        <a href="<?php echo e(route('admin.drivers.edit', $driver)); ?>" class="btn btn-primary flex-fill">
                            <i class="fa fa-edit me-1"></i> Modifier
                        </a>
                        <form action="<?php echo e(route('admin.drivers.destroy', $driver)); ?>" method="POST" class="flex-fill" onsubmit="return confirm('Supprimer ce vidangeur ?')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fa fa-trash me-1"></i> Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <?php if($driver->driverProfile): ?>
            <div class="card">
                <div class="card-header">
                    <h5>Profil vidangeur</h5>
                </div>
                <div class="card-body">
                    <?php ($profile = $driver->driverProfile); ?>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Numero de permis:</strong> <?php echo e($profile->license_number ?? '-'); ?></p>
                            <p><strong>Type de vehicule:</strong> <?php echo e(ucfirst(str_replace('_', ' ', $profile->vehicle_type ?? '-'))); ?></p>
                            <p><strong>Plaque:</strong> <?php echo e($profile->vehicle_plate ?? '-'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Capacite citerne:</strong> <?php echo e($profile->tank_capacity ? $profile->tank_capacity . ' m³' : '-'); ?></p>
                            <p><strong>Zones couvertes:</strong> <?php echo e($profile->zone_coverage ?? '-'); ?></p>
                            <p><strong>Statut verification:</strong>
                                <?php if($profile->verification_status === 'approved'): ?>
                                    <span class="badge bg-success">Approuve</span>
                                <?php elseif($profile->verification_status === 'rejected'): ?>
                                    <span class="badge bg-danger">Rejete</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">En attente</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <?php if($profile->bio): ?>
                        <p><strong>Bio:</strong> <?php echo e($profile->bio); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Documents</h5>
                </div>
                <div class="card-body">
                    <?php if($driver->driverDocuments && $driver->driverDocuments->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $driver->driverDocuments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $document): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e(ucfirst(str_replace('_', ' ', $document->type))); ?></td>
                                            <td>
                                                <?php if($document->status === 'approved'): ?>
                                                    <span class="badge bg-success">Approuve</span>
                                                <?php elseif($document->status === 'rejected'): ?>
                                                    <span class="badge bg-danger">Rejete</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">En attente</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($document->created_at->format('d/m/Y')); ?></td>
                                            <td>
                                                <a href="<?php echo e(route('admin.driver-documents.show', $document)); ?>" class="btn btn-sm btn-info">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <a href="<?php echo e(route('admin.driver-documents.download', $document)); ?>" class="btn btn-sm btn-secondary">
                                                    <i class="fa fa-download"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">Aucun document soumis.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u528935801/domains/emmaluxury.store/public_html/videzma/resources/views/admin/drivers/show.blade.php ENDPATH**/ ?>