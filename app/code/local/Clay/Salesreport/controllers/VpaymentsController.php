<?php
class Clay_Salesreport_VpaymentsController extends Mage_Core_Controller_Front_Action {
    	
	public function preDispatch(){
		parent::preDispatch();	
		if(Mage::getStoreConfig('supplier/interfaceoptions/interface_enabled')=='0'){
			$redirectPath = Mage::getUrl();
			$this->_redirectUrl($redirectPath); 
		} else if(Mage::getStoreConfig('supplier/interfaceoptions/interface_shipping')=='0'){
			$redirectPath = Mage::getUrl() . "supplier/product";
			$this->_redirectUrl($redirectPath); 
		}
	}	

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


    public function viewAction() {
        $session = Mage::getSingleton('core/session');
        $salesId = $session->getData('salesId');
        $orderId = $this->getRequest()->getParam('supplier_name');
		
        //$order = Mage::getModel('salesreport/vpayments')->load($orderId);
       // Mage::register('sales_order', $order);
        //Mage::register('order', $order);
		
        if( $salesId && $salesId != "logout" && $orderId) {
        	
            	$this->loadLayout()->renderLayout();
			
		} else {
            $redirectPath = Mage::getUrl() . "salesreport/";
            $this->_redirectUrl( $redirectPath );
        }
        if( $this->getRequest()->getParam( 'error' ) ){
            Mage::getSingleton('core/session')->addError($this->__('The username or password you entered is incorrect'));
        }
    }

	public function historyAction(){
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

    public function addcommentAction(){
        $session = Mage::getSingleton('core/session');
        $salesId = $session->getData('salesId');
        $orderId = $this->getRequest()->getParam('orderid');
        if( $salesId && $salesId != "logout") {	
	        $orderId = $this->getRequest()->getParam('order_id');
	        $order = Mage::getModel('salesreport/order')->load($orderId);
	        Mage::register('sales_order', $order);
	        $post = $this->getRequest()->getPost();
	        if ($post) {
	            $comment = trim(strip_tags($post['comment']));
	            $order->addStatusToHistory($order->getStatus(), $comment, $post['notify']);
	            $order->save();
	            $order->sendOrderUpdateEmail($post['notify'], $comment);
	        }
	        $this->loadLayout()->renderLayout();
    	} else {
            $redirectPath = Mage::getUrl() . "salesreport/";
            $this->_redirectUrl( $redirectPath );
        }
	}
	
	public function printAction(){
		$session = Mage::getSingleton('core/session');
        $salesId = $session->getData('salesId');
        $orderId = $this->getRequest()->getParam('order_id');
		$items = Mage::getModel('salesreport/order')->getCartItemsBySupplier($salesId,$orderId);
 
        if($supplierId && $supplierId != "logout") {
           $file = 'invoices_'.date("Ymd_His").'.pdf';
           $pdf = Mage::getModel('supplier/output')->getPdf($orderId,$supplierId,$items);		    
		   //print_r($settings);
		   $this->_prepareDownloadResponse($file,$pdf,'application/pdf');
        } else {
            $redirectPath = Mage::getUrl() . "supplier/";
            $this->_redirectUrl( $redirectPath );
        }
	}
	
	public function emailAction(){
		$session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
        $orderId = $this->getRequest()->getParam('order_id');
		$items = Mage::getModel('supplier/order')->getCartItemsBySupplier($supplierId,$orderId);

        if($supplierId && $supplierId != "logout") {
           $email = Mage::getModel('supplier/output')->getEmail($orderId,$supplierId,$items);		    
        } else {
            $redirectPath = Mage::getUrl() . "supplier/";
            $this->_redirectUrl( $redirectPath );
        }
	}

}