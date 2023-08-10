<?php
require_once('model/model.php');
$model = new Model();

$crossorigin = 'anonymous';

@session_start();
if (!isset($_SESSION['username_sess'])) {
    header('location: logout');
}

require_once('class/menu.php');
require_once('class/users.php');
$users = new Users();
$menu = new Menu();
$menu_list = $menu->generateMenu($_SESSION['role_id_sess']);
$menu_list = $menu_list['data'];

include 'class/chart.php';
$chart = new Chart();
$data['from'] = $users->current_week();
$data_1['from'] = $users->current_week();
$graph = $chart->Monthly_Attendance($data); //call monthly sales
$graph_2 = $chart->Weekly_Attendance($data_1); //call weekly sales

// echo $_SERVER['REQUEST_URI'];
// var_dump($_SERVER);

$filter = " AND day_taken = '" . date('Y-m-d') . "'";

$attendance = $model->runQuery("SELECT count(id) as total FROM attendance_log WHERE 1=1  $filter");

$staff = $model->runQuery("SELECT count(staff_id) as total FROM staff");

//Time to time out in seconds
$inact_min = $model->getitemlabel("parameter", "parameter_name", 'inactivity_time', 'parameter_value');
//convert by multiplying by 3600
$inact_val = ($inact_min > 0) ? $inact_min * 60 * 60 : 10 * 60 * 60;

header("Cache-Control: no-cache;no-store, must-revalidate");
header_remove("X-Powered-By");
header_remove("Server");
header('X-Frame-Options: SAMEORIGIN');
?>
<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Hospital Information System">
    <meta name="author" content="Hugh Concepts Inc.">
    <meta http-equiv="Cache-control" content="no-cache;no-store">

    <title>Dashboard</title>

    <link rel="canonical" href="dashboard" />
    <link rel="icon" href="img/logo.jpg" sizes="32x32" />

    <link type="text/css" rel="stylesheet" href="js/aks-upload/dist/aksFileUpload.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css" />

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&amp;display=swap" rel="stylesheet">
    <link class="js-stylesheet" href="css/light.css" rel="stylesheet" integrity="<?php echo $model->integrityHash('css/light.css') ?>" crossorigin="<?php echo $crossorigin ?>">
    <link href="css/select2.css" rel="stylesheet" integrity="<?php echo $model->integrityHash('css/select2.css') ?>" crossorigin="<?php echo $crossorigin ?>">
    <!-- <link href="css/jquery.signature.css" rel="stylesheet" integrity="<?php echo $model->integrityHash('css/jquery.signature.css') ?>" crossorigin="<?php echo $crossorigin ?>"> -->
    <script src="js/settings.js" integrity="<?php echo $model->integrityHash('js/settings.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>
    <script src="js/fdb76255c2.js" integrity="<?php echo $model->integrityHash('js/fdb76255c2.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>
    <script src="js/select2.js" integrity="<?php echo $model->integrityHash('js/select2.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>

</head>

