<?php
@session_start();
///////////////////	
// error_reporting(1);
ini_set('display_errors', 1);
error_reporting(E_ERROR);
ini_set("max_execution_time", 0);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once("connection.php");
require('desencrypt.php');
require('aes.php');
require('validation.php');
//use Aws\S3\S3Client;  
//use Aws\Exception\AwsException;
require_once('phpqrcode/qrlib.php');
require_once __DIR__ . '/phpmailer/src/Exception.php';
require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';

//////////////////////
class Model extends validation
{
	private $hex_iv = '00000000000000000000000000000000';
	private $key = '12kdknfim.dsmoioqw09djjd';
	private $debug = false;
	public $myconn = "";
	function __construct()
	{
		$this->key = hash('sha256', $this->key, true);

		$cnx = new Connection();
        $this->myconn = $cnx->connect();

	}
	function begin(){
        $this->myconn->begin_transaction(MYSQLI_TRANS_START_READ_ONLY);
    }
    function commit(){
        $this->myconn->commit();
    }
    function rollback(){
        $this->myconn->rollback();
    } 
	public function createAwsS3($key, $secret)
	{
		$s3Client = new S3Client([
			'region' => 'us-east-2',
			'version' => '2006-03-01',
			'credentials' => [
				'key'    => $key,
				'secret' => $secret
			]
		]);
		return $s3Client;
	}
	
	public function arrayImplode($separated, $data = array())
	{
		$string = "";
		$fields = array_keys($data);
		$values = array_values(array_map('mysql_escape_string', $data));
		$i = 0;
		while ($fields[$i]) {
			if ($i > 0) $string .= $separated;
			$string .= sprintf("%s = '%s'", $fields[$i], $values[$i]);
			$i++;
		}
		return $string;
	}
	public function DecryptData($key, $password)
	{
		$desencrypt = new DESEncryption();
		$mmm = $desencrypt->hexToString($password);
		return strip_tags($desencrypt->des($key, $mmm, 0, 0, null, null));
	}
	function EncryptData($username, $userpassword)
	{
		$desencrypt = new DESEncryption();
		$key = $username;
		$cipher_password = $desencrypt->des($key, $userpassword, 1, 0, null, null);
		$str_cipher_password = $desencrypt->stringToHex($cipher_password);
		return $str_cipher_password;
	}

	public function runQuery($sql, $object = true)
    {
		file_put_contents('query_logger.php', $sql, FILE_APPEND);
		$cnx = new Connection();
		$this->myconn = $cnx->connect();
	
        $result = $this->myconn->query($sql);
        if($this->myconn->error != "") {
            return ($object) ? null : 0;
        }

        $count = 0;
        if($object == true) {
            $count = ($this->myconn->error == "" && $result->num_rows > 0) ? $result->num_rows : $this->myconn->affected_rows;
        }else{
            $count = $this->myconn->affected_rows;
        }
       
        if($object)
        {
             if($count > 0)
             {
                 $data = array();
                 while($row = $result->fetch_assoc())
                 {
                    $data[] = $row;
                 }
                 return $data;
             }else
             {
                 return null;
             }
        }else
        {
             return $count;
        }
    }
	
	function Insert($table, $arr, $exp_arr)
	{
		$patch1  = "(";
		$patch2  = "(";
		$cnx          = new Connection();
		$this->myconn = $cnx->connect();
		foreach ($arr as $key => $value) {
			if (!in_array($key, $exp_arr)) {
				$patch1 .= $key . ",";
				$patch2 .= "'" . mysqli_real_escape_string($this->myconn, $value) . "',";
			}
		}
		$patch1 =  substr($patch1, 0, -1) . ")";
		$patch2 =  substr($patch2, 0, -1) . ")";
		$sql = "insert into " . $table . " " . $patch1 . " VALUES " . $patch2;
		file_put_contents('m_query.txt', $sql);
		$num_row = $this->runQuery($sql, false);
		return $num_row;
	}
	public function getInsert_id($conn)
	{
		return mysqli_insert_id($conn);
	}
	function Update($table, $arr, $exp_arr, $clause)
	{
		$patch1     = "";
		$key_id     = "";
		$cnx          = new Connection();
		$this->myconn = $cnx->connect();
		foreach ($arr as $key => $value) {
			if (!in_array($key, $exp_arr)) {
				$patch1 .= $key . "='" . mysqli_real_escape_string($this->myconn, $value) . "',";
			}
		}
		foreach ($clause as $key => $value) {
			$key_id .= " " . $key . "='" . $value . "' AND";
		}
		$key_id  =  substr($key_id, 0, -3);
		$patch1  =  substr($patch1, 0, -1);
		$sql    = "UPDATE " . $table . " SET " . $patch1 . " WHERE " . $key_id;
		file_put_contents("user_edit.txt", $sql);
		$num_row = $this->runQuery($sql, false);
		return $num_row;
	}
	public function insertMysql($table, $data = array())
	{
		$fields = implode(', ', array_keys($data));
		$values = implode('", "', array_map('mysql_escape_string', $data));
		$query = sprintf('INSERT INTO %s (%s) VALUES ("%s")', $table, $fields, $values);
		return $this->queryMysql($query);
	}
	public function encrypt($data, $secret = "12kdknfim.dsmoioqw09djjd")
	{
		//Generate a key from a hash
		$key = md5(utf8_encode($secret), true);

		//Take first 8 bytes of $key and append them to the end of $key.
		$key .= substr($key, 0, 8);

		//Pad for PKCS7
		$blockSize = mcrypt_get_block_size('tripledes', 'ecb');
		$len = strlen($data);
		$pad = $blockSize - ($len % $blockSize);
		$data .= str_repeat(chr($pad), $pad);

		//Encrypt data
		$encData = mcrypt_encrypt('tripledes', $key, $data, 'ecb');

		return base64_encode($encData);
	}
	public function preEncryptedData($data)
	{
		$reData = array();
		foreach ($data as $key => $value) {
			$reData[$key] = $this->encrypt($value);
		}
		return $reData;
	}
	
