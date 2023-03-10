<?php
/* Simple sample USSD registration application
 * USSD gateway that is being used is Africa's Talking USSD gateway
 */

header('Content-type: text/plain');

/* local db configuration */
// $dsn = 'bnw530k7urgmxgzkeziw;host=bnw530k7urgmxgzkeziw-mysql.services.clever-cloud.com'; //database name
// $user = 'uuvo090e1awwwfz0'; // your mysql user 
// $password = 'WknalOFgRERGk4rldEsr'; // your mysql password

// $uri='mysql://uuvo090e1awwwfz0:WknalOFgRERGk4rldEsr@bnw530k7urgmxgzkeziw-mysql.services.clever-cloud.com:3306/bnw530k7urgmxgzkeziw';

//  Create a PDO instance that will allow you to access your database
try {
    $dbh = new PDO('mysql:host=bnw530k7urgmxgzkeziw-mysql.services.clever-cloud.com;dbname=bnw530k7urgmxgzkeziw', 'uuvo090e1awwwfz0', 'WknalOFgRERGk4rldEsr');
}
catch(PDOException $e) {
    //var_dump($e);
    echo($e." PDO error occurred");
}
catch(Exception $e) {
    //var_dump($e);
    echo("Error occurred");
}

// Get the parameters provided by Africa's Talking USSD gateway
$phone = $_POST['phoneNumber'];
$session_id = $_POST['sessionId'];
$service_code = $_POST['serviceCode'];
$ussd_string= $_POST['text'];

//set default level to zero
$level = 0;

/* Split text input based on asteriks(*)
 * Africa's talking appends asteriks for after every menu level or input
 * One needs to split the response from Africa's Talking in order to determine
 * the menu level and input for each level
 * */
$ussd_string_exploded = explode ("*",$ussd_string);
// Get menu level from ussd_string reply
// $level = count($ussd_string_exploded);
$level = $ussd_string;
if($level == ""){
    
    display_menu(); // show the home/first menu
}

    else if ($level == "1")
    {
        // If user selected 1 send them to the registration menu
        register($level,$phone, $dbh);
    }

  else if ($level == "2"){
        //If user selected 2, send them to the about menu
        about($ussd_string_exploded);
    }
    else if($level == "1*") {
        register($level,$phone, $dbh);
    }

/* The ussd_proceed function appends CON to the USSD response your application gives.
 * This informs Africa's Talking USSD gateway and consecuently Safaricom's
 * USSD gateway that the USSD session is till in session or should still continue
 * Use this when you want the application USSD session to continue
*/
function ussd_proceed($ussd_text){
    echo "CON $ussd_text";
}

/* This ussd_stop function appends END to the USSD response your application gives.
 * This informs Africa's Talking USSD gateway and consecuently Safaricom's
 * USSD gateway that the USSD session should end.
 * Use this when you to want the application session to terminate/end the application
*/
function ussd_stop($ussd_text){
    echo "END $ussd_text";
}

//This is the home menu function
function display_menu()
{
    $ussd_text =    "1. Register \n 2. About \n"; // add \n so that the menu has new lines
    ussd_proceed($ussd_text);
}


// Function that hanldles About menu
function about($ussd_text)
{
    $ussd_text =    "This is a sample registration application";
    ussd_stop($ussd_text);
}

// Function that handles Registration menu
function register($details,$phone, $dbh){
    if($details == "1")
    {

        $ussd_text = "Please enter your Full Name and Email, each seperated by commas:";
        ussd_proceed($ussd_text); // ask user to enter registration details
    }
    // if($details== "1*2")
    else
    {
        if (empty($details)){
                $ussd_text = "Sorry we do not accept blank values";
                ussd_stop($ussd_text);
        } else {
        $input = explode(",",$details);//store input values in an array
        $full_name = $input[0];//store full name
        $email = $input[1];//store email
        $phone_number =$phone;//store phone number 

        // build sql statement
        $sth = $dbh->prepare("INSERT INTO customer1 (full_name, email, phone) VALUES('$full_name','$email','$phone_number')");
        //execute insert query   
        $sth->execute();
        if($sth->errorCode() == 0) {
            $ussd_text = $full_name." your registration was successful. Your email is ".$email." and phone number is ".$phone_number;
            ussd_proceed($ussd_text);
        } else {
            ussd_stop($sth->errorInfo());
        }
    }
}
}
# close the pdo connection  
$dbh = null;
// header('Content-type: text/plain');
// echo $response;