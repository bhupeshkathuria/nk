<?php 
class Clay_Operations_Block_Operations extends Mage_Core_Block_Template 
{ 
  // necessary methods 
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
?> 