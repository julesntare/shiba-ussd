<?php

//this is other comment
//read the variables read from post
$sessionId=$_POST["sessionId"];
$serviceCode=$_POST["serviceCode"];
$phoneNumber=$_POST["phoneNumber"];
$text=$_POST["text"];

if($text==""){

    //this is the first request to
    $response="CON what would you like to check \n ";
    $response .= "1.My account \n";
    $response .= "2.my phone number \n";


}

else if($text=="1"){

    $response="CON chose account information";
    $response .= "1.Account Number \n";
    $response .= "2.Account balance ";

}

else if($text=="2"){
    $response="END your phone number is ".$phoneNumber;

}

elseif($text=="1*1"){

    //this  is the second level response that
    $accountNumber="ACC1001";


    $response="END your account number is ".$accountNumber;


}
elseif($text=="1*2")
{

    $balance="KES 10,000";
    $response="END YOUR account balance is".$balance;

}

header('content-type;text/plain');
echo $response;

?>