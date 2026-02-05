<?php $__env->startSection('title', 'Utilisateurs'); ?>

<?php $__env->startSection('main_content'); ?>
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Utilisateurs</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('admin.dashboard')); ?>">Accueil</a></li>
                    <li class="breadcrumb-item active">Utilisateurs</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5>Liste des utilisateurs</h5>
            <span>Filtrer par role, statut, ou rechercher par nom, email, telephone.</span>
        </div>
        <div class="card-body">
            <form class="row g-3 mb-3" method="get">
                <div class="col-md-5">
                    <input class="form-control" type="text" name="q" value="<?php echo e(request('q')); ?>" placeholder="Rechercher nom, email, telephone">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="role">
                        <option value="">Tous les roles</option>
                        <option value="patient" <?php if(request('role') === 'patient'): echo 'selected'; endif; ?>>Patient</option>
                        <option value="doctor" <?php if(request('role') === 'doctor'): echo 'selected'; endif; ?>>Medecin</option>
                        <option value="admin" <?php if(request('role') === 'admin'): echo 'selected'; endif; ?>>Admin</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">Tous les statuts</option>
                        <option value="pending" <?php if(request('status') === 'pending'): echo 'selected'; endif; ?>>En attente</option>
                        <option value="active" <?php if(request('status') === 'active'): echo 'selected'; endif; ?>>Actif</option>
                        <option value="suspended" <?php if(request('status') === 'suspended'): echo 'selected'; endif; ?>>Suspendu</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary w-100" type="submit">OK</button>
                </div>
            </form>
            <?php ($roleLabels = ['patient' => 'Patient', 'doctor' => 'Medecin', 'admin' => 'Admin']); ?>
            <?php ($statusLabels = ['pending' => 'En attente', 'active' => 'Actif', 'suspended' => 'Suspendu']); ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Statut</th>
                            <th>Telephone</th>
                            <th>Langue</th>
                            <th>Cree le</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($user->name); ?></td>
                                <td><?php echo e($user->email); ?></td>
                                <td><?php echo e($roleLabels[$user->role] ?? $user->role); ?></td>
                                <td><?php echo e($statusLabels[$user->status] ?? $user->status); ?></td>
                                <td><?php echo e($user->phone ?? '-'); ?></td>
                                <td><?php echo e($user->locale); ?></td>
                                <td><?php echo e($user->created_at->format('Y-m-d')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7">Aucun utilisateur trouve.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php echo e($users->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\ORACLE\wadnou\resources\views/admin/users/index.blade.php ENDPATH**/ ?>