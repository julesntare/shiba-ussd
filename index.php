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
$sessionId = $_POST['sessionId'];
$userinput = urldecode($_POST["UserInput"]);
$serviceCode = $_POST["serviceCode"];
$networkCode = $_POST['networkCode'];

$ussd_string_exploded = explode("80", $userinput);

// Get menu level from ussd_string reply
$level = $ussd_string_exploded[count($ussd_string_exploded) - 1];
echo $userinput;

if ($userinput == '*662*800*80#') {
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
    $initial_msg = "Murakaza  Neza Muri SBCS System\n\n 1. kwiyandikisha(umubyeyi) \n 2. ibyerekeye system \n 3. konti yange \n";
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
    $ussd_text = "1. kwandika umwana mushya\n  3. Tanga igitekerezo\n 4. Gusohoka muri system\n 5. Subira ahabanza\n";
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
                $resSel = toggleUserMenus($temp, $dbConn, $phone, $lvl);
                $res = array_merge($res, $resSel);
            }
            break;
        case 6:
            if (empty($lvl)) {
                $res["msg"] = "ntakintu mwinjijemo ntabwo byemewe";
                $res["status"] = 0;
            } else {
                $res["msg"] = "igitsina cyumwana \n 1.gabo \n 2.gore ";
                $res["status"] = 1;
            }
            break;
        case 7:
            if (empty($lvl)) {
                $res["msg"] = "ntakintu mwinjijemo ntabwo byemewe";
                $res["status"] = 0;
            } else {
                $res["msg"] = "undi mubyeyi :\n";
                $res["status"] = 1;
                
            }
            break;
        case 8:
            if (empty($lvl)) {
                $res["msg"] = "ntakintu mwinjijemo ntabwo byemewe";
                $res["status"] = 0;
            } else {
                $res["msg"] = "Injiza itariki yivuka. urugero:\n " . date("Y-m-d") . "\n";
                $res["status"] = 1;
            }
            break;
        case 9:
            if (empty($lvl)) {
                $res["msg"] = "ntakintu mwinjijemo ntabwo byemewe";
                $res["status"] = 0;
            } else if (strtotime($lvl) < strtotime('-2 years')) {
                $res["msg"] = "Umwana agomba kuba ari munsi yimyaka 2.";
                $res["status"] = 0;
            } else if ($lvl > date('Y-m-d')) {
                $res["msg"] = "Mwashyizemo itariki itaragera.";
                $res["status"] = 0;
            } else {
                $search_result = $dbConn->query("SELECT * FROM parents WHERE phone='$phone'");
                $fetched_rows = $search_result->fetch();
                $pid = $fetched_rows['id'];
                $fname = trim(str_replace("#", '', $temp[count($temp) - 5]));
                $oname = trim(str_replace("#", '', $temp[count($temp) - 4]));
                $gender = trim(str_replace("#", '', $temp[count($temp) - 3]));
                $par2 = trim(str_replace("#", '', $temp[count($temp) - 2]));
                $born = trim(str_replace("#", '', $temp[count($temp) - 1]));

                $search_result_not = $dbConn->query("SELECT * FROM events");
                $search_result_data = $search_result_not->fetchAll();

                foreach ($search_result_data as $x => $y) {
                    $timetosend = $y[2] + time();
                    $smstext = $y[0];
                    $dbConn->exec("INSERT INTO sms (receiver_phone, smstext, timetosend) VALUES('$pid', '$smstext', '$timetosend')");
                }

                $dbConn->exec("INSERT INTO children (fname, oname, gender, pid, born, par2) VALUES('$fname', '$oname', '$gender', '$pid', '$born' , '$par2')"); // sms api
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
                        CURLOPT_POSTFIELDS => array('to' => $phone, 'from' => 'SBCA', 'unicode' => '0', 'sms' => "Muraho , umwana yanditswe muri SBCS . mubyeyi, muzajya muhabwa inama kumikurire ye " .$fname.  "Murakoze!", 'action' => 'send-sms'),
                        CURLOPT_HTTPHEADER => array(
                            'x-api-key: 35a13e16-dd2c-9c91-819b-34ed0beb5dc7-08b4b43d'
                        ),
                    )
                );

                curl_exec($curl);

                curl_close($curl);
                $res["msg"] = "Byegenze neza! " . $fname . " yanditswe muri sisitemu";
                $res["status"] = 0;
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
        $res["msg"] = "Musanzwe muri muri sisitemu!";
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
                } else if (ctype_alpha($lvl) != 1) {
                    $res["msg"] = "Hemewe inyuguti gusa";
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
                } else if (ctype_alpha($lvl) != 1) {
                    $res["msg"] = "Hemewe inyuguti gusa";
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
                } else if ((ctype_digit($lvl) != 1) || (strlen($lvl) != 16)) {
                    $res["msg"] = "Hemewe imibare 16";
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
                    $res["msg"] = "Ongera ushyiremo umubare w'ibanga:";
                    $res["status"] = 1;
                }
                break;
            case 7:
                if (empty($lvl)) {
                    $res["msg"] = "Ntakintu mwinjijemo ntabwo byemewe";
                    $res["status"] = 0;
                } else if (trim(str_replace("#", '', $temp[count($temp) - 2])) != trim(str_replace("#", '', $temp[count($temp) - 1]))) {
                    $res["msg"] = "Umubare w'i ibanga ntuhuye n uwambere";
                    $res["status"] = 0;
                } else {
                    $fname = trim(str_replace("#", '', $temp[count($temp) - 5]));
                    $oname = trim(str_replace("#", '', $temp[count($temp) - 4]));
                    $idno = trim(str_replace("#", '', $temp[count($temp) - 3]));
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
                    $comb_res .= "\n" . $i . ". " . $child_fetched_rows['fname'] . " " . $child_fetched_rows['oname'];
                    $i += 1;
                }
            }
            $res["msg"] = $comb_res;
            $res["status"] = 1;
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

function toggleUserMenus($level, $dbConn, $phone, $txt)
{
    $res = array();
    switch ($level[count($level) - 2]) {
        case 1:
            $res["msg"] = "Andika andi mazina (y'umwana):";
            $res["status"] = 1;
            break;
        case 2:
            // get parent id
            $search_result = $dbConn->query("SELECT * FROM parents WHERE phone='$phone'");
            $fetched_rows = $search_result->fetch();
            $pid = $fetched_rows['id'];

            // get children under this->parent
            $child_result = $dbConn->query("SELECT * FROM children WHERE pid='$pid'");
            $i = 0;
            while ($child_fetched_rows = $child_result->fetch()) {
                if ($child_fetched_rows[$txt - 1] == $child_fetched_rows[$i]) {
                    $res["msg"] = "Andika ibyihariye ku mwana: " . $child_fetched_rows['fname'];
                }
                $i += 1;
            }
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