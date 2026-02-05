<!DOCTYPE html>
<html lang="fr">
  <head>
    @include('layouts.head')
    @include('layouts.css')
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
      @include('layouts.header')
      <!-- Page header end-->

        <!-- Page Body Start-->
        <div class="page-body-wrapper horizontal-menu">

          <!-- Page sidebar start-->
          @include('layouts.doctor-sidebar')

          <div class="page-body">
            @yield('main_content')
          </div>

          @include('layouts.footer')
        </div>
    </div>
    @include('layouts.scripts')
  </body>
</html>
