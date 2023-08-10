<?php
include_once("../model/model.php");
$model = new Model();

// $sql_ch_type = "SELECT id,name FROM church_type";
// $church_type = $model->runQuery($sql_ch_type);

$sql_role = "SELECT * FROM role WHERE role_id order by role_name";
//$sql_role = "SELECT * FROM role";

$roles = $model->runQuery($sql_role);


if (isset($_REQUEST['op']) && $_REQUEST['op'] == 'edit') {
    $operation  = 'edit';
    $id  = $_REQUEST['id'];
    // $sql_church = "SELECT * FROM church_type WHERE id = '$id'";
    // $church     = $model->runQuery($sql_church);
} else {
    $operation = 'new';
}
?>

<script>
    doOnLoad();
    var myCalendar;

    function doOnLoad() {
        myCalendar = new dhtmlXCalendarObject(["start_date"]);
        myCalendar.hideTime();
    }
</script>
<link rel="stylesheet" href="../css/jkanban.min.css" integrity="<?php echo $model->integrityHash('../css/jkanban.min.css') ?>" crossorigin="<?php echo $crossorigin ?>">



<input type="hidden" name="op" value="Church.saveChurchType">
<input type="hidden" name="operation" value="<?php echo $operation; ?>">
<input type="hidden" name="id" value="<?php echo $id; ?>">
<div class="modal-header">
    <h4 class="modal-title" style="font-weight:bold">Role Mapping</h4>
    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>
<div class="modal-body m-3 ">
    <div class="row">
        <div class="col-sm-4">
            <div class="card flex-fill">
                <div class="card-header">
                    <h5 class="card-title mb-0">User Roles</h5>
                </div>
                <div class="card-body d-flex">
                    <select name="roles" id="role_id" onchange="loadMenus(this.value)" class="form-control">
                        <option value="">:: SELECT ROLE ::</option>
                        <?php
                        foreach ($roles as $row) {
                            echo "<option value='" . $row['role_id'] . "'>" . $row['role_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <?php include("form-footer.php"); ?>
            <button id="save_facility" onclick="saveRecord()" class="btn btn-primary btn-block">Save</button>
        </div>

        <div class="col-sm-4">
            <div class="card flex-fill">
                <div class="card-header">
                    <h5 class="card-title mb-0">Visible Menus </h5>
                    <!--              <small class="pull-right">(Super Administrator)</small>-->
                </div>
                <div class="card-body d-flex">
                    <form action="" id="form1" style="width:100%">
                        <div id="div1" class="form-group" ondrop="drop(event)" ondragover="allowDrop(event)">

                        </div>
                    </form>
                </div>
            </div>

        </div>
        <div class="col-sm-4">
            <div class="card flex-fill">
                <div class="card-header">
                    <h5 class="card-title mb-0">Invisible Menus</h5>
                </div>
                <div class="card-body d-flex">
                    <form action="" style="width:100%">
                        <div id="div2" ondrop="drop(event)" ondragover="allowDrop(event)">

                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    #div1 {
        width: 100%;
        height: 300px;
        margin: 10px;
        padding: 10px;
        overflow-y: scroll;
        border: 1px solid black;
    }

    #div2 {
        width: 100%;
        height: 300px;
        margin: 10px;
        overflow-y: scroll;
        padding: 10px;
        border: 1px solid black;
    }

    #div1 .form-group {
        background: #306450;
        color: #fff;
        padding: 10px
    }

    #div2 .form-group {
        background: #f44455;
        color: #fff;
        padding: 10px
    }
    .single-role{
        margin-bottom: 8px;
    }
</style>

<script>
    function saveRecord() {
        var role = $("#role_id").val();
        if (role != "") {
            $("#save_facility").text("Loading......");
            var dd = $("#form1").serialize();
            console.log(dd);

            $.post("helper.php?op=Menu.saveMenuGroup&role_id=" + $("#role_id").val(), dd, function(re) {
                $("#save_facility").text("Save");
                console.log(re);
                if (re.response_code == 0)
                    alert(re.response_message)
                else
                    alert(re.response_message)
            }, 'json')
        } else {
            alert("Please select a menu");
        }

    }

    function loadMenus(el) {
        //        $.blockUI();
        $.post('helper.php', {
            op: 'Menu.loadmenus',
            role_id: el
        }, function(res) {
            //            $.unblockUI();
            $('#div1').html(res.data.visible);
            $('#div2').html(res.data.invisible);
        }, 'json');
    }
</script>
<script>
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