<?php
include_once("../model/model.php");
$model = new Model();

?>
<div class="card">
  <div class="card-header">
    <h5 class="card-title">Menu List</h5>
    <h6 class="card-subtitle text-muted">The report contains Menus that have been setup in the system.</h6>
  </div>
  <div class="card-body">
    <a class="btn btn-warning" onclick="loadModal('setup/menu_setup.php','modal_div')" href="javascript:void(0)" data-toggle="modal" data-target="#defaultModalPrimary">Create Menu</a>
    <a class="btn btn-primary text-right" onclick="loadModal('setup/menu_group.php','modal_div')" href="javascript:void(0)" data-toggle="modal" data-target="#defaultModalPrimary">Create Group</a>
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
                <!--                                <th>Menu ID</th>-->
                <th>Menu Name</th>
                <th>Menu URL</th>
                <th>Parent ID</th>
                <!--                                <th>Menu Level</th>-->
                <!--                                <th>Menu Order</th>-->
                <th>Menu Icon</th>
                <th>Action</th>
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
  var op = "Menu.menuList";
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
          //          d.start_date = $("#start_date").val();
          //          d.end_date = $("#end_date").val();
        }
      }
    });
  });

  function do_filter() {
    table.draw();
  }

  function deleteMenu(id) {
    let cnf = confirm("Are you sure you want to delete menu?");
    if (cnf == true) {
      $.blockUI();
      $.ajax({
        url: "helper",
        data: {op: "Menu.deleteMenu", menu_id: id},
        type: "post",
        dataType: "json",
        success: function(re){
          $.unblockUI();
          alert(re.response_message);
          getpage('views/menu_list.php', "page");
        },
        error: function(re){
          $.unblockUI();
          alert("Request could not be processed at the moment!");
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