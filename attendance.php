<?php
include_once("model/model.php");
include_once("class/users.php");

$model = new Model();

header("Cache-Control: no-cache;no-store, must-revalidate");
header_remove("X-Powered-By");
header_remove("Server");
header('X-Frame-Options: SAMEORIGIN');

$requestUrl = $_SERVER['REQUEST_URI'];

$hostname = "localhost"; // Replace with the hostname you want to connect to

// Get the current IP address for the hostname using a DNS lookup
$ipAddress = gethostbyname($hostname);

// Make the HTTP request using the retrieved IP address
$url = "http://{$ipAddress}/";
$data = array("param1" => "value1", "param2" => "value2");
$options = array(
  "http" => array(
    "method" => "POST",
    "header" => "Content-Type: application/x-www-form-urlencoded\r\n",
    "content" => http_build_query($data),
  ),
);

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

// Process the response as needed


$crossorigin = 'anonymous';

$user = new Users();
$result = json_decode($user->takeAttendance($_REQUEST), TRUE);
$msg = '';
$icon = 'img/error-icon.png';
if ($result['response_code'] == 20) {
    $msg .= "<h3>" . $result['response_message'] . "</h3>";
    $passport = "<img src='".$result['passport']."' alt='icon' class='img-fluid rounded' width='100' height='100' />";
} else {
    $icon = 'img/success-icon.png';
    $passport = "<img src='".$result['passport']."' alt='icon' class='img-fluid rounded' width='100' height='100' />";
    $msg .= "<h3>".$result['response_message']."</h1>";
}
?>
<!DOCTYPE html>
<html lang="en">
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Attendance Mgt. System">
    <meta name="author" content="Hugh Concepts Inc.">

    <meta http-equiv="Cache-control" content="no-cache;no-store">
    <title>Attendance</title>
    <link rel="stylesheet" href="css/parsley.css" integrity="<?php echo $model->integrityHash('css/parsley.css') ?>" crossorigin="<?php echo $crossorigin ?>">
    <link rel="preconnect" href="http://fonts.gstatic.com/" crossorigin>
    <link rel="icon" href="img/icon.png" sizes="32x32" />
    <!-- PICK ONE OF THE STYLES BELOW -->
    <link href="css/light.css" rel="stylesheet" integrity="<?php echo $model->integrityHash('css/light.css') ?>" crossorigin="<?php echo $crossorigin ?>">

    <style>
        body {
            opacity: 0;
        }
    </style>
    <script src="js/settings.js" integrity="<?php echo $model->integrityHash('js/settings.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>
</head>

<body">
    <main class="main d-flex w-100">
        <div class="container d-flex flex-column">
            <div class="row">
                <div class="col-sm-12 col-md-8 col-lg-6 mx-auto d-table h-100 p-5">
                    <div class="d-table-cell align-middle">

                        <div class="card">
                            <div class="card-header">
                                <div class="text-center">
                                    <?php echo $passport?>
                                </div>
                            </div>
                            <div class="card-body text-center">
                                <div class="text-center mb-2">
                                    <img src="<?php echo $icon?>" alt="icon" class="img-fluid rounded" width="50" height="50" />
                                </div>
                                <?php echo $msg?>
                            </div>
                        </div>
                        <script src="js/jquery-3.6.0.min.js" integrity="<?php echo $model->integrityHash('js/jquery-3.6.0.min.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>
                        <script src="js/jquery.blockUI.js" integrity="<?php echo $model->integrityHash('js/jquery.blockUI.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>
                        <script src="js/parsely.js" integrity="<?php echo $model->integrityHash('js/parsely.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>

                        <script src="js/sweet_alerts.js" integrity="<?php echo $model->integrityHash('js/sweet_alerts.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>
                        <script src="js/main.js" integrity="<?php echo $model->integrityHash('js/main.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>

                    </div>
                </div>
            </div>
        </div>
    </main>
    </body>

</html>