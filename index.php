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
$phone = $_POST['msisdn'];
$p = str_replace("%2B", '+', $phone);
$session_id = $_POST['sessionId'];
$userinput = urldecode($_POST["UserInput"]);
$serviceCode = $_POST["serviceCode"];
$networkCode = $_POST['networkCode'];


/* Split text input based on asteriks(*)
 * Africa's talking appends asteriks for after every menu level or input
 * One needs to split the response from Africa's Talking in order to determine
 * the menu level and input for each level
 * */
$ussd_string_exploded = explode("70", $userinput);

// Get menu level from ussd_string reply
$level = $ussd_string_exploded[count($ussd_string_exploded) - 1];

if ($userinput == '*662*800*70#') {
    $response = display_menu(); // show the home/first menu
    $ContinueSession = 1;
} else {
    $temp = explode('*', $level);
    $level_1 = str_replace("#", '', $temp[1]);
    switch ($level_1) {
        case 1:
            // If user selected 1 send them to the registration menu
            $res_temp = register($level, $p, $dbConn);
            $response = $res_temp['msg'];
            if ($res_temp['status'] == 0) {
                $ContinueSession = 0;
            } else {
                $ContinueSession = 1;
            }
            break;
        case 2:
            //If user selected 2, send them to the about menu
            $response = about();
            $ContinueSession = 0;
            break;
        case 3:
            //If user selected 3, send them to the about menu
            $res_temp = login($level, $dbConn, $p);
            $response = $res_temp['msg'];
            if ($res_temp['status'] == 0) {
                $ContinueSession = 0;
            } else {
                $ContinueSession = 1;
            }
            break;
        default:
            $response = "Invalid selection!!!";
            $ContinueSession = 0;
            break;
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
    $initial_msg = "Murakaza neza muri SBCA System\n\n 1. kwiyandikisha(umubyeyi) \n 2. ibyerekeye system \n 3. konti yange \n";
    return $initial_msg; // add \n so that the menu has new lines
}

// Function that hanldles About menu
function about()
{
    $about_text = "This is a sample registration application";
    return $about_text;
}

function display_user_menu()
{
    $ussd_text = "1. kwandika umwana mushya\n 2. Ibiherutse gukorwa\n 3. Ibyenda gukorwa\n 4. Tanga igitekerezo\n 5. Gusohoka muri system\n 6. Subira ahabanza\n";

    return $ussd_text;
}

function login($level, $dbConn, $phone)
{
    $temp = explode('*', $level);
    $level_2 = str_replace("#", '', $temp[2]);
    $res = array();
    switch (count($temp)) {
        case 2:
            $res["msg"] = "injiza umubare wibanga:";
            $res["status"] = 1;
            break;
        case 3:
            $pin = $level_2;
            if (empty(trim($level_2))) {
                $res["msg"] = "ntakintu mwinjijemo ntabwo byemewe";
                $res["status"] = 0;
            } else {
                try {
                    $search_result = $dbConn->query("SELECT * FROM parents WHERE phone='$phone' and pin='$pin'");
                    $total_rows = $search_result->rowCount();
                    if ($total_rows == 0) {
                        $res["msg"] = "Umubare w'ibanga ntabwo ariwo.";
                        $res["status"] = 0;
                    } else {
                        $rtArr = userMenus($temp, $dbConn, $phone);
                        $res = array_merge($res, $rtArr);
                    }
                } catch (PDOException $e) {
                    $res["msg"] = "habaye ikibazo, mwongere mukanya";
                    $res["status"] = 0;
                }
            }
            break;
        default:
            $res["msg"] = "habaye ikibazo, mwongere mukanya";
            $res["status"] = 0;
            break;
    }
    //     //     case 3:
    //     //         if (empty($level[2])) {
    //     //             $ussd_text = "ntakintu mwinjijemo ntabwo byemewe";
    //     //             ussd_proceed($ussd_text);
    //     //         } else {
    //     //             $date = date("Y-m-d H:i:s");
    //     //             $search_result = $dbConn->query("SELECT * FROM parents WHERE phone='$phone'");
    //     //             $fetched_rows = $search_result->fetch();
    //     //             switch ($level[2]) {
    //     //                 case "1":
    //     //                     ussd_proceed("Izina rya gikirisitu");
    //     //                     break;
    //     //                 case "2":
    //     //                     ussd_stop("yello2");
    //     //                     break;
    //     //                 case "3":
    //     //                     // add queries here
    //     //                     $ussd_text = "andika igitekerezo:";
    //     //                     ussd_proceed($ussd_text); // ask user to enter registration details
    //     //                     break;
    //     //                 case "4":
    //     //                     ussd_stop("yello3");
    //     //                     break;
    //     //                 case "5":
    //     //                     $ussd_text = "Murakoze gukoresha sisitemu yacu. ibihe byiza!!!";
    //     //                     ussd_stop($ussd_text);
    //     //                     break;
    //     //                 case "6":
    //     //                     array_pop($level);
    //     //                     array_pop($level);
    //     //                     array_pop($level);
    //     //                     display_menu();
    //     //                     break;
    //     //                 default:
    //     //                     ussd_stop("habaye ikibazo, mwongere mukanya");
    //     //                     break;
    //     //             }
    //     //         }
    //     //         break;
    //     //     case 4:
    //     //         if (empty($level[3])) {
    //     //             $ussd_text = "ntakintu mwinjijemo ntabwo byemewe";
    //     //             ussd_proceed($ussd_text);
    //     //         } else {
    //     //             switch ($level[2]) {
    //     //                 case "1":
    //     //                     ussd_proceed("Injiza andi mazina ye");
    //     //                     break;
    //     //                 case '3':
    //     //                     $search_result = $dbConn->query("SELECT * FROM parents WHERE phone='$phone'");
    //     //                     $fetched_rows = $search_result->fetch();
    //     //                     $sender = $fetched_rows['id'];

    //     //                     $dbConn->exec("INSERT INTO comments (sender, message) VALUES('$sender','$level[3]')");
    //     //                     $ussd_text = "Murakoze! igitekerezo cyanyu cyakiriwe";
    //     //                     ussd_stop($ussd_text);
    //     //                     break;
    //     //                 default:
    //     //                     ussd_stop("habaye ikibazo, mwongere mukanya");
    //     //                     break;
    //     //             }
    //     //         }
    //     //         break;
    //     //     case 5:
    //     //         if (empty($level[4])) {
    //     //             $ussd_text = "ntakintu mwinjijemo ntabwo byemewe";
    //     //             ussd_proceed($ussd_text);
    //     //         } else {
    //     //             ussd_proceed("Injiza itariki yivuka. urugero:\n 2021-11-16\n");
    //     //         }
    //     //         break;
    //     //     case 6:
    //     //         if (empty($level[5])) {
    //     //             $ussd_text = "ntakintu mwinjijemo ntabwo byemewe";
    //     //             ussd_proceed($ussd_text);
    //     //         } else {
    //     //             $search_result = $dbConn->query("SELECT * FROM parents WHERE phone='$phone'");
    //     //             $fetched_rows = $search_result->fetch();
    //     //             $pid = $fetched_rows['id'];

    //     //             $dbConn->exec("INSERT INTO children (fname, oname, pid, born) VALUES('$level[3]', '$level[4]', '$pid', '$level[5]')");
    //     //             $ussd_text = "Byegenze neza! Umwana witwa " . $level[3] . " yinjijwe muri sisitemu";
    //     //             ussd_stop($ussd_text);
    //     //         }
    //     //         break;
    //     //     default:
    //     //         ussd_stop("habaye ikibazo, mwongere mukanyas");
    //     //         break;
    //     // }
    return $res;
}

// Function that handles Registration menu
function register($level, $phone, $dbConn)
{
    $temp = explode('*', $level);
    $lvl = trim(str_replace("#", '', $temp[count($temp) - 1]));
    $res = array();

    $search_result = $dbConn->query("SELECT * FROM parents WHERE phone='$phone'");
    $total_rows = $search_result->rowCount();
    if ($total_rows > 0) {
        $res["msg"] = "Musanzwe muri sisitemu!";
        $res["status"] = 0;
    } else {
        switch (count($temp)) {
            case 2:
                $res["msg"] = "Andika izina rya mbere:";
                $res["status"] = 1;
                break;
            case 3:
                if (empty($lvl)) {
                    $res["msg"] = "ntakintu mwinjijemo ntabwo byemewe";
                    $res["status"] = 0;
                } else {
                    $res["msg"] = "Andika andi mazina:";
                    $res["status"] = 1;
                }
                break;
            case 4:
                if (empty($lvl)) {
                    $res["msg"] = "ntakintu mwinjijemo ntabwo byemewe";
                    $res["status"] = 0;
                } else {
                    $res["msg"] = "Andika inumero y'indangamuntu:";
                    $res["status"] = 1;
                }
                break;
            case 5:
                if (empty($lvl)) {
                    $res["msg"] = "Ntakintu mwinjijemo ntabwo byemewe";
                    $res["status"] = 0;
                } else {
                    $res["msg"] = "Hitamo umubare w'ibanga:";
                    $res["status"] = 1;
                }
                break;
            case 6:
                if (empty($lvl)) {
                    $res["msg"] = "Ntakintu mwinjijemo ntabwo byemewe";
                    $res["status"] = 0;
                } else {
                    $fname = trim(str_replace("#", '', $temp[count($temp) - 4]));
                    $oname = trim(str_replace("#", '', $temp[count($temp) - 3]));
                    $idno = trim(str_replace("#", '', $temp[count($temp) - 2]));
                    $pin = trim(str_replace("#", '', $temp[count($temp) - 1]));
                    $phone_number = $phone;

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
                                // 'x-api-key: c2c1f86a-b113-97d9-ad16-76b66e1e5e68-8235bffb'
                            ),
                        ));
                        curl_close($curl);

                        //execute insert query
                        // $sth->execute();
                        $res["msg"] = "kwiyandikisha byagenze neza murakira ubutumwa bw'ikaze mukanya. Murakoze!";
                        $res["status"] = 0;
                    } catch (PDOException $e) {
                        $res["msg"] = "habaye ikibazo, mwongere mukanya";
                        $res["status"] = 0;
                    }
                }
                break;
            default:
                $res["msg"] = "habaye ikibazo, mwongere mukanya";
                $res["status"] = 0;
                break;
        }
    }
    return $res;
}

function userMenus($level, $dbConn, $phone)
{
    $arr = array();
    $lvl = trim(str_replace("#", '', $level[count($level) - 1]));
    switch (count($level)) {
        case 3:
            $arr["msg"] = display_user_menu();
            $arr["status"] = 1;
            break;
        case 4:
            if (empty($lvl)) {
                $arr["msg"] = "ntakintu mwinjijemo ntabwo byemewe";
                $arr["status"] = 0;
            } else {
                $arr["msg"] = "ok";
                $arr["status"] = 1;
            }
            break;
        default:
            $arr["msg"] = "habaye ikibazo, mwongere mukanya";
            $arr["status"] = 0;
            break;
    }
    return $arr;
}

# close the pdo connection
$dbConn = null;

$resp = array("sessionId" => $sessionId, "message" => $response, "ContinueSession" => $ContinueSession);

echo json_encode($resp);