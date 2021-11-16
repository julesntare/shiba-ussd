<?php
$sessionId   = $_POST["sessionId"];
$phoneNumber = $_POST["msisdn"];
$userinput   = urldecode($_POST["UserInput"]);
$serviceCode = $_POST["serviceCode"];
$networkCode = $_POST['networkCode'];



if($userinput=="*662*800*100#"){
    function display_menu()
    {
        $ussd_text = "1. kwiyandikisha(umubyeyi) \n 2. ibyerekeye system \n 3. konti yange  \n"; // add \n so that the menu has new lines
        ussd_proceed($ussd_text);
    }
   

$ContinueSession=1;


}

elseif($userinput=="*662*800*100*1#"){

$response  = "Your phone number is registered now ,thank you for choossing XYZ Cleaning Company";

$ContinueSession=0;
}

elseif($userinput=="*662*800*100*2#"){
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