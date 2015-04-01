<?php 
umask(0);
require_once 'app/Mage.php';
Mage::app();

$customer_email = $_POST['username'];
$customer = Mage::getModel("customer/customer");
$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
$customer->loadByEmail($customer_email); //load customer by email id //use 
if($customer->getId()){
	echo "1";	
}




?>