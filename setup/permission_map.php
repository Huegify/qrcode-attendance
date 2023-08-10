<?php
require_once('../model/model.php');
require_once('../class/permissions.php');

$model = new Model();
$role_id = "";
$sql = "SELECT role_id, role_name FROM role";
$user_roles = $model->runQuery($sql);

$operation = 'new';

$menu = new Permissions();
$actions = $menu->loadActions("");
?>
<!-- content -->
<div class="container-fluid content-top-gap">
    <!-- breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb my-breadcrumb">
            <li class="breadcrumb-item active" aria-current="page"><?php echo ucfirst($operation . ' Permission map') ?></li>
        </ol>
    </nav>
    <div class="alert alert-primary alert-outline alert-dismissible" role="alert">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <div class="alert-icon">
            <i class="far fa-fw fa-bell"></i>
        </div>
        <div class="alert-message">
            <strong>After selecting a <q>Role Name</q>, </strong>
            <ul>
                <li>Drag a menu from the Invisible permissions section to the Visible permissions section to assign menus to a Role. And vice versa</li>
            </ul>
        </div>
    </div>
</div>
<section class="forms">
    <!-- forms 1 -->
    <div class="col-lg-12 col-md-12 col-sm-12 py-2 mb-4">
        <div class="col-lg-2 col-md-2 col-sm-12 pull-left"></div>
        <div class="card card_border  card-body col-lg-12" style="border: 0px solid red;">

            <div class="col-lg-12 col-md-12 col-sm-12 pb-3">
                <div class="row">
                    <div class="form-group col-lg-5 col-md-12 col-sm-12">
                        <label for="roles" class="input_label">Role Name</label>
                        <select name="roles" id="roles" class="form-control">
                            <option value="">::Select User Role::</option>
                            <?php foreach ($user_roles as $row) { ?>
                                <option value="<?php echo $row["role_id"]; ?>"><?php echo $row["role_name"]; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="row py-3">

                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="card flex-fill">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Invisible Permissions</h5>
                                </div>
                                <div class="card-body d-flex">
                                    <form class="col-lg-12">
                                        <div id="invisible_menus" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="card flex-fill">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Visible Permissions </h5>
                                </div>
                                <div class="card-body d-flex">
                                    <form action="javascript:void(0)" id="add_menu_group" class="col-lg-12">
                                        <div id="visible_menu" class="form-group" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
                                        <input type="hidden" name="role_id" id="role_id">
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php include("form-footer.php"); ?>
                        <div class="row py-2">
                            <div class="form-group col-lg-5 col-md-5 col-sm-12">
                                <button class="btn btn-primary btn-style mt-4 save"><span class="fa fa-save save-icon"></span><span class="fa fa-spin fa-spinner saving-icon" style="display: none;"></span>
                                    <o class="save-text"> Save</o>
                                </button>
                            </div>
                            <div class="form-group col-lg-7 col-md-7 col-sm-12 msg"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- //forms -->
<style>
    #visible_menu {
        width: 100%;
        height: 300px;
        margin: 10px;
        padding: 10px;
        overflow-y: scroll;
        border: 1px solid black;
    }

    #invisible_menus {
        width: 100%;
        height: 300px;
        margin: 10px;
        overflow-y: scroll;
        padding: 10px;
        border: 1px solid black;
    }

    #visible_menu .form-group {
        background: lightgreen;
        color: #fff;
        padding: 10px
    }

    #invisible_menus .form-group {
        background: crimson;
        color: #fff;
        padding: 10px
    }

    .single-role {
        margin-bottom: 8px;
    }
</style>
</div>
<!-- //content -->

<script type="text/javascript">
    $(document).ready(function() {
        $(".saving-icon").hide();

        $(".save").click(function(e) {
            e.preventDefault();

            $(".saving-icon").show();
            $(".save-icon").hide();
            $(".save-text").html(" processing...");

            var role = $("#role_id").val();
            if (role != "") {
                var form = $("#add_menu_group").serialize();

                $.post(
                    "helper.php?op=Permissions.saveActions&role_id=" + $("#role_id").val(),
                    form,
                    function(data) {
                        $("#save_facility").text("Save");
                        // console.log(data);
                        if (data.response_code == 0) {
                            $(".saving-icon").hide();
                            $(".save-icon").hide();
                            $(".save-text").html('<span class="fa fa-save"></span> Saved');
                            $(".msg")
                                .html(
                                    '<div class="alert alert-success alert-outline alert-dismissible" role="alert"><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button><div class="alert-icon"><i class="far fa-fw fa-bell"></i></div><div class="alert-message"><strong>Alert!' +
                                    "\n" +
                                    "</strong>" +
                                    data.response_message +
                                    "</div></div>"
                                )
                                .delay("3000")
                                .fadeOut();
                            setTimeout(function() {
                                getpage("setup/permission_map.php", "page");
                            }, 3000);
                        } else {
                            $(".msg")
                                .html(
                                    '<div class="alert alert-warning alert-outline alert-dismissible" role="alert"><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button><div class="alert-icon"><i class="far fa-fw fa-bell"></i></div><div class="alert-message"><strong>Alert!' +
                                    "\n" +
                                    "</strong>" +
                                    data.response_message +
                                    "</div></div>"
                                )
                                .delay("3000")
                                .fadeOut();

                            $(".saving-icon").hide();
                            $(".save-icon").show();
                            $(".save-text").html(
                                '<span class="fa fa-save save-icon"></span> Save'
                            );
                        }
                    },
                    "json"
                );
            } else {
                alert("Please, select a role");
            }
        });

        $("#icon").keyup(function() {
            var icon = $(this).val();
            $(".display-icon")
                .html("<i class='fa " + icon + "'></i>")
                .css("font-size", "20px");
        });
    });

    $("select#roles").change(function() {
        var selected = $(this).children("option:selected").val();
        $("#role_id").attr("value", selected);

        $.ajax({
            url: "helper.php",
            type: "post",
            data: {
                op: "Permissions.loadPermissions",
                role_id: selected,
            },
            dataType: "json",
            success: function(result) {
                $("#visible_menu").html(result.data.visible);
                $("#invisible_menus").html(result.data.invisible);
            },
            error: function() {
                swal("Error!", "An error occured!", "error");
            },
        });
    });

    function allowDrop(ev) {
        ev.preventDefault();
    }

    function drag(ev) {
        ev.dataTransfer.setData("text", ev.target.id);
    }

    function drop(ev) {
        ev.preventDefault();
        var data = ev.dataTransfer.getData("text");
        ev.target.appendChild(document.getElementById(data));
    }
</script>