	public function queryMysql($sql)
	{
		if ($this->debug === false) {
			try {
				$result = mysql_query($sql);
				if ($result === false) {
					throw new Exception('MySQL Query Error: ' . mysql_error());
					//$result = '-1';
				}
				return $result;
			} catch (Exception $e) {
				return $e->getMessage();
				//exit();
			}
		} else {
			printf('<textarea>%s</textarea>', $sql);
		}
	}
	
	///////////////////////////////////////////////////////		
	function exister($table, $field1, $field2, $value1, $value2)
	{
		// counter function=>to return numbers of rows fetched or found
		function counter($resource)
		{
			return mysql_num_rows($resource);
		}
		//////////////////////////
		$existed = mysql_query("SELECT * FROM $table WHERE $field1='$value1' and $field2='$value2'") or die('Inavlid Exist Query' . mysql_error());
		$no = counter($existed);
		return $no;
	}

	function updatepinmissed($username)
	{
		$query = "update userdata set pin_missed=pin_missed+1 where username= '$username'";
		//echo $query;
		$resultid = $this->runQuery($query, false);
		$numrows = $resultid;
	}
	function resetpinmissed($username)
	{
		$query = "update userdata set pin_missed=0 where username= '$username'";
		//echo $query;
		$resultid = $this->runQuery($query, false);
		$numrows = $resultid;
	}
	function updateuserlock($username, $value)
	{
		$query = "update userdata set user_locked='$value', pin_missed = 0 where username= '$username'";
		//		echo $query;
		$resultid = $this->runQuery($query, false);
		$numrows = $resultid;
	}

	//// select a field from a table
	function getitemlabel($tablename, $table_col, $table_val, $ret_val)
	{
		$label = "";
		$table_filter = " where " . $table_col . "='" . $table_val . "'";

		$query = "select " . $ret_val . " from " . $tablename . $table_filter;
		//echo $query;
		$result = $this->runQuery($query);
		$numrows = (!empty($result)) ? count($result) : 0;
		if ($numrows > 0) {
			//		$row = mysql_fetch_array($result);
			foreach ($result as $row) {
				$label = $row[$ret_val];
			}
		}
		return $label;
	}

	function getSelectWithQuery($query, $optDisplayVal, $selarr, $initOpt, $opt = "")
	{ //opt = selected value

		$options = "<option value=''>::: Select " . $initOpt . " :::</option>";
		$filter = '';

		$optDisplayName = '';
		$cnx = new Connection();
		$this->myconn = $cnx->connect();
		$result = mysqli_query($this->myconn, $query);
		// echo $query;

		// echo $this->myconn->error;
		// print_r($result);

		$numrows = $result->num_rows;
		if ($numrows > 0) {

			for ($i = 0; $i < $numrows; $i++) {
				$row = $result->fetch_array();

				for ($j = 0; $j < count($selarr); $j++) {
					if ($j > 0) {
						$optDisplayName .= $row[$selarr[$j]] . " ";
					}
				}

				if ($opt == $row[$optDisplayVal]) $filter = 'selected';

				$options = $options . "<option value='$row[$optDisplayVal]' $filter >$optDisplayName</option>";
				$filter = '';
				$optDisplayName = "";
			}
		}

		return $options;
	}

	//////////
	function getrecordset($tablename, $table_col, $table_val)
	{
		$label = "";
		$table_filter = " where " . $table_col . "='" . $table_val . "'";

		$query = "select * from " . $tablename . $table_filter;
		//echo $query;
		$result = mysql_query($query);
		//$numrows = mysql_num_rows($result);
		/*
		if($numrows > 0){
			$row = mysql_fetch_array($result);
			$label = $row[$ret_val];
		}
		*/
		return $result;
	}
	/////////////////
	function getrecordsetdata($query)
	{
		$query = $query;
		//echo $query;
		$result = mysql_query($query);
		return $result;
	}

