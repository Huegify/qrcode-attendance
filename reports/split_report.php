<?php
include_once("../model/model.php");
$model = new Model();


$sql = "SELECT id, stakeholder_name AS name FROM lfs_stakeholder_split_table";
$stakes = $model->runQuery($sql);
$types = array('SCHOOL','STAKEHOLDER');
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
        <h5 class="card-title">Split Transactions</h5>
        <h6 class="card-subtitle text-muted">The report contains all the transaction split log.</h6>
    </div>
    <div class="card-body">

        <div class="row py-2 mb-3">
            <div class="col-sm-2">
                <label for="stakeholder_id">Beneficiary:</label>
                <select onchange="schoolByState(this.value)" name="stakeholder_id" id="stakeholder_id" class="form-control">
                    <option value="">:: ALL BENEFICIARIES ::</option>
                    <?php foreach ($stakes as $row) {
                        echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                    } ?>
                </select>
            </div>
            <div class="col-sm-2" id="churches_div">
                <label for="type_id" >Split Type:</label>
                <select id="type_id" name="type_id" class="form-control">
                    <option value="">:: ALL TYPES ::</option>
                    <?php foreach ($types as $row) {
                        echo "<option value='" . $row . "'>" . $row. "</option>";
                    } ?>
                </select>
            </div>
            <div class="col-sm-2">
                <label for="type_id" >Transaction Id:</label>
                <input type="text" name="transaction_id" id="transaction_id" class="form-control" />
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
                                <th>Entity Name</th>
                                <th>Entity Type</th>
                                <th>Amount Paid By Student</th>
                                <th>Percentage</th>
                                <th>Amount Received</th>
                                <th>Transaction Id</th>
                                <th>Action</th>
                                <th>Created</th>
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
    var table, op = "Transaction.splitTransactions";
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
                    d.stakeholder_id = $("#stakeholder_id").val();
                    d.type_id = $("#type_id").val();
                    d.transaction_id = $("#transaction_id").val();
                }
            }
        });
    });

    function do_filter() {
        table.draw();
    }

    function getModal(url, div) {
        $('#' + div).html("<h2>Loading....</h2>");
        $.post(url, {}, function(re) {
            $('#' + div).html(re);
        })
    }

    
</script>