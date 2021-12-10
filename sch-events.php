<?php
date_default_timezone_set("Africa/cairo");
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

$parent_result = $dbConn->query("SELECT * FROM parents");
$i = 1;
while ($fetched_rows = $parent_result->fetch()) {
    $child_result = $dbConn->query("SELECT * FROM children WHERE pid='" . $fetched_rows['id'] . "'");
    if ($child_result->rowCount() > 0) {
        while ($child_rows = $child_result->fetch()) {
            $vax_result = $dbConn->query("SELECT * FROM vaccines");
            if ($vax_result->rowCount() > 0) {
                while ($vax_rows = $vax_result->fetch()) {
                    $now = date('Y-m-d H:i'); // or your date as well

                    $born_date = new DateTime($child_rows['born']);
                    $ft_born_date = $born_date->format('Y-m-d H:i');
                    $vax_mins = $vax_rows['period'];
                    $time = $born_date;
                    $time->add(new DateInterval('PT' . $vax_mins . 'M'));
                    $vax_date = $time->format('Y-m-d H:i');
                    if ($now == $vax_date) {
                        // sms api script
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
                                CURLOPT_POSTFIELDS => array('to' => $fetched_rows['phone'], 'from' => 'SBCA', 'unicode' => '0', 'sms' => "Muraho  murasabwa kujya gukingiza -- " . $child_rows['fname'] . "" . $child_rows['oname'] . " --, mu minsi iri imbere tariki" . $vax_date . " . Murakoze!", 'action' => 'send-sms'),
                                CURLOPT_HTTPHEADER => array(
                                    'x-api-key: 35a13e16-dd2c-9c91-819b-34ed0beb5dc7-08b4b43d'
                                ),
                            )
                        );

                        curl_exec($curl);

                        curl_close($curl);
                        $res = "success";
                    } else {
                        $res = "failed";
                    }
                }
            }
        }
    }
}

echo json_encode($res);