	///////////////////////////////////
	function validatepassword($user, $password)
	{
		//echo 'country code : '.$countrycode;
		$desencrypt = new DESEncryption();
		$key = $user; //"mantraa360";
		$cipher_password = $desencrypt->des($key, $password, 1, 0, null, null);
		$str_cipher_password = $desencrypt->stringToHex($cipher_password);

		$label = "";
		$table_filter = " where username='" . $user . "' and password='" . $str_cipher_password . "'";

		$query = "select * from userdata" . $table_filter;
		//echo $query;
		$result = mysql_query($query);
		$numrows = mysql_num_rows($result);
		if ($numrows > 0) $label = "1";
		else $label = "-1";

		return $label;
	}

	// Change to user profile password
	function doUserPasswordChange($username, $user_password)
	{
		//		auditTrail("update","Change Password","userdata","changepassword.php","username",$username);
		$desencrypt = new DESEncryption();
		$key = $username;
		$cipher_password = $desencrypt->des($key, $user_password, 1, 0, null, null);
		$str_cipher_password = $desencrypt->stringToHex($cipher_password);
		$query_data = "update userdata set password='$str_cipher_password' where username= '$username'";
		//echo $query_data;
		$result_data = mysql_query($query_data);
		$count_entry = mysql_affected_rows();

		return $count_entry;
	}

	function paddZeros($id, $length)
	{
		$data = "";
		$zeros = "";
		$rem_len = $length - strlen($id);

		if ($rem_len > 0) {
			for ($i = 0; $i < $rem_len; $i++) {
				$zeros .= "0";
			}
			$data = $zeros . $id;
		} else {
			$data = $id;
		}
		return $data;
	}

	///////////////////////////////
	public function getnextid($tablename){
        // global $myconn ;
        $id = 0;
         // Get the new id
         $query_sel = "select table_id from gendata where table_name= '$tablename'";
         //echo $query_sel;
         
		 $cnx = new Connection();
		$this->myconn = $cnx->connect();
		
        $result_sel = $this->myconn->query($query_sel);
        $numrows_sel= $result_sel->num_rows;  

        //echo $numrows_sel;

        if($numrows_sel>0){
            $row=$result_sel->fetch_assoc();
            
            $id = $row['table_id']+1;
            
            //result count when it reaches 
            if($id > 999999998){
                $query = "update gendata set table_id=0 where table_name= '$tablename'";
                //echo $query;
                $resultid =  $this->myconn->query($query);
            }
        }

        $query = "update gendata set table_id=table_id+1 where table_name= '$tablename'";
        //echo $query;
 
        $resultid = $this->myconn->query($query);
        $numrows = @$resultid->num_rows; 
 
        //echo 'result '.$resultid . " :: ";
        if($numrows_sel==0){
            $id = 1;
            $query_ins = "insert into gendata values ('$tablename', 1)";
            //echo $query_ins;
            
            $result_ins = $this->myconn->query($query_ins);
            //echo $this->myconn->error;
            $numrows= $result_ins;
            
            //echo $numrows . ":::";  
        }
      
        return $id;
    }
	//////////////////////////////////////////
	function getuniqueid($y, $m, $d)
	{
		$month_year = array(
			'01' => '025',
			'02' => '468',
			'03' => '469',
			'04' => '431',
			'05' => '542',
			'06' => '790',
			'07' => '138',
			'08' => '340',
			'09' => '356',
			'10' => '763',
			'11' => '845',
			'12' => '890'
		);
		$year = array(
			'2009' => '111',
			'2010' => '222',
			'2011' => '333',
			'2012' => '444',
			'2013' => '555',
			'2014' => '777',
			'2015' => '000',
			'2016' => '666',
			'2017' => '999',
			'2018' => '123',
			'2019' => '321',
			'2020' => '431',
			'2021' => '521',
			'2022' => '146',
			'2023' => '246',
			'2024' => '357',
			'2025' => '768',
			'2026' => '430',
			'2027' => '770',
			'2028' => '773',
			'2029' => '873',
			'2030' => '962',
			'2031' => '909',
			'2032' => '830',
			'2033' => '349',
			'2034' => '457',
			'2035' => '248'
		);

		$day = array(
			'01' => '50',
			'02' => '31',
			'03' => '23',
			'04' => '12',
			'05' => '54',
			'06' => '67',
			'07' => '87',
			'08' => '90',
			'09' => '11',
			'10' => '34',
			'11' => '22',
			'12' => '38',
			'13' => '88',
			'14' => '78',
			'15' => '33',
			'16' => '54',
			'17' => '67',
			'18' => '77',
			'19' => '29',
			'20' => '59',
			'21' => '17',
			'22' => '32',
			'23' => '44',
			'24' => '66',
			'25' => '00',
			'26' => '04',
			'27' => '05',
			'28' => '03',
			'29' => '08',
			'30' => '20',
			'31' => '45'
		);

		$unique_id = $year[$y] . $month_year[$m] . $day[$d];
		return $unique_id;
	}
	//////////////////////////////////////////
	function getuniqueid1($y, $m, $d)
	{
		$month_year = array(
			'01' => '25',
			'02' => '68',
			'03' => '69',
			'04' => '31',
			'05' => '42',
			'06' => '90',
			'07' => '38',
			'08' => '40',
			'09' => '56',
			'10' => '63',
			'11' => '45',
			'12' => '90'
		);
		$year = array(
			'2012' => '444',
			'2013' => '555',
			'2014' => '777',
			'2015' => '000',
			'2016' => '666',
			'2017' => '999',
			'2018' => '123',
			'2019' => '321',
			'2020' => '431',
			'2021' => '521',
			'2022' => '146',
			'2023' => '246',
			'2024' => '357',
			'2025' => '768',
			'2026' => '430',
			'2027' => '770',
			'2028' => '773',
			'2029' => '873',
			'2030' => '962',
			'2031' => '909',
			'2032' => '830',
			'2033' => '349',
			'2034' => '457',
			'2035' => '888',
			'2036' => '985',
			'2037' => '394',
			'2038' => '125',
			'2039' => '745',
			'2040' => '236'
		);

		$day = array(
			'01' => '50',
			'02' => '31',
			'03' => '23',
			'04' => '12',
			'05' => '54',
			'06' => '67',
			'07' => '87',
			'08' => '90',
			'09' => '11',
			'10' => '34',
			'11' => '22',
			'12' => '38',
			'13' => '88',
			'14' => '78',
			'15' => '33',
			'16' => '54',
			'17' => '67',
			'18' => '77',
			'19' => '29',
			'20' => '59',
			'21' => '17',
			'22' => '32',
			'23' => '44',
			'24' => '66',
			'25' => '00',
			'26' => '04',
			'27' => '05',
			'28' => '03',
			'29' => '08',
			'30' => '20',
			'31' => '45'
		);

		$unique_id = $year[$y] . $day[$d];
		return $unique_id;
	}

