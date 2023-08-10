<?php
include_once("../model/model.php");
$model = new Model();

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
        <h5 class="card-title">Attendance List</h5>
        <h6 class="card-subtitle text-muted">The report contains the list of Staff Attendance.</h6>
    </div>
    <div class="card-body">

        <div id="datatables-basic_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
            <div class="row">
                <div class="col-sm-12 table-responsive">
                    <table id="page_list" class="table table-striped " style="white-space: nowrap;">
                        <thead>
                            <tr role="row">
                                <th>S/N</th>
                                <th>Staff ID</th>
                                <th>Staff Name.</th>
                                <th>Day Taken</th>
                                <th>Week Taken</th>
                                <th>Time</th>
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
    var table, op = "Setup.attendanceList";
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
   
</script>