<body data-theme="default" data-layout="fluid" data-sidebar-position="left" data-sidebar-behavior="sticky">
    <div class="wrapper">
        <nav id="sidebar" class="sidebar">
            <div class="sidebar-content js-simplebar">
                <a class="sidebar-brand" href="dashboard" style="width: 100%;">
                    <img src="img/logo.jpg" alt="LFS" style="width: 129px !important;height:100px !important">
                </a>

                <ul class="sidebar-nav">

                    <li class="sidebar-item">
                        <a href="dashboard" class="sidebar-link sidebar-link-active">
                            <i class="align-middle " data-feather="home"></i> <span class="align-middle">Dashboard</span>
                            <span class="badge badge-sidebar-primary"></span>
                        </a>
                    </li>
                    <?php foreach ($menu_list as $value) :
                        if ($value['has_sub_menu'] == false) : ?>
                            <li class="sidebar-item">
                                <a href="javascript:getpage('<?php echo $value['menu_url'] ?>','page')" class="sidebar-link sidebar-link-active">
                                    <i class="align-middle" data-feather="sliders"></i> <span class="align-middle"><?php echo ucfirst($value['menu_name']) ?></span>
                                    <span class="badge badge-sidebar-primary"></span>
                                </a>
                            </li>
                        <?php elseif ($value['has_sub_menu'] == true) : ?>
                            <li class="sidebar-item" id="<?php echo $value['menu_id'] ?>">
                                <a data-bs-target="#l<?php echo $value['menu_id'] ?>" data-bs-toggle="collapse" class="sidebar-link collapsed">
                                    <i class="align-middle" data-feather="sliders"></i> <span class="align-middle"><?php echo ucfirst($value['menu_name']) ?></span>
                                </a>
                                <ul id="l<?php echo $value['menu_id'] ?>" class="sidebar-dropdown list-unstyled collapse " data-bs-parent="#sidebar">

                                    <?php foreach ($value['sub_menu'] as $value_1) : ?>
                                        <li class="sidebar-item" id="<?php echo $value_1['menu_id'] ?>"><a class="sidebar-link sidebar-link-active" href="javascript:loadNavPage('<?php echo $value_1['menu_url'] ?>','page', '<?php echo $value_1['menu_id'] ?>')"><?php echo ucfirst($value_1['name']) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </li>
                    <?php endif;
                    endforeach; ?>
                </ul>
                <div class="sidebar-bottom d-none d-lg-block">
                    <div class="media">
                        <?php $gender = $model->getitemlabel('userdata', 'username', $_SESSION['username_sess'], 'gender');
                        $avartar = (strtolower($gender) == 'male') ? 'avartar-m' : ((strtolower($gender) == 'female') ? 'avartar-f' : 'avartar') ?>
                        <img src="img/<?php echo $avartar ?>.png" class="avatar rounded-circle mr-3" alt="<?php echo $_SESSION['firstname_sess'] . ' ' . $_SESSION['lastname_sess']; ?>" />

                        <div class="media-body">
                            <h6 style="color: white;" class="mb-1"><?php echo $_SESSION['firstname_sess'] . ' ' . $_SESSION['lastname_sess']; ?></h6>

                            <div>
                                <button class="btn btn-danger btn-block" onclick="window.location='logout'">Logout</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
        <div class="main">

            <nav class="navbar navbar-expand navbar-light navbar-bg">
                <a class="sidebar-toggle">
                    <i class="hamburger align-self-center"></i>
                </a>

                <a href="javascript:void(0)" class="d-flex mr-2" style="text-decoration: none;">
                    <?php $state_loc = ":" . $model->getitemlabel('states', 'stateid', $_SESSION['state_id'], 'State'); ?>
                    Your Role: &nbsp; <span style="font-weight:bold; color:#000; text-decoration: none;"><?php echo $_SESSION['role_id_name']; ?></span>
                </a>
                <div class="navbar-collapse collapse">
                    <ul class="navbar-nav navbar-align">

                        <li class="nav-item dropdown">
                            <a class="nav-icon dropdown-toggle d-inline-block d-sm-none" href="#" data-bs-toggle="dropdown">
                                <i class="align-middle" data-feather="settings"></i>
                            </a>

                            <a class="nav-link dropdown-toggle d-none d-sm-inline-block" href="#" data-bs-toggle="dropdown">
                                <?php $gender = $model->getitemlabel('userdata', 'username', $_SESSION['username_sess'], 'sex');
                                $avartar = (strtolower($gender) == 'male') ? 'avartar-m' : ((strtolower($gender) == 'female') ? 'avartar-f' : 'avartar') ?>
                                <img src="img/<?php echo $avartar ?>.png" class="avatar img-fluid rounded-circle me-1" alt="<?php echo $_SESSION['firstname_sess'] . ' ' . $_SESSION['lastname_sess']; ?>" /> <span class="text-dark"><?php echo $_SESSION['firstname_sess'] . ' ' . $_SESSION['lastname_sess']; ?></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="javascript:getpage('views/profile','page')"><i class="align-middle mr-1" data-feather="user"></i> Profile</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout">Sign out</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="content" id="page">
                <div class="row mb-2 mb-xl-3 p-3">
                    <div class="col-auto d-none d-sm-block">
                        <h3>Dashboard</h3>
                    </div>
                    <div class="col-7 text-end">
                        <h3 class="filtered-date-display"></h3>
                    </div>
                    <div class="col-auto ms-auto text-end mt-n1">

                        <button class="btn btn-danger shadow-sm refresh-page">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw align-middle">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <polyline points="1 20 1 14 7 14"></polyline>
                                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="row owl-carousel owl-theme" id="carousel_div">

                    <div class="col-12 col-sm-6 col-xl d-flex">
                        <div class="card flex-fill">
                            <div class="card-body py-4">
                                <div class="media">
                                    <div class="d-inline-block mt-2 mr-3">
                                        <i class="fa fa-users text-info" style="font-size:35px"></i>
                                    </div>
                                    <div class="media-body">
                                        <h3 class="mb-2"><?php echo isset($staff[0]['total']) ? $staff[0]['total'] : 0 ?></h3>
                                        <div class="mb-0"><?php echo (isset($staff[0]['total']) && $staff[0]['total'] > 1) ? 'Staff' : 'Staffers' ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-12 col-sm-6 col-xl d-flex">
                        <div class="card flex-fill">
                            <div class="card-body py-4">
                                <div class="media">
                                    <div class="d-inline-block mt-2 mr-3">
                                        <i class="fa fa-building text-success" style="font-size:35px"></i>
                                    </div>
                                    <div class="media-body">
                                        <h3 class="mb-2"><?php echo isset($attendance[0]['total']) ? $attendance[0]['total'] : 0 ?></h3>
                                        <div class="mb-0"><?php echo (isset($attendance[0]['total']) && $attendance[0]['total'] > 1) ? ' Attendance for today ' : ' Attendance for today' ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="row">
                    <div class="col-12 col-lg-8 d-flex">
                        <div class="card flex-fill w-100">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-md-10">
                                        <h5 class="card-title mb-0">Monthly Attendance (<b class="filtered_date"><?php echo date('F d, Y', strtotime($data['from'])) ?></b>)</h5>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="dropdown me-2 d-inline-block">
                                            <a class="btn btn-light bg-white shadow-sm dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">Filter</a>

                                            <div class="dropdown-menu dropdown-menu-end">
                                                <h6 class="dropdown-header">Select Week</h6>
                                                <form id="filter-range" action="javascript:void(0)">
                                                    <div class="row">
                                                        <div class="form-group p-3 col-lg-12">
                                                            <label for="from" class="p-1">Week</label>
                                                            <input type="week" class="form-control py-2" id="from">
                                                        </div>
                                                        <div class="form-group p-3 col-lg-12">
                                                            <button class="btn btn-primary col-lg-12 filter-chart"><i data-feather="search"></i></button>
                                                        </div>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="card-body">
                                <div class="chart chart-lg">
                                    <div id="chartContainer" style="display: block; height: 350px; width: 100%;" width="100" height="350" class="chartjs-render-monitor"></div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4 d-flex">
                        <div class="card flex-fill w-100">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Weekly Attendance (<b class="filtered_date"><?php echo date('F d, Y', strtotime($data_1['from'])) .' - '. date('F d, Y')?></b>)</h5>

                            </div>
                            <div class="card-body d-flex">
                                <div class="align-self-center w-100">
                                    <div class="py-3">
                                        <div class="chart chart-xs">
                                            <div id="chartjs-dashboard-pie" style="height: 370px; width: 100%;"></div>

                                        </div>
                                    </div>

                                    <table class="table mb-0 filtered_table">
                                        <thead>
                                            <tr>
                                                <th>Day</th>
                                                <th class="text-end">Month</th>
                                                <th class="text-end">No. of Attendance</th>
                                            </tr>
                                        </thead>
                                        <tbody class="filtered_body">
                                            <?php
                                            $weekly = json_decode($graph_2, true);
                                            if (!empty($weekly)) {
                                                for ($i = 0; $i < count($weekly); $i++) {
                                            ?>
                                                    <tr class="filtered_row">
                                                        <td class="filtered_product"><?php echo $weekly[$i]['day'] ?></td>
                                                        <td class="text-end filtered_amount"><?php echo date('M',strtotime($weekly[$i]['month'])) ?></td>
                                                        <td class="text-end text-success filtered_quantity"><?php echo $weekly[$i]['daily_attendance'] ?></td>
                                                    </tr>
                                                <?php }
                                            } else { ?>
                                                <tr class="filtered_row"></tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <div class="card-actions float-end">
                                <div class="dropdown position-relative">
                                    <a href="#" data-bs-toggle="dropdown" data-bs-display="static">
                                        <i class="align-middle" data-feather="more-horizontal"></i>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="#">Action</a>
                                        <a class="dropdown-item" href="#">Another action</a>
                                        <a class="dropdown-item" href="#">Something else here</a>
                                    </div>
                                </div>
                            </div>
                            <h5 class="card-title mb-0">Today's Attendance</h5>
                        </div>
                        <div style="overflow:auto; width: 100%">
                            <table id="datatables-dashboard-projects" class="table table-striped my-0" style="white-space: nowrap;">
                                <thead>
                                    <tr role="row">
                                        <th>Staff ID</th>
                                        <th>Staff Name.</th>
                                        <th>Day Taken</th>
                                        <th>Week Taken</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT * FROM attendance_log as a INNER JOIN staff AS s ON s.staff_id=a.staff_id WHERE day_taken = '" . date('Y-m-d') . "' ORDER BY a.id desc LIMIT 10";
                                    $result = $model->runQuery($sql);

                                    if (is_array($result) && sizeof($result) > 0) {
                                        foreach ($result as $row) {

                                            $week_taken = explode('-',$row['week_taken']);
                                            $week = substr($week_taken[1],1);
                                    ?>
                                            <tr>
                                                <td><?php echo $row['staff_id']; ?></td>
                                                <td><?php echo $row['firstname'] . ' ' . $row['middlename'] . ' ' . $row['lastname']; ?></td>
                                                <td><?php echo date('d M, Y', strtotime($row['day_taken'])) ?></td>
                                                <td><?php echo 'Week '.$week ?></td>
                                                <td><?php echo date('h:i a', strtotime($row['created'])) ?></td>
                                            </tr>
                                    <?php }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row text-muted">
                        <div class="col-6 text-left">
                            <ul class="list-inline">

                                <li class="list-inline-item">
                                    <a class="text-muted" target="_blank" href="https://www.facebook.com/hughconceptsinc">Help Center</a>
                                </li>

                            </ul>
                        </div>
                        <div class="col-6 text-right">
                            <p class="mb-0">
                                &copy; <?php echo date('Y'); ?> - <a href="#" class="text-muted">Attendance Mgt. System</a>
                            </p>
                        </div>
                    </div>
                </div>
            </footer>

            <div class="modal fade" id="defaultModalPrimary" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content" id="modal_div">
                        <div class="modal-header">
                            <!-- <h5 class="modal-title">Default modal</h5> -->
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body m-3">
                            <!-- <p class="mb-0">Use Bootstrap’s JavaScript modal plugin to add dialogs to your site for lightboxes, user notifications, or completely custom content.</p> -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/app.js" integrity="<?php echo $model->integrityHash('js/app.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>
    <script src="js/sweet_alerts.js" integrity="<?php echo $model->integrityHash('js/sweet_alerts.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>
    <script src="js/jquery.blockUI.js" integrity="<?php echo $model->integrityHash('js/jquery.blockUI.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>
    <script src="js/main.js" integrity="<?php echo $model->integrityHash('js/main.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>
    <script src="js/parsely.js" integrity="<?php echo $model->integrityHash('js/parsely.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>
    <script src="js/owl.carousel.js" integrity="<?php echo $model->integrityHash('js/owl.carousel.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>
    <script src="js/webcam.min.js" integrity="<?php echo $model->integrityHash('js/webcam.min.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>

    <script src="js/canvas.min.js" integrity="<?php echo $model->integrityHash('js/canvas.min.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>
    <script src="js/aks-upload/dist/aksFileUpload.js" integrity="<?php echo $model->integrityHash('js/aks-upload/dist/aksFileUpload.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>
    <script src="js/signature_pad.min.js" integrity="<?php echo $model->integrityHash('js/signature_pad.min.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>

    <script>
        var idleTime = 0;
        $(document).ready(function() {
            //Increment the idle time counter every minute.
            var idleInterval = setInterval("timerIncrement()", <?php echo $inact_val; ?>); // 1 minute

            //Zero the idle timer on mouse movement.
            $(this).mousemove(function(e) {
                idleTime = 0;
            });
            $(this).keypress(function(e) {
                idleTime = 0;
            });

            var date = '',
                d = new Date();
            d.setMonth(d.getMonth() - 1);

            allChart(date);
        });

        $('.refresh-page').click(function(e) {
            e.preventDefault();
            $.blockUI({
                message: '<img src="img/loading.gif" height="80px" width="80px"/> Just a moment...'
            });
            setTimeout(function() {
                $.unblockUI();
                location.reload();
            }, 2000);

        });

        $(".filter-chart").click(function(e) {
            e.preventDefault();
            var data = {
                op: 'Chart.Monthly_Attendance',
                from: $('#from').val(),
                to: $("#to").val()
            };
            $.ajax({
                url: 'helper',
                type: 'post',
                data: data,
                dataType: 'json',
                success: function(data) {
                    if (data == null) {
                        swal('Attention!', 'No record was found.', 'info');
                    } else {
                        allChart(data);
                        from = new Date($("#from").val());
                        from_date = from.getDate();
                        from_year = from.getFullYear();
                        from_month = from.toLocaleString('default', {
                            month: 'long'
                        });

                        $(".filtered_date").html('Filtered From a month of today: ' +from_month + ', ' +from_year );
                        $(".filtered-date-display").html('Filtered From a month of today: ' + from_month + ', ' + from_year );
                    }
                }
            })
        })

        function allChart(data) {

            if (data.length > 0) {

                var success_data = pie_success = all = [];

                all = JSON.stringify(data);
                try {
                    var parsedData = JSON.parse(all);
                } catch (e) {
                    console.log('Error parsing JSON data:' +e);
                    return;
                }

                parsedData.forEach(function(row) {
                var new_month = (row.month > 0) ? row.month - 1 : row.month;
                    success_data.push({
                        x: new Date(row.year, new_month, row.day),
                        y: row.daily_attendance
                    });
                });

                parsedData.forEach(function(row) {
                    var new_month = (row.month > 0) ? row.month - 1 : row.month;
                    pie_success.push({
                        x: new Date(row.year, new_month, row.day),
                        y: row.daily_attendance
                    });
                });

                var chart = new CanvasJS.Chart("chartContainer", {
                    animationEnabled: true,
                    title: {
                        text: "Attendance Reports"
                    },
                    axisY: {
                        title: "No. of Attendance",
                        valueFormatString: "#,###",
                        // Changed the format string to use the currency symbol and comma separator properly.
                    },
                    data: [{
                        yValueFormatString: "#,###",
                        xValueFormatString: "MMM DD",
                        type: "spline",
                        dataPoints: success_data
                    }]
                });
                chart.render();

                var pie = new CanvasJS.Chart("chartjs-dashboard-pie", {
                    animationEnabled: true,
                    title: {
                        text: "Attendance Reports"
                    },
                    data: [{
                        type: "pie",
                        startAngle: 240,
                        yValueFormatString: "#,##0.00",
                        indexLabel: "{label} ({y})",
                        dataPoints: pie_success

                    }]
                });
                pie.render();

            } else {
                var chart = new CanvasJS.Chart("chartContainer", {
                    animationEnabled: true,
                    title: {
                        text: "Attendance Reports"
                    },
                    axisY: {
                        title: "No. of Attendance",
                        valueFormatString: "#,###",
                        // Changed the format string to use the currency symbol and comma separator properly.
                    },
                    data: [{
                        yValueFormatString: "#,###",
                        xValueFormatString: "MMM DD",
                        type: "spline",
                        dataPoints: [
                            <?php
                            $data = json_decode($graph, true);
                            if (is_array($data) && sizeof($data) > 0){
                                foreach ($data as $row) {
                                    $new_month = ($row['month'] > 0) ? $row['month'] - 1 : $row['month']; ?> {
                                        x: new Date(<?php echo $row['year'] ?>, <?php echo $new_month ?>, <?php echo $row['day'] ?>),
                                        y: <?php echo $row['daily_attendance'] ?>
                                    },
                            <?php } }else{?>
                                { x: new Date(<?php echo date('Y') ?>, <?php echo date('mm',strtotime('- 1 month')) ?>, <?php echo date('d') ?>),
                                 y: 0
                                }
                            <?php } ?>
                           
                        ]
                    }]
                });
                chart.render();

                let pie = new CanvasJS.Chart("chartjs-dashboard-pie", {
                    animationEnabled: true,
                    title: {
                        text: "Attendance Report"
                    },
                    data: [{
                        type: "pie",
                        startAngle: 240,
                        yValueFormatString: "#,##0.00",
                        indexLabel: "{label} ({y})",
                        dataPoints: [
                        <?php
                        $data = json_decode($graph_2, true); // call weekly sales

                        if (!empty($data)) {
                            for ($i = 0; $i < count($data); $i++) { ?>{
                                y: <?php echo json_encode($data[$i]['daily_attendance'], JSON_NUMERIC_CHECK) ?>,
                                label: new Date(<?php echo isset($row['year']) ? $row['year'] : 0 ?>, <?php echo isset($new_month) ? $new_month : 0 ?>, <?php echo isset($row['day']) ? $row['day'] : 0 ?>)
                            },
                            <?php }
                        } else { ?>{
                            y: <?php echo json_encode(0, JSON_NUMERIC_CHECK) ?>,
                            label: '<?php echo 'No Attendance'; ?>'
                            }
                        <?php } ?>
                        ]
                    }]
                });
                pie.render();

            }

        }

        function timerIncrement() {
            idleTime = idleTime + 1;
            if (idleTime > 5) { // 5 minutes
                //alert("Logging you out");	
                window.location = "logout";
            }
        }
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            $("#datetimepicker-dashboard").datetimepicker({
                inline: true,
                sideBySide: false,
                format: "L"
            });
        });
    </script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            $("#datatables-dashboard-projects").DataTable({
                pageLength: 6,
                lengthChange: false,
                bFilter: false,
                autoWidth: false
            });
        });
    </script>

    <!-- <script>
        $(function() {
            var sig = $('#sig').signature();
            $('#disable').click(function() {
                var disable = $(this).text() === 'Disable';
                $(this).text(disable ? 'Enable' : 'Disable');
                sig.signature(disable ? 'disable' : 'enable');
            });
            $('#clear').click(function() {
                sig.signature('clear');
            });
            $('#json').click(function() {
                alert(sig.signature('toJSON'));
            });
            $('#svg').click(function() {
                alert(sig.signature('toSVG'));
            });
        });
    </script> -->
</body>

</html>