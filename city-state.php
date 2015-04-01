<?php
			require_once('app/Mage.php');
			Mage::app();
			$conn = Mage::getSingleton('core/resource')->getConnection('core_read');
			$table = Mage::getSingleton('core/resource')->getTableName('netakart_pincode');	
			$query = "SELECT city,state FROM ".$table." WHERE pincode='".$_REQUEST['zip']."'";
		
			$result = $conn->query( $query );
			$resultPin = $result->fetch();
			echo json_encode($resultPin);
	
?>