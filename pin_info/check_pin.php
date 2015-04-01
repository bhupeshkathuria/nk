<?php 

$conn = mysql_connect("localhost","root","india@123"); 
mysql_select_db("NetaKart",$conn);
extract($_REQUEST);
	 $sql = "SELECT count(*) FROM `clay_shop_service_pincodes` WHERE  pincode=$pincode";
	
echo json_encode(array(mysql_result(mysql_query($sql),0)));

@session_start();
$_SESSION['checked_pincode']=$pincode;

?>