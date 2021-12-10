<?php

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
$p =  substr($phone, 0, 1) != '+' ? (substr($phone, 0, 1) == '%' ? str_replace("%2B", '+', $phone) : "+" . $phone) : $phone;
$session_id = $_POST['sessionId'];
$userinput = urldecode($_POST["UserInput"]);
$serviceCode = $_POST["serviceCode"];
$networkCode = $_POST['networkCode'];

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

//This is the home menu function
function display_menu()
{
    $initial_msg = "Murakaza neza muri SBCS System\n\n 1. kwiyandikisha(umubyeyi) \n 2. ibyerekeye system \n 3. konti yange \n";
    return $initial_msg; // add \n so that the menu has new lines
}

// Function that hanldles About menu
function about()
{
    $about_text = "-SBCS ni gahunda izajya ifasha ababyeyi kugira amakuru ahagije kumikurire yabana bari munsi y'imyaka 2 \n 
    - umubyeyi yiyandikisha muri sisiteme hanyuma akabasha kwandika umwana we \n
    
    ";
    return $about_text;
}

function display_user_menu()
{
    $ussd_text = "1. kwandika umwana mushya\n 2. Ibyenda gukorwa\n 3. Tanga igitekerezo\n 4. Gusohoka muri system\n 5. Subira ahabanza\n";

    return $ussd_text;
}

function login($level, $dbConn, $phone)
{
    $temp = explode('*', $level);
    $lvl = trim(str_replace("#", '', $temp[count($temp) - 1]));
    $res = array();
    switch (count($temp)) {
        case 2:
            $res["msg"] = "injiza umubare wibanga:";
            $res["status"] = 1;
            break;
        case 3:
            $pin = $lvl;
            if (empty(trim($lvl))) {
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
                        $res["msg"] = display_user_menu();
                        $res["status"] = 1;
                    }
                } catch (PDOException $e) {
                    $res["msg"] = "habaye ikibazo, mwongere mukanya";
                    $res["status"] = 0;
                }
            }
            break;
        case 4:
            if (empty($lvl)) {
                $res["msg"] = "ntakintu mwinjijemo ntabwo byemewe";
                $res["status"] = 0;
            } else {
                $resSel = resSelectedMenu($lvl, $dbConn, $phone);
                $res = array_merge($res, $resSel);
            }
            break;
        case 5:
            if (empty($lvl)) {
                $res["msg"] = "ntakintu mwinjijemo ntabwo byemewe";
                $res["status"] = 0;
            } else {
                $resSel = toggleUserMenus($temp, $dbConn, $phone);
                $res = array_merge($res, $resSel);
            }
            break;
        case 6:
            if (empty($lvl)) {
                $res["msg"] = "ntakintu mwinjijemo ntabwo byemewe";
                $res["status"] = 0;
            } else {
                $res["msg"] = "Injiza itariki yivuka. urugero:\n " . date("Y-m-d") . "\n";
                $res["status"] = 1;
            }
            break;
        case 7:
            if (empty($lvl)) {
                $res["msg"] = "ntakintu mwinjijemo ntabwo byemewe";
                $res["status"] = 0;
            } else {
                $search_result = $dbConn->query("SELECT * FROM parents WHERE phone='$phone'");
                $fetched_rows = $search_result->fetch();
                $pid = $fetched_rows['id'];
                $fname = trim(str_replace("#", '', $temp[count($temp) - 3]));
                $oname = trim(str_replace("#", '', $temp[count($temp) - 2]));
                $born = trim(str_replace("#", '', $temp[count($temp) - 1]));

                $dbConn->exec("INSERT INTO children (fname, oname, pid, born) VALUES('$fname', '$oname', '$pid', '$born')");
                $res["msg"] = "Byegenze neza! " . $fname . " yanditswe muri sisitemu";
                $res["status"] = 0;
                
                $search_result_not = $dbConn->query("SELECT * FROM events");
                $search_result_not->fetchAll();

                foreach($search_result_not as $key => $values){
                    $timetosend = $values['period'] + time();
                    $smstext = $values['message'];
                //     $dbConn->exec("INSERT INTO sms (receiver_phone, smstext, timetosend) VALUES('$pid', '$smstext', '$timetosend')");
                }
            }
            break;
        default:
            $res["msg"] = "habaye ikibazo, mwongere mukanya";
            $res["status"] = 0;
            break;
    }
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

                        // sms api
                        $curl = curl_init();

                        curl_setopt_array(
                            $curl,
                            array(
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
                                    'x-api-key: 35a13e16-dd2c-9c91-819b-34ed0beb5dc7-08b4b43d'
                                ),
                            )
                        );

                        curl_exec($curl);

                        curl_close($curl);

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

