<?php
include_once("../model/model.php");
include_once("../class/menu.php");
$model = new Model();

$stmt = $model->runQuery("SELECT *  FROM lfs_transactions_split WHERE transaction_id='".$_REQUEST['payment_id']."' ");

?>
<link href="../css/select2.min.css" rel="stylesheet" integrity="<?php echo $model->integrityHash('css/select2.min.css')?>" crossorigin="<?php echo $crossorigin?>">

<script src="../js/select2.js" integrity="<?php echo $model->integrityHash('js/select2.min.js') ?>" crossorigin="<?php echo $crossorigin ?>"></script>


<div class="modal-header">
    <h4 class="modal-title" style="font-weight:bold">REF: <?php echo $_REQUEST['payment_id']?></h4>
    
</div>
<div class="modal-body m-3 ">
    <?php $i = 1; $sch =0; if(is_array($stmt) && sizeof($stmt) > 0){ 
        foreach($stmt as $val){
        $details = $model->getItemLabelArr('lfs_stakeholder_split_table', array('id'),array($val['entity_id']),array('*'));
        $stm = $model->getItemLabelArr('lfs_stakeholder_split_table', array('is_deleted'),array(0),array('*'));

        $sch += $stm['percentage'];
    ?>
    <div class="row">
        <div class="form-group col-12">
            <h5><label><?php echo '#'.$i.' Entity Type: '.$val['entity_type']?></label></h5>
        </div>
        <div class="form-group col-4 py-2">
            <b><label>Stakeholder's Name:</label></b>
        </div>
        <div class="form-group col-4 py-2">
            <b><label>Percentage:</label></b>
        </div>
        <div class="form-group col-4 py-2">
            <b><label>Amount Recieved:</label></b>
        </div>

        <div class="form-group col-4">
            <label><?php echo isset($details['stakeholder_name'])?$details['stakeholder_name']:''?></label>
        </div>
        <div class="form-group col-4">
            <label><?php echo (isset($details['percentage']) && $details['percentage'] !="")?$details['percentage']:''?>%</label>
        </div>
        <div class="form-group col-4">
            <label><?php echo '₦'.($val['amount'])?number_format($val['amount'], 2):''?></label>
        </div>
    </div>
    <hr />
    <?php $i++;}
    }else{
        echo '<h5 class="p-3">This transaction was not splitted.</h5>';
    }
    ?>
</div>