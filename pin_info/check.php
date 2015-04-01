<?php 

$conn = mysql_connect("localhost","netakart_root","india@123"); 
mysql_select_db("netakart_ClayNetaKart",$conn);
extract($_REQUEST);

	 $sql = "SELECT count(*) FROM `clay_delhivery_lm_pincode` WHERE  pin=$pincode";
	 $result = mysql_result(mysql_query($sql),0);
	 if($result > 0){
		 $resultA = mysql_result(mysql_query($sql),0);
	 } else {
		 $sql = "SELECT count(*) FROM `clay_redexpress_courier` WHERE  pincode=$pincode";
	 	 $resultA = mysql_result(mysql_query($sql),0);
	 }
echo json_encode(array($resultA,0));

@session_start();
$_SESSION['checked_pincode']=$pincode;

?>