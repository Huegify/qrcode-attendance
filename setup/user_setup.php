<?php
include_once("../model/model.php");
include_once("../class/users.php");
$model = new Users();

$sql2 = "SELECT bank_code,bank_name FROM banks WHERE bank_type = 'commercial' order by bank_name";
$banks = $model->runQuery($sql2);

$filter = (isset($_SESSION['role_id_sess']) && ($_SESSION['role_id_sess'] == 100 or

    $_SESSION['role_id_sess'] == 200 or $_SESSION['role_id_sess'] == 300)) ? "" : " AND school_id='" . $_SESSION['school_id'] . "'";

$schools = $model->runQuery("SELECT school_id AS id, school_display_name AS name FROM lfs_schools WHERE 1=1 $filter ORDER BY school_display_name ASC");

$states = $model->runQuery("SELECT DISTINCT(State) as name, stateid as id FROM states ORDER BY State ASC");

$sql_role = "SELECT * FROM role WHERE role_id NOT IN (100) ";
$roles = $model->runQuery($sql_role);

$roleid = '';
if (isset($_REQUEST['op']) && $_REQUEST['op'] == 'edit') {
    $username  = $_REQUEST['username'];
    $user      = $model->runQuery("SELECT * FROM userdata WHERE username='$username'");
    $operation = 'edit';
    $roleid = $user[0]['role_id'];

} else {
    $operation = 'new';
    $username  = '';
}

$role_options = $model->getRoleOptions('', $roleid);

?>


<style>
    #login_days>label {
        margin-right: 10px;
    }

    .asterik {
        color: red;
    }
</style>
<style type="text/css">
    .select2-container .select2-selection--single {
        height: 39px !important;
    }
</style>
<link href="css/select2.min.css" rel="stylesheet" integrity="<?php echo $model->integrityHash('css/select2.min.css')?>" crossorigin="<?php echo $crossorigin?>">

<script src="js/select2.js" integrity="<?php echo $model->integrityHash('js/select2.min.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>

