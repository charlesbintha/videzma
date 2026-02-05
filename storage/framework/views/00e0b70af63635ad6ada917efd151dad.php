<?php $__env->startSection('title', 'Tableau de bord - Medecin'); ?>

<?php $__env->startSection('main_content'); ?>
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Tableau de bord</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('doctor.dashboard')); ?>">Accueil</a></li>
                    <li class="breadcrumb-item active">Tableau de bord</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <!-- Metrics -->
    <div class="row">
        <div class="col-xl-3 col-sm-6">
            <div class="card bg-warning">
                <div class="card-body">
                    <h6 class="mb-1 text-white">Demandes en attente</h6>
                    <h3 class="mb-0 text-white"><?php echo e($metrics['consultations_pending']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card bg-success">
                <div class="card-body">
                    <h6 class="mb-1 text-white">Consultations en cours</h6>
                    <h3 class="mb-0 text-white"><?php echo e($metrics['consultations_accepted']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card bg-primary">
                <div class="card-body">
                    <h6 class="mb-1 text-white">RDV aujourd'hui</h6>
                    <h3 class="mb-0 text-white"><?php echo e($metrics['appointments_today']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card bg-info">
                <div class="card-body">
                    <h6 class="mb-1 text-white">RDV a venir</h6>
                    <h3 class="mb-0 text-white"><?php echo e($metrics['appointments_upcoming']); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Pending consultations -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Demandes en attente</h5>
                    <a href="<?php echo e(route('doctor.consultations.pending')); ?>" class="btn btn-sm btn-outline-primary">Voir tout</a>
                </div>
                <div class="card-body">
                    <?php if($pendingConsultations->isEmpty()): ?>
                        <p class="text-muted">Aucune demande en attente.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Patient</th>
                                        <th>Motif</th>
                                        <th>Echeance SLA</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $pendingConsultations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $consultation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($consultation->patient?->name ?? '-'); ?></td>
                                            <td><?php echo e(Str::limit($consultation->reason, 30)); ?></td>
                                            <td>
                                                <?php if($consultation->sla_due_at): ?>
                                                    <span class="<?php echo e($consultation->sla_due_at->isPast() ? 'text-danger' : ($consultation->sla_due_at->diffInMinutes(now()) < 30 ? 'text-warning' : '')); ?>">
                                                        <?php echo e($consultation->sla_due_at->format('d/m H:i')); ?>

                                                    </span>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo e(route('doctor.consultations.show', $consultation)); ?>" class="btn btn-sm btn-primary">Voir</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Today's appointments -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Rendez-vous aujourd'hui</h5>
                    <a href="<?php echo e(route('doctor.appointments.index')); ?>" class="btn btn-sm btn-outline-primary">Voir tout</a>
                </div>
                <div class="card-body">
                    <?php if($todayAppointments->isEmpty()): ?>
                        <p class="text-muted">Aucun rendez-vous aujourd'hui.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Heure</th>
                                        <th>Patient</th>
                                        <th>Statut</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $todayAppointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $appointment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($appointment->scheduled_at->format('H:i')); ?></td>
                                            <td><?php echo e($appointment->consultationRequest?->patient?->name ?? '-'); ?></td>
                                            <td>
                                                <?php switch($appointment->status):
                                                    case ('scheduled'): ?>
                                                        <span class="badge bg-primary">Planifie</span>
                                                        <?php break; ?>
                                                    <?php case ('in_progress'): ?>
                                                        <span class="badge bg-warning">En cours</span>
                                                        <?php break; ?>
                                                    <?php case ('completed'): ?>
                                                        <span class="badge bg-success">Termine</span>
                                                        <?php break; ?>
                                                    <?php case ('canceled'): ?>
                                                        <span class="badge bg-danger">Annule</span>
                                                        <?php break; ?>
                                                <?php endswitch; ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo e(route('doctor.appointments.show', $appointment)); ?>" class="btn btn-sm btn-primary">Voir</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.doctor', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ORACLE\wadnou\resources\views/doctor/dashboard.blade.php ENDPATH**/ ?>