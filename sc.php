<?php
$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => "https://api.mista.io/sms",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => array('action' => 'send-sms', 'to' => '+250780674459', 'from' => 'SBCA', 'sms' => 'YourMessage', 'schedule' => '12/07/2021 04:05 PM'),
    CURLOPT_HTTPHEADER => array(
        "x-api-key: 35a13e16-dd2c-9c91-819b-34ed0beb5dc7-08b4b43d"
    ),
));

$response = curl_exec($curl);

curl_close($curl);

echo $response;