	function doMenuGroup($menu_id, $exist_role)
	{
		$comp_id = "#";
		$count_entry = 0;
		$exist_role_arr = explode(",", $exist_role);
		$role_id = "";
		for ($i = 0; $i < count($exist_role_arr); $i++) {
			$role_id = $role_id . "'" . $exist_role_arr[$i] . "', ";
		}
		$role_id = substr($role_id, 0, (strlen($role_id) - 2));
		$query_data = "delete from menugroup where role_id not in ($role_id, 100) and menu_id='$menu_id' ";
		//echo $query_data.'<br>';
		$result_data = mysql_query($query_data);
		$count_entry += mysql_affected_rows();

		for ($i = 0; $i < count($exist_role_arr); $i++) {
			echo $query_data_i = "insert into menugroup values ('$exist_role_arr[$i]','$menu_id')";
			//echo $query_data_i.'<br>';
			$result_data_i = mysql_query($query_data_i);
			$count_entry += mysql_affected_rows();
		}

		//echo "Count Entry :: "+$count_entry;
		return $count_entry;
	}
	////////////////////////////////////////////////

	function gettableselect($tablename, $field1, $field2, $opt)
	{
		$filter = "";
		$options = "<option value=''>::: please select option ::: </option>";
		$query = "select distinct $field1, $field2 from $tablename  " . $filter;
		//echo $query;
		$result = mysql_query($query);
		$numrows = mysql_num_rows($result);
		if ($numrows > 0) {
			for ($i = 0; $i < $numrows; $i++) {
				$row = mysql_fetch_array($result);
				//echo $row['country_code'];
				if ($opt == $row[$field1]) $filter = 'selected';
				//echo ($opt=='$row["country_code"]'?'selected':'None');
				$options = $options . "<option value='$row[$field1]' $filter >$row[$field2]</option>";
				$filter = '';
			}
		}
		return $options;
	}
	///////////////////////////////////
	function gettableselect2($tablename, $field1, $field2, $opt, $opt2, $opt3)
	{
		$filter = "";
		$options = "<option value=''>::: please select option ::: </option>";
		$query = "select distinct $field1, $field2 from $tablename  where $opt2=$opt3" . $filter;
		//echo $query;
		$result = mysql_query($query);
		$numrows = mysql_num_rows($result);
		if ($numrows > 0) {
			for ($i = 0; $i < $numrows; $i++) {
				$row = mysql_fetch_array($result);
				//echo $row['country_code'];
				if ($opt == $row[$field1]) $filter = 'selected';
				//echo ($opt=='$row["country_code"]'?'selected':'None');
				$options = $options . "<option value='$row[$field1]' $filter >$row[$field2]</option>";
				$filter = '';
			}
		}
		return $options;
	}
	///////////////////////////////////
	function gettableselectorder($tablename, $field1, $field2, $opt, $order)
	{
		$filter = "";
		$order_by = "";
		$options = "<option value=''>::: please select option ::: </option>";
		if ($order != '') $order_by = " order by " . $order;
		$query = "select distinct $field1, $field2 from $tablename  " . $filter . $order_by;
		//echo $query;
		$result = mysql_query($query);
		$numrows = mysql_num_rows($result);
		if ($numrows > 0) {
			for ($i = 0; $i < $numrows; $i++) {
				$row = mysql_fetch_array($result);
				//echo $row['country_code'];
				if ($opt == $row[$field1]) $filter = 'selected';
				//echo ($opt=='$row["country_code"]'?'selected':'None');
				$options = $options . "<option value='$row[$field1]' $filter >$row[$field2]</option>";
				$filter = '';
			}
		}
		return $options;
	}
	/////////////////////////////////////
	function getdataselect($sql)
	{
		$filter = "";
		$options = "<option value=''>::: please select option ::: </option>";
		//$query = "select distinct $field1, $field2 from $tablename  ".$filter;
		//echo $sql;
		$result = mysql_query($sql);
		$numrows = mysql_num_rows($result);
		if ($numrows > 0) {
			for ($i = 0; $i < $numrows; $i++) {
				$row = mysql_fetch_array($result);
				$options = $options . "<option value='$row[0]' $filter >$row[1]</option>";
				$filter = '';
			}
		}
		return $options;
	}


