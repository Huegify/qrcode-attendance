<?php
session_start();
$params = session_get_cookie_params();
setcookie("PHPSESSID", session_id(), 0, $params["path"], $params["domain"],
    true, 
    true 
);

if(!isset($_SESSION['username_sess']))
{
    header('location: logout');
}
if($_SESSION['username_sess'] == "")
{
    header('location: logout');
}

include_once("model/model.php");
include_once("model/SecurityService.php");

// Include all classes in the classes folder

$model = new Model();
if(isset($_REQUEST['att-csrf-token-label'])){

    $antiCSRF = new \ATTD\SecurityService\securityService();
    $csrfResponse = json_decode($antiCSRF->validate(), true);

    if ($csrfResponse['valid'] == false) {
        echo json_encode(array('response_code' => 504, 'response_message' => $csrfResponse['response_message']));
        exit;
    }
}


foreach (glob("class/*.php") as $filename) {
    include_once($filename);
}

// User.login
if (isset($_REQUEST['op'])) {
    $op = $_REQUEST['op'];

    $operation  = array();
    $operation = explode(".", $op);


    // getting data for the class method
    $params = array();
    $params = $_REQUEST;
    $data = [$params];


    //////////////////////////////
    /// callling the method of  the class
    $foo = new $operation[0];
    if (method_exists($foo, trim($operation[1]))) {
        echo call_user_func_array(array($foo, trim($operation[1])), $data);
    } else {
        // Invalid operation, handle error here
        echo "Invalid operation.";
    }
} else {
    // op parameter not set, handle error here
    echo "Request not recognised.";
}
