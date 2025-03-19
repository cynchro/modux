<div class="container-fluid px-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb my-0">
      <?php
      // Obtener el nombre del archivo PHP actual
      $currentFile = basename($_SERVER['PHP_SELF']);

      // Verificar si existe el parámetro 'id' en la URL
      $isEditing = isset($_GET['id']);

      // Asignar nombres descriptivos para cada archivo
      $breadcrumbs = [
        'home' => 'Dashboard',
        'sucursales' => 'Sucursales',
        'clientes' => 'Clientes',
        'empleados' => 'Empleados',
        'materiales' => 'Materiales',
        'objetosEnmarcar' => 'Objetos a Enmarcar',
        'tipoEnmarcacion' => 'Tipo de Enmarcación',
        'ordenes' => 'Ordenes',
        'presupuestos' => 'Presupuestos',
        'recibos' => 'Recibos',
        'caja' => 'Caja',
        'ordenes' => 'Ordenes de Trabajo',
        'ordenesTaller' => 'Ordenes de trabajo'
      ];

      // Determinar si es una página de tipo ABM
      if (str_ends_with($currentFile, 'ABM.php')) {
        // Extraer el nombre base del archivo sin "ABM"
        $baseName = str_replace('ABM.php', '', $currentFile);

        // Obtener el nombre descriptivo desde el arreglo $breadcrumbs
        $breadcrumb = $breadcrumbs[$baseName] ?? ucfirst($baseName);

        // Agregar 'Editar' o 'Crear'
        $breadcrumb .= $isEditing ? ' / Editar' : ' / Crear';
      } else {
        // Determinar el breadcrumb para páginas normales
        $breadcrumb = $breadcrumbs[str_replace('.php', '', $currentFile)] ?? 'Página desconocida';
      }
      ?>
      <li class="breadcrumb-item"><a style="text-decoration: none; cursor: pointer;"><?= $breadcrumb; ?></a></li>
    </ol>
  </nav>
</div>
