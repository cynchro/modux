 <?php

$rou = new AuthRouter();
$session= $rou->session();

switch($session['response']['user']['rol']){
    case 1:
        include('layouts/menu_vertical_administrador.php');
        break;
}

?>