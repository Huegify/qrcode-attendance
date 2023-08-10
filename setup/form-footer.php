<?php
require_once '../model/SecurityService.php';
$antiCSRF = new \ATTD\SecurityService\securityService();
$antiCSRF->insertHiddenToken();
