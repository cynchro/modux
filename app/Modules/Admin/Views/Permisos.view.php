<?php
function base_url($path = '') {
    // Detecta si está ejecutándose en producción o en local
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];

    // Rutas específicas según el entorno
    if ($host === '127.0.0.1:8080') {
        // Local
        $base = ''; // En local, no necesitas prefijo adicional
    } else {
        // Producción
        $base = '/agarte/public';
    }

    // Construye la URL final
    return $protocol . '://' . $host . $base . '/' . ltrim($path, '/');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <base href="./">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
  <meta name="description" content="CoreUI - Open Source Bootstrap Admin Template">
  <meta name="author" content="">
  <meta name="keyword" content="">
  <title>Agarte Admin</title>
  <link rel="apple-touch-icon" sizes="57x57" href="<?= base_url('AdminDist/assets/favicon/apple-icon-57x57.png') ?>">
  <link rel="apple-touch-icon" sizes="60x60" href="<?= base_url('AdminDist/assets/favicon/apple-icon-60x60.png') ?>">
  <link rel="apple-touch-icon" sizes="72x72" href="<?= base_url('AdminDist/assets/favicon/apple-icon-72x72.png') ?>">
  <link rel="apple-touch-icon" sizes="76x76" href="<?= base_url('AdminDist/assets/favicon/apple-icon-76x76.png') ?>">
  <link rel="apple-touch-icon" sizes="114x114" href="<?= base_url('AdminDist/assets/favicon/apple-icon-114x114.png') ?>">
  <link rel="apple-touch-icon" sizes="120x120" href="<?= base_url('AdminDist/assets/favicon/apple-icon-120x120.png') ?>">
  <link rel="apple-touch-icon" sizes="144x144" href="<?= base_url('AdminDist/assets/favicon/apple-icon-144x144.png') ?>">
  <link rel="apple-touch-icon" sizes="152x152" href="<?= base_url('AdminDist/assets/favicon/apple-icon-152x152.png') ?>">
  <link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('AdminDist/assets/favicon/apple-icon-180x180.png') ?>">
  <link rel="icon" type="image/png" sizes="192x192" href="<?= base_url('AdminDist/assets/favicon/android-icon-192x192.png') ?>">
  <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('AdminDist/assets/favicon/favicon-32x32.png') ?>">
  <link rel="icon" type="image/png" sizes="96x96" href="<?= base_url('AdminDist/assets/favicon/favicon-96x96.png') ?>">
  <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('AdminDist/assets/favicon/favicon-16x16.png') ?>">
  <link rel="manifest" href="<?= base_url('AdminDist/assets/favicon/manifest.json') ?>">
  <meta name="msapplication-TileColor" content="#ffffff">
  <meta name="msapplication-TileImage" content="<?= base_url('AdminDist/assets/favicon/ms-icon-144x144.png') ?>">
  <meta name="theme-color" content="#ffffff">
  <!-- Vendors styles-->
  <link rel="stylesheet" href="<?= base_url('AdminDist/vendor/simplebar/css/simplebar.css') ?>">
  <link rel="stylesheet" href="<?= base_url('AdminDist/css/vendors/simplebar.css') ?>">
  <!-- Main styles for this application-->
  <link href="<?= base_url('AdminDist/css/style.css" rel="stylesheet') ?>">
  <!-- We use those styles to show code examples, you should remove them in your application.-->
  <link href="<?= base_url('AdminDist/css/examples.css') ?>" rel="stylesheet">
  <script src="<?= base_url('AdminDist/js/config.js') ?>"></script>
  <script src="<?= base_url('AdminDist/js/color-modes.js') ?>"></script>
  <link href="<?= base_url('AdminDist/vendor/@coreui/chartjs/css/coreui-chartjs.css') ?>" rel="stylesheet">
</head>

<body>
  <div class="sidebar sidebar-dark sidebar-fixed border-end" id="sidebar">
    <div class="sidebar-header border-bottom">
      <div class="sidebar-brand">
        <svg class="sidebar-brand-full" width="88" height="32" alt="CoreUI Logo">
          <use xlink:href=""></use>
        </svg>
        <!--
<svg class="sidebar-brand-narrow"  height="32" alt="CoreUI Logo">
    <use xlink:href="icons/logoAGArte.svg"></use>
