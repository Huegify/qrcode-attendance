<?php
class Users extends Model
{
    public $base_url = "";
    public $log;
    public function __construct()
    {
        parent::__construct();
        $this->base_url = ($_SERVER['REMOTE_ADDR'] != "::1")?$this->getitemlabel("parameter", "parameter_name", "site_live_admin_url", "parameter_value"):$this->getitemlabel("parameter", "parameter_name", "site_local_admin_url", "parameter_value");
        $this->base_url = (substr($this->base_url, -1) == "/")  ? $this->base_url : $this->base_url."/";

        include_once('log.php');

        $this->log = new Log();

    }
   
    public function login($data)
	{
		$username = $data['username'];
		$password = $data['password'];
        $validate = $this->validate(
            $data,
            array('username' => 'required', 'password' => 'required'),
            array('username' => 'Username', 'password' => 'Password')
        );
        
        $space = $this->validateSpaceInput($username);
        if($space != 1){
            return json_encode(array('response_code' => '20','response_message' =>  $space));
        }

        if($validate['error']){
            return json_encode(array('response_code'=>20,'response_message'=>$validate['messages'][0]));
        }

        $stmt = $this->getItemLabelArr('userdata',array('username'),array($username),array('password','is_mfa','mfa_otp','is_email_verified','email_token','email','firstname'));
        if($stmt == ""){
            return json_encode(array('response_code'=>20,'response_message'=>'Invalid User Account'));
        }

        $desencrypt = new DESEncryption();
        $key = $username;
        $cipher_password = $desencrypt->des($key, $password, 1, 0, null,null);
        $str_cipher_password = $desencrypt->stringToHex ($cipher_password);

        if($str_cipher_password != $stmt['password']){
            return json_encode(array('response_code'=>20,'response_message'=>'Incorrect password'));
        }

        if($stmt['is_email_verified'] == 0  &&( $stmt['email_token'] = "" or $stmt['email_token'] = NULL or $stmt['email_token'] = "NULL")){
            return $this->regenerateLink($data);
        }

        if(isset($stmt['email_token']) && ($stmt['email_token'] != "" or $stmt['email_token'] != NULL or $stmt['email_token'] != "NULL") && $stmt['is_email_verified'] == 0){
            $message = "Please, kindly follow the link that was sent to ".$this->maskEmail($stmt['email'])." to verify your email address.";
            return json_encode(array('response_code'=>20,'response_message'=>$message, 'status'=>100));
        }
        
        if(isset($stmt['is_mfa'])  && $stmt['is_mfa'] == 1){
            $message = "Please, enter the OTP that was sent to ".$this->maskEmail($stmt['email']);
            $pin = json_decode($this->generate2FAPIN($username), true);
            
            if($pin['response_code'] == 0){
                return json_encode(array('response_code'=>20,'response_message'=>base64_encode($message), 'status'=>101,
                'page'=>'verification','type'=>base64_encode('mfa'),'username'=>base64_encode($username),
                'authenticate'=>base64_encode($stmt['firstname'])));
                
            }else{
                return json_encode(array('response_code'=>20,'response_message'=>base64_encode('OTP could not be sent to '.$this->maskEmail($stmt['email'])), 'status'=>101,'page'=>'verification','type'=>base64_encode('mfa'),'username'=>base64_encode($username),'authenticate'=>base64_encode($stmt['firstname'])));
            }
        }
    
        return json_encode(json_decode($this->signIn($username, $password), true));
		
    }

