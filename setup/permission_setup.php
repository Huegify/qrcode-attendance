<?php
@session_start();
if (!isset($_SESSION['username_sess'])) {
    include('../logout.php');
}
include_once('../model/model.php');
$station = new Model();
$id = '';
$role_id = '';
if (isset($_REQUEST['op']) && $_REQUEST['op'] == 'edit') {
    $operation = 'edit';
    $id = $_REQUEST['id'];
    $permissions = $station->getItemLabelArr("permissions", array('id'), array($id), "*");
} else {
    $operation = 'new';
}


?>

<style>
    .parent {
        height: 100px;
        width: 100px;
        position: relative;
    }

    .child {
        width: 150px;
        height: 150px;
        position: absolute;
        top: 50%;
        left: 50%;
        margin: -35px 0 0 -35px;
    }

    .file-upload {
        width: 200px !important;
        height: 100px !important;
    }

    .image-upload-wrap {
        width: 200px !important;
        height: 100px !important;
    }

    .file-upload-content {
        width: 200px !important;
        height: 100px !important;
    }
</style>

<div class="modal-header">
    <!-- breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-0">
        <ol class="breadcrumb my-breadcrumb">
            <h1 class="h3 mb-3"><?php echo (isset($_REQUEST['op']) == 'edit') ? 'Edit Permissions' : 'New Permissions'; ?></h1>
        </ol>
    </nav>
    <button type="button" class="close btn-close" data-bs-dismiss="modal" onclick="closeModal()" aria-label="Close" style="float: right;">
    </button>

</div>
<div class="container-fluid p-0">

    <div class="row">

        <div class="col-md-12">
            <form id="form1" method="POST" class="simple-example" enctype="multipart/form-data" action="javascript:void(0);" novalidate autocomplete="off">
                <div class="card">

                    <div class="card-body">

                        <input type="hidden" name="operation" id="operation" value="<?php echo $operation; ?>" />
                        <input type="hidden" name="id" id="id" value="<?php echo $id; ?>" />
                        <input type="hidden" name="op" id='op' value="Permissions.savePermissions">

                        <div class="hide">
                            <div class="row py-2">

                                <div class="form-group col-lg-6 col-md-6 col-sm-12 py-2">
                                    <label class="form-label">Operation(op) </label>
                                    <input type="text" class="form-control" id="action" placeholder="Enter operation(op)" name="action" value="<?php echo isset($_REQUEST['op']) ? $permissions['action'] : '' ?>">
                                </div>
                                <div class="form-group col-lg-6 col-md-6 col-sm-12 py-2">
                                    <label class="form-label">Label</label>
                                    <input type="text" class="form-control" id="label" placeholder="Enter label" name="label" value="<?php echo isset($_REQUEST['op']) ? $permissions['label'] : '' ?>">
                                </div>
                                <div class="form-group col-lg-6 col-md-6 col-sm-12 py-2">
                                    <label class="form-label">Operation Type </label>
                                    <select class="form-control select" name="operation_type" id="operation_type">
                                        <?php echo (isset($_REQUEST['op']) && $_REQUEST['op'] == 'edit') ? '<option value="' . $permissions['operation_type'] . '">' . ucfirst($permissions['operation_type'] . ' Operations') . '</option>' : '';
                                        ?>
                                        <option>:: please select option::</option>
                                        <option value="all_op">All Operations</option>
                                        <option value="edit">Edit Operations</option>
                                        <option value="new">New Operations</option>
                                    </select>
                                </div>

                                <div class="form-group col-lg-6 col-md-6 col-sm-12 py-2">
                                    <label class="form-label">Description </label>
                                    <textarea class="form-control" name="description" rows="2" placeholder="Enter Description"><?php echo (isset($_REQUEST['op']) == 'edit') ? $permissions['description'] : ''; ?></textarea>
                                </div>
                            </div>

                            <div class="col-lg-6 col-md-6 col-sm-12 py-2">
                                <div class="server_mssg" id="server_mssg"></div>
                            </div>
                            <?php include("form-footer.php"); ?>
                            <div class="text-center py-2 mb-0">
                                <button id="save_facility" onclick="savePermissions()" name="subbtn" class="btn btn-primary"><i class=" fa fa-save"></i> Save Record </button>
                            </div>
                        </div>
                    </div>

            </form>
        </div>
    </div>
</div>
<script>
    function savePermissions() {
        $("#save_facility").text("Processing......");

        var dd = $("#form1").serialize();
        $.ajax({
            url: "helper.php",
            type: "POST",
            data: dd,
            dataType: "json",
            beforeSend: function() {
                $.blockUI({
                    message: '<img src="assets/img/loading.gif" alt=""/>&nbsp;&nbsp;processing request please wait . . .',
                });
            },
            success: function(re) {
                $("#save_facility").text("Save Record");
                $.unblockUI();
                if (re.response_code == 0) {
                    $("#save_facility").html('<i class="fa fa-save"></i> Save Record');
                    alert(re.response_message);
                    document.getElementById("form1").reset();
                    do_filter();
                    setTimeout(function() {
                        $("#defaultModal").modal("hide");
                    }, 3000);
                } else if (re.response_code == 89) {
                    $("#save_facility").html('<i class="fa fa-save"></i> Save Record');
                    alert(re.response_message);
                    document.getElementById("form1").reset();
                    do_filter();
                    setTimeout(function() {
                        myLoadModal(
                            "setup/permission_setup.php?op=edit&id=" + re.id,
                            "modal_div"
                        );
                    }, 3000);
                } else {

                    alert(re.response_message);
                }
            },
            error: function(re) {

                $("#save_facility").text("Save Record");
                $.unblockUI();

                alert("Records could not be saved due to unknown error");
            },
        });
    }

</script>