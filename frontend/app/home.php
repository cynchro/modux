<?php
require_once __DIR__.'/config/AuthRouter.php';

$router = new AuthRouter();
$router->checkAccess('pagina_home');
?>

<!DOCTYPE html>
<html lang="en">

<head>
<?php include('layouts/head.php'); ?>
</head>

<body>
  <div class="sidebar sidebar-dark sidebar-fixed border-end" id="sidebar">
    <div class="sidebar-header border-bottom">
      <div class="sidebar-brand">
      <?php //include('layouts/logo.php'); ?>
      </div>
     </div>
    <?php include('layouts/menu_vertical.php'); ?>
    <div class="sidebar-footer border-top d-none d-md-flex">
      <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
    </div>
  </div>
  <div class="wrapper d-flex flex-column min-vh-100">
    <header class="header header-sticky p-0 mb-4">
      <div class="container-fluid border-bottom px-4">
        <button class="header-toggler" type="button" onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()" style="margin-inline-start: -14px;">
          <svg class="icon icon-lg">
            <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-menu"></use>
          </svg>
        </button>
        <?php include('layouts/menu_horizontal.php'); ?>
      </div>
      <?php //include('layouts/breadcrumb.php'); ?>
    </header>
    <div class="body flex-grow-1">
      <div class="container-lg px-4">
        <div class="row g-4 mb-4">

          <!-- CONTENIDO -->
          HOME...

        </div>
      </div>
      <?php //include('layouts/footer.php'); ?>
    </div>
    <!-- CoreUI and necessary plugins-->
    <script src="vendor/@coreui/coreui/js/coreui.bundle.min.js"></script>
    <script src="vendor/simplebar/js/simplebar.min.js"></script>
    <script>
      const header = document.querySelector('header.header');

      document.addEventListener('scroll', () => {
        if (header) {
          header.classList.toggle('shadow-sm', document.documentElement.scrollTop > 0);
        }
      });
    </script>
    <!-- Plugins and scripts required by this view-->
    <script src="vendor/chart.js/js/chart.umd.js"></script>
    <script src="vendor/@coreui/chartjs/js/coreui-chartjs.js"></script>
    <script src="vendor/@coreui/utils/js/index.js"></script>
    <script src="js/paginas/general.js"></script>

</body>

</html>