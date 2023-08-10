<?php
include_once("../model/model.php");
include_once("../class/menu.php");
$model = new Model();

if (isset($_REQUEST['op']) && $_REQUEST['op'] == 'edit') {
    $operation = 'edit';
    $id = $_REQUEST['staff_id'];
    $sql_menu = "SELECT * FROM staff WHERE staff_id = '$id' LIMIT 1";
    $student = $model->runQuery($sql_menu);
    
} else {
    $operation = 'new';
}

?>
<style type="text/css">
    .select2-container .select2-selection--single {
        height: 39px !important;
    }
</style>
<link href="css/select2.min.css" rel="stylesheet" integrity="<?php echo $model->integrityHash('css/select2.min.css')?>" crossorigin="<?php echo $crossorigin?>">

<script src="js/select2.js" integrity="<?php echo $model->integrityHash('js/select2.min.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>


<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
<div class="card">
    <div class="modal-header">
        <h4 class="modal-title p-2" style="font-weight:bold"><?php echo ($operation=='new')?"Create Staff Account":"Edit Staff Account"?> (* fields are required)</h4>
    </div>
    <div class="modal-body m-3">
        <form id="form1" onsubmit="return false" autocomplete="off">
            <input type="hidden" name="op" value="Setup.savePatient">
            <input type="hidden" name="operation" value="<?php echo $operation; ?>">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="row">
                <div class="col-sm-6 py-2">
                    <div class="form-group">
                        <label class="form-label">Firstname *</label>
                        <input type="text" name="firstname" class="form-control p-2" value="<?php echo ($operation == 'edit') ? $student[0]['firstname'] : ""; ?>" placeholder="" autocomplete="off">
                    </div>
                </div>

                <div class="col-sm-6 py-2">
                    <div class="form-group">
                        <label class="form-label">Middlename </label>
                        <input type="text" name="middlename" class="form-control p-2" value="<?php echo ($operation == 'edit') ? $student[0]['middlename'] : ""; ?>" placeholder="" autocomplete="off">
                    </div>
                </div>

                <div class="col-sm-6 py-2">
                    <div class="form-group">
                        <label class="form-label">Surname *</label>
                        <input type="text" name="lastname" class="form-control p-2" value="<?php echo ($operation == 'edit') ? $student[0]['lastname'] : ""; ?>" placeholder="" autocomplete="off">
                    </div>
                </div>

                <div class="col-sm-6 py-2">
                    <div class="form-group">
                        <label class="form-label">Phone Number *</label>
                        <input type="tel" name="phone_number" class="form-control p-2" value="<?php echo ($operation == 'edit') ? $student[0]['phone_number'] : ""; ?>" placeholder="" autocomplete="off">
                    </div>
                </div>
                <div class="col-sm-6 py-2">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control p-2" value="<?php echo ($operation == 'edit') ? $student[0]['email'] : ""; ?>" placeholder="" autocomplete="off">
                    </div>
                </div>

                <div class="col-sm-6 py-2">
                    <div class="form-group">
                        <label class="form-label">Staff Id *</label>
                        <input type="text" autocomplete="off" name="staff_id"  <?php echo ($operation == 'edit') ? 'readonly' : '' ?> value="<?php echo ($operation == 'edit') ? $student[0]['staff_id'] : ""; ?>" class="form-control p-2" autocomplete="off" />
                    </div>
                </div>
                
                <div class="col-sm-6 py-2">
                    <div class="form-group">
                        <label class="form-label">Date Of Birth *</label>
                        <input type="date" name="dob" class="form-control p-2" value="<?php echo ($operation == 'edit') ? $student[0]['dob'] : ""; ?>" placeholder="<?php echo ($operation == 'edit') ? $student[0]['dob'] : ""; ?>" autocomplete="off">
                    </div>
                </div>

                <div class="col-sm-6 py-2">
                    <div class="form-group">
                        <label class="form-label">Gender: *</label>
                        <select name="gender" id="gender" class="form-control">
                            <?php echo ($operation == "edit")?'<option value="'.$student[0]['gender'].'" selected>'.ucfirst($student[0]['gender']).'</option>':''?>
                            <option <?php echo ($operation == "edit")?'':'selected'?> value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                </div>

            </div>

            <?php include("form-footer.php"); ?>

            <div id="err"></div>
            <div class="form-group py-3">
                <button id="save_facility" onclick="saveRecord()" class="btn btn-primary mb-1 p-2">Submit</button>
            </div>

        </form>
    
    </div>
</div>
<script>
    $('select').select2();
    
    function saveRecord() {
        $("#save_facility").text("Loading......");
        var dd = $("#form1").serialize();
        $.post("helper", dd, function(re) {
            $("#save_facility").text("Save");
            console.log(re);
            if (re.response_code == 0) {

                $("#err").css('color', 'green')
                $("#err").html(re.response_message)
                getpage('views/patient_list.php', 'page');

            } else {

                $("#err").css('color', 'red')
                $("#err").html(re.response_message)
                $("#warning").val("0");
            }

        }, 'json')
    }
</script>