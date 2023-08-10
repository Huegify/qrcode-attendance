<?php
if (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') {
    ini_set('session.cookie_secure', true);
} else {
    ini_set('session.cookie_httponly', true);
}
include_once("../model/model.php");
$model = new Model();

// echo $model->generateHTTP();

header_remove("X-Powered-By");
header_remove("Server");
$crossorigin = 'anonymous';


?>
<style>
    .kbw-signature {
        width: 400px;
        height: 200px;
    }
</style>


<div class="card">
    <div class="card-header">
        <!-- <h5 class="card-title mb-0">Capture Image</h5> -->
    </div>
    <div class="card-body">
        <div class="form-group py-2">
            <div class="form-group input-group mb-3">
                <label class="form-label col-12">Enter Staff Id </label>
                <input class="form-control form-control-lg p-2" type="text" name="staff_id" id="staff_id" required placeholder="Enter Staff Id" autocomplete="off">
                <div class="input-group-append" style="cursor: pointer;">
                    <span class="input-group-text search p-3 text-uppercase">search</span>
                </div>
            </div>

        </div>
    </div>
</div>
<div class="card loader-section py-5" style="display: none;">
    <center class="loader"><img src="img/loading2.gif" /><br /><br /><span class="text-success">Just a moment...</span></center>
    <center class="feedback" style="display: none;"></center>
</div>
<div class="card capture-section" style="display: none;">
    <div class="card-header">
        <h5 class="card-title mb-0">Capture Image</h5>
    </div>
    <div class="card-body">
        <form id="form1" autocomplete="off" action="javascript:void(0)">
            <input type="hidden" name="op" value="Setup.savePassport">
            <input type="hidden" name="username" value="<?php echo $_SESSION['username_sess']; ?>">
            <input type="hidden" name="id" id="id">
            <div class="webcam-section">
                <h4 class="setction-2"></h4>
                <fieldset>
                    <!-- <legend>Capture Image</legend> -->

                    <div class="d-flex my-3">

                        <div class="col-md-4">
                            <div id="my_camera" class="bg-default"></div>
                            <br />
                            <div class="camera-alert"></div>
                            <button type="button" class="btn btn-danger p-2" onClick="take_snapshot()"><i class="fa fa-save"></i> Snapshot</button>
                            <input type="hidden" name="passport" id="passport" class="image-field">
                            <!-- <button class="btn btn-success p-2" id="save_facility"><i class="fa fa-save"></i> Save </button> -->
                        </div>

                        <div class="col-md-8 row">
                            <div class="col-8 py-5">
                                <div class="form-group">
                                    <h5>Name: <l class="name"></l>
                                    </h5>
                                </div>
                                <div class="form-group">
                                    <h5>Staff ID: <l class="studentno"></l>
                                    </h5>
                                </div>
                                <div class="form-group">
                                    <h5>Gender: <l class="gender"></l>
                                    </h5>
                                </div>
                            </div>
                            <div class="col-4">
                                <div id="results"></div>
                            </div>
                        </div>

                    </div>
                        <div class="col-lg-12 row">
                            <div class="col-lg-6">
                                <canvas id="signature" width="450" height="150" style="border: 1px solid #ddd;"></canvas>
                                <input type="hidden" name="signature" id="signat" />
                                <br>
                                <button id="save_facility" class="btn btn-success py-2">Save</button>
                                <button id="clear-signature" class="btn btn-danger py-2 text-end">Clear</button>
                                
                            </div>
                            <div class="col-6">
                                <img id="imgCapture" alt="" style="display:none;border:1px solid #ccc" />
                            </div>
                        </div>
                </fieldset>
            </div>
        </form>
    </div>
</div>

