<?php
class Magentomasters_Supplier_Block_Orderleft extends Magentomasters_Supplier_Block_Abstract{
	public function isLoggedIn(){
	 	$session = Mage::getSingleton('core/session');
	    $supplierId = $session->getData('supplierId');
	    if($supplierId && $supplierId != "logout" ) {
	      	return true;
	    } else {
	    	return false;
	    }   
 	}
	
	public function getCustomStatus(){
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('sales_order_status');		
		$sql = "SELECT * FROM ".$table." ORDER BY sort";
		$result = $connect->query( $sql );
		 return $result->fetchAll();
	}	
}
	