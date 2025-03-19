<?php

require_once __DIR__ . '/config/AuthRouter.php';

$router = new AuthRouter();
$menu = $router->checkAccess('pagina_sucursalesABM');
$cambioSucursales = 0;
$session = $router->session();
$idSucursalSeleccionada = $session['response']['user']['id_sucursal'];





//----------------------
$seccion = 'Presupuestos';

if (isset($_GET['id'])) {
  $tipo = 'Editar';
} else {
  $tipo = 'Crear';
}
$idPresupuesto = $_GET['id'] ?? 0;

$campos = [
  "id" => 0,
  "id_sucursal" => $idSucursalSeleccionada,
  "fecha" => "2021-01-01",
  "id_cliente" => 0,
  "id_estado" => 0,
  "total" => "0",
  "descuento" => "0",
  "sub_total" => "0",
  "cliente_nombre" => "",
  "cliente_email" => "",
  "cliente_domicilio" => "",
  "cliente_telefono" => "",
  "alto" => 0,
  "ancho" => 0,
  "id_objeto_a_enmarcar" => 0,
  "modelo" => "",
  "propio" => 0,
  "id_empleado" => 0,
  "id_tipo_enmarcacion" => 0,
  "comentarios" => "",
  "creado_por" => 0,
  "modificado_por" => 0,
  "cantidad" => 1,
];





if (!empty($_GET['id'])) {
  $presupuesto = $router->getRequest('presupuestos/' . $_GET['id'], $_COOKIE['auth_token']);
  $campos = !empty($presupuesto['response']) ? $presupuesto['response']['presupuesto'] : $campos;
  $idSucursalSeleccionada = $campos['id_sucursal'];
}


//$presupuesto = $router->getRequest('me/' . $_GET['id'], $_COOKIE['auth_token']);


// {url}}/tipo_enmarcacion?paginate=false&id_sucursal=1

// {{url}}/objetos_enmarcar?paginate=false&id_sucursal=1

// {{url}}/presupuestos?paginate=false&id_sucursal=1

?>

