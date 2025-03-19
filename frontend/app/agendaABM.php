<?php

require_once 'config/AuthRouter.php';
require_once 'componentes/generar_combo.php';


$router = new AuthRouter();
$menu = $router->checkAccess('pagina_clientesABM');

$campos = [
  'id' => '',
  'nombre' => '',
  'domicilio' => '',
  'id_localidad' => '',
  'email' => '',
  'telefono' => '',
  'id_tipo_documento' => '',
  'documento' => '',
  'id_condicion_iva' => '',
  'fecha_alta' => '',
  'creado_por' => '',
  'modificado_por' => '',
  'fecha_baja' => '',
  'descuento' => '0'
];

if (!empty($_GET['id'])) {
  $clientes = $router->getRequest('clientes/' . $_GET['id'], $_COOKIE['auth_token']);
  $campos = !empty($clientes['response']) ? $clientes['response'] : $campos;
}

$localidades = $router->getRequest('localidades?paginate=false', $_COOKIE['auth_token']);
$iva = $router->getRequest('iva?paginate=false', $_COOKIE['auth_token']);  
$tipo_documento = $router->getRequest('documentos?paginate=false', $_COOKIE['auth_token']);  
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
  <title><?= $_ENV['APP_NAME']; ?></title>
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
  <style>
  /* Aplica un borde y/o fondo diferente para resaltar los campos habilitados */
  input:not([disabled]) {
    border: 2px solidrgb(18, 23, 61); /* Verde */
    background-color:rgb(209, 209, 209); /* Fondo verde claro */
    color: black;
  }
    </style>
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

</head>

<body>
  <div class="sidebar sidebar-dark sidebar-fixed border-end" id="sidebar">
    <div class="sidebar-header border-bottom">
      <div class="sidebar-brand">
      <?php include('layouts/logo.php'); ?>
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
      <?php include('layouts/breadcrumb.php'); ?>
    </header>
    <div class="body flex-grow-1">
      <div class="container-lg px-4">
        <div class="row g-4 mb-4">
          <!-- COMIENZO CONTENIDO -->
          <div class="container mt-5">
            <!-- COMIENZO DE LA TARJETA -->
            <div class="card shadow">
              <div class="card-header text-white">
                <h5 class="card-title mb-0">Clientes</h5>
              </div>
              <div class="card-body">

                <form id="createEditSucursalForm">
                  <div class="form-group mt-2 row">
                    <div class="col-md-6">
                      <label for="nombre">Nombre</label>
                      <input type="text" class="form-control" value="<?=$campos['nombre'];?>" id="nombre" aria-describedby="nombre" placeholder="Ingrese el nombre">
                      <small id="error-nombre" class="form-text text-danger"></small>
                    </div>
                    <div class="col-md-6">
                      <label for="telefono">Teléfono</label>
                      <input type="text" class="form-control" value="<?=$campos['telefono'];?>" id="telefono" placeholder="Ingrese el teléfono">
                      <small id="error-telefono" class="form-text text-danger"></small>
                    </div>
                  </div>

                  <div class="form-group mt-2 row">
                    <div class="col-md-8">
                      <label for="domicilio">Domicilio</label>
                      <input type="text" class="form-control" value="<?=$campos['domicilio'];?>" id="domicilio" placeholder="Ingrese el domicilio">
                      <small id="error-domicilio" class="form-text text-danger"></small>
                    </div>
                    <div class="col-md-4">
                      <label for="localidades">Localidades</label>
                      <select class="form-select" id="localidades">
                        <?= generarCombo($localidades, $campos['id_localidad']); ?>
                      </select>
                      <small id="error-localidades" class="form-text text-danger"></small>
                    </div>
                  </div>

                  <div class="form-group mt-2 row">
                    <div class="col-md-6">
                      <label for="email">Email</label>
                      <input type="email" class="form-control" value="<?=$campos['email'];?>" id="email" placeholder="Ingrese el email">
                      <small id="error-email" class="form-text text-danger"></small>
                    </div>
                    <div class="col-md-6">
                      <label for="iva">Condición frente al IVA</label>
                      <select class="form-select" id="iva">
                      <?= generarCombo($iva, $campos['id_condicion_iva']); ?>
                      </select>
                      <small id="error-iva" class="form-text text-danger"></small>
                    </div>
                  </div>


                  <div class="form-group mt-2 row">
                    <div class="col-md-6">
                      <label for="tipo_documento">Tipo de documento</label>
                      <select class="form-select" id="tipo_documento">
                      <?= generarCombo($tipo_documento, $campos['id_tipo_documento']); ?>
                      </select>
                      <small id="error-tipo_documento" class="form-text text-danger"></small>
                    </div>
                    <div class="col-md-6">
                      <label for="documento">Documento</label>
                      <input type="text" class="form-control" value="<?=$campos['documento'];?>" id="documento" placeholder="Ingrese el numero de documento">
                      <small id="error-documento" class="form-text text-danger"></small>
                    </div>
                  </div>
                  <div class="form-group mt-2 row">
                    <div class="col-md-6">
                      <label for="descuento">Descuento %</label>
                      <input type="numeric" step="0.01" class="form-control" value="<?=$campos['descuento'];?>" id="descuento" placeholder="Ingrese el Descuento">
                      <small id="error-descuento" class="form-text text-danger"></small>
                    </div>
                    
                  </div>

                  <input type="hidden" id="id" value="<?=$campos['id'];?>"/>
                  <div class="mt-4">
                    <button type="submit" class="btn btn-primary me-2">Guardar</button>
                    <button type="button" class="btn btn-secondary" onclick="window.history.back();">Volver</button>
                  </div>
                </form>
              </div>

              <!-- FIN CONTENIDO -->
            </div>
          </div>
          <!-- FIN DE LA TARJETA -->
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
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="vendor/chart.js/js/chart.umd.js"></script>
    <script src="vendor/@coreui/chartjs/js/coreui-chartjs.js"></script>
    <script src="vendor/@coreui/utils/js/index.js"></script>
    <script src="js/paginas/general.js"></script>
    <script src="js/paginas/clientesABM.js"></script>
</body>

</html>