<div class="card">
    <div class="modal-header">
        <h4 class="modal-title p-2" style="font-weight:bold"><?php echo ($operation == "edit") ? "Edit " : ""; ?>User Setup<div><small style="font-size:12px">All asterik fields are required</small></div>
        </h4>
        <!-- <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button> -->
    </div>
    <div class="modal-body m-3 ">
        <form id="form1" onsubmit="return false" autocomplete="off">
            <input type="hidden" name="op" value="Users.register">
            <input type="hidden" name="operation" value="<?php echo $operation; ?>">
            <!-- <input type="hidden" name="id" id="id" value="<?php echo $username; ?>"> -->
            <div class="row" style="<?php echo ($operation == "edit") ? "display:none" : ""; ?>">
                <div class="col-sm-6 py-2">
                    <div class="form-group">
                        <label class="form-label">Email<span class="asterik">*</span></label><small style="float:right;color:red">This will be used to login</small>
                        <input type="text" name="username" <?php echo ($operation == "edit") ? "disabled" : ""; ?> class="form-control p-2" value="<?php echo ($operation == "edit") ? $username : ""; ?>" placeholder="" autocomplete="off">

                    </div>
                </div>
                <div class="col-sm-6 py-2">
                    <div class="form-group ">
                        <label class="form-label" style="display:block !important">Password<span class="asterik">*</span></label>
                        <div class="input-group">
                            <input type="password" <?php echo ($operation == "edit") ? "disabled" : ""; ?> autocomplete="off" name="password" value="<?php echo ($operation == "edit") ? $church[0]['date_of_inception'] : ""; ?>" id="password" class="form-control p-2" autocomplete="off" />
                            <div class="input-group-append" style="cursor:pointer; <?php echo ($operation == "edit") ? "display:none" : ""; ?>">
                                <span class="input-group-text p-2" id="show">Show</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <?php if ($operation == "edit") { ?>
                <input type="hidden" name="username" class="form-control p-2" value="<?php echo $username; ?>" placeholder="" autocomplete="off">
            <?php } ?>
            <div class="row">
                <div class="col-sm-6 py-2">
                    <div class="form-group">
                        <label class="form-label">First Name<span class="asterik">*</span></label>
                        <input type="text" name="firstname" value="<?php echo ($operation == "edit") ? $user[0]['firstname'] : "" ?>" class="form-control p-2" autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-6 py-2">
                    <div class="form-group">
                        <label class="form-label">Last Name<span class="asterik">*</span></label>
                        <input type="text" name="lastname" value="<?php echo ($operation == "edit") ? $user[0]['lastname'] : "" ?>" class="form-control p-2" autocomplete="off">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 py-2">
                    <div class="form-group">
                        <label class="form-label">Phone Number<span class="asterik">*</span></label>
                        <input type="number" name="mobile_phone" value="<?php echo ($operation == "edit") ? $user[0]['mobile_phone'] : "" ?>" class="form-control p-2" autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-6 py-2">
                    <div class="form-group">
                        <label class="form-label">Gender<span class="asterik">*</span></label>
                        <select class="form-control select" name="sex" id="sex">
                            <?php echo ($operation == "edit") ? '' : '<option>::: Select Gender :::</option>' ?>
                            <option value="male" <?php echo ($operation == "edit") ? (($user[0]['sex'] == "male") ? "selected" : "") : ""; ?>>Male</option>
                            <option value="female" <?php echo ($operation == "edit") ? (($user[0]['sex'] == "female") ? "selected" : "") : ""; ?>>Female</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">

                <div class="col-sm-6 py-2">
                    <div class="form-group">
                        <label class="form-label">Role<span class="asterik">*</span></label>
                        <select class="form-control" name="role_id" id="role_id" onchange="getRole(this.value)">

                            <?php echo $role_options;
                            // if ($operation == "new") {
                            //     echo '<option value="">::SELECT USER ROLE::</option>';
                            //     foreach ($roles as $row) {
                            //         $selected = (isset($user[0]['role_id']) == $row['role_id']) ? "selected" : "";
                            //         echo "<option $selected value='" . $row['role_id'] . "'>" . $row['role_name'] . "</option>";
                            //     }
                            // } else {
                            //     foreach ($roles as $row) {
                            //         $selected = (isset($user[0]['role_id']) == $row['role_id']) ? "selected" : "";
                            //         echo "<option $selected value='" . $row['role_id'] . "'>" . $row['role_name'] . "</option>";
                            //     }
                            // }

                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group py-2 col-6 state-level" style="display:<?php echo ($operation != 'edit') ? 'none' : (($operation == 'edit' && ($user[0]['role_id'] == 203 or $user[0]['role_id'] == 302 or $user[0]['role_id'] == 202 or $user[0]['role_id'] == 301)) ? 'block' : 'none') ?> ;">
                    <label class="text-start" for="state_id">State: *</label>
                    <select name="office_state" id="states_id" class="form-control p-2 select">
                        <?php echo (isset($_REQUEST['op']) && $operation == 'edit') ? '<option selected value="' . $user[0]['office_state'] . '">' . $model->getitemlabel('states', 'stateid', $user[0]['office_state'], 'State') . '</option>' : '<option selected value="none">::: Select State :::</option>' ?>
                        <?php foreach ($states as $state) {
                            echo '<option value="' . $state['id'] . '">' . $state['name'] . '</option>';
                        } ?>

                    </select>
                </div>

                <div class="col-sm-6 py-2 school-level" style="display:<?php echo ($operation != 'edit') ? 'none' : (($operation == 'edit' && ($user[0]['role_id'] == 202 or $user[0]['role_id'] == 301)) ? 'block' : 'none') ?> ;">
                    <div class="form-group">
                        <label class="form-label">School Name *</label>
                        <select <?php echo ($operation == 'new') ? 'disabled' : '' ?> name="school_id" id="schools_id" class="form-control p-2 select" <?php echo isset($_REQUEST['op']) ? '' : 'disabled' ?>>
                            <?php echo ($operation == 'edit') ? '<option selected value="' . $user[0]['school_id'] . '">' . $model->getitemlabel('lfs_schools', 'school_id', $user[0]['school_id'], 'school_display_name') . '</option>' : '<option selected value="none">::: Select School :::</option>' ?>
                            <?php foreach ($schools as $sch) {
                                echo '<option value="' . $sch['id'] . '">' . $sch['name'] . '</option>';
                            } ?>
                        </select>
                    </div>
                </div>
            </div>


            <div class="row mt-2">
                <div class="col-sm-12">
                    <label for=""><b>Login Days</b></label>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group" id="login_days">
                        <label class="form-label" id="day1"><input type="checkbox" value="<?php echo isset($user[0]['day_1']) ? $user[0]['day_1'] : '1'; ?>" <?php echo (isset($user[0]['day_1'])) ? ($user[0]['day_1'] == 0) ? "" : "checked" : "checked"; ?> name="day_1"> Sunday</label>
                        <label class="form-label" id="day2"><input type="checkbox" value="<?php echo isset($user[0]['day_2']) ? $user[0]['day_2'] : '1'; ?>" <?php echo (isset($user[0]['day_2'])) ? ($user[0]['day_2'] == 0) ? "" : "checked" : "checked"; ?> name="day_2"> Monday</label>
                        <label class="form-label" id="day3"><input type="checkbox" value="<?php echo isset($user[0]['day_3']) ? $user[0]['day_3'] : '1'; ?>" <?php echo (isset($user[0]['day_3'])) ? ($user[0]['day_3'] == 0) ? "" : "checked" : "checked"; ?> name="day_3"> Tuesday</label>
                        <label class="form-label" id="day4"><input type="checkbox" value="<?php echo isset($user[0]['day_4']) ? $user[0]['day_4'] : '1'; ?>" <?php echo (isset($user[0]['day_4'])) ? ($user[0]['day_4'] == 0) ? "" : "checked" : "checked"; ?> name="day_4"> Wednesday</label>
                        <label class="form-label" id="day5"><input type="checkbox" value="<?php echo isset($user[0]['day_5']) ? $user[0]['day_5'] : '1'; ?>" <?php echo (isset($user[0]['day_5'])) ? ($user[0]['day_5'] == 0) ? "" : "checked" : "checked"; ?> name="day_5"> Thursday</label>
                        <label class="form-label" id="day6"><input type="checkbox" value="<?php echo isset($user[0]['day_6']) ? $user[0]['day_6'] : '1'; ?>" <?php echo (isset($user[0]['day_6'])) ? ($user[0]['day_6'] == 0) ? "" : "checked" : "checked"; ?> name="day_6"> Friday</label>
                        <label class="form-label" id="day7"><input type="checkbox" value="<?php echo isset($user[0]['day_7']) ? $user[0]['day_7'] : '1'; ?>" <?php echo (isset($user[0]['day_7'])) ? ($user[0]['day_7'] == 0) ? "" : "checked" : "checked"; ?> name="day_7"> Saturday</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <label for=""><b>Security Settings</b></label>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label class="form-label" id="day1"><input type="checkbox" value="<?php echo isset($user[0]['user_locked']) ? $user[0]['user_locked'] : '0'; ?>" <?php echo (isset($user[0]['user_locked'])) ? (($user[0]['user_locked'] == 0) ? "" : "checked") : ""; ?> name="user_locked" id="day1"> Lock User</label><br />
                        <label class="form-label" id="day1"><input type="checkbox" value="<?php echo isset($user[0]['passchg_logon']) ? $user[0]['passchg_logon'] : '1'; ?><?php echo isset($user[0]['passchg_logon']) ? $user[0]['passchg_logon'] : '1'; ?>" name="passchg_logon" <?php echo (isset($user[0]['passchg_logon'])) ? (($user[0]['passchg_logon'] == 0) ? "" : "checked") : "checked"; ?> id="passchg_logon"> Change password on first login</label><br />
                        <label class="form-label" id="day1">
                            <input type="checkbox" onclick="check('is_mfa')" name="is_mfa" value="<?php echo (isset($user[0]['is_mfa']) && $user[0]['is_mfa'] == 1) ? $user[0]['is_mfa'] : '' ?>" <?php echo (isset($user[0]['is_mfa']) && $user[0]['is_mfa'] == 1) ? 'checked' : '' ?> id="is_mfa">
                            <label class="form-label is_mfa" for="is_mfa"><?php echo (isset($user[0]['is_mfa']) && $user[0]['is_mfa'] == 1) ? 'Disable 2-Factor Authentication' : ((isset($user[0]['is_mfa']) && $user[0]['is_mfa'] == 0) ? 'Enable 2-Factor Authentication' : 'Enable 2-Factor Authentication') ?></label>
                        </label>

                    </div>
                </div>

            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div id="server_mssg"></div>
                </div>
            </div>
            <?php include("form-footer.php"); ?>
            <button id="save_facility" onclick="saveRecord()" class="btn btn-primary">Submit</button>
        </form>
    </div>