    public function signIn($username, $password)
    {
        $sql      = "SELECT username,firstname,lastname,sex,role_id,school_id,office_state,password,user_locked,user_disabled,pin_missed,day_1,day_2,day_3,day_4,day_5,day_6,day_7,passchg_logon,photo FROM userdata WHERE username = '$username' LIMIT 1";
        $result   = $this->runQuery($sql,true);
		$count    = count($result); 

		if($count > 0){
            if($result[0]['passchg_logon'] == 0){
                if($result[0]['pin_missed'] < 5){
                    $encrypted_password = $result[0]['password'];
                    $is_locked     = $result[0]['user_locked'];
                    $is_disabled     = $result[0]['user_disabled'];

                    $desencrypt = new DESEncryption();
                    $key = $username;
                    $cipher_password = $desencrypt->des($key, $password, 1, 0, null,null);
                    $str_cipher_password = $desencrypt->stringToHex ($cipher_password);

                    if($str_cipher_password == $encrypted_password)
                    // if(1 == 1)
                    {
                        
                        if($is_disabled != 1)
                        {
                            if($is_locked != 1)
                            {
                                $work_day = $this->workingDays($result[0]);
                                if($work_day['code'] != "44")
                                {
                                    // if($result[0]['church_id'] != "99")
                                    // {
                                        $_SESSION['username_sess']   = $result[0]['username'];
                                        $_SESSION['firstname_sess']  = $result[0]['firstname'];
                                        $_SESSION['lastname_sess']   = $result[0]['lastname'];
                                        $_SESSION['sex_sess']        = $result[0]['sex'];
                                        $_SESSION['role_id_sess']    = $result[0]['role_id'];
                                        $_SESSION['photo_file_sess']  = $result[0]['photo'];
                                        $_SESSION['photo_path_sess']  = "img/profile_photo/".$result[0]['photo'];
                                        $_SESSION['school_id']      = $result[0]['school_id'];
                                        $_SESSION['state_id']       = $result[0]['office_state'];
                                        $_SESSION['role_id_name']    = $this->getitemlabel('role','role_id',$result[0]['role_id'],'role_name');

                                        
                                        //update pin missed and last_login
                                        $this->resetpinmissed($username);
                                        return json_encode(array("response_code"=>0,"response_message"=>"Login Successful"));
                                    // }
                                    // else
                                    // {
                                    //     return json_encode(array("response_code"=>779,"response_message"=>"You can't login now... A profile transfer is currently ongoing. Try again at a later time or contact the Administrator"));
                                    // }

                                }
                                else
                                {
                                    return json_encode(array("response_code"=>20,"response_message"=>$work_day['mssg']));
                                }
                            }
                            else {
                                //inform the user that the account has been locked, and to contact admin, user has to provide useful info b4 he is unlocked
                                return json_encode(array("response_code"=>20,"response_message"=>"Your account has been locked, kindly contact the administrator."));
                            }
                        } else {
                            return json_encode(array("response_code"=>20,"response_message"=>"Your user privilege has been revoked. Kindly contact the administrator"));
                        }
                    } else {
                        $this->updatepinmissed($username);
                        
                        $remaining = (($result[0]['pin_missed']+1) <= 5)?(5-($result[0]['pin_missed']+1)):0;
                        return json_encode(array("response_code"=>20,"response_message"=>"Invalid username or password, ".$remaining." attempt remaining"));
                    }
                }elseif($result[0]['pin_missed'] == 5){
                    $this->updateuserlock($username,'1');
                    return json_encode(array("response_code"=>20,"response_message"=>"Your account has been locked, kindly contact the administrator."));
                }else{
                    return json_encode(array("response_code"=>20,"response_message"=>"Your account has been locked, kindly contact the administrator."));
                }
            }else{
                return json_encode(array("response_code"=>20,"response_message"=>"Please, kindly change your password to continue.",'status'=>114));
            }
		}else{
			return json_encode(array("response_code"=>20,"response_message"=>"Invalid username or password"));
		}
    }
    public function userlist($data)
    {
        $table_name    = "userdata";
        $primary_key   = "username";
        $columner = array(
            array('db' => 'username', 'dt' => 0),
            array('db' => 'username', 'dt' => 1),
            array('db' => 'firstname',  'dt' => 2),
            array('db' => 'lastname',   'dt' => 3),
            array('db' => 'mobile_phone',   'dt' => 4),
            array('db' => 'role_id',   'dt' => 5, 'formatter' => function ($d, $row) {
                return  $this->getitemlabel('role', 'role_id', $d, 'role_name');
            }),
            array('db' => 'email',   'dt' => 6),
            array('db' => 'pin_missed',   'dt' => 7),
            array('db' => 'user_disabled',   'dt' => 8, 'formatter' => function ($d, $row) {
                return ($d == 1) ? 'Disabled' : 'Enabled';
            }),
            
            array('db' => 'created',   'dt' => 9),
            array('db' => 'username',   'dt' => 10, 'formatter' => function ($d, $row) {
                $locking = ($row['user_disabled'] == 1) ? "Enable" : "Disable";
                $locking_class = ($row['user_disabled'] == 1) ? "btn-success" : "btn-danger";
                // if ($_SESSION['role_id_sess'] == 100) {
                    $reset = "<span onclick=\"resetPassword('$d','')\" style='cursor:pointer' class='badge bg-warning p-2 '>Reset password</span>";
                    return  $reset." <button onclick=\"trigUser('" . $d . "','" . $row['user_disabled'] . "')\" class='btn btn-sm " . $locking_class . "'>" . $locking . "</button> | <a class='btn btn-sm btn-primary mt-2'     href=\"javascript:getpage('setup/user_setup.php?op=edit&username=" . $d . "','page')\">EDIT</a>";
                // } else if ($_SESSION['role_id_sess'] == 003) {
                //     return  "<button onclick=\"trigUser('" . $d . "','" . $row['user_disabled'] . "')\" class='btn btn-sm " . $locking_class . "'>" . $locking . "</button>&nbsp;|&nbsp;<a class='btn btn-sm btn-warning mt-2'   onclick=\"loadModal('setup/user_edit.php?op=edit&username=" . $d . "','modal_div')\"  href=\"javascript:void(0)\" data-toggle=\"modal\" data-target=\"#defaultModalPrimary\" >EDIT</a>";
                // }
            })
        );

        $filter = " AND role_id <> '100' AND role_id <> '$_SESSION[role_id_sess]'";
        $school_users_filter = ($_SESSION['role_id_sess'] == '100' || $_SESSION['role_id_sess'] == '200'  || $_SESSION['role_id_sess'] == '201' || $_SESSION['role_id_sess'] == '300') ? "" :(($_SESSION['role_id_sess'] == '203' || $_SESSION['role_id_sess'] == '302') ?" AND office_state = '$_SESSION[state_id]'":" AND school_id = '$_SESSION[school_id]'");
        $filter = $filter . $school_users_filter;
        $datatableEngine = new engine();

		echo $datatableEngine->generic_table($data,$table_name,$columner,$filter,$primary_key);
    }
    public function generatePwdLink($data)
    {

        $username               = $data['username'];
        $rr                     = $this->runQuery("SELECT username,email FROM userdata WHERE username = '$username'");
        if($rr > 0)
        {
            if (filter_var($rr[0]['email'], FILTER_VALIDATE_EMAIL)) {
                $data                   = $username."::".date('Y-m-d h:i:s');
                $desencrypt             = new DESEncryption();
                $key                    = "accessis4life_tlc";
                $cipher_password        = $desencrypt->des($key, $data, 1, 0, null,null);
                $sqltr_cipher_password  = $desencrypt->stringToHex ($cipher_password);
                $link                   = $sqltr_cipher_password;
                $val                    = $this->getitemlabelarr("userdata",array('username'),array($username),array('firstname','lastname','email'));
                $firstname              = $val['firstname'];
                $lastname               = $val['lastname'];
                $email                  = $val['email'];

                $sql                    = "UPDATE userdata SET reset_pwd_link = '$link', pass_change = '1' WHERE username = '$username' AND pass_change = '0' LIMIT 1";
                $this->runQuery($sql, false);

                $subject = "Attendance Mgt. System - Password Reset Link";
                $tmpl_file = 'email_template/notification.php';

                $message = "To reset your password kindly follow this <a href='".$this->base_url."pwd_reset?ga=".$link."'>link</a> <br /> Or copy and paste the link below in your web browser <br />";
                $message .= $this->base_url."pwd_reset?ga=".$link;
                $text = (isset($_SESSION['username_sess']) && $_SESSION['username_sess'] != $rr[0]['username'])?'A reset password link has been sent to '.$email:'A reset password link has been sent to your email, kindly login to your mail and follow the direction.';
                $data = array(
                    'alert_message' => $message,
                    'full_name' => $firstname,
                    'logo_url' => $this->base_url.'img/logo.jpg',
                    'type' => 'password'
                );

                $stmt = $this->sendMail($email, $subject, $tmpl_file, $data);
                if($stmt == 1):
                    return json_encode(array('response_code'=>0,'response_message'=>$text));
                else:
                    return json_encode(array('response_code' => 20, 'response_message' => 'Password link could not be sent. Please, try again.'));
                endif;
            }else {
                return json_encode(array('response_code'=>20,'response_message'=>'Email email address was not setup properly'));
            }
        }else {
            return json_encode(array('response_code'=>20,'response_message'=>'Username does not exist'));
        }   
    }

