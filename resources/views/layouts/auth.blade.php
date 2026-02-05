<!DOCTYPE html>
<html lang="en">
  <head>
    @include('layouts.head')
    @include('layouts.css')
  </head>
  <body>
    <div class="container-fluid p-0">
      @yield('content')
    </div>
    @include('layouts.scripts')
  </body>
</html>