</div>
<script>
    $('select').select2();
    function getRole(d) {
        $("#states_id").append('<option selected value="">::: Select State :::</option>');

        if (d == 203 || d == 302) {
            $("#states_id").val('');
            $("#schools_id").val('');
            $('.state-level').show();
            $('.school-level').hide();
        } else if (d == 202 || d == 301) {
            $("#states_id").val('');
            $("#schools_id").val('');
            $('.state-level').show();
            $('.school-level').show();
        } else {
            $("#states_id").val('');
            $("#schools_id").val('');
            $('.state-level').hide();
            $('.school-level').hide();
        }
    }

    function saveRecord() {
        $("#save_facility").text("Loading......");
        var dd = $("#form1").serialize();
        $.post("helper.php", dd, function(re) {
            console.log(re);
            $("#save_facility").text("Save");
            if (re.response_code == 0) {
                $("#server_mssg").text(re.response_message);
                $("#server_mssg").css({
                    'color': 'green',
                    'font-weight': 'bold'
                });
                getpage('views/user_list.php', 'page');
                setTimeout(() => {
                    $('#defaultModalPrimary').modal('hide');
                }, 1000)
            } else {
                $("#server_mssg").text(re.response_message);
                $("#server_mssg").css({
                    'color': 'red',
                    'font-weight': 'bold'
                });
            }

        }, 'json');
    }
    if ($("#sh_display").is(':checked')) {

    }

    function show_bank_details(val) {
        if (val == 003) {
            $("#parish_pastor_div").show();
        } else {
            $("#parish_pastor_div").hide();
        }
    }

    function check(d) {
        if ($('#' + d).is(':checked')) {
            $('.' + d).html('Disable 2-Factor Authentication').show('fast');
        } else {
            $('.' + d).html('Enable 2-Factor Authentication').show('fast');
        }
    }

    $("#show").click(function() {
        var password = $("#password").attr('type');
        if (password == "password") {
            $("#password").attr('type', 'text');
            $("#show").text("Hide");
        } else {
            $("#password").attr('type', 'password');
            $("#show").text("Show");
        }
    });

    $("select#states_id").change(function() {
        var selected = $(this).children("option:selected").val();
        getSchool(selected);

    })

    function getSchool(d) {
        var payload = {
            op: 'Payment.getSchoolDetails',
            state: $("#states_id").val(),
            type: 'school_by_state'
        };

        $.ajax({
            url: 'helper',
            type: 'post',
            data: payload,
            dataType: 'json',
            success: function(data) {
                $("#schools_id").attr('disabled', false).html('<option selected value="">::: Select School :::</option>');

                for (var i = 0; i < data.school.option_value.length; i++) {
                    $("#schools_id").append('<option value="' + data.school.option_id[i] + '">' + data.school.option_value[i] + '</option>');
                }

            }
        })

    }
</script>