    public function confirmCurrent($data)
    {
        $current_password = $data['current'];
        // if(strlen($current_password) < 8):
        //     return json_encode(array('response_code'=>20,'response_message'=>''));
        // else:
            $username = base64_decode($data['username']);

            $get_current_password = $this->getitemlabel('userdata','username',$username,'password');

            $desencrypt          = new DESEncryption();
            $key                 = strtolower($username);
            $cipher_password     = $desencrypt->des($key, $data['current'], 1, 0, null,null);
            $str_cipher_password = $desencrypt->stringToHex ($cipher_password);
            $current_password    = $str_cipher_password;

            if($current_password == $get_current_password):
                return json_encode(array('response_code'=>0,'response_message'=>'Correct'));
            elseif($current_password != $get_current_password):
                return json_encode(array('response_code'=>20,'response_message'=>'Invalid password'));
            endif;
        // endif;
    }
    public function resetPassword($data)
    {
        $validation = $this->validate($data,
            array(
                'new-password'=>'required|min:8',
                'confirm-password'=>'required|matches:new-password'
            ),
            array('new-password'=>'Password','confirm-password'=>'Confirm password')
        );
        if(!$validation['error'])
        {
            $new_password = $data['new-password'];
            $username = $data['username_'];
            
            $regex = $this->validateUserPassword($new_password);

            if($regex != 1){
                return json_encode(array('response_code' => '20','response_message' =>  $regex));
            }
            
            $get_current_password = $this->getitemlabel('userdata','username',$username,'password');

            $desencrypt          = new DESEncryption();
            $key                 = strtolower($username);
            $cipher_password     = $desencrypt->des($key, $new_password, 1, 0, null,null);
            $str_cipher_password = $desencrypt->stringToHex ($cipher_password);
            $password['password']    = $str_cipher_password;
            $password['passchg_logon'] = 0;
            $password['pass_change'] = 1;

            $current_data = $this->log->getCurrentData('userdata','username',$username);

            if($get_current_password == $str_cipher_password):
                return json_encode(array('response_code'=>20,'response_message'=>'Password is the same with your current password.'));
            else:
                $stmt = $this->Update('userdata',$password,array('op','new-password','confirm-password'),array('username'=>$username));
                if($stmt == 1):
                    $this->log->logData($current_data,$data,["table_name"=>'userdata',"table_id"=>$current_data['user_id'],"table_alias"=>'Password Change'],['op','new-password','confirm-password','att-csrf-token-label']);

                    return json_encode(array('response_code'=>0,'response_message'=>'Password has been successfully updated.'));
                else:
                    if($data['current_'] == $data['new-password']):
                        return json_encode(array('response_code'=>20,'response_message'=>'Kindly choose a password that is different from your current one.'));
                    else:
                        return json_encode(array('response_code'=>20,'response_message'=>'Password could not be updated. Please, try again.'));
                    endif;
                endif;
            endif;
        }else{
            return json_encode(array("response_code"=>20,"response_message"=>$validation['messages'][0]));
        }
        //encrypt password with email
    }

    public function getUserID()
    {
        $stmt = $this->runQuery("SELECT max(user_id) AS id FROM userdata")[0]['id'];

        if(empty($stmt)){
            $stmt = '0001';
        }else if($stmt > 0){
            $stmt = $stmt + 1;
        }
        return $stmt;
    }

