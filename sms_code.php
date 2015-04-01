<?php
//Please Enter Your Details
extract($_REQUEST);
$user="clayt"; //your username
$password="we34vc"; //your password
$mobilenumbers=$number; //enter Mobile numbers comma seperated
$message = $message; //enter Your Message
$senderid="NTKART"; //Your senderid
$messagetype=0; //Type Of Your Message
$DReports="Y"; //Delivery Reports
$url="http://121.241.242.121:8000/bulksms/bulksms?";
$message = urlencode($message);
$ch = curl_init();
if (!$ch){die("Couldn't initialize a cURL handle");}
$ret = curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt ($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt ($ch, CURLOPT_POSTFIELDS,
"username=$user&password=$password&type=0&dlr=1&destination=$mobilenumbers&source=$senderid&message=$message");
$ret = curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//If you are behind proxy then please uncomment below line and provide your proxy ip with port.
//$ret = curl_setopt($ch, CURLOPT_PROXY, "121.241.242.121:8000");
$curlresponse = curl_exec($ch); // execute

if(curl_errno($ch))
echo 'curl error : '. curl_error($ch);
if (empty($ret)) {
// some kind of an error happened
die(curl_error($ch));
curl_close($ch); // close cURL handler
} else {
$info = curl_getinfo($ch);
curl_close($ch); // close cURL handler
//echo "";
echo $curlresponse; //echo "Message Sent Succesfully" ;
}
?>