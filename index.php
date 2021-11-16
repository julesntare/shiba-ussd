<?php
$sessionId   = $_POST["sessionId"];
$phoneNumber = $_POST["msisdn"];
$userinput   = urldecode($_POST["UserInput"]);
$serviceCode = $_POST["serviceCode"];
$networkCode = $_POST['networkCode'];



if($userinput=="*662*800*70#"){
   $response  = "Welcome to XYZ Cleaning Company\n";
   $response .="1. Register\n 2.Check your balance";
   

$ContinueSession=1;


}
  $resp = array("sessionId"=>$sessionId,"message"=>$response,"ContinueSession"=>$ContinueSession);

  echo json_encode($resp); 