</svg> -->
      </div>
    </div>
    <aside>
      <?php include __DIR__ . '/Layouts/Menu.php'; ?>
    </aside>
    <div class="sidebar-footer border-top d-none d-md-flex">
      <button class="sidebar-toggler" type="button" data-coreui-toggle="unfoldable"></button>
    </div>
  </div>
  <div class="wrapper d-flex flex-column min-vh-100">
    <header class="header header-sticky p-0 mb-4">
      <div class="container-fluid border-bottom px-4">
        <button class="header-toggler" type="button" onclick="coreui.Sidebar.getInstance(document.querySelector('#sidebar')).toggle()" style="margin-inline-start: -14px;">
          <svg class="icon icon-lg">
            <use xlink:href="<?= base_url('AdminDist/vendor/@coreui/icons/svg/free.svg#cil-menu') ?>"></use>
          </svg>
        </button>
        <ul class="header-nav">
          <li class="nav-item dropdown">
            <!-- <a class="nav-link py-0 pe-0" href="" id="" role="button"> -->
            <div class="avatar avatar-md">
              <img class="avatar-img" src="<?= base_url('AdminDist/assets/img/avatars/avatar2.jpg') ?>" alt="user@email.com">
            </div>
          </li>
        </ul>
      </div>
      <div class="container-fluid px-4">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb my-0">
            <li class="breadcrumb-item"><a style="text-decoration: none; cursor: pointer;">Permisos</a></li>
          </ol>
        </nav>
      </div>
    </header>
    <div class="body flex-grow-1">
      <div class="container mt-5">

        <!-- Card sin botón de cierre -->
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">Permisos</h5>
            <a class="btn btn-success" href="/admin/permisos/abm/0">CrearPermiso</a>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <div id="myTable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                <div class="row">
                  <div class="col-sm-12">
                    <table id="myTable" class="table table-striped table-bordered dataTable no-footer" role="grid" aria-describedby="myTable_info" style="width: 1241px;">
                      <thead>
                        <tr role="row">
                          <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 177px;">Key</th>
                          <th class="sorting_disabled" rowspan="1" colspan="1" style="width: 407px;">Estado</th>
                          <th class="sorting_disabled text-center" rowspan="1" colspan="1" style="width: 171px;">Acciones</th>
                        </tr>
                      </thead>
                      <!-- Aquí irían las filas de la tabla -->
                      <tbody>
                      <?php foreach($permisos as $p){ ?>
                        <tr role="row" class="odd">
                          <td><?= $p['key']; ?></td>
                          <td><?= $p['estado'] == 0 ? 'habilitado' : 'suspendido'; ?></td>
                          <td class=" text-center"><a class="btn btn-warning btn-sm" href="/admin/permisos/abm/<?= $p['id']; ?>">
                              <svg class="icon icon-xs">
                                <use xlink:href="<?= base_url('AdminDist/vendor/@coreui/icons/svg/free.svg#cil-pencil') ?>"></use>
                              </svg>
                            </a>
                            <!-- <button class="btn btn-danger btn-sm" onclick="eliminar(1)">
                              <svg class="icon icon-xs">
                                <use xlink:href="<?= base_url('AdminDist/vendor/@coreui/icons/svg/free.svg#cil-trash') ?>"></use>
                              </svg>
                            </button> -->
                          </td>
                        </tr>
                        <?php } ?>
                      </tbody>
                    </table>
                    <div id="myTable_processing" class="dataTables_processing card" style="display: none;">Procesando...</div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-sm-12 col-md-5">
                    <div class="dataTables_info" id="myTable_info" role="status" aria-live="polite">Mostrando 1 a 3 de 3 registros</div>
                  </div>
                  <div class="col-sm-12 col-md-7">
                    <div class="dataTables_paginate paging_simple_numbers" id="myTable_paginate">
                      <ul class="pagination">
                        <li class="paginate_button page-item previous disabled" id="myTable_previous"><a href="#" aria-controls="myTable" data-dt-idx="0" tabindex="0" class="page-link">Anterior</a></li>
                        <li class="paginate_button page-item active"><a href="#" aria-controls="myTable" data-dt-idx="1" tabindex="0" class="page-link">1</a></li>
                        <li class="paginate_button page-item next disabled" id="myTable_next"><a href="#" aria-controls="myTable" data-dt-idx="2" tabindex="0" class="page-link">Siguiente</a></li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <footer class="footer px-4">
        <!-- <div><a href="https://coreui.io">CoreUI </a><a href="https://coreui.io/product/free-bootstrap-admin-template/">Bootstrap Admin Template</a> © 2024 creativeLabs.</div> -->
        <div class="ms-auto">Developed by&nbsp;<a href="#">Cobax</a></div>
      </footer>
    </div>
    <!-- CoreUI and necessary plugins-->
    <script src="<?= base_url('AdminDist/vendor/@coreui/coreui/js/coreui.bundle.min.js') ?>"></script>
    <script src="<?= base_url('AdminDist/vendor/simplebar/js/simplebar.min.js') ?>"></script>
    <script>
      const header = document.querySelector('header.header');

      document.addEventListener('scroll', () => {
        if (header) {
          header.classList.toggle('shadow-sm', document.documentElement.scrollTop > 0);
        }
      });
    </script>
    <!-- Plugins and scripts required by this view-->
    <script src="<?= base_url('AdminDist/vendor/chart.js/js/chart.umd.js') ?>"></script>
    <script src="<?= base_url('AdminDist/vendor/@coreui/chartjs/js/coreui-chartjs.js') ?>"></script>
    <script src="<?= base_url('AdminDist/vendor/@coreui/utils/js/index.js') ?>"></script>

</body>

</html>