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
    } elseif ($ussd_string_exploded[0] == "3") {
        login($ussd_string_exploded, $dbConn, $phone);
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
    $ussd_text = "1. kwiyandikisha(umubyeyi) \n 2. ibyerekeye system \n 3. konti yange  \n"; // add \n so that the menu has new lines
    ussd_proceed($ussd_text);
}

// Function that hanldles About menu
function about($ussd_text)
{
    $ussd_text = "This is a sample registration application";
    ussd_stop($ussd_text);
}

function display_user_menu()
{
    $ussd_text = "1.kwandika umwana mushya \n 1.Ibiherutse gukorwa \n 2. Ibyenda gukorwa   \n 3. Tanga igitekerezo  \n 4. Gusohoka muri system  \n"; // add \n so that the menu has new lines
    ussd_proceed($ussd_text);
}

function login($details, $dbConn, $phone)
{
    switch (count($details)) {
        case 1:
            $ussd_text = "injiza umubare wibanga:";
            ussd_proceed($ussd_text); // ask user to enter registration details
            break;
        case 2:
            if (empty($details[1])) {
                $ussd_text = "ntakintu mwinjijemo ntabwo byemewe";
                ussd_proceed($ussd_text);
            } else {

                try {
                    $search_result = $dbConn->query("SELECT * FROM parents WHERE phone='$phone' and pin='$details[1]'");
                    $total_rows = $search_result->rowCount();
                    if ($total_rows == 0) {
                        ussd_stop("Umubare w'ibanga ntabwo ariwo");
                        return;
                    }
                    display_user_menu();
                } catch (PDOException $e) {
                    ussd_stop("Error:" . $e->getMessage());
                }
            }
            break;
        case 3:
            if (empty($details[2])) {
                $ussd_text = "ntakintu mwinjijemo ntabwo byemewe";
                ussd_proceed($ussd_text);
            } else {
                $date = date("Y-m-d H:i:s");
                $search_result = $dbConn->query("SELECT * FROM parents WHERE phone='$phone'");
                $fetched_rows = $search_result->fetch();
                switch ($details[2]) {
                    case "1":
                        ussd_stop("yello");
                        break;
                    case "2":
                        ussd_stop("yello2");
                        break;
                    case "3":
                        // add queries here
                        $ussd_text = "andika igitekerezo:";
                        ussd_proceed($ussd_text); // ask user to enter registration details
                        break;
                    case "4":
                        $ussd_text = "Murakoze gukoresha sisitemu yacu. ibihe byiza!!!";
                        ussd_stop($ussd_text);
                        break;
                    default:
                        ussd_stop("habaye ikibazo, mwongere mukanya");
                        break;
                }
            }
            break;
        case 4:
            if (empty($details[3])) {
                $ussd_text = "ntakintu mwinjijemo ntabwo byemewe";
                ussd_proceed($ussd_text);
            } else {
                // add queries here
                $search_result = $dbConn->query("SELECT * FROM customer3 WHERE phone='$phone'");
                $fetched_rows = $search_result->fetch();
                $sender = $fetched_rows['id'];

                $dbConn->exec("INSERT INTO comments (sender, message) VALUES('$sender','$details[3]')");
                $ussd_text = "Murakoze! igitekerezo cyanyu cyakiriwe";
                ussd_stop($ussd_text);
            }
            break;
        default:
            ussd_stop("habaye ikibazo, mwongere mukanya");
            break;
    }
}

// Function that handles Registration menu
function register($details, $phone, $dbConn)
{
    switch (count($details)) {
        case 1:
            $ussd_text = "injiza izina ryambere:";
            ussd_proceed($ussd_text); // ask user to enter registration details
            break;
        case 2:
            if (empty($details[1])) {
                $ussd_text = "ntakintu mwinjijemo ntabwo byemewe";
                ussd_proceed($ussd_text);
            } else {
                $ussd_text = "injiza andi mazina:";
                ussd_proceed($ussd_text); // ask user to enter registration details
            }
            break;
        case 3:
            if (empty($details[2])) {
                $ussd_text = "ntakintu mwinjijemo ntabwo byemewe";
                ussd_proceed($ussd_text);
            } else {
                $ussd_text = "injiza numero yirangamuntu:";  // ask user to enter home location
                ussd_proceed($ussd_text);
            }
            break;
        case 4:
            if (empty($details[3])) {
                $ussd_text = "ntakintu mwinjijemo ntabwo byemewe";
                ussd_proceed($ussd_text);
            } else {
                $ussd_text = "hitamo umubare wibanga :";  // ask user to enter home location
                ussd_proceed($ussd_text);
            }
            break;
        case 5:
            if (empty($details[4])) {
                $ussd_text = "ntakintu mwinjijemo ntabwo byemewe";
                ussd_proceed($ussd_text);
            } else {
                $fname = $details[1]; //store full name of the baby
                $oname = $details[2]; //father name
                $idno = $details[3]; //mother name
                $pin = $details[4]; //pin number
                //$mid = $details[4]; //mother id
                $phone_number = $phone; //store phone number

                // build sql statement
                try {
                    $dbConn->exec("INSERT INTO parents (fname,oname,idno,phone,pin) VALUES('$fname','$oname','$idno','$phone_number','$pin')");

                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://api.mista.io/sms',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => array('to' => $phone_number, 'from' => 'SBCA', 'unicode' => '0', 'sms' => "Muraho  " . $fname . ",kwiyandikisha byagenze neza. mushobora kwandikisha umwana wanyu muri SBCS mukajya mubona inama kumikurire myiza yumwana Murakoze!", 'action' => 'send-sms'),
                        CURLOPT_HTTPHEADER => array(
                            'x-api-key: c2c1f86a-b113-97d9-ad16-76b66e1e5e68-8235bffb'
                        ),
                    ));

                    $response = curl_exec($curl);

                    curl_close($curl);

                    //execute insert query
                    // $sth->execute();
                    $ussd_text = " kwiyandikisha byagenze neza murakira ubutumwa bw'ikaze murakoze!";
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