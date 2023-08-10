<?php
include_once("../model/model.php");
$model = new Model();

$stmt = $model->runQuery("SELECT *  FROM log_details AS l INNER JOIN log_table AS t ON t.id=l.log_id WHERE log_id='".$_REQUEST['log_id']."' ");

?>

<div class="modal-header">
    <h4 class="modal-title" style="font-weight:bold"><?php echo $stmt[0]['table_alias'].' by '.$stmt[0]['username']?></h4>
    <button type="button" class="close text-end" data-bs-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>
<div class="modal-body m-3 ">
    
    <div class="row">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Field Name</th>
                    <th>Previous Value</th>
                    <th>Current Value</th>
                    <th>Date Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach($stmt as $val){
                echo '<tr>
                    <td>'.$i.'</td>
                    <td>'.ucwords(str_replace('_',' ',$val['field_name'])).'</td>
                    <td>'.$val['previous_data'].'</td>
                    <td>'.$val['current_data'].'</td>
                    <td>'.date('M d, Y h:i a',strtotime($val['created'])).'</td>
                </tr>';
                $i++;} ?>
            </tbody>
        </table> 
    </div>
    
</div>