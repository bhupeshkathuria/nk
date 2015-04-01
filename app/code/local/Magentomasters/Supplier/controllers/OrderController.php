<?php
class Magentomasters_Supplier_OrderController extends Mage_Core_Controller_Front_Action {
    	
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
        $supplierId = $session->getData('supplierId');
        $orderId = $this->getRequest()->getParam('orderid');
        if( $supplierId && $supplierId != "logout") {
            $this->loadLayout();
            $this->renderLayout();
        } else {
            $redirectPath = Mage::getUrl() . "supplier/";
            $this->_redirectUrl( $redirectPath );
        }
    }


    public function viewAction() {
		
        $session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
        $orderId = $this->getRequest()->getParam('order_id');
		
        $order = Mage::getModel('sales/order')->load($orderId);
        Mage::register('sales_order', $order);
        Mage::register('order', $order);
        if( $supplierId && $supplierId != "logout" && $orderId) {
        	
			$check = Mage::getModel('supplier/order')->checkOrderAuth($supplierId,$orderId); 
			
            if(!$check){
            	$this->_redirectUrl(Mage::getUrl() . "supplier/order"); 
			} else {
            	$this->loadLayout()->renderLayout();
			}
		} else {
			
			
            $redirectPath = Mage::getUrl() . "supplier/";
            $this->_redirectUrl( $redirectPath );
        }
        if( $this->getRequest()->getParam( 'error' ) ){
            Mage::getSingleton('core/session')->addError($this->__('The username or password you entered is incorrect'));
        }
    }

	public function historyAction(){
		$session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
        $orderId = $this->getRequest()->getParam('orderid');
        if( $supplierId && $supplierId != "logout") {
        	$this->loadLayout();
            $this->renderLayout();
        } else {
            $redirectPath = Mage::getUrl() . "supplier/";
            $this->_redirectUrl( $redirectPath );
        }
	}

    public function addcommentAction(){
        $session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
        $orderId = $this->getRequest()->getParam('orderid');
        if( $supplierId && $supplierId != "logout") {	
	        $orderId = $this->getRequest()->getParam('order_id');
	        $order = Mage::getModel('sales/order')->load($orderId);
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
            $redirectPath = Mage::getUrl() . "supplier/";
            $this->_redirectUrl( $redirectPath );
        }
	}
	
	public function printAction(){
		$session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
        $orderId = $this->getRequest()->getParam('order_id');
		$items = Mage::getModel('supplier/order')->getCartItemsBySupplier($supplierId,$orderId);
 
        if($supplierId && $supplierId != "logout") {
           $file = 'invoices_'.date("Ymd_His").'.pdf';
           $pdf = Mage::getModel('supplier/output')->getPdf($orderId,$supplierId,$items);		    
		   //print_r($settings);
		  // die;
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


	public function getShippingId1($id){
			$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        	$table = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment');
			$query = "SELECT entity_id,total_qty FROM ".$table." WHERE order_id = ".$id."";
			$result = $connect->query( $query );
        	return $result->fetch();
	}
	
	public function customstatusAction(){
		$post = $this->getRequest()->getPost();
		$cstatus = $post['chkStatus'];	
		$aststus = $post['aStatus'];
		if($post['advCustom'] != 0){
			$cstatus = $post['advCustom'];	
			$aststus = $post['advStatus'];
		}
		$id = "('" . implode( "','", $post['aOrderid'] ) . "');" ;
		foreach($post['aOrderid'] as $aorder){
				
				$order = Mage::getModel('sales/order')->loadByIncrementId($aorder);
				$state = $aststus;
				$status = $aststus;
				$isCustomerNotified = true;
				$order->setState($state, $status);
				$order->save();	
				
		}
		
				$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
				$connect = Mage::getSingleton('core/resource')->getConnection('core_read');
				$query = "UPDATE $table SET status='".$cstatus."' WHERE order_number IN $id";
				$connect->query($query);
				$redirectPath = Mage::getUrl() . "supplier/order";
				$this->_redirectUrl( $redirectPath );
		/*print_r($post);
		die;
		//echo $count = count($post);	
			$id = "('" . implode( "','", $post['aOrderid'] ) . "');" ;
			$cstatus = $post['chkStatus'];	
			$aststus = $post['aStatus'];
			for($i = 0; $i < count($post['aOrderid']); $i++){
				
				$aorder = $post['aOrderid'][$i];
				$order = Mage::getModel('sales/order')->loadByIncrementId($aorder);
				$state = $aststus;
				$status = $aststus;
				$isCustomerNotified = true;
				$order->setState($state, $status);
				$order->save();
			}
				/*$entityid = $this->getShippingId1($aorder);
				$entityidA = $entityid['entity_id'];
				$qtyA = $entityid['total_qty'];
				$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
				$connect = Mage::getSingleton('core/resource')->getConnection('core_read');
				$query = "UPDATE $table SET status='".$cstatus."' WHERE order_number IN $id";
				$connect->query($query);
			$redirectPath = Mage::getUrl() . "supplier/order";
            $this->_redirectUrl( $redirectPath );*/
	}
	
	public function manifestAction(){
		$session = Mage::getSingleton('core/session'); 
        $supplierId = $session->getData('supplierId');
        $post = $this->getRequest()->getPost();
		$items = Mage::getModel('supplier/order')->getCartItemsBySupplierManifest($supplierId,$post['aOrderid']);
		
		if($supplierId && $supplierId != "logout") {
           //$file = 'manifest_'.date("Ymd_His").'.pdf';
           //$pdf = Mage::getModel('supplier/output')->getManifestPdf($post['aOrderid'],$supplierId,$items);		    
		   
		   $this->loadLayout();
           $this->renderLayout();
		   //print_r($settings);
		  // die;
		  // $this->_prepareDownloadResponse($file,$pdf,'application/pdf');
        } else {
            $redirectPath = Mage::getUrl() . "supplier/";
            $this->_redirectUrl( $redirectPath );
        }	
	}
	public function shippingprintAction(){
		$session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
			/*include('/var/www/html/app/code/community/Delhivery/Lastmile/controllers/Adminhtml/LastmileController.php');
		$shipping = new Delhivery_Lastmile_Adminhtml_LastmileController;
		$shipping->shippinglabelAction();*/
		$waybills = $this->getRequest()->getParam('lastmile');
		$couriers = $this->getRequest()->getParam('courier');
		//$waybills = array('18','20','40','55','56','58','60','62','64','65','66','69','70','71','73','75','77','78','82','84','86','87','88','89','91','92','93','94','95','96','97','98','100','102','103','104','106','108','109','111','112','115','117','118','119','120','121','122','125','126','128','129','130','131','132','133','134','136','137','138','139','140','141','143','144','147','148','161','165','182');
		
		
		mage::log("Shipping Label Printed for these waybills $waybills");
	$flag = false;
	if (!empty($waybills)) {
		$labelperpage = 1;
		$totalpages = sizeof($waybills)/$labelperpage;   			
        $pdf = new Zend_Pdf();
        $style = new Zend_Pdf_Style();		
		for ($page_index = 0; $page_index <= $totalpages; $page_index++)
        {
			$page = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4);
			$pdf->pages[] = $page;
		}
		$pagecounter = -1;
		$i=0; $y=830;
		
	
		foreach ($waybills as $key=>$waybill) {			
			//$awb = Mage::getModel('lastmile/lastmile')->load($waybill,$couriers[$key]);
			
			$courierTable = Mage::getModel('supplier/order')->getCourierTableName($couriers[$key]);
			
			$courierName = Mage::getModel('supplier/order')->getCourierName($couriers[$key]);
			
			$awb = Mage::getModel('supplier/order')->loadawb($waybill,$courierTable);
			
			
			
			
			
			if($awb['state']==2)
			continue;
			$i++;
			// check if next page;
			if($i%$labelperpage == 0)
			{
			$pagecounter++; // Set to use new page
			$y = 830; // Set position for first label on new page
			}
			//$pdf->pages[$pagecounter];
			$shipments = Mage::getResourceModel('sales/order_shipment_collection')->setOrderFilter($awb['orderid'])->load();
			
			
			
			if ($shipments->getSize()) {
				$flag = true;
				
				//$pdf = $this->getPdf($awb->awb,$shipment);
				foreach ($shipments as $shipment) {
 					
					Mage::getModel('lastmile/shippinglabel')->getContent($pdf->pages[$pagecounter], $shipment->getStore(), $awb['awb'], $shipment->getOrder(),$y,$this->getRequest()->getPost(),$courierName);
				}			
				
			}
			// Set position for the next label on same page
			$y = $y-190;
						
		}
		if ($flag) {
			return $this->_prepareDownloadResponse(
				'shippinglabel'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
				'application/pdf'
			);
		} else {
			//$this->_getSession()->addError($this->__('There are no printable shipping labels related to selected waybills.'));
			$this->_redirect('*/*/');
		}
	}
	$this->_redirect('*/*/');
	}
	public function uploadmanifestAction(){
			$session = Mage::getSingleton('core/session');
			$supplierId = $session->getData('supplierId');
		
			if(isset($_FILES['manifest']['name']) && $_FILES['manifest']['name'] != '')
			{
    			try{
					$filename = explode("_",$_FILES['manifest']['name']);
					$path = Mage::getBaseDir().DS.'media'.DS.'manifest'.DS;
					$fname = $_FILES['manifest']['name'];                         
        			$uploader = new Varien_File_Uploader('manifest');
        			//$uploader->setAllowedExtensions(array('pdf'));
        			$uploader->setAllowCreateFolders(true);
        			$uploader->setAllowRenameFiles(false); 
			    	$uploader->setFilesDispersion(false);
        			$uploader->save($path,$fname); //save the file on the specified path
         			$table = Mage::getSingleton('core/resource')->getTableName('supplier_manifest');
					$connect = Mage::getSingleton('core/resource')->getConnection('core_read');
					$query = "insert into $table(supplier_id,file_name,date_time) values('".$supplierId."','".$_FILES['manifest']['name']."','".date("Y-m-d H:i:s")."')";
					//$query = "UPDATE $table SET date_time='".date("Y-m-d H:i:s")."' WHERE manifest_id='$filename[1]'";
					$connect->query($query);
				}
    			catch(Exception $e){
        			echo 'Error Message: '.$e->getMessage();
					die();
    			}
			}
			$redirectPath = Mage::getUrl() . "supplier/order/?s=4&u=y";
            $this->_redirectUrl( $redirectPath );
	}
	public function picklistAction(){
		
		$session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
		
		if($supplierId && $supplierId != "logout") {
    	//$status = array($post['status']);
		$status = array(1,2,3,4,5,8);
		$picklists = Mage::getModel("supplier/order")->picklistOrders($supplierId, $status);
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=picklist.csv');
		$output = fopen('php://output', 'w');
		fputcsv($output, array('Waybill','Order No','Order Date','Consignee Name', 'City', 'State', 'Country', 'Address', 'Pincode', 'Mobile', 'Payment Mode', 'Package Amount', 'Status','Product to be Shipped','Product Sku','Product Quantity','Courier\'s Name', 'Updated At'));
	
		foreach($picklists as $picklist){
		
		$row= array();	
		
		$order = Mage::getModel('sales/order')->loadByIncrementId($picklist['order_number']);
		if(!is_object($order->getPayment())){
			continue;
		}
		$customer = $order->getBillingAddress();
		$paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
		
		
		//$items = Mage::getModel('sales/order')->loadByIncrementId($picklist['order_number'])->getAllItems();
		

		//print_r($customer->getData());
		
		$row['waybill'] = $picklist['awb'];
		$row['order_no'] = $picklist['order_number'];
		$row['order_date'] = $picklist['created_time'];
		$row['consignee_name'] = $customer->getData('firstname');
        $row['city'] = $customer->getData('city');
        $row['state'] = $customer->getData('region');
		$row['country'] = str_replace("IN","India",$customer->getData('country_id'));
		$row['address'] = $customer->getStreet(1).' '.$customer->getStreet(2);
		$row['pincode'] = $customer->getData('postcode');
		$row['mobile'] = $customer->getData('telephone');
		$row['payment_mode'] = $paymentMethod;
		$row['package_amount'] = $picklist['price'];
		$row['status'] = $picklist['pstatus'];
		$row['product_to_be_shipped'] = $picklist['product_name'];
		$row['product_sku'] = $picklist['sku'];
		$row['product_qty'] = $picklist['qty'];
		$row['courier_name'] = $picklist['courier_name'];
		$row['update_at'] = $picklist['updated_time'];
		
		fputcsv($output, $row);	
		}
		
	} else {
            $redirectPath = Mage::getUrl() . "supplier/";
            $this->_redirectUrl( $redirectPath );
        }
	}
}