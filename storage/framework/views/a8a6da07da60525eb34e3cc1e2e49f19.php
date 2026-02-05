<!DOCTYPE html>
<html lang="fr">
  <head>
    <?php echo $__env->make('layouts.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('layouts.css', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  </head>
  <body>
    <!-- loader starts-->
    <div class="loader-wrapper">
      <div class="loader-index"> <span></span></div>
      <svg>
        <defs></defs>
        <filter id="goo">
          <fegaussianblur in="SourceGraphic" stddeviation="11" result="blur"></fegaussianblur>
          <fecolormatrix in="blur" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 19 -9" result="goo"> </fecolormatrix>
        </filter>
      </svg>
    </div>
    <!-- loader ends-->

    <!-- tap on top starts-->
    <div class="tap-top"><i data-feather="chevrons-up"></i></div>
    <!-- tap on tap ends-->

     <!-- page-wrapper Start-->
    <div class="page-wrapper compact-wrapper" id="pageWrapper">

      <!-- Page header start -->
      <?php echo $__env->make('layouts.header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      <!-- Page header end-->

        <!-- Page Body Start-->
        <div class="page-body-wrapper horizontal-menu">

          <!-- Page sidebar start-->
          <?php echo $__env->make('layouts.doctor-sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

          <div class="page-body">
            <?php echo $__env->yieldContent('main_content'); ?>
          </div>

          <?php echo $__env->make('layouts.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
    <?php echo $__env->make('layouts.scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  </body>
</html>
<?php /**PATH C:\ORACLE\wadnou\resources\views/layouts/doctor.blade.php ENDPATH**/ ?>