<?php
$sessionId   = $_POST["sessionId"];
$phoneNumber = $_POST["msisdn"];
$userinput   = urldecode($_POST["UserInput"]);
$serviceCode = $_POST["serviceCode"];
$networkCode = $_POST['networkCode'];



if($userinput=="*662*800*70#"){
   $response  = "shibasaki naamoto\n";
   $response .="1. Register\n 2.Check your balance";
   

$ContinueSession=1;


}

elseif($userinput=="*662*800*700*1#"){

$response  = "Your phone number is registered now ,thank you for choossing XYZ Cleaning Company";

$ContinueSession=0;
}

elseif($userinput=="*662*800*700*2#"){
  $response  = "Enter Your House Number";
  $ContinueSession=1;

}

//in this demo we have used static "HM10" as a house number ,you are free to define yours or load the value from DB
if($userinput=="*662*800*100*2*HM10#"){

$response  = "Your Balance is 3000 RWF";
$ContinueSession=0;
  

}

  $resp = array("sessionId"=>$sessionId,"message"=>$response,"ContinueSession"=>$ContinueSession);

  echo json_encode($resp); 