<?php

$menus = [
    [
        'nombre' => 'Home',
        'ruta'   => '/admin',
        'icono'  => 'cil-speedometer'
    ],
    [
        'nombre' => 'Roles',
        'ruta'   => '/admin/roles',
        'icono'  => 'cil-people'
    ],
    [
        'nombre' => 'Permisos',
        'ruta'   => '/admin/permisos',
        'icono'  => 'cil-house'
    ],
    [
        'nombre' => 'Logs',
        'ruta'   => '/admin/logs',
        'icono'  => 'cil-notes'
    ],
    [
        'nombre' => 'Impersonalización',
        'ruta'   => '/admin/impersonalizar',
        'icono'  => 'cil-contact'
    ]
];
?>

<ul class="sidebar-nav" data-coreui="navigation" data-simplebar="">
    <?php foreach ($menus as $menu): ?>
        <li class="nav-item">
            <a class="nav-link" href="<?= htmlspecialchars($menu['ruta']) ?>">
                <svg class="nav-icon">
                <use xlink:href="<?= base_url('AdminDist/vendor/@coreui/icons/svg/free.svg#' . htmlspecialchars($menu['icono'])) ?>"></use>
                </svg> <?= htmlspecialchars($menu['nombre']) ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>