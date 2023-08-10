<?php
include_once("../model/model.php");
$model = new Model();

?>
<div class="card">
  <div class="card-header">
    <h5 class="card-title">Settlement Report</h5>
    <h6 class="card-subtitle text-muted">The report contains stakeholders' settlement logs on the system.</h6>
  </div>
  <div class="card-body">
    <div class="row py-3 mb-3">
        <div class="col-sm-3">
            <label for="start_date">From:</label>
            <input type="date" name="start_date" id="start_date" class="form-control" placeholder="" />
        </div>

        <div class="col-sm-3">
            <label for="end_date">To:</label>
            <input type="date" name="end_date" id="end_date" class="form-control" placeholder="" />
        </div>

        <div class="col-sm-3">
            <label for="is_settled">Status:</label>
            <select name="is_settled" id="is_settled" class="form-control">
                <option selected value="">::: Select Status:::</option>
                <option value="1">Settled</option>
                <option value="0">Not Settled</option>
            </select>
        </div>

        <div class="col-sm-2">
            <label for="search">&nbsp;</label>
            <button onclick="do_filter()" id="search" class="btn btn-info btn-block">Search</button>
        </div>

        <div class="col-sm-12 mt-5 text-center">
            <h3 class="filter-text" style="color: #a55198;">Filtered for <?php echo date('F d, Y', strtotime('-1 day'))?></h3>
        </div>

    </div>
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
                    <th>Entity Name</th>
                    <th>Entity Type</th>
                    <th>Bank Name</th>
                    <th>Account Name</th>
                    <th>Account No.</th>
                    <th>Amount</th>
                    <th>Transaction Id</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Action</th>
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
    $("#is_settled").select2();
  var table;
  var editor;
  var op = "Transaction.settlementReport";
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
          d.start_date = $("#start_date").val();
          d.end_date = $("#end_date").val();
          d.is_settled = $("#is_settled").val();
        }
      }
    });
  });

  function do_filter() {
    table.draw();
    from = new Date ( $("#start_date").val() );
	from_date = from.getDate();
	from_year = from.getFullYear();
	from_month = from.toLocaleString('default', { month: 'long' });
	
	to = new Date ( $("#end_date").val() );
	to_date = to.getDate();
	to_year = to.getFullYear();
	to_month = to.toLocaleString('default', { month: 'long' });

    $('.filter-text').html('Filtered from '+from_month+' '+from_date+', '+from_year+' and '+to_month+' '+to_date+', '+to_year);
  }

  function deleteMenu(id) {
    let cnf = confirm("Are you sure you want to perform this action?");
    if (cnf == true) {
      $.blockUI();
      $.ajax({
        url: "helper",
        data: {op: "Transaction.manageSettlement", id: id},
        type: "post",
        dataType: "json",
        success: function(re){
          $.unblockUI();
          alert(re.response_message);
          getpage('reports/settlement_report.php', "page");
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