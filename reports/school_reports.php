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
<div class="card">
    <div class="card-header">
        <h5 class="card-title">School Transaction List</h5>
        <h6 class="card-subtitle text-muted">The report contains all the payment histories.</h6>
    </div>
    <div class="card-body">
        <!-- <div class="row">
            <div class="col-sm-4">
                <fieldset class="form-group">
                    <legend style="color:red; font-weight:bold">Filter Options</legend>
                    <label for="school_filter">
                        <input type="radio" onclick="hide_div(this)" id="school_filter" name="filter" checked value="branch">&nbsp;School Name
                    </label>&nbsp;&nbsp;&nbsp;&nbsp;
                    <label for="c_type_filter">
                        <input type="radio" id="c_type_filter" onclick="hide_div(this)" name="filter" value="school_type">&nbsp;School Type
                    </label>
                    <input type="hidden" id="filter" value="branch">
                </fieldset>
            </div>
        </div> -->

        <div class="row py-3 mb-3">
            <div class="col-sm-2">
                <label for="state_id">State:</label>
                <select onchange="schoolByState(this.value)" name="state_id" id="state_id" class="form-control">
                    <option value="">:: ALL STATE ::</option>
                    <?php foreach ($states as $row) {
                        echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                    } ?>
                </select>
            </div>
            <div class="col-sm-2" id="churches_div">
                <label for="school_id" style="color:blue">School Name:</label>
                <select id="school_id" name="school_id" class="form-control">
                    <option value="">:: ALL Schools ::</option>
                    <?php foreach ($schools as $row) {
                        echo "<option value='" . $row['school_id'] . "'>" . $row['school_display_name'] . "</option>";
                    } ?>
                </select>
            </div>

            <div class="col-sm-2" id="region_div" style="display:none">
                <label for="category_id" style="color:blue">School Type:</label>
                <select id="category_id" class="form-control">
                    <option value="">:: ALL School Types ::</option>
                    <?php foreach ($school_type as $row) {
                        echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                    } ?>
                </select>
            </div>

            <div class="col-sm-2">
                <label for="term">Academic Term:</label>
                <select id="term" name="term" class="form-control">
                    <option value="">:: ALL Terms ::</option>
                    <option value="1">First Term</option>
                    <option value="2">Second Term</option>
                    <option value="3">Third Term</option>
                </select>
            </div>

            <div class="col-sm-2">
                <label for="class_id">Class:</label>
                <select id="class_id" name="class_id" class="form-control">
                    <option value="">:: ALL Classes ::</option>
                    <?php foreach ($classes as $row) {
                        echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                    } ?>
                </select>
            </div>

            <div class="col-sm-2">
                <label for="status">Payment Status:</label>
                <select id="status" name="status" class="form-control">
                    <option value="">:: ALL Status ::</option>
                    <option value="0">Successful Payments</option>
                    <option value="99">Pending Payments</option>
                    <option value="000">Failed Payments</option>
                </select>
            </div>

            <div class="col-sm-2">
                <label for="search">&nbsp;</label>
                <button onclick="do_filter()" id="search" class="btn btn-info btn-block">Search</button>
            </div>
        </div>
        <div id="datatables-basic_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
            <div class="row">
                <div class="col-sm-12 table-responsive">
                    <table id="page_list" class="table table-striped " style="white-space: nowrap;">
                        <thead>
                            <tr role="row">
                                <th>S/N</th>
                                <th>Admission No</th>
                                <th>School Name</th>
                                <th>Bank Name</th>
                                <th>Account Name</th>
                                <th>Amount Paid</th>
                                <th>Payment ID</th>
                                <th>Description</th>
                                <th>Status</th>
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
<!--<script src="../js/sweet_alerts.js"></script>-->
<!--<script src="../js/jquery.blockUI.js"></script>-->
<script>
    $("select").select2();
    var table, op = "Transaction.schoolTransactions";
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

    function hide_div(el) {
        if (el.id == "school_filter") {
            $("#churches_div").show();
            $("#region_div").hide();
            $("#filter").val(el.value);
            $("#category_id").val('');
        } else {
            $("#churches_div").hide();
            $("#region_div").show();
            $("#filter").val(el.value);
            $("#school_id").val('');
        }
    }

    function getModal(url, div) {
        $('#' + div).html("<h2>Loading....</h2>");
        $.post(url, {}, function(re) {
            $('#' + div).html(re);
        })
    }

    $("select#category_id").change(function(){
        var selected = $(this).children("option:selected").val();
        getClass(selected);

    })
    function getClass(d) {
        var payload = {
            op: 'Payment.getSchoolDetails',
            id: d,
            type: 'class'
        };

        $.ajax({
            url: 'helper',
            type: 'post',
            data: payload,
            dataType: 'json',
            success: function (data) {
                console.log(data);
                $("#class_id").attr('disabled', false).html('<option selected value="">::: Select Class :::</option>');
                
                for (var i = 0; i < data.option_value.length; i++) {
                    $("#class_id").append('<option value="' + data.option_id[i] + '">' + data.option_value[i] + '</option>');
                }
            
            }
        })

    }

    function schoolByState(el) {
        do_filter();
        $("#school_id").val('');
        $.ajax({
            url: 'helper',
            type: 'post',
            data: {
                op: 'Payment.getSchoolDetails',
                id: el,
                type: 'category'
            },
            dataType: 'json',
            success: function(data) {
                // console.log(data);
                $("#school_id").attr('disabled', false).html('<option selected value="">::: Select School :::</option>');
                if (data.school != "") {

                    for (var i = 0; i < data.school.option_value.length; i++) {
                        $("#school_id").append('<option value="' + data.school.option_id[i] + '">' + data.school.option_value[i] + '</option>');
                    }
                }
            }
        })

    }
</script>