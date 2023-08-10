<?php
include_once("../model/model.php");
$model = new Model();

// echo $model->generateHTTP();
header("Cache-Control: no-cache;no-store, must-revalidate");
header_remove("X-Powered-By");
header_remove("Server");
header('X-Frame-Options: SAMEORIGIN');

$crossorigin = 'anonymous';
$username  = $_SESSION['username_sess'];
$user      = $model->runQuery("SELECT * FROM userdata WHERE username='$username'");
?>

<script>
	// doOnLoad();
	// var myCalendar;

	// function doOnLoad() {
	// 	myCalendar = new dhtmlXCalendarObject(["start_date"]);
	// 	myCalendar.hideTime();
	// }
</script>
<style>
	.asterik {
		color: red;
	}

	.ajax-upload-dragdrop,
	.ajax-file-upload-container {
		width: auto !important;
	}

	.ajax-file-upload-progress {
		width: 15px
	}

	.ajax-file-upload-container {
		overflow-x: hidden;
		/*        display: none;*/
		text-align: left;
	}

	.ajax-file-upload-statusbar {
		font-size: 12px;
	}
</style>
<div class="">
	<!--
    <div class="card-header">
        <h5 class="card-title mb-0">Change Password</h5>
    </div>
-->
	<!--    <div class= "card-body">-->
	<div class="row">
		<div class="col-md-4 col-xl-3" id="photo_display">
			<div class="card mb-3">
				<div class="card-header">
					<h5 class="card-title mb-0">Profile Photo</h5>
				</div>
				<div class="card-body text-center">
					<?php $gender = $user[0]['sex']; $avartar = (strtolower($gender) == 'male') ? 'avartar-m':((strtolower($gender) == 'female')?'avartar-f':'avartar')?>
                    <img src="img/<?php echo $avartar?>.png" class="avatar img-fluid rounded-circle me-1" alt="<?php echo $_SESSION['firstname_sess'] . ' ' . $_SESSION['lastname_sess']; ?>" /> 
					<!-- <span class="text-dark"><?php echo $_SESSION['firstname_sess'] . ' ' . $_SESSION['lastname_sess']; ?></span> -->
                            
					<!-- <img src="<?php echo $_SESSION['photo_path_sess']; ?>" id="avatar_profile" alt="<?php echo $user[0]['firstname'] . " " . $user[0]['lastname'] ?>" class="img-fluid rounded-circle mb-2" width="128" height="128" /> -->

					<h5 class="card-title mt-3 mb-0"><?php echo $user[0]['firstname'] . " " . $user[0]['lastname'] ?></h5>
					<div class="text-muted mt-2 mb-2"><?php echo $_SESSION['role_id_name'] ?></div>
					<!-- <div>
						<div id="fileuploader"></div>
						<div id="photo_response" style="color:green;display:none">Click on 'Save Changes' button to accept changes</div>
																<a class="btn btn-primary btn-block" href="#"><span data-feather="message-square"></span> Upload Photo</a>-->
					<!--</div> -->

				</div>
				<hr class="my-0" />
				
				
			</div>
			<div class="mt-3">
				<a class="btn btn-primary btn-sm edit-profile" href="javascript:void(0)">Edit Profile</a>
				<a class="btn btn-primary btn-sm" href="javascript:getpage('change_password','page')">Change Password</a>
			</div>
		</div>

		<div class="col-md-8 col-xl-9">
			<div class="card">
				<div class="card-header">
					
					<h5 class="card-title mb-0">Bio Data</h5>
				</div>
				<div class="card-body h-100">
					<div class="row">
						<div class="col-sm-6">
							<div class="form-group">
								<label class="form-label"><b>Full Name</b></label>
								<div><?php echo $user[0]['firstname'].' '.$user[0]['lastname']; ?></div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group">
								<label class="form-label"><b>Gender</b></label>
								<div><?php echo ucfirst($user[0]['sex']); ?></div>
							</div>
						</div>
						
					</div>
					<div class="row mt-2">
						<div class="col-sm-6">
							<div class="form-group">
								<label class="form-label"><b>Phone No.</b></label>
								<div><?php echo $user[0]['mobile_phone']; ?></div>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-group">
								<label class="form-label"><b>Email.</b></label>
								<div><?php echo $user[0]['email']; ?></div>
							</div>
						</div>
						
					</div>
				</div>
			</div>

			
		</div>
		<div class="col-md-12 col-xl-12 profile-section mt-2" style="display: none;">
			<div class="card">
				<div class="card-header">
					<div class="card-actions text-end">
						<div class="dropdown show">
							<button type="button" class="btn btn-close"></button>
						</div>
					</div>
					<h5 class="card-title mb-0">Edit Profile</h5>
				</div>
				<div class="card-body h-100">
					<form action="" id="form1" autocomplete="off">
						<input type="hidden" name="op" value="Users.profileEdit">
						<input type="hidden" name="username" value="<?php echo $username; ?>">
						<input type="hidden" name="photo" id="photo" value="<?php echo $_SESSION['photo_file_sess'] ?>">
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label class="form-label">First Name<span class="asterik">*</span></label>
									<input type="text" name="firstname" value="<?php echo $user[0]['firstname'] ?>" class="form-control" autocomplete="off">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="form-label">Last Name<span class="asterik">*</span></label>
									<input type="text" name="lastname" value="<?php echo $user[0]['lastname'] ?>" class="form-control" autocomplete="off">
								</div>
							</div>
						</div>
						<div class="row">
							<div class="col-sm-6">
								<div class="form-group">
									<label class="form-label">Phone Number<span class="asterik">*</span></label>
									<input type="number" name="mobile_phone" value="<?php echo $user[0]['mobile_phone'] ?>" class="form-control" autocomplete="off">
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<label class="form-label">Gender<span class="asterik">*</span></label>
									<select class="form-control" name="sex" id="sex">
										<option value="male" <?php echo ($user[0]['sex'] == "male") ? "selected" : ""; ?>>Male</option>
										<option value="female" <?php echo ($user[0]['sex'] == "female") ? "selected" : ""; ?>>Female</option>
									</select>
								</div>
							</div>
						</div>
						<div class="row py-3">
							<div class="col-sm-6">
								<div class="form-group">
									<input type="checkbox" onclick="check('is_mfa')" name="is_mfa" value="<?php echo $user[0]['is_mfa'] ?>" <?php echo ($user[0]['is_mfa'] == 1)?'checked':''?> id="is_mfa">
									<label class="form-label is_mfa" for="is_mfa"><?php echo ($user[0]['is_mfa'] == 1)?'Disable 2-Factor Authentication':'Enable 2-Factor Authentication'?></label>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-group">
									<a href="javascript:saveRecord()" class="btn btn-sm btn-success" style="color:#fff">
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2 align-middle mr-2">
											<path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path>
										</svg>Save Changes
									</a>
								</div>
							</div>
							
						</div>
					</form>
				</div>
			</div>

		</div>
	</div>
	<!--    </div>-->