<script>
    /*webcam section*/
    Webcam.set({
        width: 340,
        height: 280,
        image_format: 'jpeg',
        force_flash: false,
        crop_width: 251,
        crop_height: 200,
        jpeg_quality: 100
    });
    Webcam.attach('#my_camera');

    function take_snapshot() {
        //Enable
        $("#save_passport").attr("disabled", false);

        // take snapshot and get image data
        Webcam.snap(function(data_uri) {
            $("#passport").attr('value', data_uri);
            // display results in page

            document.getElementById('results').innerHTML = '<img src="' + data_uri + '" class="img-fluid img-rounded"/>';
            // alert($("#image").val());
        });

    }
    $(document).on('keyup', '#staff_id', function() {
        var val = $(this).val();
        $("#id").attr('value', val);
    });

    $(document).on('click', '.search', () => {
        $('.capture-section').hide();
        $('.loader-section').show();
        $('.loader').fadeIn();

        $.ajax({
            url: "helper",
            data: {
                op: 'Setup.getStudentDetails',
                id: $("#staff_id").val()
            },
            type: "post",
            dataType: "json",
            success: function(data) {
                if (data.response_code == 0) {
                    setTimeout(() => {
                        $('.loader-section').hide();
                        $('.loader').hide();
                        $('.capture-section').fadeIn();

                    }, 5000);

                    $('.name').html(data.data.fullname);
                    $('.gender').html(data.data.gender);
                    $('.studentno').html(data.data.studentno);
                    $('#passport').attr('value',data.data.encoded_passport);
                    $('#signat').attr('value',data.data.encoded_signature);
                    $('#results').html((data.data.passport != "") ? '<img src="' + data.data.passport + '" class="img-fluid img-rounded"/>' : "");
                    $('#imgCapture').attr('src', data.data.signature).show().addClass('img-fluid img-rounded');
                } else {

                    setTimeout(() => {
                        $('.loader-section').show();
                        $('.loader').hide();
                        $('.feedback').html('<h5 class="text-danger">' + data.response_message + '</h5>').show();

                    }, () => {
                        $('.loader-section').hide();
                    }, 5000);

                }
            },
            error: function() {

                setTimeout(() => {
                    $('.loader-section').show();
                    $('.loader').hide();
                    $('.feedback').html('<h5 class="text-danger">Request could not be processed at the moment!</h5>').show();
                }, () => {
                    $('.loader-section').hide();
                }, 5000);
            }
        });
    })
    // $(document).on('click', '#save_facility', () => {
    //     $.ajax({
    //         url: "helper",
    //         data: $("#form1").serialize(),
    //         type: "post",
    //         dataType: "json",
    //         success: function(re) {
    //             $.unblockUI();
    //             setTimeout(() => {
    //                 $('.capture-section').hide();
    //                 $('.loader').hide();
    //                 $('.loader-section').show();
    //             }, 5000);
    //             swal({
    //                 text: re.response_message
    //             });

    //             // getpage('views/academic_term_list.php', "page");
    //         },
    //         error: function(re) {
    //             $.unblockUI();
    //             swal({
    //                 text: "Request could not be processed at the moment!"
    //             });
    //         }
    //     });
    // })

    $(document).ready(function() {

        var canvas = document.getElementById("signature");
        var signaturePad = new SignaturePad(canvas);


        $('#clear-signature').on('click', function() {
            signaturePad.clear();
        });

    });

    $("#save_facility").bind("click", function() {
        var base64 = $('#signature')[0].toDataURL();
        $("#imgCapture").attr("src", base64);
        $("#imgCapture").show();
        $("#signat").attr('value', base64);
        var payload = {
            op: 'Setup.saveSignature',
            passport: $('#passport').val(),
            signature: $('#signat').val(),
            id: $("#id").val()
        }
        $.ajax({
            url: "helper",
            data: payload,
            type: "post",
            dataType: "json",
            success: function(re) {
                $.unblockUI();
                setTimeout(() => {
                    $('.capture-section').hide();
                    $('.loader').hide();
                    $('.loader-section').show();
                }, 5000);
                swal({
                    text: re.response_message
                });

            },
            error: function(re) {
                $.unblockUI();
                swal({
                    text: "Request could not be processed at the moment!"
                });
            }
        });
    });
</script>