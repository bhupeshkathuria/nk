<?php
class Magentomasters_Supplier_Block_Header extends Magentomasters_Supplier_Block_Abstract{
	public function isLoggedIn(){
	 	$session = Mage::getSingleton('core/session');
	    $supplierId = $session->getData('supplierId');
	    if($supplierId && $supplierId != "logout" ) {
	      	return true;
	    } else {
	    	return false;
	    }   
 	}
	
	
	public function getSupplierName(){
		$session = Mage::getSingleton('core/session');
	    $supplierId = $session->getData('supplierId');
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_users');	
		$sql = "SELECT surname FROM ".$table." WHERE id = ".$supplierId."";
		$result = $connect->query( $sql );
		$name = $result->fetch();
		return $name['surname'];
	}
		
}
	