<?php
$sessionId   = $_POST["sessionId"];
$phoneNumber = $_POST["msisdn"];
$userinput   = urldecode($_POST["UserInput"]);
$serviceCode = $_POST["serviceCode"];
$networkCode = $_POST['networkCode'];



  $resp = array("sessionId"=>$sessionId,"message"=>$response,"ContinueSession"=>$ContinueSession);

  echo json_encode($resp); 