	function getTblField($tablename, $field1, $field2, $field3)
	{
		$query = "select distinct $field1 from $tablename  where $field2='$field3'";
		//echo $query;
		$result = mysql_query($query);
		$numrows = mysql_num_rows($result);
		if ($numrows > 0) {
			$row = mysql_fetch_array($result);
			$options = $row[$field1];
		}
		return $options;
	}

	function getTblItemList($tablename, $field1)
	{
		$options = "<option value=''>::: please select option ::: </option>";
		$query = "select distinct $field1 from $tablename";
		//echo $query;
		$result = mysql_query($query);
		while ($row = mysql_fetch_array($result)) {
			$options .= "<option value='$row[$field1]'>$row[$field1]</option>";
		}
		return $options;
	}

	function getFormInput($tablename, $field2, $field3, $field4, $field5)
	{
		$query = "select * from $tablename  where $field2='$field3' and $field4='$field5'";
		//echo $query;
		$result = mysql_query($query);
		//$numrows = mysql_num_rows($result);
		/*while($row = mysql_fetch_array($result)){
			$options .= "<input type='checkbox' name='<?php echo $row[$field1]; ?>' id='<?php echo $row[$field1]; ?>'> ".$row[$field]."  &nbsp;&nbsp;&nbsp;&nbsp;".$row[$field1]."<br /><hr></hr>";
		}*/
		return $result;
	}



	function doPasswordChangeExp($username, $user_password, $new_expdate)
	{

		$desencrypt = new DESEncryption();
		$count_entry = 0;
		$key = $username;
		$cipher_password = $desencrypt->des($key, $user_password, 1, 0, null, null);
		$str_cipher_password = $desencrypt->stringToHex($cipher_password);
		$query_data = "update userdata set password='$str_cipher_password', pass_dateexpire='$new_expdate' where username= '$username'";
		//echo $query_data;
		$result_data = mysql_query($query_data);
		$count_entry = mysql_affected_rows();

		return $count_entry;
	}
	///////////////////////////////
	// Do password change on logon
	function doPasswordChangeLogon($username, $user_password)
	{
		$desencrypt = new DESEncryption();
		$count_entry = 0;
		$key = $username;
		$cipher_password = $desencrypt->des($key, $user_password, 1, 0, null, null);
		$str_cipher_password = $desencrypt->stringToHex($cipher_password);
		$query_data = "update userdata set password='$str_cipher_password', passchg_logon='0' where username= '$username'";
		//echo $query_data;
		$result_data = mysql_query($query_data);
		$count_entry = mysql_affected_rows();

		return $count_entry;
	}


