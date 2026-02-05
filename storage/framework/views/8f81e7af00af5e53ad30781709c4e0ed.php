<!DOCTYPE html>
<html lang="en" <?php if(Route::currentRouteName() == 'rtl_layout'): ?> dir="rtl" <?php endif; ?>
<?php if(Route::currentRouteName() === 'layout_dark'): ?> data-theme="dark" <?php endif; ?>>
  <head>
    <?php echo $__env->make('layouts.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('layouts.css', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  </head>
  <?php switch(Route::currentRouteName()):
   
    case ('box_layout'): ?>
        <body class="box-layout">
        <?php break; ?>

    <?php case ('rtl_layout'): ?>
        <body class="rtl">
        <?php break; ?>

    <?php case ('layout_dark'): ?>
        <body class="dark-only">
        <?php break; ?>

    <?php default: ?>
         <body>
  <?php endswitch; ?>
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
          <?php echo $__env->make('layouts.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

          <div class="page-body">
            <?php echo $__env->yieldContent('main_content'); ?>
          </div>
          
          <?php echo $__env->make('layouts.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
    <?php echo $__env->make('layouts.scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  </body>
</html><?php /**PATH C:\ORACLE\wadnou\resources\views/layouts/master.blade.php ENDPATH**/ ?>