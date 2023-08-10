<?php
    include("model/model.php");
    $model = new Model();
    $url = ($_SERVER['REMOTE_ADDR'] == '::1' or $_SERVER['REMOTE_ADDR'] == 'localhost')?
    $model->getitemlabel('parameter','parameter_name','site_local_admin_url','parameter_value'):
    $model->getitemlabel('parameter','parameter_name','site_live_admin_url','parameter_value');
    session_destroy();
    header('location:'.$url);
?>

