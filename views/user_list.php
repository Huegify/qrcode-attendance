<?php
include_once("../model/model.php");
//var_dump($_SESSION);
?>
<style>

</style>
<div class="card">
    <div class="card-header">
        <h5 class="card-title">User List</h5>
        <h6 class="card-subtitle text-muted">The report contains Users that have been setup in the system.</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <?php
            if ($_SESSION['role_id_sess'] == 100 || $_SESSION['role_id_sess'] == 200 || $_SESSION['role_id_sess'] == 202) {
            ?>
                <div class="col-sm-2">
                    <a class="btn btn-warning" href="javascript:getpage('setup/user_setup.php','page')">Create User</a>
                </div>
            <?php } ?>
        </div>

        <div id="datatables-basic_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
            <div class="row">
                <div class="col-sm-3">
                    <label for=""></label>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12 table-responsive">
                    <table id="page_list" class="table table-striped" style="width:100%; white-space: nowrap;">

                        <thead style="white-space: nowrap;">
                            <tr role="row">
                                <th>S/N</th>
                                <th>Username</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Phone Number</th>
                                <th>Role</th>
                                <th>Email</th>
                                <th>Password Missed Count</th>
                                <th>Login Status</th>
                                <th>Created</th>
                                <th style="width:500px">Action</th>
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
    var table;
    var editor;
    var op = "Users.userlist";
    $(document).ready(function() {
        table = $("#page_list").DataTable({
            processing: true,
            columnDefs: [{
                    orderable: false,
                    targets: 0
                },
                {
                    width: "3100",
                    targets: "3"
                }
            ],
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

                }
            }
        });
    });

    function do_filter() {
        table.draw();
    }

    function trigUser(user, status) {
        var r_status = (status == 1) ? "Enable this user" : "Disable this user";
        var cnf = confirm("Are you sure you want to " + r_status + " this user ?");
        if (cnf) {
            $.blockUI();
            $.post('helper', {
                op: 'Users.changeUserStatus',
                current_status: status,
                username: user
            }, function(resp) {
                $.unblockUI();
                if (resp.response_code == 0) {
                    //                           alert(resp.response_message);
                    getpage('views/user_list', 'page');
                }

            }, 'json')
        }
    }

    function sackUser(username_1, status_1) {
        let tt = confirm("Are you sure you want to perform this action");
        if (tt) {
            $.post("helper", {
                op: "Users.sackUser",
                username: username_1,
                status: status_1
            }, function(rr) {
                alert(rr.response_message);
                getpage('views/user_list', 'page');
            }, 'json');
        }
    }

    function resetPassword(d) {
        let tt = confirm("Are you sure you want to perform this action");
        if (tt) {
            $.post("helper", {
                op: "Users.generatePwdLink",
                username: d
            }, function(rr) {
                alert(rr.response_message);
                getpage('views/user_list', 'page');
            }, 'json');
        }
    }

    function getModal(url, div) {
        $('#' + div).html("<h2>Loading....</h2>");
        //        $('#'+div).block({ message: null });
        $.post(url, {}, function(re) {
            $('#' + div).html(re);
        })
    }
</script>