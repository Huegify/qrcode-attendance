<?php
$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_segments = explode('/', $uri_path);
$ccc = count($uri_segments) - 1;
$uri_segment = ucfirst($uri_segments[$ccc]);
$uri_segment = basename($uri_segment, '.php');

session_start();
?>

<!-- content -->
<div class="container-fluid content-top-gap">

    <!-- breadcrumbs -->
    <div class="row col-12">
        <div class="col-8">
            <h3><a style="color: forestgreen !important;" href="dashboard"><?php echo str_replace('_', ' ', $uri_segment) ?></a></h3>
        </div>

    </div>

    <section class="forms">
        <!-- forms 1 -->
        <div class="card card_border py-2 mb-4">
            <div class="card-body col-lg-12">
                <div class="row" style="margin-bottom:30px">
                    <div class="card-header col-lg-12" style="border-bottom: 1px solid #228b22 !important">
                        <div class="row ">
                            <div class="col-lg-6">
                                <h5 class="card-title mb-0">List of available operations</h5>
                            </div>
                            <div class="col-lg-6 text-right">
                                <button id="save_facility9" onclick="getpage('setup/permission_map.php', 'page')" name="subbtn" class="btn btn-warning"><i class=" fa fa-gear"></i> Map Permission </button>
                                &nbsp;
                                <button id="save_facility10" onclick="myLoadModal('setup/permission_setup.php', 'modal_div')" name="subbtn" class="btn btn-primary"><i class=" fa fa-plus"></i> Add Permission </button>
                            </div>
                        </div>
                    </div>

                </div>
                <table class="table table-responsive table-hover table-striped table-bordered pull-left col-lg-12" id="datatables-ajax" style="width: 100%;white-space: nowrap;">
                    <thead>
                        <tr>
                            <th>Sn</th>
                            <th>Operation</th>
                            <th>Label</th>
                            <th>Operation Type</th>
                            <th>Description</th>
                            <th>Posted By</th>
                            <th>Posted IP</th>
                            <th>Status</th>
                            <th>Added On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </section>

</div>

<div class="modal fade" id="defaultModal" tabindex="-1" role="dialog" aria-hidden="true">

    <div class="modal-dialog" role="document">
        <div class="modal-content" id="modal_div">

        </div>
    </div>
</div>

<!-- //content -->
<script type="text/javascript">
    var table;
    var editor;
    var op = "Permissions.permission_list";
    $(document).ready(function() {
        table = $("#datatables-ajax").DataTable({
            processing: true,
            scrollX: true,
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
                url: "helper.php",
                type: "POST",
                data: function(d, l) {
                    d.op = op;
                    d.li = Math.random();
                    d.filter = $("#filter").val();
                    //          d.end_date = $("#end_date").val();
                }
            }
        });
    });


    function closeModal() {
        $("#defaultModal").modal("hide");
    }

    function do_filter() {
        table.draw();
    }

    $(document).ready(function() {
        /*delete script begins here*/
        $(document).on("click", ".delete", function(e) {
            var ref = $(this).data("id"),
                title = $(this).data("title"),
                operation = "Permissions.deletePermission",
                icons;
               var conf = confirm("Are you sure you want to delete " + title + " ?");

            if (conf == true) {
                //delete product
                $.ajax({
                    type: "post",
                    url: "helper.php",
                    data: {
                        id: ref,
                        op: operation,
                    },
                    dataType: "json",
                    beforeSend: function() {
                        $(".delete-icon" + ref).hide();
                        $(".deleting-icon" + ref).show();
                    },
                    success: function(rel) {
                        if (rel.response_code == 0) {
                            $(this).parent("td").remove();
                            $(".delete-icon" + ref).hide();
                            $(".deleting-icon" + ref).hide();
                            $(this).attr("disabled", true).html("deleted");
                            alert(rel.response_message);
                            getpage("permissions_list.php", "page");
                        } else {
                            $(".delete-icon" + ref).show();
                            $(".deleting-icon" + ref).hide();
                            alert(rel.response_message);
                        }
                    },
                    error: function() {
                        $(".delete-icon" + ref).show();
                        $(".deleting-icon" + ref).hide();
                        alert("Something went wrong...");
                    },
                });
            } else {}

        });
    });
</script>