	function getuniqueid2()
	{
		$month_year = array(
			'01' => '025',
			'02' => '468',
			'03' => '469',
			'04' => '431',
			'05' => '542',
			'06' => '790',
			'07' => '138',
			'08' => '340',
			'09' => '356',
			'10' => '763',
			'11' => '845',
			'12' => '890'
		);

		$year = array(
			'2009' => '111',
			'2010' => '222',
			'2011' => '333',
			'2012' => '444',
			'2013' => '555',
			'2014' => '777',
			'2015' => '000',
			'2016' => '666',
			'2017' => '999',
			'2018' => '123',
			'2019' => '321',
			'2020' => '431',
			'2021' => '521',
			'2022' => '146',
			'2023' => '246',
			'2024' => '357',
			'2025' => '768',
			'2026' => '430',
			'2027' => '770',
			'2028' => '773',
			'2029' => '873',
			'2030' => '962',
			'2031' => '909',
			'2032' => '830',
			'2033' => '349',
			'2034' => '457',
			'2035' => '248'
		);

		$day = array(
			'01' => '50',
			'02' => '31',
			'03' => '23',
			'04' => '12',
			'05' => '54',
			'06' => '67',
			'07' => '87',
			'08' => '90',
			'09' => '11',
			'10' => '34',
			'11' => '22',
			'12' => '38',
			'13' => '88',
			'14' => '78',
			'15' => '33',
			'16' => '54',
			'17' => '67',
			'18' => '77',
			'19' => '29',
			'20' => '59',
			'21' => '17',
			'22' => '32',
			'23' => '44',
			'24' => '66',
			'25' => '00',
			'26' => '04',
			'27' => '05',
			'28' => '03',
			'29' => '08',
			'30' => '20',
			'31' => '45'
		);
		//////////////--------> get 2day's date		
		$today_date = @date('Y-m-d');
		$date_arr = explode("-", $today_date);
		$unique_id = $year[$date_arr[0]] . $month_year[$date_arr[1]] . $day[$date_arr[2]];
		return $unique_id;
	}


	function getitemcount($tablename, $table_col, $table_val, $ret_val)
	{
		$label = "";
		$table_filter = " where " . $table_col . "='" . $table_val . "'";

		$query = "select Count(" . $ret_val . ") counter from " . $tablename . $table_filter;
		//echo $query;
		file_put_contents("jude.txt", $query);
		$result = mysql_query($query); //or die(mysql_error());
		$numrows = mysql_num_rows($result);
		if ($numrows > 0) {
			$row = mysql_fetch_array($result);
			$label = $row['counter'];
		}
		return $label;
	}


	function getrecordsetArr($tablename, $table_col_arr, $table_val_arr)
	{
		$where_clause = " ";
		for ($i = 0; $i < count($table_col_arr); $i++) {
			$where_clause .= $table_col_arr[$i] . "='" . $table_val_arr[$i] . "' and ";
		}

		$where_clause = rtrim($where_clause, " and ");
		//echo 'country code : '.$countrycode;
		$label = "";
		$table_filter = " where " . $where_clause;

		$query = "select * from " . $tablename . $table_filter;
		//echo $query;
		$result = mysql_query($query);
		return $result;
	}

