<?php
require '../src/CloudApi.php';

$vCloud = new \BtcRelax\CloudApi('cloud.fastfen.club', 80);

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://cloud.fastfen.club/ocs/v2.php/apps/serverinfo/api/v1/info?format=json",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "Authorization: Basic Z29kOk0zR25SLUFDOHpiLXNyWm1ELTJEem9xLXQ5THRF"
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;
