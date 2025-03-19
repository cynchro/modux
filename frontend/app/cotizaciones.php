<?php

require_once __DIR__.'/config/AuthRouter.php';

$router = new AuthRouter();
$menu = $router->checkAccess('pagina_sucursales');

$seccion = 'Presupuestos';
$tipo = 'Grilla';

?>

<!DOCTYPE html><!--
* CoreUI - Free Bootstrap Admin Template
* @version v5.1.1
* @link https://coreui.io/product/free-bootstrap-admin-template/
* Copyright (c) 2024 creativeLabs Łukasz Holeczek
* Licensed under MIT (https://github.com/coreui/coreui-free-bootstrap-admin-template/blob/main/LICENSE)
-->
<html lang="en">

<head>
  <base href="./">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
  <meta name="description" content="CoreUI - Open Source Bootstrap Admin Template">
  <meta name="author" content="Łukasz Holeczek">
  <meta name="keyword" content="Bootstrap,Admin,Template,Open,Source,jQuery,CSS,HTML,RWD,Dashboard">
  <title><?= $_ENV['APP_NAME'];?></title>
  <link rel="apple-touch-icon" sizes="57x57" href="assets/favicon/apple-icon-57x57.png">
  <link rel="apple-touch-icon" sizes="60x60" href="assets/favicon/apple-icon-60x60.png">
  <link rel="apple-touch-icon" sizes="72x72" href="assets/favicon/apple-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="76x76" href="assets/favicon/apple-icon-76x76.png">
  <link rel="apple-touch-icon" sizes="114x114" href="assets/favicon/apple-icon-114x114.png">
  <link rel="apple-touch-icon" sizes="120x120" href="assets/favicon/apple-icon-120x120.png">
  <link rel="apple-touch-icon" sizes="144x144" href="assets/favicon/apple-icon-144x144.png">
  <link rel="apple-touch-icon" sizes="152x152" href="assets/favicon/apple-icon-152x152.png">
  <link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-icon-180x180.png">
  <link rel="icon" type="image/png" sizes="192x192" href="assets/favicon/android-icon-192x192.png">
  <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="assets/favicon/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
  <link rel="manifest" href="assets/favicon/manifest.json">
  <meta name="msapplication-TileColor" content="#ffffff">
  <meta name="msapplication-TileImage" content="assets/favicon/ms-icon-144x144.png">
  <meta name="theme-color" content="#ffffff">
  <!-- Vendors styles-->
  <link rel="stylesheet" href="vendor/simplebar/css/simplebar.css">
  <link rel="stylesheet" href="css/vendors/simplebar.css">
  <!-- Main styles for this application-->
  <link href="css/style.css" rel="stylesheet">
  <!-- We use those styles to show code examples, you should remove them in your application.-->
  <link href="css/examples.css" rel="stylesheet">
  <script src="js/config.js"></script>
  <script src="js/color-modes.js"></script>
  <link href="vendor/@coreui/chartjs/css/coreui-chartjs.css" rel="stylesheet">
  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

</head>

<body>
  <div class="sidebar sidebar-dark sidebar-fixed border-end" id="sidebar">
    <div class="sidebar-header border-bottom">
      <div class="sidebar-brand">
      <?php include('layouts/logo.php'); ?>
      </div>
      <button class="btn-close d-lg-none" type="button" data-coreui-dismiss="offcanvas" data-coreui-theme="dark" aria-label="Close" onclick="coreui.Sidebar.getInstance(document.querySelector(&quot;#sidebar&quot;)).toggle()"></button>
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
      <?php include('layouts/breadcrumb.php'); ?>
    </header>
    <div class="body flex-grow-1">
      <div class="container-lg px-4">
        <div class="row g-4 mb-4">

          <!-- COMIENZO CONTENIDO -->

          <div class="container mt-5">
          <div class="card mb-5">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Filtros</h5>
              </div>
              <div id="filtrosCollapse" class="collapse show">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex gap-3">
                      <!-- Filtro Cliente -->
                      <div>
                        <label for="clienteFiltro" class="form-label">Cliente</label>
                        <input type="text" id="clienteFiltro" class="form-control" placeholder="Ingrese el Cliente">
                      </div>
                      <!-- Filtro Fecha Desde -->
                      <div>
                        <label for="fechaDesdeFiltro" class="form-label">Desde</label>
                        <input type="date" id="fechaDesdeFiltro" class="form-control" placeholder="01/01/2020">
                      </div>
                      <!-- Filtro Domicilio -->
                      <div>
                        <label for="fechaHastaFiltro" class="form-label">Hasta</label>
                        <input type="date" id="fechaHastaFiltro" class="form-control" placeholder="">
                      </div>
                      <div>
                        <label for="numeroFiltro" class="form-label">Numero</label>
                        <input type="numeric" id="numeroFiltro" class="form-control" placeholder="Numero de Presupuesto">
                      </div>
                    </div>
                    <div class="d-flex gap-2 align-items-center mt-3">
                      <button id="filtrarBtn" class="btn btn-primary">Filtrar</button>
                      <!-- <button id="limpiarFiltrosBtn" class="btn btn-secondary">Limpiar filtros</button>-->
                    </div>
                  </div>
                </div>
              </div>
            </div>


            <!-- Card sin botón de cierre -->
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">Presupuestos</h5>
                <a class="btn btn-success" href="presupuestosABM.php">Nuevo</a>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table id="myTable" class="table table-striped table-bordered">
                    <thead>
                      <tr>
                        <th>Nº</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Cargado Por</th>
                        <th>Acciones</th>
                      </tr>
                    </thead>
                    <!-- Aquí irían las filas de la tabla -->
                  </table>
                </div>
              </div>
            </div>
          </div>

          <!-- jQuery, Bootstrap y DataTables JS -->
          <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
          <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
          <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>

          <!-- JS Datatables -->
          <script src="js/paginas/general.js"></script>
          <script src="js/paginas/presupuestos.js"></script>
          <!-- Script para inicializar DataTable y cargar datos desde JSON -->


          <!-- FIN CONTENIDO -->

        </div>
      </div>
      <?php include('layouts/footer.php'); ?>
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
    <style>
      /* Tu CSS aquí */
      .dataTables_info {
        margin-top: 50px !important;
      }

      .dataTables_paginate{
        margin-top: 50px !important;
      }

    </style>

       <!-- Bootstrap JS y Popper.js -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.8/umd/popper.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.min.js"></script>

</body>

</html>