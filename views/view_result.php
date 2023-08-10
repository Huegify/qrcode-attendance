<?php
include_once("../model/model.php");
include_once("../class/setup.php");
$model = new Model();
$stmt = new Setup();

?>
<link href="../css/select2.min.css" rel="stylesheet" integrity="<?php echo $model->integrityHash('css/select2.min.css')?>" crossorigin="<?php echo $crossorigin?>">

<script src="../js/select2.js" integrity="<?php echo $model->integrityHash('js/select2.min.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>


<div class="modal-header">
    <h4 class="modal-title" style="font-weight:bold">RESULT </h4>
</div>
<div class="modal-body m-3 ">
    <div class="row bg-white">
        <!-- <div class="col-2"></div> -->
        <div class="col-lg-12" id="result"><?php echo $stmt->generateResult($_REQUEST)?></div>
        <!-- <div class="col-2"></div> -->
        <!-- <div class="col-lg-12">
            <div class="col-3">
                <img src="img/img.jpg" class="img-fluid img-response" style="height: 100px !important;"/>
            </div>
            <div class="col-9">
                <h5></h5>
            </div>
        </div> -->

        <!-- <object data="https://media.geeksforgeeks.org/wp-content/cdn-uploads/20210101201653/PDF.pdf" width="800" height="500"></object>  -->
        <a href="javascript:void(0)" class="btn btn-primary btn-sm" onclick="printDiv('result')">Print Result</a>
    </div>
    <hr />
</div>
<script>
    function printElem(e){
        var mywindow = window.open('', 'PRINT', 'height=400, width=600');
        mywindow.document.write('<html><head><title>'+document.title+'</title>');
        mywindow.document.write('</head><body>');
        mywindow.document.write('</h1>'+document.title+'</h1>');
        mywindow.document.write(document.getElementById(e).innerHTML);
        mywindow.document.write('</body></html>');
        mywindow.document.close();
        mywindow.focus();
        mywindow.print();
        mywindow.close();

        return true;
    }
</script>