</div>
<link rel="stylesheet" href="css/uploadfile.css" integrity="<?php echo $model->integrityHash('css/uploadfile.css') ?>" crossorigin="<?php echo $crossorigin ?>">
<script src="js/jquery.uploadfile.min.js" integrity="<?php echo $model->integrityHash('js/jquery.uploadfile.min.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>
<script>
	$('.edit-profile').click(function(){
		$('.profile-section').show();
		$(this).hide();
		// $(this).removeClass('edit-profile btn-primary');
	})

	$('.btn-close').click(function(){
		$('.profile-section').hide();
		$('.edit-profile').show();
	})

	$(document).ready(function() {
		$("#fileuploader").uploadFile({
			url: "upload.php",
			fileName: "upfile",
			showPreview: false,
			uploadStr: "Upload Photo",
			statusBarWidth: "50%",
			previewHeight: "100px",
			previewWidth: "100px",
			allowedTypes: "jpg,png",
			maxFileSize: 1000000,
			onSelect: function(files) {
				$("#photo_response").css('display', 'none');
				return true; //to allow file submission.
			},
			onSubmit: function(files) {
				$("#photo_display").block({
					message: 'processing image'
				});

				//files : List of files to be uploaded
				//return flase;   to stop upload
			},
			onSuccess: function(files, data, xhr, pd) {
				$("#photo_display").unblock();
				var resss = JSON.parse(data);
				if (resss.response_code == 0) {
					$("#photo").val(resss.data.file + "." + resss.data.ext);
					$("#avatar_profile").attr('src', 'img/profile_photo/' + resss.data.file + "." + resss.data.ext);
					$("#photo_response").css('display', 'block');
				} else {
					$("#photo_response").css('display', 'block');
					$("#photo_response").css('color', 'red');
					$("#photo_response").text(resss.response_message);
				}
			}
		});
	});

	function saveRecord() {
		$.blockUI();
		$("#save_facility").text("Loading......");
		var dd = $("#form1").serialize();
		$.post("helper", dd, function(re) {
			$.unblockUI();
			$("#save_facility").text("Save");
			console.log(re);
			if (re.response_code == 0) {
				alert(re.response_message);
			} else
				// regenerateCORS();
			alert(re.response_message)
		}, 'json')
	}

	function check(d){
		if($('#'+d).is(':checked')){
			$('.'+d).html('Disable 2-Factor Authentication').show('fast');
		}else{
			$('.'+d).html('Enable 2-Factor Authentication').show('fast');
		}
	}
</script>