	public function get_posted_ip() {
        $ipaddress = '';
        if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

	function sendMail($to, $subject,$template_file,$data, $with_cc_list = false)
    {
        
        $to      = "<$to>";
        $subject = $subject;

        $data['transaction_date'] =  date('l d M Y H:i:s') . " from " . $this->get_posted_ip();

        $app_link = ($_SERVER['REMOTE_ADDR'] != "::1")?$this->getitemlabel("parameter", "parameter_name", "site_live_url", "parameter_value"):$this->getitemlabel("parameter", "parameter_name", "site_local_url", "parameter_value");
        $app_link = (substr($app_link, -1) == "/")  ? $app_link : $app_link."/";

        $data['site_url'] =$app_link;
        $data['logo_url'] = $app_link . "img/logo.jpg";
      
      
        // Set content-type header for sending HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
        $headers .= ($_SERVER['REMOTE_ADDR'] != "::1")?"From: <noreply@".$_SERVER['SERVER_NAME'].">". "\r\n":"From: <noreply@".$_SERVER['SERVER_NAME'].">". "\r\n";

        if(@$with_cc_list != false) {

            //comma seperated values
            $headers .= "Cc: ". $with_cc_list . "\r\n";
        }

        // Urgent message
        $headers .= "X-Priority: 1\r\n";

        $file = $template_file;
        $rows = array(
          $data
        //  array( 'id' => 2, 'name' => 'second row', 'etc' => 'nothing special' ),
        );

        $output = '';

        foreach ( $rows as $row )
        {
          $output.= $this->template($file, $row);
        }

        $to = trim($to);
        $is_sent = mail($to,$subject,$output,$headers);

        if(!$is_sent) {

            $is_sent  = mail($to,$subject,$output,$headers);
        }

        
        return $is_sent;
    }
	function template($file, $args)
    {

      // Make values in the associative array easier to access by extracting them
      if ( is_array( $args ) )
      {
        extract( $args );
      }

        // buffer the output (including the file is "output")
        ob_start();
        include $file;
        return ob_get_clean();
    }

	public function percent($num_amount, $num_total)
	{
		$count1 = $num_amount / 100;
		$count2 = $count1 * $num_total;
		$count = number_format($count2, 0);
		return $count;
	}
	public function generateBARCODE($staff_id)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
		$url = "";
		if ($ip == '::1' || $ip == 'localhost') {
			$url = $this->getitemlabel("parameter", "parameter_name", "qrcode_url", "parameter_value");
			$root = $_SERVER['DOCUMENT_ROOT'].'/attendance/';
		} else {
			$root = $_SERVER['DOCUMENT_ROOT'].'/';
			$url = $this->getitemlabel("parameter", "parameter_name", "site_live_url", "parameter_value");
		}

		$path = 'uploads/barcodes/';
		if (!file_exists($path)) {
			mkdir($path);
		}

		// 		require_once $root.'vendor/autoload.php';

		// $generator = new BarcodeGenerator();
		// try {
		//     $url = 'https://example.com';
		//     $barcode = $generator->getBarcode($url, $generator::TYPE_QR);
		//     file_put_contents('barcode.png', $barcode);
		// } catch (BarcodeException $e) {
		//     echo 'Error: ' . $e->getMessage();
		// }


		require_once($root.'vendor/autoload.php');
		$url = 'https://attd.ngautos.com.ng/';
		$text = $url . "attendance?key=" . base64_encode($staff_id);
		
		$file = $path . $staff_id . ".png";

		$blackColor = [0, 0, 0];

		// // instantiate the barcode class
		// 	$barcode = new \Com\Tecnick\Barcode\Barcode();

		// 	// generate a barcode
		// 	$bobj = $barcode->getBarcodeObj(
		// 		'QRCODE,H',                     // barcode type and additional comma-separated parameters
		// 		$text,          // data string to encode
		// 		-4,                             // bar width (use absolute or negative value as multiplication factor)
		// 		-4,                             // bar height (use absolute or negative value as multiplication factor)
		// 		'black',                        // foreground color
		// 		array(-2, -2, -2, -2)           // padding (use absolute or negative values as multiplication factors)
		// 		)->setBackgroundColor('white'); // background color

		// 	// // output the barcode as HTML div (see other output formats in the documentation and examples)
		// 	// echo $bobj->getHtmlDiv();

		// $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
		// // file_put_contents($file, $bobj);
		// file_put_contents($file, $generator->getBarcode($text, $generator::TYPE_CODE_128, 1, 50, $blackColor));

        return json_encode(array('response_code'=>0, 'response_message'=>$url.$file));

    }
	public function generateQRCODE($staff_id)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$url = "";
		if ($ip == '::1' || $ip == 'localhost') {
			$url = $this->getitemlabel("parameter", "parameter_name", "qrcode_url", "parameter_value");
		} else {

			$url = $this->getitemlabel("parameter", "parameter_name", "site_live_url", "parameter_value");
		}

		$path = 'uploads/grcodes/';
		if (!file_exists($path)) {
			mkdir($path);
		}

		$text = $url . "attendance?key=" . base64_encode($staff_id);
		$file = $path . $staff_id . ".png";

		$ecc = 'L';
		$pixel_Size = 10;
		$frame_Size = 10;

		// Generating a barcode
		QRcode::png($text, $file, $ecc, $pixel_Size, $frame_Size);

