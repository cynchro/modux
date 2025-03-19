<?php

require_once __DIR__.'/../config/AuthRouter.php';

?>
<ul class="header-nav">
<li class="nav-item dropdown">
  <a class="nav-link py-0 pe-0" href="#" id="dropdownToggle" role="button">
  <?= $session['response']['user']['usuario']; ?>
    <div class="avatar avatar-md">
      <img class="avatar-img" src="assets/img/avatars/avatar2.jpg" alt="<?= $session['response']['user']['usuario'] ?>">
    </div>
  </a>
  <div class="dropdown-menu dropdown-menu-end pt-0" id="dropdownMenu">
    <div class="dropdown-header bg-body-tertiary text-body-secondary fw-semibold rounded-top mb-2"><?= $session['response']['user']['nombre_rol'] ?></div>
    <a class="dropdown-item" href="logout.php">
      <svg class="icon me-2">
        <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-account-logout"></use>
      </svg> Logout
    </a>
  </div>
  <!-- <div class="dropdown-menu dropdown-menu-end pt-0" id="dropdownMenu">
    <div class="dropdown-header bg-body-tertiary text-body-secondary fw-semibold rounded-top mb-2">Account</div>
    <a class="dropdown-item" href="#">
      <svg class="icon me-2">
        <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-bell"></use>
      </svg> Updates<span class="badge badge-sm bg-info ms-2">42</span>
    </a>
    <a class="dropdown-item" href="#">
      <svg class="icon me-2">
        <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-envelope-open"></use>
      </svg> Messages<span class="badge badge-sm bg-success ms-2">42</span>
    </a>
    <a class="dropdown-item" href="#">
      <svg class="icon me-2">
        <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-task"></use>
      </svg> Tasks<span class="badge badge-sm bg-danger ms-2">42</span>
    </a>
    <a class="dropdown-item" href="#">
      <svg class="icon me-2">
        <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-comment-square"></use>
      </svg> Comments<span class="badge badge-sm bg-warning ms-2">42</span>
    </a>
    <div class="dropdown-header bg-body-tertiary text-body-secondary fw-semibold my-2">Settings</div>
    <a class="dropdown-item" href="#">
      <svg class="icon me-2">
        <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-user"></use>
      </svg> Profile
    </a>
    <a class="dropdown-item" href="#">
      <svg class="icon me-2">
        <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-settings"></use>
      </svg> Settings
    </a>
    <a class="dropdown-item" href="#">
      <svg class="icon me-2">
        <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-credit-card"></use>
      </svg> Payments<span class="badge badge-sm bg-secondary ms-2">42</span>
    </a>
    <a class="dropdown-item" href="#">
      <svg class="icon me-2">
        <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-file"></use>
      </svg> Projects<span class="badge badge-sm bg-primary ms-2">42</span>
    </a>
    <div class="dropdown-divider"></div>
    <a class="dropdown-item" href="#">
      <svg class="icon me-2">
        <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-lock-locked"></use>
      </svg> Lock Account
    </a>
    <a class="dropdown-item" href="#">
      <svg class="icon me-2">
        <use xlink:href="vendor/@coreui/icons/svg/free.svg#cil-account-logout"></use>
      </svg> Logout
    </a>
  </div> -->
</li>
</ul>

<!-- Por alguna razon no me deja indexar desde afuera esto -->
<style>
  .dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    z-index: 1000;
  }
  .dropdown-menu.show {
    display: block;
  }
</style>

<script>
  document.getElementById('dropdownToggle').addEventListener('click', function(event) {
    event.preventDefault();
    const dropdownMenu = document.getElementById('dropdownMenu');
    dropdownMenu.classList.toggle('show');
  });

  document.addEventListener('click', function(event) {
    const dropdownMenu = document.getElementById('dropdownMenu');
    const dropdownToggle = document.getElementById('dropdownToggle');
    if (!dropdownToggle.contains(event.target) && !dropdownMenu.contains(event.target)) {
      dropdownMenu.classList.remove('show');
    }
  });
</script>