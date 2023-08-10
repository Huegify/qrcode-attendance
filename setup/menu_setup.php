<?php
include_once("../model/model.php");
include_once("../class/menu.php");
$model = new Model();
//$sql = "SELECT DISTINCT(State) as state,stateid FROM lga order by State";
//$states = $model->runQuery($sql);
//
//$sql2 = "SELECT bank_code,bank_name FROM banks WHERE bank_type = 'commercial' order by bank_name";
//$banks = $model->runQuery($sql2);
//
//$sql_pastor = "SELECT username,firstname,lastname FROM userdata WHERE role_id = '003'";
//$pastors = $model->runQuery($sql_pastor);

$sql = "SELECT * FROM font_awsome ORDER BY code ";
$fonts = $model->runQuery($sql);

$menu = "";
if(isset($_REQUEST['op']) && $_REQUEST['op'] == 'edit')
{
    $operation = 'edit';
    $menu_id = $_REQUEST['menu_id'];
    $sql_menu = "SELECT * FROM menu WHERE menu_id = '$menu_id' LIMIT 1";
    $menu = $model->runQuery($sql_menu);
}else
{
    $operation = 'new';
}
?>

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
<script>
    doOnLoad();
    var myCalendar;
function doOnLoad()
{
   myCalendar = new dhtmlXCalendarObject(["start_date"]);
    myCalendar.setSensitiveRange(null, "<?php echo date('Y-m-d') ?>");
   myCalendar.hideTime();
}
</script>
<div class="modal-header">
    <h4 class="modal-title" style="font-weight:bold">Menu Setup</h4>
    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>
<div class="modal-body m-3 ">
    <form id="form1" onsubmit="return false" autocomplete="off">
       <input type="hidden" name="op" value="Menu.saveMenu">
       <input type="hidden" name="operation" value="<?php echo $operation; ?>">
       <input type="hidden" name="id" value="<?php echo $menu_id; ?>">
       <div class="row">
           <div class="col-sm-6">
               <div class="form-group">
                    <label class="form-label">Menu Name</label>
                    <input type="text" autocomplete="off" name="menu_name" onkeyup="validateCode(this.value)" value="<?php echo ( $operation == 'edit') ? $menu[0]['menu_name'] : ""; ?>"  class="form-control" autocomplete="off" />
                </div>
           </div>
           <div class="col-sm-6">
               <div class="form-group">
                    <label class="form-label">Menu URL</label>
                    <input type="text" name="menu_url" class="form-control" value="<?php echo ( $operation == 'edit') ? $menu[0]['menu_url'] : ""; ?>" placeholder="" autocomplete="off">
                </div>
           </div>
           
       </div>
        <?php
            $rr = new menu();
            $p = $rr->loadParentMenu("");
        ?>
         <div class="row">
            <div class="col-sm-6">
               <div class="form-group">
                    <label class="form-label">Set Parent Menu</label>
                    <select name="parent_id" class="form-control">
                       <option hidden value="">Select a parent menu</option>
                       <option value="#" <?php echo (isset($menu[0]['parent_id']) && $menu[0]['parent_id'] == "#")?"selected":""; ?> >:: This menu is a parent menu::</option>
                       <?php
                        
                        if($p['response_code'] == "0")
                        {
                            foreach($p['data'] as $key)
                            {
                                $selected = ($key[0] == $menu[0]['parent_id'])?"selected":"";
                        ?>
                                <option <?php echo $selected; ?> value="<?php echo $key[0]; ?>"><?php echo $key[1]; ?></option>
                        <?php
                            }
                        }
                        ?>
                       
                    </select>
                </div>
           </div> 
           <div class="col-sm-6 py-2">
                  <label for="">Menu icon</label>
                  <select name="icon" onchange="display_icon(this.value)" id="icon" class="form-control">
                       <option value="">::SELECT ICON::</option>
                       <?php
                            foreach($fonts as $row)
                            {
                                $selected = ($cat[0]['icon'] == $row['code'])?"selected":"";
                                echo "<option $selected value='".$row['code']."'>".str_replace("bi bi-","",$row['code'])."</option>";
                            }
                        ?>
                   </select>
                
           </div>
        </div>
        <div class="row mb-3">
            <div class="col-sm-6">
                <div id="icon-display" align="center" style="font-size:20px">
                        <?php echo "<i class='bi ".$fonts[0]['code']."'></i>"; ?>
                    </div>
            </div>
        </div>
        
        <?php include("form-footer.php"); ?>
       
       <div id="err"></div>
        <button id="save_facility" onclick="saveRecord()" class="btn btn-primary mb-1">Submit</button>
        
    </form>
</div>
<script>
    function saveRecord()
    {
        $("#save_facility").text("Loading......");
        var dd = $("#form1").serialize();
        $.post("helper",dd,function(re)
        {
            $("#save_facility").text("Save");
            console.log(re);
            if(re.response_code == 0)
                {
                    
                    $("#err").css('color','green')
                    $("#err").html(re.response_message)
                    getpage('views/menu_list.php','page');
                    
                }
            else
                {
                    regenerateCORS();
                     $("#err").css('color','red')
                    $("#err").html(re.response_message)
                    $("#warning").val("0");
                }
                
        },'json')
    }
    
//    function automatic()
//    {
//        if($("#auto").is(':checked'))
//        {
//            $("#auto_val").val(1)
//        }else{
//             $("#auto_val").val(0)
//        }
//    }
//    
    function fetchLga(el)
    {
        getRegions(el);
        $("#lga-fds").html("<option>Loading Lga</option>");
        $.post("helper.php",{op:'Church.getLga',state:el},function(re){
            $("#lga-fds").empty();
            $("#lga-fds").html(re.state);
            
        },'json');
//        $.blockUI();
    }
    function getRegions(state_id)
    {
        $("#church_region_select").html("<option>Loading....</option>");
        $.post("helper.php",{op:'Church.getRegions',state:state_id},function(re){
            $("#church_region_select").empty();
            $("#church_region_select").html(re);
            
        });
    }
    
    function fetchAccName(acc_no)
    {
        if(acc_no.length == 10)
            {
                var account  = acc_no;
                var bnk_code = $("#bank_name").val();
                $("#acc_name").text("Verifying account number....");
                $("#account_name").val("");
                $.post("helper.php",{op:"Church.getAccountName",account_no:account,bank_code:bnk_code},function(res){
                    
                    $("#acc_name").text(res);
                    $("#account_name").val(res);
                });
            }else{
                $("#acc_name").text("Account Number must be 10 digits");
            }
        
    }
    function display_icon(ee)
    {
        $("#icon-display").html(`<i class="${ee}"></i>`);
    }
</script>