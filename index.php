<?php
/* Simple sample USSD registration application
 * USSD gateway that is being used is Africa's Talking USSD gateway
 */

// Print the response as plain text so that the gateway can read it
header('Content-type: text/plain');

/* local db configuration */
$dbHost = "bnw530k7urgmxgzkeziw-mysql.services.clever-cloud.com";
$dbName = "bnw530k7urgmxgzkeziw";
$dbUser = "uuvo090e1awwwfz0";      //by default root is user name.
$dbPassword = "WknalOFgRERGk4rldEsr";     //password is blank by default
try {
    $dbConn = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPassword);
    $dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo "Connection failed" . $e->getMessage();
}

// Get the parameters provided by Africa's Talking USSD gateway
$phone = $_POST['phoneNumber'];
$session_id = $_POST['sessionId'];
$service_code = $_POST['serviceCode'];
$ussd_string = $_POST['text'];

//set default level to zero
$level = 0;

/* Split text input based on asteriks(*)
 * Africa's talking appends asteriks for after every menu level or input
 * One needs to split the response from Africa's Talking in order to determine
 * the menu level and input for each level
 * */
$ussd_string_exploded = explode("*", $ussd_string);

// Get menu level from ussd_string reply
$level = count($ussd_string_exploded);

if ($ussd_string_exploded[0] == '') {
    display_menu(); // show the home/first menu
} else {
    if ($ussd_string_exploded[0] == "1") {
        // If user selected 1 send them to the registration menu
        register($ussd_string_exploded, $phone, $dbConn);
    } else if ($ussd_string_exploded[0] == "2") {
        //If user selected 2, send them to the about menu
        about($ussd_string_exploded);
    } else {
        ussd_stop("Invalid selection!!!");
    }
}

/* The ussd_proceed function appends CON to the USSD response your application gives.
 * This informs Africa's Talking USSD gateway and consecuently Safaricom's
 * USSD gateway that the USSD session is till in session or should still continue
 * Use this when you want the application USSD session to continue
*/
function ussd_proceed($ussd_text)
{
    echo "CON $ussd_text";
}

/* This ussd_stop function appends END to the USSD response your application gives.
 * This informs Africa's Talking USSD gateway and consecuently Safaricom's
 * USSD gateway that the USSD session should end.
 * Use this when you to want the application session to terminate/end the application
*/
function ussd_stop($ussd_text)
{
    echo "END $ussd_text";
}

//This is the home menu function
function display_menu()
{
    $ussd_text = "1. Register \n 2. About \n"; // add \n so that the menu has new lines
    ussd_proceed($ussd_text);
}


// Function that hanldles About menu
function about($ussd_text)
{
    $ussd_text = "This is a sample registration application";
    ussd_stop($ussd_text);
}

// Function that handles Registration menu
function register($details, $phone, $dbConn)
{
    switch (count($details)) {
        case 1:
            $ussd_text = "Please enter your Full Name:";
            ussd_proceed($ussd_text); // ask user to enter registration details
            break;
        case 2:
            $name = $details[1];
            if (empty($details[1])) {
                $ussd_text = "Sorry we do not accept blank values, fill valid data";
                ussd_proceed($ussd_text);
            } else {
                $ussd_text = "Please enter your Email:";
                ussd_proceed($ussd_text); // ask user to enter registration details
            }
            break;
        case 3:
            if (empty($details[2])) {
                $ussd_text = "Sorry we do not accept blank values, fill valid data";
                ussd_proceed($ussd_text);
            } else {
                $full_name = $details[2]; //store full name
                $email = $details[3]; //store email
                $phone_number = $phone; //store phone number

                // build sql statement
                try {
                    $dbConn->exec("INSERT INTO customer1 (full_name, email, phone) VALUES('$full_name','$email','$phone_number')");
                    //execute insert query
                    // $sth->execute();
                    $ussd_text = $full_name . " your registration was successful. Your email is " . $email . " and phone number is " . $phone_number;
                    ussd_stop($ussd_text);
                } catch (PDOException $e) {
                    // $errors = $sth->errorInfo();
                    ussd_stop("Error:" . $e->getMessage());
                }
            }
            break;
        default:
            ussd_stop("Something went wrong");
            break;
    }
}
# close the pdo connection
$dbConn = null;