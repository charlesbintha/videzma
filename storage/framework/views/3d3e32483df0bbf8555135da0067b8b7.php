<?php $__env->startSection('title', 'Demande #' . $serviceRequest->id); ?>

<?php $__env->startSection('main_content'); ?>
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Demande de service #<?php echo e($serviceRequest->id); ?></h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.service-requests.index')); ?>">Demandes</a></li>
                    <li class="breadcrumb-item active">#<?php echo e($serviceRequest->id); ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php if(session('success')): ?>
        <div class="alert alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Details de la demande</h5>
                    <?php switch($serviceRequest->status):
                        case ('pending'): ?>
                            <span class="badge bg-warning fs-6">En attente</span>
                            <?php break; ?>
                        <?php case ('assigned'): ?>
                            <span class="badge bg-info fs-6">Assigne</span>
                            <?php break; ?>
                        <?php case ('accepted'): ?>
                            <span class="badge bg-primary fs-6">Accepte</span>
                            <?php break; ?>
                        <?php case ('in_progress'): ?>
                            <span class="badge bg-secondary fs-6">En cours</span>
                            <?php break; ?>
                        <?php case ('completed'): ?>
                            <span class="badge bg-success fs-6">Termine</span>
                            <?php break; ?>
                        <?php default: ?>
                            <span class="badge bg-danger fs-6"><?php echo e(ucfirst($serviceRequest->status)); ?></span>
                    <?php endswitch; ?>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Client</h6>
                            <p class="mb-1"><strong><?php echo e($serviceRequest->client->name ?? '-'); ?></strong></p>
                            <p class="mb-1"><?php echo e($serviceRequest->client->email ?? ''); ?></p>
                            <p class="mb-0"><?php echo e($serviceRequest->client->phone ?? ''); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Vidangeur assigne</h6>
                            <?php if($serviceRequest->driver): ?>
                                <p class="mb-1"><strong><?php echo e($serviceRequest->driver->name); ?></strong></p>
                                <p class="mb-1"><?php echo e($serviceRequest->driver->email); ?></p>
                                <p class="mb-0"><?php echo e($serviceRequest->driver->phone ?? ''); ?></p>
                            <?php else: ?>
                                <p class="text-muted">Non assigne</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Adresse</h6>
                            <p><?php echo e($serviceRequest->address); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Type de fosse</h6>
                            <p><?php echo e(ucfirst(str_replace('_', ' ', $serviceRequest->fosse_type ?? '-'))); ?></p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Volume estime</h6>
                            <p class="fs-5"><?php echo e($serviceRequest->estimated_volume ?? '-'); ?> m³</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Volume reel</h6>
                            <p class="fs-5"><?php echo e($serviceRequest->actual_volume ?? '-'); ?> m³</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Urgence</h6>
                            <?php if($serviceRequest->urgency_level === 'emergency'): ?>
                                <span class="badge bg-danger">Urgence</span>
                            <?php elseif($serviceRequest->urgency_level === 'urgent'): ?>
                                <span class="badge bg-warning">Urgent</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Normal</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Prix</h6>
                            <p class="fs-4 text-primary"><?php echo e(number_format($serviceRequest->price_amount ?? 0, 0, ',', ' ')); ?> FCFA</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Mode de paiement</h6>
                            <p><?php echo e(ucfirst(str_replace('_', ' ', $serviceRequest->payment_method ?? '-'))); ?></p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted mb-2">Statut paiement</h6>
                            <?php if($serviceRequest->payment_status === 'paid'): ?>
                                <span class="badge bg-success">Paye</span>
                            <?php else: ?>
                                <span class="badge bg-warning">En attente</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if($serviceRequest->client_notes): ?>
                        <div class="mb-4">
                            <h6 class="text-muted mb-2">Notes du client</h6>
                            <p class="bg-light p-3 rounded"><?php echo e($serviceRequest->client_notes); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if($serviceRequest->rating): ?>
                        <div class="mb-4">
                            <h6 class="text-muted mb-2">Evaluation</h6>
                            <p>
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fa fa-star <?php echo e($i <= $serviceRequest->rating ? 'text-warning' : 'text-muted'); ?>"></i>
                                <?php endfor; ?>
                                <span class="ms-2">(<?php echo e($serviceRequest->rating); ?>/5)</span>
                            </p>
                            <?php if($serviceRequest->rating_comment): ?>
                                <p class="bg-light p-3 rounded"><?php echo e($serviceRequest->rating_comment); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Historique</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Demande creee</span>
                            <span><?php echo e($serviceRequest->requested_at?->format('d/m/Y H:i') ?? $serviceRequest->created_at->format('d/m/Y H:i')); ?></span>
                        </li>
                        <?php if($serviceRequest->assigned_at): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Vidangeur assigne</span>
                            <span><?php echo e($serviceRequest->assigned_at->format('d/m/Y H:i')); ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if($serviceRequest->accepted_at): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Demande acceptee</span>
                            <span><?php echo e($serviceRequest->accepted_at->format('d/m/Y H:i')); ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if($serviceRequest->rejected_at): ?>
                        <li class="list-group-item d-flex justify-content-between text-danger">
                            <span>Demande rejetee</span>
                            <span><?php echo e($serviceRequest->rejected_at->format('d/m/Y H:i')); ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if($serviceRequest->started_at): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Intervention demarree</span>
                            <span><?php echo e($serviceRequest->started_at->format('d/m/Y H:i')); ?></span>
                        </li>
                        <?php endif; ?>
                        <?php if($serviceRequest->completed_at): ?>
                        <li class="list-group-item d-flex justify-content-between text-success">
                            <span>Intervention terminee</span>
                            <span><?php echo e($serviceRequest->completed_at->format('d/m/Y H:i')); ?></span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <?php if($serviceRequest->status === 'pending' || $serviceRequest->status === 'assigned'): ?>
            <div class="card">
                <div class="card-header">
                    <h5>Assigner un vidangeur</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.service-requests.assign', $serviceRequest)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label class="form-label" for="driver_id">Vidangeur</label>
                            <select class="form-select" id="driver_id" name="driver_id" required>
                                <option value="">Selectionner...</option>
                                <?php $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($driver->id); ?>" <?php if($serviceRequest->driver_id == $driver->id): echo 'selected'; endif; ?>>
                                        <?php echo e($driver->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-user-check me-1"></i> Assigner
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5>Modifier le statut</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.service-requests.update', $serviceRequest)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>
                        <div class="mb-3">
                            <label class="form-label" for="status">Statut</label>
                            <select class="form-select" id="status" name="status">
                                <option value="pending" <?php if($serviceRequest->status === 'pending'): echo 'selected'; endif; ?>>En attente</option>
                                <option value="assigned" <?php if($serviceRequest->status === 'assigned'): echo 'selected'; endif; ?>>Assigne</option>
                                <option value="accepted" <?php if($serviceRequest->status === 'accepted'): echo 'selected'; endif; ?>>Accepte</option>
                                <option value="in_progress" <?php if($serviceRequest->status === 'in_progress'): echo 'selected'; endif; ?>>En cours</option>
                                <option value="completed" <?php if($serviceRequest->status === 'completed'): echo 'selected'; endif; ?>>Termine</option>
                                <option value="cancelled" <?php if($serviceRequest->status === 'cancelled'): echo 'selected'; endif; ?>>Annule</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="admin_notes">Notes admin</label>
                            <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3"><?php echo e(old('admin_notes', $serviceRequest->admin_notes)); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-secondary w-100">
                            <i class="fa fa-save me-1"></i> Mettre a jour
                        </button>
                    </form>
                </div>
            </div>

            <?php if($serviceRequest->intervention): ?>
            <div class="card">
                <div class="card-header">
                    <h5>Intervention associee</h5>
                </div>
                <div class="card-body">
                    <p><strong>Statut:</strong> <?php echo e(ucfirst($serviceRequest->intervention->status)); ?></p>
                    <p><strong>Prevue:</strong> <?php echo e($serviceRequest->intervention->scheduled_at?->format('d/m/Y H:i') ?? '-'); ?></p>
                    <a href="<?php echo e(route('admin.interventions.show', $serviceRequest->intervention)); ?>" class="btn btn-outline-primary w-100">
                        Voir l'intervention
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/u528935801/domains/emmaluxury.store/public_html/videzma/resources/views/admin/service-requests/show.blade.php ENDPATH**/ ?>