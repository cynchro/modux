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
  <meta name="msapplication-TileImage" content="assets/favicon/ms-icon-144x144.png">
  <meta name="theme-color" content="#ffffff">
  <!-- Vendors styles-->
  <link rel="stylesheet" href="<?= base_url('AdminDist/vendor/simplebar/css/simplebar.css') ?>">
  <link rel="stylesheet" href="<?= base_url('AdminDist/css/vendors/simplebar.css') ?>">
  <!-- Main styles for this application-->
  <link href="<?= base_url('AdminDist/css/style.css') ?>" rel="stylesheet">
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
              <img class="avatar-img" src="<?= base_url('AdminDist/assets/img/avatars/avatar2.jpg" alt="user@email.com') ?>">
            </div>
          </li>
        </ul>
      </div>
      <div class="container-fluid px-4">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb my-0">
            <li class="breadcrumb-item"><a style="text-decoration: none; cursor: pointer;">Roles</a></li>
          </ol>
        </nav>
      </div>
    </header>
    <div class="body flex-grow-1">
      <div class="container-lg px-4">
        <div class="row g-4 mb-4">

          <form method="POST" action="/admin/roles/createOrUpdate" enctype="multipart/form-data">
            <div class="col-md-12">
              <div class="card shadow">
                <div class="card-header text-white d-flex align-items-center justify-content-between">
                  <!-- Título Rol -->
                  <h5 class="card-title mb-0">Rol</h5>

                  <!-- Botón Guardar -->
                  <button type="submit" name="accion" value="actualizarDatos" class="btn btn-primary btn-sm">
                    Guardar
                  </button>

                </div>
                <div class="card-body">

                  <div class="row">
                    <!-- Input text para Nombre -->
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" value="<?= $nombre; ?>" class="form-control" placeholder="Escribe el nombre" />
                      </div>
                    </div>
                    <?php if ($rol != 0) { ?>
                      <!-- Select para Estado -->
                      <div class="col-md-6">
                        <div class="form-group">
                          <label for="estado">Estado</label>
                          <select id="estado" name="estado" class="form-control">
                            <option>Seleccione una opción</option>
                            <?php if ($estado == 0) { ?>
                              <option value="0" selected>Habilitado</option>
                              <option value="1">Suspendido</option>
                            <?php } elseif ($estado == 1) { ?>
                              <option value="1" selected>Suspendido</option>
                              <option value="0">Habilitado</option>
                            <?php } else { ?>
                              <option value="1">Suspendido</option>
                              <option value="0">Habilitado</option>
                            <?php } ?>
                          </select>
                        </div>
                      </div>
                    <?php } ?>
                  </div>
                </div>
              </div>
            </div>
            <input type="hidden" name="rol" value=" <?= $rol; ?>" />
          </form>
          <?php if ($rol != 0) { ?>
            <div class="col-md-12">
              <div class="card shadow">
                <div class="card-header text-white">
                  <h5 class="card-title mb-0">Gestión de Permisos</h5>
                </div>
                <div class="card-body">
                  <div class="container mt-5">
                    <form method="POST" action="/admin/roles/createOrUpdate" enctype="multipart/form-data">
                      <div class="row">
                        <!-- Permisos Disponibles -->
                        <div class="col-md-4">
                          <h3 class="text-center">Permisos Disponibles</h3>
                          <ul class="list-group">
                            <li class="list-group-item">
                              <label>
                                <input type="checkbox" id="select-all-disponibles">
                                Todos
                              </label>
                            </li>
                            <?php if (empty($permisosDisponibles)): ?>
                              <li class="list-group-item">No hay permisos asignados.</li>
                            <?php else: ?>
                            <?php foreach ($permisosDisponibles as $permiso): ?>
                              <li class="list-group-item">
                                <label>
                                  <input type="checkbox" class="permiso-disponible-checkbox" name="permisos_disponibles[]" value="<?= $permiso['id_permiso']; ?>"
                                    <?= in_array($permiso, $permisosAsignados) ? 'checked' : ''; ?>>
                                  <?= ucfirst(str_replace('_', ' ', $permiso['key'])); ?>
                                </label>
                              </li>
                            <?php endforeach; ?>
                            <?php endif; ?>
                          </ul>
                        </div>
                        <!-- Botones -->
                        <div class="col-md-4 text-center d-flex flex-column justify-content-center align-items-center">
                          <div class="d-grid gap-3">
                            <button type="submit" name="accion" value="asignar" class="btn btn-primary">Asignar >></button>
                            <button type="submit" name="accion" value="desasignar" class="btn btn-danger">
                              << Desasignar</button>
                          </div>
                        </div>

                        <!-- Permisos Asignados -->
                        <div class="col-md-4">
                          <h3 class="text-center">Permisos Asignados</h3>
                          <ul class="list-group">
                            <li class="list-group-item">
                              <label>
                                <input type="checkbox" id="select-all-asignados">
                                Todos
                              </label>
                            </li>
                            <?php if (empty($permisosAsignados)): ?>
                              <li class="list-group-item">No hay permisos asignados.</li>
                            <?php else: ?>
                              <?php foreach ($permisosAsignados as $permiso): ?>
                                <li class="list-group-item">
                                  <label>
                                    <input type="checkbox" class="permiso-asignado-checkbox" name="permisos_asignados[]" value="<?= $permiso['id_permiso']; ?>">
                                    <?= ucfirst(str_replace('_', ' ', $permiso['key'])); ?>
                                  </label>
                                </li>
                              <?php endforeach; ?>
                            <?php endif; ?>
                          </ul>
                        </div>
                      </div>
                      <input type="hidden" name="rol" value=" <?= $rol; ?>" />
                    </form>

                  </div>

                </div>

                <!-- FIN CONTENIDO -->
              </div>
            </div>
          <?php } ?>
        </div>
      </div>
      <footer class="footer px-4">
        <div class="ms-auto">Developed by&nbsp;<a href="#">Cobax</a></div>
      </footer>
    </div>
    <!-- CoreUI and necessary plugins-->
    <script src="<?= base_url('AdminDist/vendor/@coreui/coreui/js/coreui.bundle.min.js') ?>"></script>
    <script src="<?= base_url('AdminDist/vendor/simplebar/js/simplebar.min.js') ?>"></script>

    <!-- Plugins and scripts required by this view-->
    <script src="<?= base_url('AdminDist/vendor/chart.js/js/chart.umd.js') ?>"></script>
    <script src="<?= base_url('AdminDist/vendor/@coreui/chartjs/js/coreui-chartjs.js') ?>"></script>
    <script src="<?= base_url('AdminDist/vendor/@coreui/utils/js/index.js') ?>"></script>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Permisos Disponibles
        const selectAllDisponibles = document.getElementById('select-all-disponibles');
        const disponiblesCheckboxes = document.querySelectorAll('.permiso-disponible-checkbox');

        selectAllDisponibles.addEventListener('change', function() {
          const isChecked = this.checked;
          disponiblesCheckboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
          });
        });

        disponiblesCheckboxes.forEach(checkbox => {
          checkbox.addEventListener('change', function() {
            selectAllDisponibles.checked = Array.from(disponiblesCheckboxes).every(cb => cb.checked);
          });
        });

        // Permisos Asignados
        const selectAllAsignados = document.getElementById('select-all-asignados');
        const asignadosCheckboxes = document.querySelectorAll('.permiso-asignado-checkbox');

        selectAllAsignados.addEventListener('change', function() {
          const isChecked = this.checked;
          asignadosCheckboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
          });
        });

        asignadosCheckboxes.forEach(checkbox => {
          checkbox.addEventListener('change', function() {
            selectAllAsignados.checked = Array.from(asignadosCheckboxes).every(cb => cb.checked);
          });
        });
      });
    </script>
</body>

</html>