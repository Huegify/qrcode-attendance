<?php
include_once("../model/model.php");
$ip = $_SERVER['REMOTE_ADDR'];
if ($ip == 'localhost' or $ip == '::1') :
	$root = $_SERVER['DOCUMENT_ROOT'] . '/attendance/';
	$base_url = 'http://localhost/attendance/';
else :
	$root = $_SERVER['DOCUMENT_ROOT'].'/';
	$base_url = 'http://' . $_SERVER['SERVER_NAME'] . '/'; //or you hardcode your domain name here
endif;

?>


<div class="modal-header">
    <h4 class="modal-title" style="font-weight:bold">Upload Staff File (CSV)</h4>
    <a href="<?php echo $base_url?>uploads/records/staff_record_file_upload_format.csv" class="text-end" download="">Download Format</a>
    <button type="button" class="close text-end" data-bs-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>

</div>
<div class="modal-body m-3 ">
    <form id="form1" onsubmit="return false" autocomplete="off">
        <input type="hidden" name="op" value="Setup.uploadPatient">
        <div class="row py-3">
            <div class="col-lg-12 col-md-12 col-sm-12 py-3">
                <aks-file-upload></aks-file-upload>
                <!-- <p id="uploadfile" type="json"></p> -->
            </div>
        </div>
       
        <?php include("form-footer.php"); ?>
       
       <div id="err"></div>
        <button id="save_facility" onclick="saveRecord()" class="btn btn-primary mb-1">Upload</button>
        
    </form>
</div>
<script>
    $(function () {
		$("aks-file-upload").aksFileUpload({
			fileUpload: "#uploadfile",
			dragDrop: true,
			maxSize: "1 MB",
			multiple: false,
			maxFile: 1
		});
		
	});

    function saveRecord()
    {
        var upload = new FormData();
        $.each(jQuery('#form1 input[type=file]'), function(i, value){
            upload.append('aksfileupload['+i+']', value.files[0]);
        });
        
        $("#save_facility").text("Processing...");

        $.ajax({
            url: 'helper?'+$("#form1").serialize(),
            type: 'post',
            data: upload,
            dataType: 'json',
            processData: false,
            contentType: false,
            cache: false,
            success: function(data){
                $("#save_facility").text("Upload");
                if(data.response_code == 0) {
                    $("#err").css('color','green')
                    $("#err").html(data.response_message).fadeOut(2000)
                    getpage('views/staff_list','page');
                    
                }else {
                    $("#err").css('color','red')
                    $("#err").html(data.response_message).fadeOut(2000)
                    $("#warning").val("0");
                }
            }, 
            error:function(jqxhr, textStatus){
                $("#err").css('color','red')
                $("#err").html('Something went wrong... '+textStatus);
                $("#warning").val("0"); 
            }
        })

        
    }

</script>