function resSelectedMenu($level, $dbConn, $phone)
{
    switch ($level) {
        case 1:
            $res["msg"] = "Andika Izina rya mbere (ry'umwana):";
            $res["status"] = 1;
            break;
        case 2:
            // get parent id
            $search_result = $dbConn->query("SELECT * FROM parents WHERE phone='$phone'");
            $fetched_rows = $search_result->fetch();
            $pid = $fetched_rows['id'];
            $comb_res = "Ibyenda gukorwa:\n";

            // get children under this->parent
            $child_result = $dbConn->query("SELECT * FROM children WHERE pid='$pid'");
            if ($child_result->rowCount() < 1) {
                $res["msg"] = "Nta mwana mwandikishije.";
                $res["status"] = 0;
            } else {
                $i = 1;
                while ($child_fetched_rows = $child_result->fetch()) {
                    $comb_res .= "\n" . $i . ". " . $child_fetched_rows['fname'] . " " . $child_fetched_rows['oname'] . ":";
                    $child_bd = $child_fetched_rows['born'];

                    $vax_result = $dbConn->query("SELECT * FROM vaccines");
                    if ($vax_result->rowCount() < 1) {
                        $comb_res .= "\nNta gikorwa gihari.";
                    } else {
                        $fetched_vax = $vax_result->fetch();
                        $period = $fetched_vax['period'];
                        $now = time(); // or your date as well
                        $your_date = strtotime($child_bd);
                        $datediff = $now - $your_date;

                        $diff = round($datediff / (60 * 60 * 24));
                        if ($diff <= $period) {
                            $comb_res .= "\n -> " . $fetched_vax['name'];
                        } else {
                            $comb_res .= "\n Nta gikorwa gihari.";
                        }
                    }
                    $i += 1;
                }
            }
            $res["msg"] = $comb_res;
            $res["status"] = 0;
            break;
        case 3:
            $res["msg"] = "Andika igitekerezo cyawe:";
            $res["status"] = 1;
            break;
        case 4:
            $res["msg"] = "Murakoze gukoresha sisitemu.";
            $res["status"] = 0;
            break;
        case 5:
            $res["msg"] = display_menu();
            $res["status"] = 1;
            break;
        default:
            $res["msg"] = "habaye ikibazo, mwongere mukanya";
            $res["status"] = 0;
            break;
    }
    return $res;
}

function toggleUserMenus($level, $dbConn, $phone)
{
    $res = array();
    switch ($level[count($level) - 2]) {
        case 1:
            $res["msg"] = "Andika andi mazina (y'umwana):";
            $res["status"] = 1;
            break;
        case 3:
            $search_result = $dbConn->query("SELECT * FROM parents WHERE phone='$phone'");
            $fetched_rows = $search_result->fetch();
            $sender = $fetched_rows['id'];
            $msg = trim(str_replace("#", '', $level[count($level) - 1]));
            $dbConn->exec("INSERT INTO comments (sender, message) VALUES('$sender','$msg')");
            $res["msg"] = "Murakoze! igitekerezo cyanyu cyakiriwe";
            $res["status"] = 0;
            break;
        default:
            $res["msg"] = "habaye ikibazo, mwongere mukanya";
            $res["status"] = 0;
            break;
    }
    return $res;
}


# close the pdo connection
$dbConn = null;

$resp = array("sessionId" => $sessionId, "message" => $response, "ContinueSession" => $ContinueSession);

echo json_encode($resp);