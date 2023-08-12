<?

require "vendor/autoload.php";

use GuzzleHttp\Client;

$client = new Client([
    "base_uri" => "https://muohio.libcal.com",
]);

$response = $client->request("POST", "1.1/oauth/token", [
	"form_params" => [
		"client_id"     => "230",
		"client_secret" => "2a776c14a7bf130b46295f4ac41173cb",
		"grant_type"    => "client_credentials"
	]
]);

$token = json_decode($response->getBody())->access_token;


// ID: 6672 for Category King Study Rooms (Swipe Accessible)
$response = $client->request("GET", "/1.1/space/bookings?cid=6672", [
	"headers" => [
		"Authorization" => "Bearer {$token}"
	]
]);

$bookings = json_decode($response->getBody());
echo $response->getBody() . "\n";
//$code = $response->getStatusCode();
//echo $code . "\n";

$allowed_doors = array(
      25187 => "King 102",
      25188 => "King 103",
      25189 => "King 104",
      25190 => "King 105",
      25191 => "King 106",
      25192 => "King 107",
      25193 => "King 108");

function formatDate($time) {
  $time = new DateTime($time);
  $formattedTime = $time->format('m/d/Y H:i:s');
  return $formattedTime;
}

$file = "cancelled.txt";
if (filesize($file) == 0) {
	$cancelled = [];
} else {
	$cancelled = explode("\n", file_get_contents($file));
}

date_default_timezone_set("America/New_York");
echo date('m/d/Y H:i:s') . "\n";

foreach ($bookings as $booking) {
	$status = $booking->status;
	$bid = $booking->bookId;
	$uid = strstr($booking->email, "@", true);
	$loc = $allowed_doors[$booking->eid];
	$bdt = formatDate($booking->fromDate);
	$edt = formatDate($booking->toDate);
	$hash = "gfe45554$%g45%$45g&67jh6782@@@Dghghv";
	$site = "https://www.hdg.miamioh.edu/Code/MyCard/MyCSGoldDoorAccessUpdater.php?";

	echo $bdt . "\n";
	
	if (date('m/d/Y H:i:s') < $bdt) {//if the reservation time isn't passed...
		if ($status != "Confirmed" && !in_array($bid, $cancelled)){ //if the room is cancelled and we haven't revoked privileges yet...
			//note that we have already revoked room access so we don't send multiple requests
			file_put_contents($file, $bid . "\n", FILE_APPEND);
			//send room access removal request
			$encoded_data = "uid={$uid}&loc=" . urlEncode($loc) . "&bdt=" . urlEncode($bdt) . "&edt=" . urlEncode($edt) . "&aord=d&hash=" . urlEncode($hash);
			echo "We cancelled {$bid}\n";
			//$openDoor = fopen("$site" . "$opener", 'r'); 
			//fclose($openDoor);
		} elseif ($status == "Confirmed") {
			echo "We opened {$loc}\n";
			$opener = "uid={$uid}&loc=" . urlEncode($loc) . "&bdt=" . urlEncode($bdt) . "&edt=" . urlEncode($edt) . "&aord=a&hash=" . urlEncode($hash);
			//if this comes back as an empty array check if there are any live bookings before freaking out
			//$openDoor = fopen("$site" . "$opener", 'r');
			//fclose($openDoor);
		}
	}
}