		return json_encode(array('response_code'=>0, 'response_message'=>$url.$file));
	}


	function getItemLabelArr($tablename,$table_col_arr,$table_val_arr,$ret_val_arr)
    {
        $label = "";
        $selectClause = "";
        $whrClause = "";
        $retValue ="";

        /////////////////////////////////////////////////////////////////
        ////////// select clause starts here////////////////////////////////
        if($ret_val_arr=="*")
        {
            $qquery = "SHOW COLUMNS FROM $tablename ";
            //echo $qquery;
            $result = $this->myconn->query($qquery);
            //echo $this->myconn->error;
            if($this->myconn->error == "" && $result->num_rows > 0){

				while($roww =$result->fetch_row())
				{
					$selectClause .=$roww[0].", ";
					$ret_val[] = $roww[0];
				}
				$retCount =$ret_val;
				$selectClause = rtrim($selectClause,", ");
			}
        }else
        {
            for($i=0; $i<count($ret_val_arr);$i++)
            {
                $selectClause .=$ret_val_arr[$i].", ";
            }
            $selectClause = rtrim($selectClause,", ");
            $retCount = $ret_val_arr;
            //echo $setClause;
        }
        /////////////////////////////////////////////////////////////////
        ///////////////where clause starts here/////////////////////////
        for($j=0; $j<count($table_col_arr);$j++)
        {
            $whrClause .= " AND ".$table_col_arr[$j]."='".$table_val_arr[$j]."' ";
        }
        $whrClause = rtrim($whrClause,", ");
        /////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////
        $table_filter = " where 1=1 ".$whrClause;
    
        $query = "select ".$selectClause." from ".$tablename.$table_filter." LIMIT 1";
        // echo $query;
        
        $result = $this->myconn->query($query);
        // $this->dolog('Label Array: '.$query.'=>'.$this->myconn->error, 'getitemlabel_log.log');
       
        $numrows = ($this->myconn->error != "") ? 0 : $result->num_rows;
        if($numrows > 0)
        { 
            $retValue = $result->fetch_assoc();
        }
        return $retValue;
    }

	function StrongPasswordChecker($pwd)
	{
		if (strlen($pwd) < 8) {
			$error .= "Password too short! Minimum of 8 Xters Required!<br/>";
		}

		if (strlen($pwd) > 20) {
			$error .= "Password too long! Maximum of 20 Xters Required!<br/>";
		}

		if (!preg_match("#[0-9]+#", $pwd)) {
			$error .= "Password must include at least One Number!<br/>";
		}


		if (!preg_match("#[a-z]+#", $pwd)) {
			$error .= "Password must include at least One SMALL Letter! <br/>";
		}


		if (!preg_match("#[A-Z]+#", $pwd)) {
			$error .= "Password must include at least one CAPS! <br/>";
		}


		if (!preg_match("#\W+#", $pwd)) {
			$error .= "Password must include at least One Symbol!<br/>";
		}

		if ($error) {
			$ErrorResp =  $error;
		} else {
			$ErrorResp = '1';
		}
		return $ErrorResp;
	}

	function encrypt_password($username, $userpassword)
	{
		$desencrypt = new DESEncryption();
		$key = $username;
		$cipher_password = $desencrypt->des($key, $userpassword, 1, 0, null, null);
		$str_cipher_password = $desencrypt->stringToHex($cipher_password);
		return $str_cipher_password;
	}

	function decrypt_password($username, $pass_crypt)
	{
		$key = $username;
		$desencrypt = new DESEncryption();
		$cipher_password = $desencrypt->hexToString($pass_crypt);
		$plain_pass = $desencrypt->des($key, $cipher_password, 0, 1);
		return $plain_pass;
	}

	function logger($mssg)
	{
		$myfile = fopen("logs.txt", "a");
		$txt = "[ " . date("Y/M/d h:i:s") . " ] --> " . $mssg . "\n";
		fwrite($myfile, $txt);
		fclose($myfile);
	}


	public function gateway($data)
	{
		if (empty($data['op'])) {
			return json_encode(array("response_code" => 501, "response_message" => "Unrecognized request."));
		}

		$role_id = $_SESSION['role_id_sess'];

		$operation = isset($data['operation']) ? strtolower($data['operation']) : '';
		$operation_type = ($operation != "") ? " AND LOWER(operation_type) = '$operation' " : "";
		$op_check = strtolower($data['op']);

		$sql = "SELECT id, operation_type FROM permissions WHERE id IN (SELECT permission_id FROM permissions_map WHERE role_id = '$role_id') AND LOWER(action) = '$op_check' $operation_type";
		$check = $this->runQuery($sql);

		if (empty($check)) {
			return json_encode(array("response_code" => 501, "response_message" => "You do not have permission to carry out this operation!"));
		}

		if (!empty($data['operation']) && $data['operation'] != $check[0]["operation_type"]) {
			return json_encode(array("response_code" => 501, "response_message" => "You do not have permission to carry out this operation!"));
		}
	}

	public function generateHTTP()
    {
        $result = base64_encode(openssl_random_pseudo_bytes(32));
        if (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') {
            header("Set-Cookie: Access=$result; Secure");
			header("Set-Cookie: Access=$result; HttpOnly");
        } else {
            header("Set-Cookie: Access=$result; HttpOnly");
        }

		header_remove("X-Powered-By");
		header_remove("Server");
		header("Cache-Control: no-cache;no-store, must-revalidate");
    }

	public function integrityHash($data)
    {
		$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		$uri_segments = explode('/', $uri_path);
		$ccc = count($uri_segments) - 2;
		$admin = '';
		if(isset($uri_segments[$ccc]) && $uri_segments[$ccc] == 'admin'){
			$admin .= 'admin/';
		}
		$ip = $_SERVER['REMOTE_ADDR'];
        $app_local_link = $this->getitemlabel("parameter", "parameter_name", "site_local_url", "parameter_value").'admin/'.$data;
        $app_live_link = $this->getitemlabel("parameter", "parameter_name", "site_live_url", "parameter_value").$admin.$data;
		$link = ($ip == '::1' || $ip == 'localhost' || $ip == "12.34.56.78:85") ? $app_local_link : $app_live_link;
        $data = base64_encode(hash("sha256", file_get_contents($link), true));

		return $data;
    }

}