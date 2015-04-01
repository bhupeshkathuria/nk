<?php 
class Clay_Courier_Block_Courier extends Mage_Core_Block_Template 
{ 
  // necessary methods 
  public function isLoggedIn(){
	 	$session = Mage::getSingleton('core/session');
	    $courierId = $session->getData('courierId');
	    if($courierId && $courierId != "logout" ) {
	      	return true;
	    } else {
	    	return false;
	    }   
 	}	
} 
?> 