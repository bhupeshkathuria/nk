<?php 
class Clay_Salesreport_Block_Salesreport extends Mage_Core_Block_Template 
{ 
  // necessary methods 
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
?> 