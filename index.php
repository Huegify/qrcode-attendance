<?php
// session_start();

include_once("model/model.php");

$model = new Model();

$url = ($_SERVER['REMOTE_ADDR'] == '::1' or $_SERVER['REMOTE_ADDR'] == 'localhost')?
$model->getitemlabel('parameter','parameter_name','site_local_admin_url','parameter_value'):
$model->getitemlabel('parameter','parameter_name','site_live_admin_url','parameter_value');
// echo $model->generateHTTP();
header("Cache-Control: no-cache;no-store, must-revalidate");
header_remove("X-Powered-By");
header_remove("Server");
header('X-Frame-Options: SAMEORIGIN');
$crossorigin = 'anonymous';
// echo $model->generateBARCODE('1234567890');
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

	<title>Login</title>

	<link rel="canonical" href="<?php echo $url?>" />
	<link rel="shortcut icon" href="img/logo.jpg" sizes="32x32">

	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&amp;display=swap" rel="stylesheet">

	<link class="js-stylesheet" href="css/light.css" rel="stylesheet" integrity="<?php echo $model->integrityHash('css/light.css') ?>" crossorigin="<?php echo $crossorigin ?>">
	<script src="js/settings.js" integrity="<?php echo $model->integrityHash('js/settings.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>


</head>

<body>
	<main class="main d-flex w-100">
		<div class="container d-flex flex-column">
			<div class="row py-5 mb-2">
				<div class="col-sm-12 col-md-8 col-lg-6 mx-auto d-table h-100">
					<div class="d-table-cell align-middle">

						<div class="text-center mt-4">
							<!-- <h1 class="h2">Welcome to Living Faith Schools Payment Management Portal</h1> -->
							<h1 class="h2">Welcome Back</h1>
							<p class="lead">
								Sign in to your account
							</p>
						</div>

						<div class="card">
							<div class="card-body">
								<div class="m-sm-4">
									<div class="text-center">
										<img src="img/logo.jpg" alt="Chris Wood" class="img-fluid rounded" width="132" height="132" />
									</div>
									<form id="form1" onsubmit="return false" autocomplete="off">
										<input type="hidden" name="op" value="Users.login">
										<div class="form-group py-3">
											<label>Username</label>
											<input class="form-control form-control-lg" type="text" id="username" name="username" required placeholder="Enter your username" autocomplete="off" />
										</div>
										<div class="form-group py-3">
											<div class="form-group input-group mb-3">
												<label class="form-label col-12">Password</label>
							  					<input class="form-control p-2 password" id="password" type="password" name="password" placeholder="Enter your password">
												<div class="input-group-append" style="cursor: pointer;">
													<span class="input-group-text toggle p-2">SHOW</span>
												</div>
											</div>
											<small class="mt-2">
												<a href="forgot-password" style="text-decoration: none;">Forgot password?</a>
												<!-- <a href="resend-activation-link" class="text-end" style="text-decoration:none; float:right">Resend Activation Link ?</a> -->
											</small>
										</div>
										
										<div id="server_mssg"></div>
										<div class="text-center mt-3">
											<button onclick="sendLogin('form1')" id="button" class="btn btn-lg btn-danger btn-block">Sign in</button>
										</div>
									</form>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
	</main>

	<script src="js/jquery-3.6.0.min.js" integrity="<?php echo $model->integrityHash('js/jquery-3.6.0.min.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>
	<script src="js/jquery.blockUI.js" integrity="<?php echo $model->integrityHash('js/jquery.blockUI.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>
	<script src="js/parsely.js" integrity="<?php echo $model->integrityHash('js/parsely.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>

	<script src="js/sweet_alerts.js" integrity="<?php echo $model->integrityHash('js/sweet_alerts.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>
	<script src="js/main.js" integrity="<?php echo $model->integrityHash('js/main.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>
	<script>
		var ip = window.location.host, url;
		if (ip == 'localhost' || ip == '::1') {
			url = window.location.protocol + "//" + window.location.host + "/lfs/admin/";
		} else {
			url = window.location.protocol + "//" + window.location.host + "/khms/admin/";
		}
		function sendLogin(id) {
			var forms = $('#' + id);
			forms.parsley().validate();
			if (forms.parsley().isValid()) {
				$.blockUI();
				var data = $("#" + id).serialize();

				$.ajax({
					type: "post",
					url: "router",
					data: data,
					dataType: "json",
					beforeSend: function() {
						$.blockUI({
							message: "Processing..... Please wait...",
						});
					},
					success: function(data) {
						$.unblockUI();

						if (data.response_code == 0) {
							$("#button").attr("disabled", true);
							$("#server_mssg").text(data.response_message);
							setTimeout(() => {
								window.location = 'dashboard';
							}, 2000);
						} else {
							if (data.status == 114) {
								$('#server_mssg').html('<div class="alert alert-warning alert-outline alert-dismissible" role="alert"><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button><div class="alert-icon"><i class="far fa-fw fa-bell"></i></div><div class="alert-message"><strong>Alert!' + "\n" + '</strong>You are required to change your password.</div></div>');
								setTimeout(function() {
									window.location.href = 'change_psw_logon?_ga=' + $('#username').val();
								}, 3000);

							} else if (data.status == 100) {
								$("small").hide();
								$('#server_mssg').html('<div class="alert alert-info alert-outline alert-dismissible" role="alert"><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button><div class="alert-icon"><i class="far fa-fw fa-bell"></i></div><div class="alert-message"><strong>Alert!' + "\n" + '</strong>' + data.response_message + '</div></div>');
							} else if (data.status == 101) {
								$("small").html('Just a moment...').fadeIn(500);
								setTimeout(function() {
									window.location = '<?php echo $url ?>' + data.page + '?type=' + data.type + '&gsd=' + data.username ; //2FA
								}, 2000);

							} else {
								$('.login').attr('disabled', false);
								$('#loging-in-btn').hide();
								$('#login-btn').show();
								$('.login-text').html(' Login');
								swal('Warning!', data.response_message, 'warning');
							}
							
						}
					},
					error: function(data) {
						$.unblockUI();
						$("#server_mssg").html("Unable to process request at the moment! Please try again");
					},
				});
			}
		}

		$('.toggle').click(function (e) {
			// toggle the type attribute
			var type = $('.password').attr('type');
			if (type == 'password') {
				$('.password').attr('type', 'text');
				$('.toggle').text('HIDE');
			}else if (type == 'text') {
				$('.password').attr('type', 'password');
				$('.toggle').text('SHOW');
			}
			// toggle the eye / eye slash icon
		});
	</script>
</body>

</html>