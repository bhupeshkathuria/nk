<?php
class Clay_Salesreport_VendorwiseController extends Mage_Core_Controller_Front_Action {
    	

    public function indexAction() {
        $session = Mage::getSingleton('core/session');
        $salesId = $session->getData('salesId');
        $orderId = $this->getRequest()->getParam('orderid');
        if( $salesId && $salesId != "logout") {
            $this->loadLayout();
            $this->renderLayout();
        } else {
           $redirectPath = Mage::getUrl() . "salesreport/";
            $this->_redirectUrl( $redirectPath );
			
        }
    }

}
?>