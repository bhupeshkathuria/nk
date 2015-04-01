<?php
class Clay_Courier_Block_Header extends Clay_Courier_Block_Abstract{
	public function isLoggedIn(){
	 	$session = Mage::getSingleton('core/session');
		$courierId = $session->getData('courierId');
	    if($courierId && $courierId != "logout") {
	      	return true;
	    } else {
	    	return false;
	    }   
 	}	
}