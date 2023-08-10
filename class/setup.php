<?php

class Setup extends Model
{
    public $model;
    public $myconn;
    public $base_url = "";
    public $root = "";
    public $log;
    public $user;
    public $admin_url;
    public $barcode;
    public $pdf;

    public function __construct()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($ip == 'localhost' or $ip == '::1') :
            $this->root = $_SERVER['DOCUMENT_ROOT'] . '/attendance/';
            $this->base_url = 'http://localhost/attendance/';
        else :
            $this->root = $_SERVER['DOCUMENT_ROOT'] . '/';
            $this->base_url = 'http://' . $_SERVER['SERVER_NAME'] . '/'; //or you hardcode your domain name here
        endif;

        include_once($this->root . "model/connection.php");
        include_once('log.php');
        include_once('users.php');

        $this->log = new Log();
        $this->user = new Users();
        $conn = new Connection();
        $this->myconn = $conn->connect();

        $this->admin_url = ($_SERVER['REMOTE_ADDR'] != "::1")?$this->getitemlabel("parameter", "parameter_name", "site_live_admin_url", "parameter_value"):$this->getitemlabel("parameter", "parameter_name", "site_local_admin_url", "parameter_value");
        $this->admin_url = (substr($this->admin_url, -1) == "/")  ? $this->admin_url : $this->admin_url."/";

    }
    public function savePassport($data)
    {
        // var_dump($data);
        $validate = $this->validate(
            $data,
            array('firstname' => 'required', 'lastname' => 'required', 'phone_number' => 'unique:staff.phone_number', 'email' => 'required|unique:staff.email', 'gender' => 'required', 'dob' => 'required'),
            array('firstname' => 'Firstname', 'lastname' => 'Lastname', 'phone_number' => 'Phone Number', 'email' => 'Email', 'gender' => 'Gender', 'dob' => 'Date of Birth')
        );
        if (!$validate['error']) {
            $passport = isset($data['passport']) ? $data['passport'] : '';
            $signature = isset($data['signature']) ? $data['signature'] : '';
            $staff_id = date('his');
            // split the string on commas
            // $pass[ 0 ] == "data:image/png;base64"
            // $pass[ 1 ] == <actual base64 string>
            $pass = explode(',', $passport);
            $sign = explode(',', $signature);

            $pfile = base64_decode($pass[1]);
            $sfile = base64_decode($sign[1]);

            $ip = $_SERVER['REMOTE_ADDR'];

            if ($ip == '::1' || $ip == 'localhost') {
                $upload_path = 'uploads/';
            } else {
                //$output_file = $_SERVER['DOCUMENT_ROOT'].'/uploads/';
                $upload_path = 'uploads/';
            }

            if (!file_exists($upload_path . 'passport/')) {
                mkdir($upload_path . 'passport/', 0777, true);
            }

            if (!file_exists($upload_path . 'signature/')) {
                mkdir($upload_path . 'signature/', 0777, true);
            }

            if (!empty($pass[1]) && !empty($sign[1])) {  //check if the base64 is empty

                if (!empty($pfile) && !empty($sfile)) {

                    $ext = 'jpg';
                    $pflname = 'passport_' . $staff_id . '.' . $ext;
                    $sflname = 'signature_' . $staff_id . '.' . $ext;

                    $exist = scandir($upload_path);
                    $exist = array_diff(scandir($upload_path), array('.', '..'));

                    $passport_filename = $upload_path . 'passport/' . $pflname;
                    $signature_filename = $upload_path . 'signature/' . $sflname;
                    $data['passport'] = $passport_filename;
                    $data['signature'] = $signature_filename;
                    $data['staff_id'] = $staff_id;
                    $data['created'] = date('Y-m-d h:i:s');
                    $data['posted_ip'] = $_SERVER['REMOTE_ADDR']; //posted ip
                    $data['posted_by'] = 'system'; //posted_username

                    // foreach ($exist as $l) {
                    //     if ($flname == $l) {
                    //         unlink($upload_path . $l);
                    //     }
                    // }


                    if (file_put_contents($passport_filename, $pfile) && file_put_contents($signature_filename, $sfile)) {
                        $stmt = $this->Insert('staff', $data, ['op', 'email-newsletter']);
                        if ($stmt > 0) {

                            return json_encode(array('response_code' => 0, 'response_message' => 'Successful'));
                        } else {
                            return json_encode(array('response_code' => 20, 'response_message' => 'Account could not be created.'));
                        }
                    } else {
                        return json_encode(array('response_code' => 20, 'response_message' => 'Account could not be created'));
                    }
                } else {
                    return json_encode(array('response_code' => 20, 'response_message' => 'Unrecognized file format'));
                }
            } else {
                return json_encode(array('response_code' => 20, 'response_message' => 'Please, confirm that you have taken a photoshot and signed the signed the form'));
            }
        } else {
            return json_encode(array('response_code' => 20, 'response_message' => $validate['messages'][0]));
        }
    }

    public function staffList($data)
    {
        $table_name    = "staff";
        $primary_key   = "staff_id";
        $columner = array(
            array('db' => 'staff_id', 'dt' => 0),
            array('db' => 'passport',   'dt' => 1, 'formatter' => function ($d, $row) {
                return ($d != "") ? '<img src="' . $d . '" class="img-fluid rounded" />' : '<img src="img/avartar.png" class="img-fluid rounded" />';
            }),
            array('db' => 'firstname',  'dt' => 2),
            array('db' => 'middlename',  'dt' => 3),
            array('db' => 'lastname',  'dt' => 4),
            array('db' => 'staff_id',   'dt' => 5),
            array('db' => 'phone_number',   'dt' => 6, 'formatter' => function ($d, $row) {
                return '<a href="tel:"' . $d . '" style="text-decoration:none">' . $d . '</a>';
            }),
            array('db' => 'email',   'dt' => 7, 'formatter' => function ($d, $row) {
                return ($d != "") ? '<a href="mailto:"' . $d . '" style="text-decoration:none">' . $d . '</a>' : '';
            }),
            array('db' => 'qrcode',   'dt' => 8, 'formatter' => function ($d, $row) {
                return ($d != "") ? '<a href="' . $this->base_url.$d . '" style="text-decoration:none" download class="fa fa-download"> Download</a>' : '';
            }),
            array('db' => 'gender',   'dt' => 9, 'formatter' => function ($d, $row) {
                return ucfirst($d);
            }),
            array('db' => 'dob',   'dt' => 10, 'formatter' => function ($d, $row) {
                return date('M d, Y', strtotime($d));
            }),
            array('db' => 'created',  'dt' => 11, 'formatter' => function ($d, $row) {
                return date('F d, Y h:m a', strtotime($d));
            }),
            array('db' => 'staff_id',     'dt' => 12, 'formatter' => function ($d, $row) {

                return "<a href=\"javascript:getpage('setup/staff_setup?staff_id=$d&op=edit','page')\" class='btn btn-primary' >Edit Staff</a>";
            })

        );

        $filter = "";
        
        $datatableEngine = new engine();

        echo $datatableEngine->generic_table($data, $table_name, $columner, $filter, $primary_key);
    }
    public function attendanceList($data)
    {
        $table_name    = "attendance_log";
        $primary_key   = "staff_id";
        $columner = array(
            array('db' => 'staff_id', 'dt' => 0),
            array('db' => 'staff_id', 'dt' => 1),
            array('db' => 'staff_id',   'dt' => 2, 'formatter' => function ($d, $row) {
                $get_staff = $this->getItemLabelArr('staff',array('staff_id'),array($d),'*');
                return $get_staff['firstname'].' '.$get_staff['middlename'].' '.$get_staff['lastname'];
            }),
            array('db' => 'day_taken',  'dt' => 3, 'formatter' => function ($d, $row) {
                return date('F d, Y', strtotime($d));
            }),
            array('db' => 'week_taken',   'dt' => 4, 'formatter' => function ($d, $row) {
                $week_taken = explode('-',$d);
                $week = substr($week_taken[1],1);
                return 'Week '.$week;
            }),
            array('db' => 'created',  'dt' => 5, 'formatter' => function ($d, $row) {
                return date('h:i a', strtotime($d));
            })

        );

        $filter = "";
        
        $datatableEngine = new engine();

        echo $datatableEngine->generic_table($data, $table_name, $columner, $filter, $primary_key);
    }

    public function savePatient($data)
    {
        $ignore = array(
            'firstname' => 'required', 'lastname' => 'required', 'staff_id' => 'required', 'phone_number' => 'required', 'dob' => 'required', 'gender' => 'required'
        );
        $ignore_1 = array(
            'firstname' => 'Firstname', 'lastname' => 'Surname', 'staff_id' => 'Patient No.', 'phone_number' => 'Mobile Number',
            'dob' => 'Date of Birth', 'gender' => 'Gender'
        );

        $validate = $this->validate($data, $ignore, $ignore_1);

        if (!$validate['error']) {
            $data['posted_ip'] = $_SERVER['REMOTE_ADDR']; //posted ip
            $data['posted_by'] = $_SESSION['username_sess']; //posted_username
            if ($data['operation'] == 'new') {

                $data['created'] = date('Y-m-d H:i:s'); //created

                $stmt = $this->Insert('staff', $data, array('op', 'operation', 'id', 'state_id', 'school_cat_id', 'att-csrf-token-label'));
                if ($stmt > 0) {
                    return json_encode(array('response_code' => 0, 'response_message' => 'Record has been successfully added to the system'));
                } else {
                    return json_encode(array('response_code' => 20, 'response_message' => 'Record could not be added to the system'));
                }
            } else {
                $data['lastmodified'] = date('Y-m-d H:i:s'); //lastmodified
                $current_data = $this->log->getCurrentData('staff', 'staff_id', $data['id']);

                $phone_owner = $this->getitemlabel('staff', 'phone_number', $data['email'], 'staff_id');
                if ($phone_owner != "" && ($data['phone_number'] != $phone_owner)) {
                    return json_encode(array('response_code' => 20, 'response_message' => 'Phone Number already exists'));
                }

                $email_owner = $this->getitemlabel('staff', 'email', $data['email'], 'staff_id');
                if ($email_owner != "" && ($data['id'] != $email_owner)) {
                    return json_encode(array('response_code' => 20, 'response_message' => 'Email address already exists'));
                }

                $stmt = $this->Update('staff', $data, array('op', 'operation', 'id', 'att-csrf-token-label', 'staff_id'), array('staff_id' => $data['id']));
                if ($stmt > 0) {
                    $this->log->logData($current_data, $data, ["table_name" => 'staff', "table_id" => $data['id'], "table_alias" => 'Edited Student Record'], ['op', 'operation', 'id', 'staff_id', 'att-csrf-token-label', 'lastmodified']);

                    return json_encode(array('response_code' => 0, 'response_message' => 'Record has been successfully updated'));
                } else {
                    return json_encode(array('response_code' => 20, 'response_message' => 'Record could not be updated'));
                }
            }
        } else {
            return json_encode(array('response_code' => 20, 'response_message' => $validate['messages'][0]));
        }
    }

    public function uploadPatient($data)
    {
        $files = $_FILES;
        $data = $_REQUEST;
        $i = 0;

        if (!in_array($files['aksfileupload']['type'][$i], $this->mimeTypeCSV())) {
            return json_encode(array("response_code" => 20, "response_message" => $files['aksfileupload']['type'][$i] . 'files are not allowed. Only CSV files are allowed'));
        }

        if (empty($files['aksfileupload']['name'][$i])) {
            return json_encode(array("response_code" => 20, "response_message" => 'Please, select a file to upload'));
        }

        $path    = 'uploads/records/';
        if (!file_exists($path)) {
            mkdir($path, 0777);
        }

        $exist = scandir($path);
        $exist = array_diff(scandir($path), array('.', '..'));
        $output = array();
        $tmpPath = $files['aksfileupload']['tmp_name'][$i];
        $fileName = $files['aksfileupload']['name'][$i];
        $extension = explode(".", $fileName);
        $file_name = '';
        $filename = 'staff_record';
        if ($tmpPath != "") {
            $file = str_replace(' ', '_', strtolower($filename . '_' . date('d-m-Y'))) . '.' . $extension[1];
            $file_name .= $path . $file;

            foreach ($exist as $l) {
                if ($file == $l) {
                    unlink($path . $l);
                }
            }

            if (move_uploaded_file($tmpPath, $file_name)) {
                return $this->readCSV($files, $file_name, 'records');
            }
        }
    }

    public function mimeTypeCSV()
    {
        return array(
            'text/x-comma-separated-values',
            'text/comma-separated-values',
            'application/octet-stream',
            'application/vnd.ms-excel',
            'application/x-csv',
            'text/x-csv',
            'text/csv',
            'application/csv',
            'application/excel',
            'application/vnd.msexcel',
            'text/plain'
        );
    }

    
    private function readCSV($files, $data, $type)
    {

        // Validate whether selected file is a CSV file
        if (($_FILES['aksfileupload']['size'][0] > 0) && in_array($files['aksfileupload']['type'][0], $this->mimeTypeCSV())) {
            $importCount = 0;

            $handler = fopen($data, "r");
            if (!$handler) {
                $output = array('response_code' => 20, 'response_message' => 'Could not open file');
            } else {
                // Skip the first line
                fgets($handler);
                
                while ($column = fgetcsv($handler)) {
                    if (!empty($column) && is_array($column)) {
                        $std['firstname'] = $column[0];    //firstname
                        $std['middlename'] = $column[1];   //middlename
                        $std['lastname'] = $column[2];     //middlename
                        $std['gender'] = $column[3];       //gender
                        $std['dob'] = $column[4];          //dob
                        $std['email'] = $column[5];          //email
                        $std['phone_number'] = $column[6];    //phone no
                        $std['staff_id'] = substr($column[6],1);  //staff id 10 digit
                        $std['address'] = $column[7];          //address
                        $std['posted_ip'] = $_SERVER['REMOTE_ADDR']; //posted ip
                        $std['posted_by'] = $_SESSION['username_sess']; //posted_username
                        // $std['created'] = date('Y-m-d H:i:s'); //created
                        
                        if($std['phone_number'] == ""){
                            return json_encode(array('response_code' => 20, 'response_message' => "Phone number is required."));
                        }

                        $stmt = $this->getItemLabelArr('staff', array('staff_id'),array($std['staff_id']), '*');
                        if($stmt != ""){
                            $output["response_code"] = 20;
                            $output["response_message"] = "Record {$std['phone_number']} already exists.";
                        }else{

                            $std['grcode'] = '';
                            // $grcode = json_decode($this->generateBARCODE($std['staff_id']), true);
                            $grcode = json_decode($this->generateQRCODE($std['staff_id']), true);
                            if($grcode['response_message'] != ""){
                                $std['qrcode'] = $grcode['response_message'];
                            }else{
                                $output["response_code"] = 20;
                                $output["response_message"] = "Could not generate staff [{$std['staff_id']}] QRCODE";
                            }
    
                            $stmt = $this->myconn->query("INSERT INTO 
                                staff(firstname,middlename,lastname,gender, dob,email,phone_number,posted_ip,posted_by,qrcode,staff_id)
                                VALUES('" . $std['firstname'] . "','" . $std['middlename'] . "','" . $std['lastname'] . "','" . $std['gender'] . "','" . $std['dob'] . "'
                                ,'" . $std['email'] . "','" . $std['phone_number'] . "','" . $std['posted_ip'] . "','" . $std['posted_by'] . "','".$std['qrcode']."','".$std['staff_id']."')");
    
                            
                            if ($this->myconn->affected_rows > 0) {
                                $output["response_code"] = 0;
                                $output["response_message"] = "Records has been successfully uploaded.";
                            } else {
                                
                            }
                        }

                    } else {
                        $output["response_code"] = 20;
                        $output["response_message"] = "Problem in importing data.";
                    }
                }
            }

            fclose($handler);
            return json_encode($output);
        }
    }

    function hasEmptyRow(array $column)
    {
        $columnCount = count($column);
        $isEmpty = true;
        for ($i = 0; $i < $columnCount; $i++) {
            if (!empty($column[$i]) || $column[$i] !== '') {
                $isEmpty = false;
            }
        }
        return $isEmpty;
    }
    public function getLogTable($data)
    {
        $table_name    = "log_table";
        $primary_key   = "id";
        $columner = array(
            array('db' => 'id', 'dt' => 0),
            array('db' => 'table_name',  'dt' => 1),
            array('db' => 'table_alias',  'dt' => 2),
            array('db' => 'username',  'dt' => 3),
            array('db' => 'table_id',  'dt' => 4, 'formatter' => function ($d, $row) {

                $stmt = $this->runQuery("SELECT count(table_id) AS counter FROM log_table WHERE table_id='$d'")[0];
                $text = ($stmt['counter'] > 1) ? $stmt['counter'] . ' operations' : $stmt['counter'] . ' operation';
                return '<a href="javascript:getpage(\'views/log_details?op=view&log_id=' . $d . '\',\'page\')" class="badge badge-soft-primary fa fa-eye p-2" style="text-decoration:none;"> ' . $text . '</a>';
            }),
            array('db' => 'created',     'dt' => 5, 'formatter' => function ($d, $row) {
                return date('M d, Y h:i a', strtotime($d));
            }),


        );
        $filter = " GROUP BY table_id";
        $datatableEngine = new engine();

        echo $datatableEngine->generic_table($data, $table_name, $columner, $filter, $primary_key);
    }
    public function getLogDetails($data)
    {
        $table_name    = "log_table";
        $primary_key   = "id";
        $columner = array(
            array('db' => 'id', 'dt' => 0),
            array('db' => 'table_alias',  'dt' => 1),
            array('db' => 'username',  'dt' => 2),
            array('db' => 'id',  'dt' => 3, 'formatter' => function ($d, $row) {

                $stmt = $this->runQuery("SELECT count(log_id) AS counter FROM log_details WHERE log_id='$d'")[0];
                $text = ($stmt['counter'] > 1) ? $stmt['counter'] . ' activities' : $stmt['counter'] . ' activity';
                return '<a onclick="loadModal(\'views/log_views?op=view&log_id=' . $d . '\',\'modal_div\')" href="javascript:void(0)" class="badge badge-soft-primary fa fa-eye p-2" style="text-decoration:none;" data-toggle="modal" data-target="#defaultModalPrimary"> ' . $text . '</a>';
            }),
            array('db' => 'created',     'dt' => 4, 'formatter' => function ($d, $row) {
                return date('M d, Y h:i a', strtotime($d));
            }),


        );
        $filter = " AND table_id='" . $data['id'] . "'";
        $datatableEngine = new engine();

        echo $datatableEngine->generic_table($data, $table_name, $columner, $filter, $primary_key);
    }

    public function saveSignature($data)
    {
        $passport = isset($data['passport']) ? $data['passport'] : '';
        $signature = isset($data['signature']) ? $data['signature'] : '';
        $staff_id = $data['id'];
        // split the string on commas
        // $pass[ 0 ] == "data:image/png;base64"
        // $pass[ 1 ] == <actual base64 string>
        $pass = explode(',', $passport);
        $sign = explode(',', $signature);

        $pfile = base64_decode($pass[1]);
        $sfile = base64_decode($sign[1]);

        $ip = $_SERVER['REMOTE_ADDR'];

        if ($ip == '::1' || $ip == 'localhost') {
            $upload_path = 'uploads/';
        } else {
            //$output_file = $_SERVER['DOCUMENT_ROOT'].'/uploads/';
            $upload_path = 'uploads/';
        }

        if (!file_exists($upload_path . 'passport/')) {
            mkdir($upload_path . 'passport/', 0777, true);
        }

        if (!file_exists($upload_path . 'signature/')) {
            mkdir($upload_path . 'signature/', 0777, true);
        }

        if (!empty($pass[1]) && !empty($sign[1])) {  //check if the base64 is empty

            if (!empty($pfile) && !empty($sfile)) {

                $ext = 'jpg';
                $pflname = 'passport_' . $staff_id . '.' . $ext;
                $sflname = 'signature_' . $staff_id . '.' . $ext;

                $pexist = scandir($upload_path.'passport/');
                $pexist = array_diff(scandir($upload_path.'passport/'), array('.', '..'));

                $sexist = scandir($upload_path.'signature/');
                $sexist = array_diff(scandir($upload_path.'signature/'), array('.', '..'));

                $passport_filename = $upload_path . 'passport/' . $pflname;
                $signature_filename = $upload_path . 'signature/' . $sflname;
                $update['passport'] = $passport_filename;
                $update['signature'] = $signature_filename;
                $update['lastmodified'] = date('Y-m-d h:i:s');
                $update['posted_ip'] = $_SERVER['REMOTE_ADDR']; //posted ip
                $update['posted_by'] = 'system'; //posted_username

                foreach ($pexist as $l) {
                    if ($pflname == $l) {
                        unlink($upload_path.'passport/' . $l);
                    }
                }

                foreach ($sexist as $l) {
                    if ($sflname == $l) {
                        unlink($upload_path.'signature/' . $l);
                    }
                }

                $current_data = $this->log->getCurrentData('staff', 'staff_id', $data['id']);

                if (file_put_contents($passport_filename, $pfile) && file_put_contents($signature_filename, $sfile)) {
                    $stmt = $this->Update('staff',$update,[], ['staff_id'=>$staff_id]);
                    if ($stmt > 0) {

                        $this->log->logData($current_data, $update, ["table_name" => 'staff', "table_id" => $staff_id, "table_alias" => 'Patient Passport/Signature'], ['op', 'operation', 'id', 'att-csrf-token-label', 'image', 'created']);

                        return json_encode(array('response_code' => 0, 'response_message' => 'Successful'));
                    } else {
                        return json_encode(array('response_code' => 20, 'response_message' => 'Record could not be updated.'));
                    }
                } else {
                    return json_encode(array('response_code' => 20, 'response_message' => 'Record could not be uploaded'));
                }
            } else {
                return json_encode(array('response_code' => 20, 'response_message' => 'Unsuccessful'));
            }
        }
    }

    public function getStudentDetails($data)
    {
        if (empty($data['id'])) {
            return json_encode(array('response_code' => 20, 'response_message' => "Please, enter patient's Id"));
        }

        $filter_col = (isset($_SESSION['role_id_sess']) && $_SESSION['role_id_sess'] == 100 or $_SESSION['role_id_sess'] == 200) ? "staff_id" : "staff_id";
        $filter_val = (isset($_SESSION['role_id_sess']) && $_SESSION['role_id_sess'] == 100 or $_SESSION['role_id_sess'] == 200) ? $data['id'] : $data['id'] ;

        $stmt = $this->getItemLabelArr('staff', array($filter_col), array($filter_val), '*');
        if ($stmt != "") {
            $format = "data:image/jpeg;base64,";
            $encoded_passport = $format.base64_encode(file_get_contents($this->admin_url.$stmt['passport']));
            $encoded_signature = $format.base64_encode(file_get_contents($this->admin_url.$stmt['signature']));

            return json_encode(array('response_code' => 0, 'data' => array(
                'fullname' => $stmt['firstname'] . ' ' . $stmt['lastname'],
                'gender' => (isset($stmt['gender']) && ($stmt['gender'] == 'male' or $stmt['gender'] == 'm')) ? 'Male' : 'Female',
                'studentno' => $data['id'],
                'passport' => $stmt['passport'],
                'signature' => $stmt['signature'],
                'encoded_passport' => $encoded_passport,
                'encoded_signature' => $encoded_signature
            )));
        } else {
            return json_encode(array('response_code' => 20, 'response_message' => 'No Record was found for ' . $data['id']));
        }
    }

    protected function get_env()
	{
		// app configuration settings
		

	}
}
