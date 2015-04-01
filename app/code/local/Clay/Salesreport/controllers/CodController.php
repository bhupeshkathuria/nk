<?php
class Clay_Salesreport_CodController extends Mage_Core_Controller_Front_Action {
    	
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
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('salesreport/cod')->load($orderId);
        Mage::register('sales_order', $order);
        Mage::register('order', $order);
        if( $salesId && $salesId != "logout" && $orderId) {
        	$check = Mage::getModel('supplier/order')->checkOrderAuth($salesId,$orderId); 
            if(!$check){
            	$this->_redirectUrl(Mage::getUrl() . "salesreport/order"); 
			} else {
            	$this->loadLayout()->renderLayout();
			}
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

	public function exporttocsvAction(){
		$session = Mage::getSingleton('core/session');
        $salesId = $session->getData('salesId');
        if( $salesId && $salesId != "logout") {
        	$orders = Mage::getModel('salesreport/cod')->getRemit($_GET['from'],$_GET['to']);
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename=cod_remit.csv');
			$output = fopen('php://output', 'w');
			fputcsv($output, array('Waybill','Order No','Order Date','Customer Details', 'COD', 'Sale', 'COD Remit','Courier Name', 'Vendor Name'));
			
			 $i = 0; foreach($orders as $order) : ?>
	    <?php
				if($order->getPayment()->getMethodInstance()->getCode()=='cashondelivery'):
				$items = $order->getAllVisibleItems();
				
				$itemcount= count($items);
				$data = array();
				$supplierDetails = Mage::getModel('salesreport/cod')->getRemitCustomSku($order->getRealOrderId()); 
				
				#loop for all order items
				foreach ($items as $itemId => $item):
					
				$row= array();
                $row['waybill'] = Mage::getModel('salesreport/cod')->getRemitWaybill($order->getRealOrderId());
                $row['order_no'] = $order->getRealOrderId();
                $row['order_date'] = $order->getCreatedAtStoreDate()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
                $row['contact_details'] = $order->getBillingAddress()->getName();
                $row['cod'] = $order->getPayment()->getMethodInstance()->getTitle();
                $row['sale'] = Mage::helper('core')->currency(($item->getPrice())*($item->getData('qty_ordered')), true, false);
                $cod = Mage::getModel('salesreport/cod')->getCodRemit($order->getRealOrderId()); 
				$uncod = Mage::getModel('salesreport/cod')->getUnCodRemit($order->getRealOrderId()); 
				if($cod['codremit']!='' && $cod['codremit']!='0.0000'){
					//$row['paid'] = Mage::helper('core')->currency($cod['codremit'], true, false);
					$row['paid'] = 'Yes';
					if($_POST['payment_status']=='N'){
						continue;
					}
				}
                
				else if($uncod['codremit']=='0.0000' && $item->getPrice() != $cod['codremit']){
				 //$row['unpaid'] = Mage::helper('core')->currency($uncod['price'], true, false);	
				 	$row['unpaid']='No';
					if($_POST['payment_status']=='Y'){
						continue;
					}
				}// Here is some issue , Please check // Navneet
                $row['courier_name'] = 'Delhivery';
                $row['vendor_name'] = $supplierDetails['surname'];
               
				fputcsv($output, $row);	
                
	    		
				endforeach; 
					
				endif;	
                endforeach; 
		   		
		} else {
           $redirectPath = Mage::getUrl() . "salesreport/";
            $this->_redirectUrl( $redirectPath );
			
        }	
	}
	public function codAction(){
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		
		if ($_FILES[csv][size] > 0) {
		//get the csv file
		$file = $_FILES[csv][tmp_name];
		$handle = fopen($file,"r");
    //loop through the csv file and insert into database
				$i=0;
				while ($data = fgetcsv($handle,1000,",","'")){
					if($i == 0){
					$i= 1;
					continue;	
					}
					$order_details = Mage::getModel('sales/order')->loadByIncrementId($data[1]);
					$totalAmount = round($order_details->getGrandTotal());
				if($totalAmount == round($data[2])){
					$sql="UPDATE ".$table." SET `codremit` = '".round($data[2]).".0000' WHERE `order_number` = '".$data[1]."'";
					
					$result = $connect->query($sql);
					if(!$result){
						echo $result;
						die;
					}
					} else {
						
						$error = $data[2];
					
					}
			}
		
			
			if($error){
				
				$redirectPath = Mage::getUrl() . "salesreport/cod?success=".$error."";
				
			} else {
				$redirectPath = Mage::getUrl() . "salesreport/cod?success=1";
			}
			
            $this->_redirectUrl( $redirectPath ); 
		
		}			
	}
}