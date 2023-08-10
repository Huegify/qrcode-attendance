<?php
include_once("../model/model.php");
$model = new Model();

$sql = "SELECT DISTINCT(State) as name,stateid AS id FROM states  order by State ASC";
$states = $model->runQuery($sql);

$sql = "SELECT * FROM lfs_schools";
$schools = $model->runQuery($sql);

$sql = "SELECT category_id AS id, category_display_name AS name FROM school_categories";
$school_type = $model->runQuery($sql);

$sql = "SELECT class_id AS id, class_display_name AS name FROM lfs_class_setup";
$classes = $model->runQuery($sql);

?>

<style>
    fieldset {
        display: block;
        margin-left: 2px;
        margin-right: 2px;
        padding-top: 0.35em;
        padding-bottom: 0.625em;
        padding-left: 0.75em;
        padding-right: 0.75em;
        border: 1px solid #ccc;
    }

    legend {
        font-size: 14px;
        padding: 5px;
        font-weight: bold;
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
    <div class="card-header">
        <h5 class="card-title">Staff List</h5>
        <h6 class="card-subtitle text-muted">The report contains the list of Staff members that have been setup in the system.</h6>
    </div>
    <div class="card-body">
        <?php if($_SESSION['role_id_sess'] == 100){?>
        <a class="btn btn-primary mb-4 text-right" onclick="loadModal('setup/staff_csv','modal_div')" href="javascript:void(0)" data-toggle="modal" data-target="#defaultModalPrimary">Upload staff' Record</a>
        <?php }?>
        

        <!-- <div class="row py-3 mb-3">
            <div class="col-sm-2">
                <label for="state_id">State:</label>
                <select onchange="schoolByState(this.value)" name="state_id" id="state_id" class="form-control">
                    <option value="" selected>:: ALL STATE ::</option>
                    <?php foreach ($states as $row) {
                        echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                    } ?>
                </select>
            </div>
            <div class="col-sm-2" id="churches_div">
                <label for="school_id" style="color:blue">School Name:</label>
                <select id="school_id" name="school_id" class="form-control">
                    <option value="" selected>:: ALL Schools ::</option>
                    <?php foreach ($schools as $row) {
                        echo "<option value='" . $row['school_id'] . "'>" . $row['school_display_name'] . "</option>";
                    } ?>
                </select>
            </div>

            <div class="col-sm-2" id="region_div" style="display:block">
                <label for="category_id" style="color:blue">School Type:</label>
                <select id="category_id" class="form-control">
                    <option value="" selected>:: ALL School Types ::</option>
                    <?php foreach ($school_type as $row) {
                        echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                    } ?>
                </select>
            </div>

            <div class="col-sm-2">
                <label for="class_id">Class:</label>
                <select id="class_id" name="class_id" class="form-control">
                    <option value="" selected>:: ALL Classes ::</option>
                    <?php foreach ($classes as $row) {
                        echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                    } ?>
                </select>
            </div>

            <div class="col-sm-2">
                <label for="search">&nbsp;</label>
                <button onclick="do_filter()" id="search" class="btn btn-info btn-block p-2">Search</button>
            </div>
        </div> -->

        <div id="datatables-basic_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
            <div class="row">
                <div class="col-sm-12 table-responsive">
                    <table id="page_list" class="table table-striped " style="white-space: nowrap;">
                        <thead>
                            <tr role="row">
                                <th>S/N</th>
                                <th></th>
                                <th>First Name</th>
                                <th>Middle Name</th>
                                <th>Surname</th>
                                <th>Staff ID</th>
                                <th>Phone Number</th>
                                <th>Email</th>
                                <th>QRCODE</th>
                                <th>Gender</th>
                                <th>Date Of Birth</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $("select").select2();
    var table, op = "Setup.staffList";
    $(document).ready(function() {
        table = $("#page_list").DataTable({
            processing: true,
            columnDefs: [{
                orderable: false,
                targets: 0
            }],
            serverSide: true,
            paging: true,
            oLanguage: {
                sEmptyTable: "No record was found, please try another query"
            },

            ajax: {
                url: "helper",
                type: "POST",
                data: function(d, l) {
                    d.op = op;
                    d.li = Math.random();
                    d.state_id = $("#state_id").val();
                    d.class_id = $("#class_id").val();
                    d.status = $("#status").val();
                    d.term = $("#term").val();
                    d.school_id = $("#school_id").val();
                    d.category_id = $("#category_id").val();
                }
            }
        });
    });

    function do_filter() {
        table.draw();
    }

    function deleteMenu(id) {

        jQuery(function validation() {
            swal({
                title: 'Confirm',
                text: "Are you sure you want to perform this action ?",
                icon: 'warning',
                buttons: true,
                deleteMode: true,
            }).then((willDelete) => {
                if (willDelete) { 
                    $.ajax({
                        url: "helper",
                        data: {op: "Setup.deletePatient", staff_id: id},
                        type: "post",
                        dataType: "json",
                        success: function(re){
                        $.unblockUI();
                        alert(re.response_message);
                        getpage('views/patient_list.php', "page");
                        },
                        error: function(re){
                        $.unblockUI();
                        alert("Request could not be processed at the moment!");
                        }
                    });
                } else {

                }
            });
        })
        
    }
   
</script>