<!DOCTYPE html><!--
* CoreUI - Free Bootstrap Admin Template
* @version v5.1.1
* @link https://coreui.io/product/free-bootstrap-admin-template/
* Copyright (c) 2024 creativeLabs Łukasz Holeczek
* Licensed under MIT (https://github.com/coreui/coreui-free-bootstrap-admin-template/blob/main/LICENSE)
-->
<html lang="es">

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
  <!-- Vendors styles-->
  <link rel="stylesheet" href="vendor/simplebar/css/simplebar.css">
  <link rel="stylesheet" href="css/vendors/simplebar.css">
  <!-- Main styles for this application-->
  <link href="css/style.css" rel="stylesheet">
  <style>
    /* Aplica un borde y/o fondo diferente para resaltar los campos habilitados */
    input:not([disabled]) {
      border: 2px solidrgb(18, 23, 61);
      /* Verde */
      background-color: rgb(209, 209, 209);
      /* Fondo verde claro */
      color: black;
    }
  </style>
  <!-- We use those styles to show code examples, you should remove them in your application.-->
  <link href="css/examples.css" rel="stylesheet">
  <style>
    /* Aplica un borde y/o fondo diferente para resaltar los campos habilitados */
    input:not([disabled]) {
      border: 2px solidrgb(18, 23, 61);
      /* Verde */
      background-color: rgb(209, 209, 209);
      /* Fondo verde claro */
      color: black;
    }

    select:not([disabled]) {
      border: 2px solidrgb(18, 23, 61);
      /* Verde */
      background-color: rgb(209, 209, 209);
      /* Fondo verde claro */
      color: black;
    }
  </style>
  <script src="js/config.js"></script>
  <script src="js/color-modes.js"></script>
  <link href="vendor/@coreui/chartjs/css/coreui-chartjs.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/@coreui/icons/css/coreui-icons.min.css" rel="stylesheet">
  <!-- sweet alerts -->
  <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
  <script src="js/modals.js"></script>
  <link href="css/sweetalerts.css" rel="stylesheet">
</head>

<body>
  <input type="hidden" id="idPresupuesto" value="<?= $idPresupuesto ?>">
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
          <div class="container mt-5">
            <!-- COMIENZO CONTENIDO -->
            <div class="row">
              <div class=" col-12">

                <!-- COMIENZO card -->
                <div class="card mb-5">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Cliente</h5>
                  </div>
                  <div id="filtrosCollapse" class="collapse show">
                    <div class="card-body">
                      <div class="row">
                        <div class="p-2 col-md-6 col-xs-12 col-xl-6">
                          <div class="form-group ">
                            <label for="nombre">Cliente</label>
                            <input type="text" class="form-control" id="nombre" aria-describedby="nombre" placeholder="Ingrese el nombre" value="<?= $campos['cliente_nombre'] ?>">
                            <small id="error-nombre" class="form-text text-danger"></small>
                          </div>
                        </div>
                        <div class="p-2 col-md-6 col-xs-12 col-xl-3">
                          <div class="form-group ">
                            <label for="telefono">Telefono</label>
                            <input type="text" class="form-control" id="telefono" placeholder="Ingrese el telefono" value="<?= $campos['cliente_telefono'] ?>">
                            <small id="error-telefono" class="form-text text-danger"></small>
                          </div>
                        </div>
                        <div class="p-2 col-md-6 col-xs-12 col-xl-3">
                          <div class="form-group ">
                            <label for="email">Email</label>
                            <input type="text" class="form-control" id="email" placeholder="Ingrese el email" value="<?= $campos['cliente_email'] ?>">
                            <small id="error-email" class="form-text text-danger"></small>
                          </div>
                        </div>
                        <div class="p-2 col-12">
                          <div class="form-group ">
                            <label for="domicilio">Domicilio</label>
                            <input type="text" class="form-control" id="domicilio" placeholder="Ingrese el domicilio" value="<?= $campos['cliente_domicilio'] ?>">
                            <small id="error-domicilio" class="form-text text-danger"></small>
                          </div>
                        </div>


                        <div class="mt-4">
                          <input type="hidden" id="idCliente" />
                          <button type="button" class="btn btn-primary me-2" data-coreui-toggle="modal" data-coreui-target="#modalBuscadorClientes">Buscar</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- fin card -->

              </div>
            </div>
            <div class="row">
              <div class=" col-12">
                <!-- COMIENZO card -->
                <div class="card mb-5">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Objeto a Enmarcar</h5>
                  </div>
                  <div id="filtrosCollapse" class="collapse show">
                    <div class="card-body">
                      <div class="row">
                        <div class="p-2 col-3">
                          <div class="form-group">
                            <label for="nombre">Tipo</label>
                            <select id="tipoObjeto" class="form-control">
                              <?php $idTipoObjeto = $campos['id_objeto_a_enmarcar'];
                              include_once "componentes/combo_tipo_objeto.php"
                              ?>
                            </select>
                            <small id="error-tipoObjeto" class="form-text text-danger"></small>
                          </div>
                        </div>

                        <div class="p-2 col-3">
                          <div class="form-group ">
                            <label for="modelo">Modelo</label>
                            <input type="text" class="form-control" id="modelo" placeholder="" value="<?= $campos['modelo'] ?>">
                            <small id="error-modelo" class="form-text text-danger"></small>
                          </div>
                        </div>
                        <div class="p-2 col-3">
                          <input type="checkbox" class="btn-check" id="propio" autocomplete="off">
                          <label class="btn btn-outline-primary" for="propio">Propio</label>
                        </div>
                        <div class="p-2 col-12">
                          <div class="form-group mt-12">
                            <label for="comentarios">Comentarios</label>
                            <input type="text" class="form-control" id="comentarios" placeholder="" value="<?= $campos['comentarios'] ?>">
                            <small id="error-comentarios" class="form-text text-danger"></small>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- fin card -->
              </div>
            </div>
            <div class="row">
              <div class=" col-12">
                <!-- COMIENZO card -->
                <div class="card mb-5">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Detalle</h5>
                  </div>
                  <div id="filtrosCollapse" class="collapse show">
                    <div class="card-body">
                      <div class="row">
                        <div class="p-2 col-md-6 col-xs-12 col-xl-3">
                          <div class="form-group">
                            <label for="tipoEnmarcacion">Tipo Enmarcacion</label>
                            <select id="tipoEnmarcacion" class="form-control">

                              <?php
                              $idTipoEnmarcacion = $campos['id_tipo_enmarcacion'];
                              include_once "componentes/combo_tipo_enmarcacion.php"
                              ?>
                            </select>
                            <small id="error-tipoEnmarcacion" class="form-text text-danger"></small>
                          </div>
                        </div>
                        <div class="p-2 col-md-6 col-xs-6 col-xl-2">
                          <div class="form-group ">
                            <label for="alto">Alto(en cm)</label>
                            <input type="number" class="form-control" id="alto" placeholder="" value="<?= $campos['alto'] ?>">
                            <small id="error-alto" class="form-text text-danger"></small>
                          </div>
                        </div>
                        <div class="p-2 col-md-6 col-xs-6 col-xl-2">
                          <div class="form-group ">
                            <label for="ancho">Ancho(en cm)</label>
                            <input type="number" class="form-control" id="ancho" placeholder="" value="<?= $campos['ancho'] ?>">
                            <small id="error-ancho" class="form-text text-danger"></small>
                          </div>
                        </div>
                        <div class="p-2 col-md-6 col-xs-6 col-xl-2">
                          <div class="form-group ">
                            <label for="ancho">Cantidad</label>
                            <input type="number" class="form-control" id="cantidad" placeholder="" value="<?= $campos['cantidad'] ?>" step="1" min="1">
                            <small id="error-ancho" class="form-text text-danger"></small>
                          </div>
                        </div>
                        <div class="p-2 col-md-6 col-xs-12 col-xl-3">
                          <div class="form-group">
                            <label for="sucursal">Sucrsal</label>
                            <select id="sucursal" class="form-control" disabled>

                              <?php
                              $idSucursal = $idSucursalSeleccionada;
                              include "componentes/combo_sucursales.php"
                              ?>
                            </select>
                            <small id="error-tipoEnmarcacion" class="form-text text-danger"></small>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- fin card -->
              </div>
            </div>
            <div class="row">
              <div class=" col-md-6 col-xs-12 col-xl-6">
                <!-- COMIENZO card -->
                <div class="card mb-5">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Molduras </h5>
                    <button type="button" class="btn btn-primary me-2" data-coreui-toggle="modal" id="btnBuscarMoldura" data-coreui-target="#modalBuscadorMaterial">Agregar</button>
                  </div>
                  <div id="filtrosCollapse" class="collapse show">
                    <div class="card-body">
                      <table id="tablaMolduras" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                          <tr>
                            <td>Modelo</td>
                            <td>Color</td>
                            <td></td>
                          </tr>
                        </thead>
                      </table>
                    </div>
                  </div>
                </div>
                <!-- fin card -->
              </div>
              <div class=" col-md-6 col-xs-12 col-xl-6">
                <!-- COMIENZO card -->
                <div class="card mb-5">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Vidrios y fibrofacil</h5>
                    <button type="button" class="btn btn-primary me-2" data-coreui-toggle="modal" id="btnBuscarVidrio" data-coreui-target="#modalBuscadorMaterial">Agregar</button>
                  </div>
                  <div id="filtrosCollapse" class="collapse show">
                    <div class="card-body">
                      <table id="tablaVidrios" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                          <tr>
                            <td>Modelo</td>
                            <td>Color</td>
                            <td></td>
                          </tr>
                        </thead>
                      </table>
                    </div>
                  </div>
                </div>
                <!-- fin card -->
              </div>
              <div class=" col-md-6 col-xs-12 col-xl-12">
                <!-- COMIENZO card -->
                <div class="card mb-5">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Paspartout </h5>
                    <button type="button" class="btn btn-primary me-2" data-coreui-toggle="modal" id="btnBuscarPaspartout" data-coreui-target="#modalBuscadorMaterial">Agregar</button>
                  </div>
                  <div id="filtrosCollapse" class="collapse show">
                    <div class="card-body">
                      <table id="tablaPaspartout" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                          <tr>
                            <td>Modelo</td>
                            <td>Color</td>
                            <td>Cm</td>
                            <td>C/S</td>
                            <td></td>
                          </tr>
                        </thead>
                      </table>
                    </div>
                  </div>
                </div>
                <!-- fin card -->
              </div>

              <div class=" col-md-6 col-xs-12 col-xl-12">
                <!-- COMIENZO card -->
                <div class="card mb-5">
                  <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Otros</h5>
                    <button type="button" class="btn btn-primary me-2" id="btnBuscarOtros" onclick="AgregarOtros();">Agregar</button>
                  </div>
                  <div id="filtrosCollapse" class="collapse show">
                    <div class="card-body">
                      <table id="tablaOtros" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                          <tr>
                            <td>Descripcion</td>
                            <td>Cant.</td>
                            <td>Precio Unit.</td>
                            <td></td>
                          </tr>
                        </thead>
                      </table>
                    </div>
                  </div>
                </div>
                <!-- fin card -->
              </div>




              <div class="row">
                <div class=" col-12">
                  <!-- COMIENZO card -->
                  <div class="card mb-5">
                    <div class="card-header d-flex justify-content-between align-items-center">
                      <h5 class="card-title mb-0">Presupuesto</h5>
                      <button type="button" class="btn btn-primary me-2" id="btnCalcular" onclick="guardarPresupuesto();">Calcular</button>
                    </div>
                    <div id="filtrosCollapse" class="collapse show">
                      <div class="card-body">
                        <div class="row">
                          <div class="p-2 col-3">
                            <div class="form-group ">
                              <label for="subtotal">Sub Total</label>
                              <input type="text" class="form-control" id="subtotal" placeholder="" value="<?= $campos['sub_total'] ?>" readonly>
                              <small id="error-subtotal" class="form-text text-danger"></small>
                            </div>
                          </div>
                          <div class="p-2 col-3">
                            <div class="form-group ">
                              <label for="descuento">Descuento</label>
                              <input type="text" class="form-control" id="descuento" placeholder="" value="<?= $campos['descuento'] ?>">
                              <small id="error-descuento" class="form-text text-danger"></small>
                            </div>
                          </div>
                          <div class="p-2 col-3">
                            <div class="form-group ">
                              <label for="total">Total</label>
                              <input type="text" class="form-control" id="total" placeholder="" value="<?= $campos['total'] ?>" readonly>
                              <small id="error-total" class="form-text text-danger"></small>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!-- fin card -->
                </div>
              </div>
              <!-- FIN CONTENIDO -->
            </div>

            <div class="d-flex justify-content-end gap-2">
              <button type="button" class="btn btn-warning" onclick="imprimirPresupuestoPDF();">Imprimir PDF</button>
              <button type="button" class="btn btn-primary" onclick="generarOrden(<?= $idPresupuesto; ?>,<?= $idSucursal; ?>);">Generar Orden</button>
            </div>

          </div>
        </div>
      </div>
      <?php include('layouts/footer.php'); ?>

      <?php
      include_once "componentes/buscador_clientes.php";
      include_once "componentes/buscador_material.php";
      ?>

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
      <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
      <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
      <script src="vendor/chart.js/js/chart.umd.js"></script>
      <script src="vendor/@coreui/chartjs/js/coreui-chartjs.js"></script>
      <script src="vendor/@coreui/utils/js/index.js"></script>
      <script src="js/paginas/general.js"></script>
      <script src="js/paginas/presupuestoABM.js"></script>
</body>

</html>