<?php $__env->startSection('title', 'Consultation #' . $consultation->id); ?>

<?php $__env->startSection('main_content'); ?>
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Consultation #<?php echo e($consultation->id); ?></h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('doctor.dashboard')); ?>">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo e(route('doctor.consultations.index')); ?>">Consultations</a></li>
                    <li class="breadcrumb-item active">#<?php echo e($consultation->id); ?></li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php if(session('status')): ?>
        <div class="alert alert-success"><?php echo e(session('status')); ?></div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <p class="mb-0"><?php echo e($error); ?></p>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Consultation details -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Details de la consultation</h5>
                    <?php switch($consultation->status):
                        case ('pending'): ?>
                        <?php case ('assigned'): ?>
                            <span class="badge bg-warning">En attente</span>
                            <?php break; ?>
                        <?php case ('accepted'): ?>
                            <span class="badge bg-success">Accepte</span>
                            <?php break; ?>
                        <?php case ('rejected'): ?>
                            <span class="badge bg-danger">Rejete</span>
                            <?php break; ?>
                        <?php case ('closed'): ?>
                            <span class="badge bg-secondary">Cloture</span>
                            <?php break; ?>
                        <?php case ('expired'): ?>
                            <span class="badge bg-dark">Expire</span>
                            <?php break; ?>
                    <?php endswitch; ?>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Motif:</strong>
                            <p><?php echo e($consultation->reason); ?></p>
                        </div>
                        <div class="col-md-6">
                            <strong>Notes:</strong>
                            <p><?php echo e($consultation->notes ?? '-'); ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Demande le:</strong>
                            <p><?php echo e($consultation->requested_at?->format('d/m/Y H:i') ?? '-'); ?></p>
                        </div>
                        <div class="col-md-4">
                            <strong>Echeance SLA:</strong>
                            <p class="<?php echo e($consultation->sla_due_at && $consultation->sla_due_at->isPast() ? 'text-danger' : ''); ?>">
                                <?php echo e($consultation->sla_due_at?->format('d/m/Y H:i') ?? '-'); ?>

                            </p>
                        </div>
                        <div class="col-md-4">
                            <?php if($consultation->accepted_at): ?>
                                <strong>Accepte le:</strong>
                                <p><?php echo e($consultation->accepted_at->format('d/m/Y H:i')); ?></p>
                            <?php elseif($consultation->rejected_at): ?>
                                <strong>Rejete le:</strong>
                                <p><?php echo e($consultation->rejected_at->format('d/m/Y H:i')); ?></p>
                            <?php elseif($consultation->closed_at): ?>
                                <strong>Cloture le:</strong>
                                <p><?php echo e($consultation->closed_at->format('d/m/Y H:i')); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Patient info -->
                    <h6 class="mt-4">Patient</h6>
                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Nom:</strong>
                            <p><?php echo e($consultation->patient?->name ?? '-'); ?></p>
                        </div>
                        <div class="col-md-4">
                            <strong>Telephone:</strong>
                            <p><?php echo e($consultation->patient?->phone ?? '-'); ?></p>
                        </div>
                        <div class="col-md-4">
                            <strong>Email:</strong>
                            <p><?php echo e($consultation->patient?->email ?? '-'); ?></p>
                        </div>
                    </div>

                    <!-- Location info -->
                    <?php if($consultation->location): ?>
                        <h6 class="mt-4">Localisation</h6>
                        <hr>
                        <p><?php echo e($consultation->location->address ?? 'Coordonnees: ' . $consultation->location->latitude . ', ' . $consultation->location->longitude); ?></p>
                    <?php endif; ?>

                    <!-- Appointment info -->
                    <?php if($consultation->appointment): ?>
                        <h6 class="mt-4">Rendez-vous</h6>
                        <hr>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Planifie pour:</strong>
                                <p><?php echo e($consultation->appointment->scheduled_at->format('d/m/Y H:i')); ?></p>
                            </div>
                            <div class="col-md-4">
                                <strong>Statut:</strong>
                                <p><?php echo e($consultation->appointment->status); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Comments section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Commentaires</h5>
                </div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $consultation->comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between">
                                <strong><?php echo e($comment->author?->name ?? 'Inconnu'); ?></strong>
                                <small class="text-muted"><?php echo e($comment->created_at->format('d/m/Y H:i')); ?></small>
                            </div>
                            <p class="mb-0 mt-2"><?php echo e($comment->content); ?></p>
                            <?php if($comment->is_internal): ?>
                                <small class="text-warning"><i class="fa fa-lock"></i> Note interne</small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-muted">Aucun commentaire.</p>
                    <?php endif; ?>

                    <!-- Add comment form -->
                    <form action="<?php echo e(route('doctor.consultations.comments.store', $consultation)); ?>" method="POST" class="mt-4">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label class="form-label">Ajouter un commentaire</label>
                            <textarea name="content" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" name="is_internal" value="1" class="form-check-input" id="is_internal">
                            <label class="form-check-label" for="is_internal">Note interne (non visible par le patient)</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Actions sidebar -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5>Actions</h5>
                </div>
                <div class="card-body">
                    <?php if(in_array($consultation->status, ['pending', 'assigned'])): ?>
                        <form action="<?php echo e(route('doctor.consultations.accept', $consultation)); ?>" method="POST" class="mb-3">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fa fa-check"></i> Accepter la demande
                            </button>
                        </form>

                        <form action="<?php echo e(route('doctor.consultations.reject', $consultation)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <div class="mb-2">
                                <textarea name="reason" class="form-control" rows="2" placeholder="Raison du rejet (optionnel)"></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fa fa-times"></i> Rejeter la demande
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if($consultation->status === 'accepted'): ?>
                        <?php if(!$consultation->appointment): ?>
                            <a href="<?php echo e(route('doctor.appointments.create')); ?>?consultation_id=<?php echo e($consultation->id); ?>" class="btn btn-primary w-100 mb-3">
                                <i class="fa fa-calendar-plus"></i> Planifier un RDV
                            </a>
                        <?php endif; ?>

                        <form action="<?php echo e(route('doctor.consultations.close', $consultation)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <div class="mb-2">
                                <textarea name="notes" class="form-control" rows="2" placeholder="Notes de cloture (optionnel)"></textarea>
                            </div>
                            <button type="submit" class="btn btn-secondary w-100">
                                <i class="fa fa-archive"></i> Cloturer la consultation
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if(in_array($consultation->status, ['closed', 'rejected', 'expired'])): ?>
                        <p class="text-muted text-center">Cette consultation est terminee.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.doctor', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ORACLE\wadnou\resources\views/doctor/consultations/show.blade.php ENDPATH**/ ?>