    public function register($data)
    {
        // check if record does not exists before then insert
        $data['day_1'] = (isset($data['day_1'])) ? 1 : 0;
        $data['day_2'] = (isset($data['day_2'])) ? 1 : 0;
        $data['day_3'] = (isset($data['day_3'])) ? 1 : 0;
        $data['day_4'] = (isset($data['day_4'])) ? 1 : 0;
        $data['day_5'] = (isset($data['day_5'])) ? 1 : 0;
        $data['day_6'] = (isset($data['day_6'])) ? 1 : 0;
        $data['day_7'] = (isset($data['day_7'])) ? 1 : 0;
        $data['passchg_logon'] = (isset($data['passchg_logon'])) ? 1 : 0;
        $data['user_disabled'] = (isset($data['user_disabled'])) ? 1 : 0;
        $data['user_locked']   = (isset($data['user_locked'])) ? 1 : 0;
        $data['posted_user']     = $_SESSION['username_sess'];
        $data['is_mfa'] = (isset($data['is_mfa'])) ? 1 : 0;
        $data['office_state'] = (isset($data['office_state'])) ? $data['office_state'] : 0;
        $data['school_id'] = (isset($data['school_id'])) ? $data['school_id'] : 0;
        $data['user_id'] = $this->getUserID();

        if ($data['operation'] != 'edit') {
            $validation = $this->validate(
                $data,
                array(
                    'firstname' => 'required|min:2',
                    'lastname' => 'required',
                    'mobile_phone' => 'required|int|unique:userdata.mobile_phone|max:11',
                    'sex' => 'required',
                    'role_id' => 'required',
                    'username' => 'required|email|unique:userdata.username',
                    'password' => 'required|min:6'
                ),
                array('username' => 'Username', 'password' => 'Password', 'firstname' => 'First Name', 'lastname' => 'Last name', 'role_id' => 'Role ID', 'mobile_phone' => 'Phone Number', 'sex' => 'Gender')
            );
            if (!$validation['error']) {
                $data['email']       = $data['username'];
                $data['created']     = date('Y-m-d h:i:s');

                $link = str_replace('+','',base64_encode(openssl_random_pseudo_bytes(32)));
                $data['email_token'] = $link ;
                $data['email_token'] = $link ;
                $data['is_email_verified'] = 0;
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    return json_encode(array('response_code' => 20, 'response_message' => $data['email'] . ' is not a valid email address.'));
                }

                $desencrypt          = new DESEncryption();
                $key                 = $data['username'];
                $cipher_password     = $desencrypt->des($key, $data['password'], 1, 0, null, null);
                $str_cipher_password = $desencrypt->stringToHex($cipher_password);
                $data['password']    = $str_cipher_password;
                $office_state = isset($data['office_state']) ? $data['office_state'] : 0;
                $sql = "SELECT username FROM userdata WHERE role_id = '$data[role_id]' AND office_state = '$office_state' school_id='".$data['school_id']."' AND  LIMIT 1";
                $role_cnt = $this->runQuery($sql, false);
                if ($role_cnt < 1) {
                    $count = $this->Insert('userdata', $data, array('op', 'confirm_password', 'operation', 'att-csrf-token-label','PHPSESSID','amp_6e403e'));
                    if ($count == 1) {
                        $full_name = isset($data['school_id'])?$this->getitemlabel('lfs_schools','school_id',$data['school_id'],'school_display_name'):$data['firstname'].' '.$data['lastname'];
                        $email_data = array(
                            "email" => $data["email"],
                            "email_token" =>  $data["email_token"],
                            "full_name" => $full_name,
                            "is_mfa" => $data["is_mfa"],
                            "role_id" => $data['role_id'],
                            "school_id" => $data['school_id'],
                            "school" => $full_name
                        );
                        $this->sendEmailActivationLink($email_data);

                        //                        rename('user_passport/'.$temp_pass,'user_passport/'.$data['email'].".".end($array));
                        return json_encode(array("response_code" => 0, "response_message" => 'Record saved successfully'));
                    } else {
                        return json_encode(array("response_code" => 78, "response_message" => 'Failed to save record'));
                    }
                } else {
                    $role_name = $this->getitemlabel('role', 'role_id', $data['role_id'], 'role_name');
                    $school_name = $this->getitemlabel('lfs_schools', 'school_id', $data['school_id'], 'school_display_name');
                    return json_encode(array("response_code" => 20, "response_message" => $role_name . " already exist for " . $school_name));
                }
            } else {
                return json_encode(array("response_code" => 20, "response_message" => $validation['messages'][0]));
            }
        } else {
            //                EDIT EXISTING USER 
            $data['modified_date'] = date('Y-m-d h:i:s');
            $validation = $this->validate(
                $data,
                array(
                    'firstname' => 'required|min:2',
                    'lastname' => 'required',
                    'mobile_phone' => 'required|int',
                    'sex' => 'required',
                    'role_id' => 'required',
                    'username' => 'required|email',
                ),
                array('firstname' => 'First Name', 'lastname' => 'Last name', 'role_id' => 'Role ID', 'mobile_phone' => 'Phone Number', 'sex' => 'Gender', 'username' => 'Username')
            );
            if (!$validation['error']) {
                $current_data = $this->log->getCurrentData('userdata','username',$data['username']);

                $count = $this->Update('userdata', $data, array('op', 'operation', 'username', 'password', 'att-csrf-token-label','PHPSESSID','amp_6e403e'), array('username' => $data['username']));
                if ($count == 1) {
                    $this->log->logData($current_data,$data,["table_name"=>'userdata',"table_id"=>$current_data['user_id'],"table_alias"=>'Edited User Account'],['op', 'operation', 'username', 'password', 'att-csrf-token-label','modified_date','user_id']);

                    return json_encode(array("response_code" => 0, "response_message" => 'Record saved successfully'));
                } else {
                    return json_encode(array("response_code" => 78, "response_message" => 'Failed to save record'));
                }
            } else {
                return json_encode(array("response_code" => 20, "response_message" => $validation['messages'][0]));
            }
        }
    }
    public function userEdit($data)
    {
        $data['day_1'] = (isset($data['day_1'])) ? 1 : 0;
        $data['day_2'] = (isset($data['day_2'])) ? 1 : 0;
        $data['day_3'] = (isset($data['day_3'])) ? 1 : 0;
        $data['day_4'] = (isset($data['day_4'])) ? 1 : 0;
        $data['day_5'] = (isset($data['day_5'])) ? 1 : 0;
        $data['day_6'] = (isset($data['day_6'])) ? 1 : 0;
        $data['day_7'] = (isset($data['day_7'])) ? 1 : 0;
        $data['is_mfa'] = (isset($data['is_mfa'])) ? 1 : 0;
        $data['passchg_logon'] = (isset($data['passchg_logon'])) ? 1 : 0;
        $data['user_disabled'] = (isset($data['user_disabled'])) ? 1 : 0;
        $data['user_locked']   = (isset($data['user_locked'])) ? 1 : 0;
        $data['posted_user']     = $_SESSION['username_sess'];

        $current_data = $this->log->getCurrentData('userdata','username',$data['username']);

        $cnt = $this->Update('userdata', $data, array('op', 'operation', 'att-csrf-token-label'), array('username' => $data['username']));
        if ($cnt == 1) {
            $this->log->logData($current_data,$data,["table_name"=>'userdata',"table_id"=>$current_data['user_id'],"table_alias"=>'Edited User Account'],['op', 'operation', 'username', 'password', 'att-csrf-token-label','modified_date','user_id']);

            return json_encode(array("response_code" => 0, "response_message" => 'Record saved successfully'));
        } else {
            return json_encode(array("response_code" => 78, "response_message" => 'Failed to save record'));
        }
    }
    
    public function profileEdit($data)
    {
        $validate = $this->validate($data, array('username' => 'required|email', 'firstname' => 'required', 'lastname' => 'required', 'mobile_phone' => 'required', 'sex' => 'required'), 
        array('username' => 'Username','mobile_phone' => 'Phone Number', 'firstname' => 'First Name', 'lastname' => 'Last Name', 'sex' => 'Gender'));
        if (!$validate['error']) {
            $data['is_mfa'] = isset($data['is_mfa'])?1:0;
            $current_data = $this->log->getCurrentData('userdata','username',$data['username']);
            
            $cnt = $this->Update('userdata', $data, array('op', 'operation', 'att-csrf-token-label'), array('username' => $data['username']));
            if ($cnt == 1) {
                $this->log->logData($current_data,$data,["table_name"=>'userdata',"table_id"=>$current_data['user_id'],"table_alias"=>'Edited User Account'],['op', 'operation', 'username', 'password', 'att-csrf-token-label','modified_date','user_id']);

                return json_encode(array("response_code" => 0, "response_message" => 'Record saved successfully'));
            } else {
                return json_encode(array("response_code" => 78, "response_message" => 'No update was made'));
            }
        } else {
            return json_encode(array('response_code' => 13, 'response_message' => $validate['messages'][0]));
        }
    }
    
    public function workingDays($dbrow)
    {
        $days_of_week = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        $db_day       = array('day_1', 'day_2', 'day_3', 'day_4', 'day_5', 'day_6', 'day_7');
        $ddate        = date('w');
        $mssg         = array('code' => 0, 'mssg' => '');
        foreach ($days_of_week as $k => $v) {
            if ($dbrow[$db_day[$k]] == 0 && $ddate == $k) {
                $mssg = array("mssg" => "You are not allowed to login on $days_of_week[$k]", "code" => "44");
            }
        }
        if ($dbrow['passchg_logon'] == '1') {
            $mssg = array("mssg" => "You are required to change your password, follow this link to  <a href='change_psw_logon.php?username={$dbrow['username']}'> change password </a>", "code" => "44");
        }
        return $mssg;
    }
    public function emailPasswordReset($data)
    {
        $email = $data['email'];
        $today = @date("Y-m-d H:i:s");
        $pass_dateexpire = @date("Y-m-d H:i:s", strtotime($today . "+ 24 hours"));

        $current_data = $this->log->getCurrentData('userdata','username',$data['username']);

        $upd = $this->runQuery("UPDATE userdata SET pwd_expiry='" . $pass_dateexpire . "' WHERE username = '$email'");

        $this->log->logData($current_data,$data,["table_name"=>'userdata',"table_id"=>$current_data['user_id'],"table_alias"=>'Password Reset Link'],['op', 'operation', 'username', 'password', 'att-csrf-token-label','modified_date','user_id']);

        $recordBiodata = $this->getItemLabelArr('userdata', array('email'), array($email), '*');

        $fname = $recordBiodata['first_name'];
        $lname = $recordBiodata['last_name'];

        return json_encode(array("response_code" => 0, "response_message" => 'Check your mail'));
    }

    public function sackUser($data)
    {
        $username = $data['username'];
        $status   = ($data['status'] == 1) ? "0" : "1";
        $current_data = $this->log->getCurrentData('userdata','username',$data['username']);

        
        $sql      = "UPDATE userdata SET status = '$status' WHERE username = '$username' LIMIT 1";
        $cc = $this->runQuery($sql, false);
        if ($cc) {
            $this->log->logData($current_data,$data['status'],["table_name"=>'userdata',"table_id"=>$current_data['user_id'],"table_alias"=>'Locked Account'],['op', 'operation', 'username', 'password', 'att-csrf-token-label','modified_date','user_id']);
            return json_encode(array('response_code' => 0, 'response_message' => 'Action on user profile is now effective'));
        } else {
            return json_encode(array('response_code' => 432, 'response_message' => 'Action failed'));
        }
    }
    
    public function changeUserStatus($data)
    {
        $username = $data['username'];
        $status = ($data['current_status'] == 1) ? 0 : 1;

        $current_data = $this->log->getCurrentData('userdata','username',$data['username']);

        $sql = "UPDATE userdata SET user_disabled = '$status' WHERE username = '$username'";
        $this->runQuery($sql, false);

        $this->log->logData($current_data,$data['current_status'],["table_name"=>'userdata',"table_id"=>$current_data['user_id'],"table_alias"=>'Disabled User Account'],['op', 'operation', 'username', 'password', 'att-csrf-token-label','modified_date','user_id']);
        return json_encode(array("response_code" => 0, "response_message" => "updated successfully"));
    }

    public function doForgotPasswordChange($data)
    {
        $validation = $this->validate(
            $data,
            array(
                'username' => 'required',
                'password' => 'required|min:6',
                'confirm_password' => 'required|matches:password'
            ),
            array('username' => 'Username', 'password' => 'Password', 'confirm_password' => 'Current Password')
        );

        if (!$validation['error']) {
            $username      = $data['username'];
            $user_password = $data['password'];
            $key            = $username;
            $desencrypt             = new DESEncryption();
            $cipher_password = $desencrypt->des($key, $user_password, 1, 0, null, null);
            $str_cipher_password = $desencrypt->stringToHex($cipher_password);

            $current_data = $this->log->getCurrentData('userdata','username',$data['username']);

            $query_data = "UPDATE userdata set password='$str_cipher_password', passchg_logon = '0', user_locked = '0' where username= '$username'";
            $result_data = $this->runQuery($query_data, false);
            if ($result_data > 0) {
                $this->log->logData($current_data,['password'=>$str_cipher_password],["table_name"=>'userdata',"table_id"=>$current_data['user_id'],"table_alias"=>'Changed Password'],['op', 'operation', 'username', 'password', 'att-csrf-token-label','modified_date','user_id']);
                return json_encode(array('response_code' => 0, 'response_message' => 'Your password was changed successfully'));
            } else {
                return json_encode(array('response_code' => 45, 'response_message' => 'Your password was changed successfully'));
            }
        } else {
            return json_encode(array("response_code" => 20, "response_message" => $validation['messages'][0]));
        }
    }
    public function doPasswordChange($data)
    {
        $validation = $this->validate(
            $data,
            array(
                'username' => 'required',
                'current_password' => 'required',
                'password' => 'required|min:6',
                'confirm_password' => 'required|matches:password'
            ),
            array('confirm_password' => 'Confirm password', 'username' => 'Username', 'password' => 'User password', 'current_password' => 'Current Password')
        );
        if ($data['current_password'] == $data['password']) {
            $validation['error'] = true;
            $validation['messages'][0] = "Kindly choose a password that is different from your current one.";
        }
        if (!$validation['error']) {
            $username      = $data['username'];
            $user_password = $data['password'];
            $user_curr_password = $data['current_password'];

            $desencrypt = new DESEncryption();
            $key = $username;
            $cipher_password = $desencrypt->des($key, $user_curr_password, 1, 0, null, null);
            $str_cipher_password = $desencrypt->stringToHex($cipher_password);

            $current_data = $this->log->getCurrentData('userdata','username',$data['username']);
            
            $sql = "SELECT username FROM userdata WHERE username = '$username' AND password = '$str_cipher_password'";
            $rr  = $this->runQuery($sql, false);
            if ($rr == 1) {
                
                $cipher_password = $desencrypt->des($key, $user_password, 1, 0, null, null);
                $str_cipher_password = $desencrypt->stringToHex($cipher_password);
                $query_data = "UPDATE userdata set password='$str_cipher_password', passchg_logon = '0' where username= '$username'";
                $result_data = $this->runQuery($query_data, false);

                if ($result_data > 0) {
                    $this->log->logData($current_data,['password'=>$str_cipher_password],["table_name"=>'userdata',"table_id"=>$current_data['user_id'],"table_alias"=>'Changed Password'],['op', 'operation', 'username', 'password', 'att-csrf-token-label','modified_date','user_id']);

                    if ($data['page'] == 'first_login') {
                        return json_encode(array('response_code' => 0, 'response_message' => 'Your password was changed successfully... <a href="index.php">Proceed to login</a>'));
                    } else {
                        return json_encode(array('response_code' => 0, 'response_message' => 'Your password was changed successfully... logging you out'));
                    }
                } else {
                    return json_encode(array('response_code' => 45, 'response_message' => 'Your password could not be changed'));
                }
            } else {
                return json_encode(array('response_code' => 455, 'response_message' => 'current password is invalid'));
            }
        } else {
            return json_encode(array("response_code" => 20, "response_message" => $validation['messages'][0]));
        }
    }
    public function passwordHash($secret)
    {
        $hashvalue = password_hash($secret, PASSWORD_DEFAULT);
        return $hashvalue;
    }

    public function verifyEmail($link)
    {
        $sql = "SELECT firstname,lastname,username FROM userdata WHERE email_token = '$link' LIMIT 1";
        $result = $this->runQuery($sql);
        if($result > 0) {
            $current_data = $this->log->getCurrentData('userdata','username',$result[0]['username']);

            
            $sql = "UPDATE userdata SET email_token = '', is_email_verified = 1 WHERE username = '".$result[0]['username']."' AND email_token='$link' LIMIT 1";
            $this->runQuery($sql, false);

            $this->log->logData($current_data,['email_token'=>$link],["table_name"=>'userdata',"table_id"=>$current_data['user_id'],"table_alias"=>'Verifed Email'],['op', 'operation', 'username', 'password', 'att-csrf-token-label','modified_date','user_id']);

            return json_encode(array('response_code'=>0,'response_message'=>'Thank you for verifying your email. You can now proceed to <a href="./">login</a>','data'=>array('username'=>$result[0]['username'],'firstname'=>$result[0]['firstname'],'lastname'=>$result[0]['lastname'])));
        }else {
            return json_encode(array('response_code'=>20,'response_message'=>'This link has already been used or tampared with'));
        }
    }

    public function confirm2FA($data)
    {
        $username = base64_decode($data['username']);
        $encrypted_password  = $this->getitemlabel('userdata','username',$username,'password');
        $plain_password = $this->DecryptData($username, $encrypted_password);
        $otp = $data['digit-1'].$data['digit-2'].$data['digit-3'].$data['digit-4'].$data['digit-5'].$data['digit-6'];
        $pin['mfa_otp'] = '';
        $pin['otp_date'] = '';
        
        $stmt = $this->getItemLabelArr('userdata', array('mfa_otp', 'username'), array($otp, $username), array('mfa_otp','otp_date'));
        
        if(!$stmt){
            return json_encode(array('response_code'=>20,'response_message'=>'Invalid OTP'));
        }

        $date = isset($stmt['otp_date'])?$stmt['otp_date']:'';

        $date1  = strtotime($date);  
        $date2  = strtotime(date('Y-m-d h:i:s'));  
        // Formulate the Difference between two dates 
        $diff   = abs($date2 - $date1);
        // To get the year divide the resultant date into 
        // total seconds in a year (365*60*60*24) 
        $years  = floor($diff / (365*60*60*24));   
        $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));  
        $days   = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
        $hours  = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24) / (60*60));
        $mins  = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60) / (60));
        if($mins > 10){
            return json_encode(array('response_code'=>20,'response_message'=>'This OTP has expired', 'expired'=>true));
        }else{
            $stmt = $this->Update('userdata',$pin,[],array('username'=>$username,'mfa_otp'=>$otp));
            if($stmt == 1){
                $retval = json_decode($this->signIn($username, $plain_password),true);
                
                return json_encode(array('response_code'=>$retval['response_code'],'response_message'=>$retval['response_message'], 'status'=>isset($retval['status'])?$retval['status']:''));
            }else{
                return json_encode(array('response_code'=>20,'response_message'=>'Invalid OTP'));
            }
        }
    }

    public function maskEmail($email)
    {
        if(filter_var($email, FILTER_VALIDATE_EMAIL))
        {
            list($first, $last) = explode('@', $email);
            $first = str_replace(substr($first, '3'), str_repeat('*', strlen($first)-3), $first);
            $last = explode('.', $last);
            $last_domain = str_replace(substr($last['0'], '1'), str_repeat('*', strlen($last['0'])-1), $last['0']);
            $maskEmail = $first.'@'.$last_domain.'.'.$last['1'];
            return $maskEmail;
        }
    }
    
    public function generate2FAPIN($username)
    {
        if(is_array($username)){
            $username = base64_decode($username['username']);
        }else{
            $username = $username;
        }

        $pin = substr(str_shuffle('12345678900987654321'),0,6);
        $data['mfa_otp'] = $pin;
        $data['otp_date'] = date('Y-m-d h:i:s');
        $stmt = $this->Update('userdata',$data,[],array('username'=>$username));

        $subject = "Attendance Mgt. System - 2-Factor Authenticaion PIN";
        $tmpl_file = 'email_template/notification.php';

        $message = "<b>".$pin."</b> is your your 2-Factor Authentication Pin.";
        $data = array(
            'alert_message' => $message,
            'full_name' => $this->getitemlabel('userdata','username',$username,'firstname'),
            'logo_url' => $this->base_url.'img/logo.jpg',
            'type' => 'otp'
        );

        if($_SERVER['REMOTE_ADDR'] == '::1' OR $_SERVER['REMOTE_ADDR'] == 'localhost'){
            return json_encode(array('response_code'=>0));
        }

        $stmt = $this->sendMail($this->getitemlabel('userdata','username',$username,'email'), $subject, $tmpl_file, $data);
        if($stmt == 1):
            return json_encode(array('response_code'=>0));
        else:
            return json_encode(array('response_code' => 20));
        endif;

    }
    public function verifyLink($link)
    {
        $desencrypt      = new DESEncryption();
        $key             = "accessis4life_tlc";
        $json_value      = $this->DecryptData($key,$link);
        $arr             = explode("::",$json_value);
        $date            = $arr[1];
        $username        = $arr[0];

        $sql = "SELECT reset_pwd_link,firstname,lastname,username FROM userdata WHERE username = '$username' AND reset_pwd_link = '$link' LIMIT 1";
        $result = $this->runQuery($sql);
        if($result > 0)
        {
            $date1  = strtotime($date);  
            $date2  = strtotime(date('Y-m-d h:i:s'));  
            // Formulate the Difference between two dates 
            $diff   = abs($date2 - $date1);
            // To get the year divide the resultant date into 
            // total seconds in a year (365*60*60*24) 
            $years  = floor($diff / (365*60*60*24));   
            $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));  
            $days   = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
            $hours  = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24) / (60*60));
            if($hours > 72)
            {
                return json_encode(array('response_code'=>88,'response_message'=>'This link has expired'));
            }else
            {
                $current_data = $this->log->getCurrentData('userdata','email',$username);

                $sql = "UPDATE userdata SET reset_pwd_link = '', pass_change = '0' WHERE email = '$username' LIMIT 1";
                $this->runQuery($sql, false);
                
                $this->log->logData($current_data,['reset_pwd_link'=>'','pass_change'=>0],["table_name"=>'userdata',"table_id"=>$current_data['user_id'],"table_alias"=>'Email Activation Link'],['op', 'operation', 'username', 'password', 'att-csrf-token-label','modified_date','user_id']);
                return json_encode(array('response_code'=>0,'response_message'=>'OK','data'=>array('username'=>$result[0]['username'],'firstname'=>$result[0]['firstname'],'lastname'=>$result[0]['lastname'])));
            }
        }else
        {
            return json_encode(array('response_code'=>848,'response_message'=>'This link has already been used or tampared with'));
        }
    }
    public function takeAttendance($data)
    {
        if(!isset($data['key'])){
            return json_encode(array('response_code' => 20, 'response_message'=>'Invalid parameter was parsed','passport'=>'img/avartar.png'));
        }else{
            $staff_id = base64_decode($data['key']);
            // var_dump($staff_id);
            $stmt = $this->getItemLabelArr('staff',array('staff_id'),array($staff_id),'*');
            if($stmt == ""){
                return json_encode(array('response_code' => 20, 'response_message'=>'Staff could be identified.','passport'=>'img/avartar.png'));
            }
            if($stmt['passport'] != ""){
                $passport = $stmt['passport'];
            }else{
                if(in_array(strtolower($stmt['gender']), array('m','male'))){
                    $passport = 'img/avartar-m.png';
                }elseif(in_array(strtolower($stmt['gender']), array('f','female'))){
                    $passport = 'img/avartar-f.png';
                }else{
                    $passport = 'img/avartar.png';
                }
            }

            $days_allowed = array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
            if(!in_array($this->day_name(), $days_allowed)){
                return json_encode(array('response_code' => 20, 'response_message'=>"Attendance is not applicable for {$this->day_name()}s",'passport'=>$passport));
            }

            $today = date('Y-m-d');

            $check_attendance = $this->getItemLabelArr('attendance_log',array('staff_id','day_taken'),array($staff_id,$today),'*');
            if($check_attendance != ""){
                return json_encode(array('response_code' => 20, 'response_message'=>'You attendance has already been taken for today.','passport'=>$passport));
            }

            $take['staff_id'] = $staff_id;
            $take['day_taken'] = $today;
            $take['created'] = date('Y-m-d h:i:s');
            $take['week_taken'] = $this->current_week();

            $log = $this->Insert('attendance_log',$take,[]);
            if($log > 0){
                return json_encode(array('response_code' => 0, 'response_message'=>"Thank you, {$stmt['firstname']} for showing up today.","passport"=>$passport));
            }else{
                return json_encode(array('response_code' => 20, 'response_message'=>'We could not log your reguest. Please, try again.',"passport"=>$passport));
            }
        }
        
    }

    public function day_name()
    {
        // Get the current timestamp
        $timestamp = time();

        // Get the name of the current day of the week (e.g. "Monday")
        $day_name = date("l", $timestamp);

        // Output the day of the week
        return $day_name;

    }
    public function current_week()
    {
        // Set the default timezone to your local timezone
        date_default_timezone_set('Africa/Lagos');

        // Get the current year and week number in ISO week date format
        $current_week = date('o-\WW');

        // Output the current week
        return $current_week;

    }

    public function regenerateLink($data)
    {
        if (empty($data['username'])) {
            return json_encode(array("response_code" => 20, "response_message" => 'Username field required! Please enter your username!'));
        }
        $records = $this->getItemLabelArr("userdata", array("username"), array($data["username"]), array("role_id","is_mfa", "is_email_verified", "email_token", "firstname", "lastname", "email", "username","school_id"));
        $link = str_replace('+','',base64_encode(openssl_random_pseudo_bytes(32)));

        if (empty($records)) {
            return json_encode(array("response_code" => 20, "response_message" => 'User account not found on our system!'));
        }

        if (isset($records['email_token']) && $records['email_token'] != "" && $records['is_email_verified'] == 0) {
            $message = "Please, kindly follow the link that was sent to ".$records['email']." to verify your email address.";
            return json_encode(array('response_code'=>20,'response_message'=>$message, 'status'=>100));
        }
        

        if (isset($records['is_email_verified']) && $records['is_email_verified'] == "1") {
            return json_encode(array("response_code" => 20, "response_message" => 'User account has previously been verified for 2 - Factor Authentication!'));
        }

        $current_data = $this->log->getCurrentData('userdata','username',$records['username']);
        
        $count = $this->Update('userdata', array("email_token" => $link), array('op', 'username'), array("username" => $records["username"]));
        if ($count > 0) {

            $this->log->logData($current_data,$data['email_token']=$link,["table_name"=>'userdata',"table_id"=>$current_data['user_id'],"table_alias"=>'Email Activation Link'],['op', 'operation', 'username', 'password', 'att-csrf-token-label','modified_date','user_id']);

            $email_data = array(
                "email" => $records["email"],
                "email_token" => $link,
                "full_name" => $records["firstname"] . " " . $records["lastname"],
                "school" => $this->getitemlabel("lfs_schools","school_id",$records['school_id'],'school_display_name'),
                "role_id" => $records['role_id'],
                "type" => '',
                "school_id" => $records['school_id']
            );
            return $this->sendEmailActivationLink($email_data);
        } else {
            return json_encode(array('response_code' => 20, 'response_message' => 'Unable to send activation link at the moment!, Please try again'));
        }
    }

    public function sendEmailActivationLink($data)
    {
        $email = $data['email'];
        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {

            $link = $this->base_url . "verification?ga=" . $data['email_token'] . "&type=" . base64_encode("email");
            $url = "<a href='" . $link . "'>link</a>";
            $url_1 = '<a href="' . $link . '" style="background-color: #a55198;color:#fff; padding:10px; text-decoration: none;" target="_blank">Activate Now </a>';
            $subject = "Attendance Mgt. System - Account Activation Link for " . $data['full_name'];
            $tmpl_file = 'email_template/notification.php';
            $school = (isset($data['school']) && $data['school'] !="")?" for ".$data['school']:'';
            $role_name = $this->getitemlabel('role','role_id',$data['role_id'],'role_name');
            $message = (isset($data['school_id']) && $data['school_id'] !="")?"<p>You have been profiled as the ".$role_name." ".$school.".</p>":"<p>You have been profiled as a ".$role_name.".</p>";
            $message .= "<br />  <p>Please, click " . $url . " to activate your account.</p><br /><p>" . $url_1 . "</p><br /> <p>Or copy and paste the link below in any web browser to activate your account <b style='font-size:12px'>" . $link . "</b></p>"; //first contact person
            $content = array(
                'alert_message' => $message,
                'full_name' => $data['full_name'],
                'logo_url' => $this->base_url . 'img/logo.jpg',
                'type' => isset($data['type'])?$data['type']:''
            );

            $stmt = $this->sendMail($email, $subject, $tmpl_file, $content);
            if ($stmt == 1) :
                return json_encode(array('response_code' => 0, 'response_message' =>'Email verification has been sent to you mail'));
            else :
                return json_encode(array('response_code' => 20, 'response_message' => 'Email verification link could not be sent. Please, try again.'));
            endif;
        } else {
            return json_encode(array('response_code' => 20, 'response_message' => 'The supplied email address is not valid'));
        }
    }

    public function getRoleOptions($default_role, $selected = "") 
    {

        $role_id = isset($_SESSION['role_id_sess']) ? $_SESSION['role_id_sess'] : '';

        //school admin
        if($role_id == '202')
        {
            $role_id = '301'; //accountant only
            $role_name = $this->getitemlabel("role", "role_id", $role_id, "role_name");
            $selectOptions = "<option value='$role_id'>$role_name</option>";
        }
        elseif($role_id == '100' || $role_id == '200') {
            $sql = "SELECT role_id, role_name FROM role WHERE role_id NOT IN(100) AND is_deleted NOT IN (1)";
            $selectOptions = $this->getSelectWithQuery($sql, 'role_id', array('role_id', 'role_name'), "Role", $selected);
        }
        else {
            $role_name = $this->getitemlabel("role", "role_id", $default_role, "role_name");
            $selectOptions = ($role_name) ? "<option value='$default_role'>$role_name</option>"  : "<option>::NO AVAILABLE ROLE::</option>";
        }
        
        return $selectOptions;    
    }
}
