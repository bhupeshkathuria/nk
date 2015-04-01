<?php
class Clay_Operations_Block_Header extends Clay_Operations_Block_Abstract{
	public function isLoggedIn(){
	 	$session = Mage::getSingleton('core/session');
	    $operationsId = $session->getData('operationsId');
	    if($operationsId && $operationsId != "logout" ) {
	      	return true;
	    } else {
	    	return false;
	    }   
 	}	
}
	