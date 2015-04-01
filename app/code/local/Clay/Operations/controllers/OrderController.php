<?php
class Clay_Operations_OrderController extends Mage_Core_Controller_Front_Action {
    	
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
        $operationsId = $session->getData('operationsId');
        $orderId = $this->getRequest()->getParam('orderid');
        if( $operationsId && $operationsId != "logout") {
            $this->loadLayout();
            $this->renderLayout();
        } else {
           $redirectPath = Mage::getUrl() . "operations/";
            $this->_redirectUrl( $redirectPath );
			
        }
    }


    public function viewAction() {
        $session = Mage::getSingleton('core/session');
        $operationsId = $session->getData('operationsId');
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('operations/order')->load($orderId);
        Mage::register('sales_order', $order);
        Mage::register('order', $order);
        if( $salesId && $salesId != "logout" && $orderId) {
        	$check = Mage::getModel('supplier/order')->checkOrderAuth($operationsId,$orderId); 
            if(!$check){
            	$this->_redirectUrl(Mage::getUrl() . "operations/order"); 
			} else {
            	$this->loadLayout()->renderLayout();
			}
		} else {
            $redirectPath = Mage::getUrl() . "operations/";
            $this->_redirectUrl( $redirectPath );
        }
        if( $this->getRequest()->getParam( 'error' ) ){
            Mage::getSingleton('core/session')->addError($this->__('The username or password you entered is incorrect'));
        }
    }

	public function historyAction(){
		$session = Mage::getSingleton('core/session');
        $operationsId = $session->getData('operationsId');
        $orderId = $this->getRequest()->getParam('orderid');
        if( $operationsId && $operationsId != "logout") {
        	$this->loadLayout();
            $this->renderLayout();
        } else {
            $redirectPath = Mage::getUrl() . "operations/";
            $this->_redirectUrl( $redirectPath );
        }
	}

    public function addcommentAction(){
        $session = Mage::getSingleton('core/session');
        $operationsId = $session->getData('operationsId');
        $orderId = $this->getRequest()->getParam('orderid');
        if( $operationsId && $operationsId != "logout") {	
	        $orderId = $this->getRequest()->getParam('order_id');
	        $order = Mage::getModel('operations/order')->load($orderId);
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
            $redirectPath = Mage::getUrl() . "operations/";
            $this->_redirectUrl( $redirectPath );
        }
	}
	
	public function printAction(){
		$session = Mage::getSingleton('core/session');
        $operationsId = $session->getData('operationsId');
        $orderId = $this->getRequest()->getParam('order_id');
		$items = Mage::getModel('operations/order')->getCartItemsBySupplier($operationsId,$orderId);
 
        if($supplierId && $supplierId != "logout") {
           $file = 'invoices_'.date("Ymd_His").'.pdf';
           $pdf = Mage::getModel('operations/output')->getPdf($orderId,$supplierId,$items);		    
		   //print_r($settings);
		   $this->_prepareDownloadResponse($file,$pdf,'application/pdf');
        } else {
            $redirectPath = Mage::getUrl() . "supplier/";
            $this->_redirectUrl( $redirectPath );
        }
	}
	
	public function emailAction(){
		$session = Mage::getSingleton('core/session');
        $operationsId = $session->getData('operationsId');
        $orderId = $this->getRequest()->getParam('order_id');
		$items = Mage::getModel('operations/order')->getCartItemsBySupplier($operations,$orderId);

        if($operationsId && $operationsId != "logout") {
           $email = Mage::getModel('operations/output')->getEmail($orderId,$supplierId,$items);		    
        } else {
            $redirectPath = Mage::getUrl() . "operations/";
            $this->_redirectUrl( $redirectPath );
        }
	}

}