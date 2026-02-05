<?php $__env->startSection('title', 'SLA'); ?>

<?php $__env->startSection('main_content'); ?>
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>SLA</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Accueil</a></li>
                    <li class="breadcrumb-item active">SLA</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Configurations SLA</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Actif</th>
                                    <th>Validation</th>
                                    <th>Expiration</th>
                                    <th>Annulation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $configs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $config): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($config->name); ?></td>
                                        <td><?php echo e($config->is_active ? 'Oui' : 'Non'); ?></td>
                                        <td><?php echo e($config->validation_minutes); ?>m</td>
                                        <td><?php echo e($config->expiration_minutes); ?>m</td>
                                        <td><?php echo e($config->cancellation_minutes); ?>m</td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5">Aucune configuration SLA.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo e($configs->links()); ?>

                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5>Regles SLA</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Configuration</th>
                                    <th>Nom</th>
                                    <th>Actif</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $rules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e(optional($rule->config)->name ?? '-'); ?></td>
                                        <td><?php echo e($rule->name); ?></td>
                                        <td><?php echo e($rule->is_active ? 'Oui' : 'Non'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="3">Aucune regle SLA.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo e($rules->links()); ?>

                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ORACLE\wadnou\resources\views/admin/sla/index.blade.php ENDPATH**/ ?>