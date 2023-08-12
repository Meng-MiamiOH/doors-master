<?php

$endpoint = "https://api2.libcal.com/1.1/oauth/token";
$params = array(
  'client_id'     => '230',
  'client_secret' => '2a776c14a7bf130b46295f4ac41173cb',
  'grant_type'    => 'client_credentials');

$curl = curl_init($endpoint);
curl_setopt($curl, CURLOPT_HEADER, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_HEADER,'Content-Type: application/x-www-form-urlencoded');

$postData = "";
foreach($params as $k => $v){
   $postData .= $k . '='.urlencode($v).'&';
}
$postData = rtrim($postData, '&');
curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);

$json_response = curl_exec($curl);
$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
if ($status != 200) {
  throw new Exception("Error: call to URL $endpoint failed with status $status, response $json_response, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl) . "\n");
}
curl_close($curl);

$response = json_decode($json_response);
$token = $response->access_token;

$curl = curl_init();
// swipe rooms group ID = 6672
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api2.libcal.com/1.1/space/bookings?cid=6672",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "Authorization: Bearer {$token}",
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response . "\n";
}

$allowed_doors = array(
      25183 => "King 302",
      25186 => "King 227",
      25187 => "King 102",
      25188 => "King 103",
      25189 => "King 104",
      25190 => "King 105",
      25191 => "King 106",
      25192 => "King 107",
      25193 => "King 108",
      25194 => "King 113A");

function formatDate($time) {
  $time = new DateTime($time);
  $formattedTime = $time->format('m/d/Y H:i:s');
  return $formattedTime;
}

$bookings = json_decode($response);

foreach ($bookings as $booking) {
  $status = $booking->status;
  $uid = strstr($booking->email, '@', true);
  $loc = $allowed_doors[$booking->eid];
  $bdt = formatDate($booking->fromDate);
  $edt = formatDate($booking->toDate);
  $hash = "gfe45554$%g45%$45g&67jh6782@@@Dghghv";
  $site = "https://www.hdg.miamioh.edu/Code/MyCard/MyCSGoldDoorAccessUpdater.php?";
  $opener = "uid={$uid}&loc=" . urlEncode($loc) . "&bdt=" . urlEncode($bdt) . "&edt=" . urlEncode($edt) . "&aord=a&hash=" . urlEncode($hash);
  //if this comes back as an empty array, check if there are any live bookings before freaking out
  if ($status == "Confirmed") {
    $openDoor = fopen("$site" . "$opener", 'r');
    fclose($openDoor);
  }
}