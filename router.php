<?php
// error_reporting(1);
session_start(); 
if (isset($_REQUEST['op'])) {
    $type = $_REQUEST['op'];

    include_once("model/model.php");

    include('class/users.php');
    include('class/setup.php');

    $operation  = array();
    $operation = explode(".", $type);

    // getting data for the class method
    $params = array();
    $params = $_REQUEST;
    $data = [$params];


    //////////////////////////////
    /// calling the method of the class
    $foo = new $operation[0];
    if (method_exists($foo, trim($operation[1]))) {
        var_dump($_SESSION);
        echo call_user_func_array(array($foo, trim($operation[1])), $data);
    } else {
        // Invalid operation, handle error here
        echo "Invalid operation.";
    }
} else {
    // op parameter not set, handle error here
    echo "Request not recognised.";
}
