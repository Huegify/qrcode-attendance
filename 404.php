<?php
// session_start();

include_once("model/model.php");

$model = new Model();

echo $model->generateHTTP();

$crossorigin = 'anonymous';
?>
<!DOCTYPE html>
<html lang="en">


<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="Responsive Bootstrap 5 Admin &amp; Dashboard Template">
	<meta name="author" content="Bootlab">
	<meta http-equiv="Cache-control" content="no-cache;no-store">
	
	<title>Page Not Found</title>

	<link rel="canonical" href="#" />
	<link rel="shortcut icon" href="img/icon.png" sizes="32x32">

	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&amp;display=swap" rel="stylesheet">

	<link class="js-stylesheet" href="css/light.css" rel="stylesheet" crossorigin="<?php echo $crossorigin ?>">
	<link href="css/light.css" rel="stylesheet" crossorigin="<?php echo $crossorigin ?>">
	<script src="js/settings.js" crossorigin="<?php echo $crossorigin ?>"></script>


</head>

<body>
	<main class="main d-flex w-100">
		<div class="container d-flex flex-column">
			<div class="row py-5 mb-2">
				<div class="col-sm-12 col-md-8 col-lg-6 mx-auto d-table h-100">
					<div class="d-table-cell align-middle">
						<div class="card">
							<div class="card-body">
								<div class="m-sm-4">
									<div class="text-center p-4">
                                        <img src="admin/img/illustrations/searching.png" alt="Page Not Found" class="img-fluid rounded" width="132" height="132" />

                                        <h2 class="text-grey">Oops! Error 404</h2>
                                        <h4>Page Not Found</h4>
                                        <p> can't seem to find the page you're looking for.</p>
                                        <p> Try to use the search.</p>
                                        <p> <a href="javascript:history.go(-1)" style="text-decoration: none;"> Go Back</a> </p>
									</div>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
	</main>

</body>

</html>