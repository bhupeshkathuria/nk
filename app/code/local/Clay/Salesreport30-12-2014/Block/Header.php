<?php
class Clay_Salesreport_Block_Header extends Clay_Courier_Block_Abstract{
	public function isLoggedIn(){
	 	$session = Mage::getSingleton('core/session');
	    $salesId = $session->getData('salesId');
	    if($salesId && $salesId != "logout" ) {
	      	return true;
	    } else {
	    	return false;
	    }   
 	}	
}
	