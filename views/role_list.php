<?php
include_once("../model/model.php");
//var_dump($_SESSION);
?>
<div class="card">
  <div class="card-header">
    <h5 class="card-title">Role List</h5>
    <h6 class="card-subtitle text-muted">This record contains User Roles that have been setup in the system.</h6>
  </div>
  <div class="card-body">
    <a class="btn btn-info mb-3" onclick="loadModal('setup/role_setup.php','modal_div')" href="javascript:void(0)" data-toggle="modal" data-target="#defaultModalPrimary">Create New Role</a>
    <div id="datatables-basic_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
      <!--
            <div class="row">
                <div class="col-sm-3">
                    <label for="">Create Role</label>
                </div>
            </div>
-->
      <div class="row">
        <div class="col-sm-12 table-responsive">
          <table id="page_list" class="table table-striped " style="white-space: nowrap;">
            <thead>
              <tr role="row">
                <th>S/N</th>
                <th>Role ID</th>
                <th>Role Name</th>
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
  var table;
  var editor;
  var op = "Role.role_list";
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
        url: "helper.php",
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


  function getModal(url, div) {
    //        alert('dfd');
    $('#' + div).html("<h2>Loading....</h2>");
    //        $('#'+div).block({ message: null });
    $.post(url, {}, function(re) {
      $('#' + div).html(re);
    })
  }

  /*delete script begins here*/
  $(document).on('click', '.delete', function(e) {
    var ref = $(this).data('id'),
      title = $(this).data('title'),
      type = $(this).data('type'),
      operation = 'Role.delete_role',
      icon = (type == 'enable') ? 'success' : 'error';

    jQuery(function validation() {
      swal({
        title: type.charAt(0).toUpperCase() + type.slice(1),
        text: "Are you sure you want to " + type + " " + title + " ?",
        icon: icon,
        buttons: true,
        deleteMode: true,
      }).then((willDelete) => {
        if (willDelete) { //delete role
          $.ajax({
            type: 'post',
            url: 'helper',
            data: {
              id: ref,
              op: operation,
              type: type
            },
            dataType: 'json',
            beforeSend: function() {
              $('.delete-icon' + ref).hide();
              $('.deleting-icon' + ref).show();
            },
            success: function(rel) {
              if (rel.response_code == 0) {
                $('.delete-icon' + ref).hide();
                $('.deleting-icon' + ref).hide();
                $(this).attr('disabled', true).html('deleted')
                swal('Success!', rel.response_message, "success");
                getpage('views/role_list', 'page');

              } else {
                $('.delete-icon' + ref).show();
                $('.deleting-icon' + ref).hide();
                swal('Error', rel.response_message, "error");
              }
            },
            error: function() {
              $('.delete-icon' + ref).show();
              $('.deleting-icon' + ref).hide();;
              swal('Warning', "Something went wrong...", "warning");
            }
          });
        } else {

        }
      });
    })
  });
</script>