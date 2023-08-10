<?php
include_once("../model/model.php");
$model = new Model();

$stmt = $model->runQuery("SELECT * FROM log_table WHERE table_id='".$_REQUEST['log_id']."'")[0];

?>
<div class="card">
  <div class="card-header">
    <h5 class="card-title">Operations on <?php echo $stmt['table_name']?></h5>
    <h6 class="card-subtitle text-muted">The report contains the system's activity histories on <?php echo $stmt['table_name']?> table.</h6>
  </div>
  <div class="card-body">
    <div id="datatables-basic_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
      <div class="row">
        <div class="col-sm-3">
          <label for=""></label>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12 table-responsive">
          <table id="page_list" class="table table-striped " style="white-space: nowrap;">
            <thead>
              <tr role="row">
                <th>S/N</th>
                <th>Label</th>
                <th>Username</th>
                <th>Counts</th>
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
  var table;
  var editor;
  var op = "Setup.getLogDetails";
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
          d.id = '<?php echo $_REQUEST['log_id']?>';
        }
      }
    });
  });

  function do_filter() {
    table.draw();
  }

  function deleteMenu(id) {
    let cnf = confirm("Are you sure you want to perform this action?");
    if (cnf == true) {
      $.blockUI();
      $.ajax({
        url: "helper",
        data: {op: "Setup.deleteFeesCategory", id: id},
        type: "post",
        dataType: "json",
        success: function(re){
          $.unblockUI();
          alert(re.response_message);
          getpage('views/fees_category_list.php', "page");
        },
        error: function(re){
          $.unblockUI();
          swal({text:"Request could not be processed at the moment!"});
        }
      });
    }

  }

  function getModal(url, div) {
    //        alert('dfd');
    $('#' + div).html("<h2>Loading....</h2>");
    //        $('#'+div).block({ message: null });
    $.post(url, {}, function(re) {
      $('#' + div).html(re);
    })
  }
</script>