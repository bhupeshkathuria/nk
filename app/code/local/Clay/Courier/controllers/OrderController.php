<?php
class Clay_Courier_OrderController extends Mage_Core_Controller_Front_Action {
    	
	public function manifestAction(){
		$session = Mage::getSingleton('core/session'); 
        $courierId = $session->getData('courierId');
		if($courierId && $courierId != "logout") {
           //$file = 'manifest_'.date("Ymd_His").'.pdf';
           //$pdf = Mage::getModel('supplier/output')->getManifestPdf($post['aOrderid'],$supplierId,$items);		    
		   
		   $this->loadLayout();
           $this->renderLayout();
		   //print_r($settings);
		  // die;
		  // $this->_prepareDownloadResponse($file,$pdf,'application/pdf');
        } else {
            $redirectPath = Mage::getUrl() . "courier/";
            $this->_redirectUrl( $redirectPath );
        }	
	}	
		
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
        $courierId = $session->getData('courierId');
        $orderId = $this->getRequest()->getParam('orderid');
        if( $courierId && $courierId != "logout") {
            $this->loadLayout();
            $this->renderLayout();
        } else {
           $redirectPath = Mage::getUrl() . "courier/";
            $this->_redirectUrl( $redirectPath );
			
        }
    }


    public function viewAction() {
        $session = Mage::getSingleton('core/session');
        $courierId = $session->getData('courierId');
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        Mage::register('sales_order', $order);
        Mage::register('order', $order);
        if( $courierId && $courierId != "logout" && $orderId) {
        	$check = Mage::getModel('supplier/order')->checkOrderAuth($courierId,$orderId); 
            if(!$check){
            	$this->_redirectUrl(Mage::getUrl() . "courier/order"); 
			} else {
            	$this->loadLayout()->renderLayout();
			}
		} else {
            $redirectPath = Mage::getUrl() . "courier/";
            $this->_redirectUrl( $redirectPath );
        }
        if( $this->getRequest()->getParam( 'error' ) ){
            Mage::getSingleton('core/session')->addError($this->__('The username or password you entered is incorrect'));
        }
    }

	public function historyAction(){
		$session = Mage::getSingleton('core/session');
        $courierId = $session->getData('courierId');
        $orderId = $this->getRequest()->getParam('orderid');
        if( $courierId && $courierId != "logout") {
        	$this->loadLayout();
            $this->renderLayout();
        } else {
            $redirectPath = Mage::getUrl() . "courier/";
            $this->_redirectUrl( $redirectPath );
        }
	}

    public function addcommentAction(){
        $session = Mage::getSingleton('core/session');
        $courierId = $session->getData('courierId');
        $orderId = $this->getRequest()->getParam('orderid');
        if( $courierId && $courierId != "logout") {	
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
            $redirectPath = Mage::getUrl() . "courier/";
            $this->_redirectUrl( $redirectPath );
        }
	}
	
	public function printAction(){
		$session = Mage::getSingleton('core/session');
        $courierId = $session->getData('courierId');
        $orderId = $this->getRequest()->getParam('order_id');
		$items = Mage::getModel('courier/order')->getCartItemsBySupplier($courierId,$orderId);
 
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
	
	
	public function csvAction(){
		
		$session = Mage::getSingleton('core/session');
        $courierId = $session->getData('courierId'); 
		$cstatus = 4;
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		$table2 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
		$sql = "SELECT d.dropship_id, d.order_id,d.order_number,order_item_id,d.supplier_id,d.courier_id,d.awbno,d.product_id,d.product_name,d.sku,d.qty,d.cost, d.price,d.status as dstatus,d.method,d.date,s.status from ".$table." AS d INNER JOIN ".$table2." s ON d.order_id = s.entity_id WHERE d.courier_id = ".$courierId." and d.status = '".$cstatus."'";
		$result = $connect->query( $sql );
		$orders = $result->fetchAll();
			$contents .= "Sr. no,";
			$contents .= "Order Date,";
			$contents .= "Order ID,";
			$contents .= "SKUCode,";
			$contents .= "Product Details,";
			$contents .= "Qty,";
			$contents .= "Supplier Details,";
			$contents .= "Customer Name,";
			$contents .= "Customer Phone,";
			//$contents .= "Customer Address,";
			$contents .= "Customer City,";
			$contents .= "Customer State,";
			$contents .= "Customer Country,";
			$contents .= "Payment Mode,";
			$contents .= "AwbNo,";
			$contents .= "Logistic Partner,";
			$contents .= "Supplier,";
			$contents .= "Total Amt,";
			$contents .="\n"; 
			
			// Get Records from the table
			$i=1;
			foreach($orders as $row){
				
				$order1 = Mage::getModel("sales/order")->load($row['order_id']); //load order by order id 
				$billing_address = $order1->getBillingAddress(); 
				
				$orderPayment = new Mage_Sales_Model_Order();
				$orderPayment->loadByIncrementId($row['order_number']);
				$payment_method = $orderPayment->getPayment()->getMethodInstance()->getTitle();
				
				$remove = array(',',';',"'",'/',':','-',',','.'); 
				$supplierDetails = $this->getSupplierDetails($row['supplier_id']);
				
				$supplier = str_replace($remove,'',$supplierDetails['address1']);
				//$model = Mage::getModel('lastmile/dlastmile')->getTrackingInfo($row['awbno']);
				$contents.= $i.",";
				$contents.= Mage::helper('core')->formatDate($row['date'], 'short', $showTime=true).",";
				$contents.= $row['order_number'].",";
				$contents.= $row['sku'].","; 
				$contents.= $row['product_name'].",";
				$contents.= $row['qty'].","; 
				$contents.= $supplier.","; 
				$contents.= str_replace('  Â ','',$billing_address->getName()).",";
				$contents.= $billing_address->getTelephone().",";
				//$contents.= str_replace($remove,'',$billing_address->getData('street')).",";
				$contents.= $billing_address->getData('city').",";
				$contents.= $billing_address->getData('state').",";
				$contents.= $billing_address->getData('country').",";
				$contents.= $payment_method.","; 
				$contents.= $row['awbno'].","; 
				$contents.= "Delhivery,"; 
				$contents.= $supplierDetails['company'].",";
				$contents.= $row['price']."\n";
				$i++;
			}
			/*foreach($model as $m){
					if($m['status']):
					echo $m['status'];	
					endif;
				}
				die;	*/
			// remove html and php tags etc.
			$contents = strip_tags($contents);
			//header to make force download the file
			header("Content-Type: application/force-download");
			header("Content-Type: application/octet-stream");
			header("Content-Type: application/download");
			// disposition / encoding on response body
			header("Content-Disposition: attachment;filename=ProductsReport".date('d-m-Y').".csv");
			header("Content-Transfer-Encoding: binary");
			print $contents; 
		
		
	}
	
	
	public function getSupplierDetails($sid){
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_users');		
		$sql = "SELECT * FROM ".$table." WHERE id = ".$sid."";
		$result = $connect->query( $sql );
		return $result->fetch();			
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
	
	public function customstatusAction(){
		$post = $this->getRequest()->getPost();
		$cstatus = $post['chkStatus'];	
		$aststus = $post['chkAction'];
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
				$redirectPath = Mage::getUrl() . "courier/order";
				$this->_redirectUrl( $redirectPath );
	}
	
	public function changeOrderStatusAction(){
		
			$orders = Mage::getModel('sales/order')->getCollection()
			->addFieldToFilter('status', 'processing')
			;
			foreach ($orders as $order) {
				$email = $order->getStatus();
				echo $email . "\n";
			}
		die;
	}
	
	public function picklistAction(){
		
		$session = Mage::getSingleton('core/session');
        $courierId = $session->getData('courierId');
		if($courierId && $courierId != "logout") {
        
		$post = $this->getRequest()->getPost();
		$from = $post['from'];
		$to = $post['to'];
		
		$status = explode(",",$post['status']);
		$picklists = Mage::getModel("courier/order")->picklistOrders($courierId,$from,$to,$status);
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=picklist.csv');
		$output = fopen('php://output', 'w');
		fputcsv($output, array('Waybill','Order No','Order Date','Consignee Name', 'City', 'State', 'Country', 'Address', 'Pincode', 'Phone', 'Mobile', 'Weight', 'Payment Mode', 'Package Amount', 'COD Amount', 'Status', 'Product to be Shipped', 'Shipping Client', 'Length', 'Breadth', 'Height', 'Vendor\'s Name', 'Vendor\'s Address' , 'Vendor\'s Pincode', 'Updated At'));
	
		foreach($picklists as $picklist){
		
		$row= array();	
		
		$order = Mage::getModel('sales/order')->loadByIncrementId($picklist['order_number']);
		
		if(!is_object($order->getPayment())){
			continue;
		}

		$paymentMethod = $order->getPayment()->getMethodInstance()->getTitle();
		$customer = $order->getBillingAddress();
		//$paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
		
		
		$items = Mage::getModel('sales/order')->loadByIncrementId($picklist['order_number'])->getAllItems();
		
		foreach($items as $item){
			if($item->getSku() != $picklist['sku']){
				continue;
			}
			else{
				$weight = $item->getWeight();
				break;	
			}
		}
		
		//print_r($customer->getData());
		
		$row['waybill'] = $picklist['awb'];
		$row['order_no'] = $picklist['order_number'];
		$row['order_id'] = $picklist['created_time'];
		$row['consignee_name'] = $customer->getData('firstname');
        $row['city'] = $customer->getData('city');
        $row['state'] = $customer->getData('region');
		$row['country'] = $customer->getData('country_id');
		$row['address'] = $customer->getStreet(1).' '.$customer->getStreet(2);
		$row['pincode'] = $customer->getData('postcode');
		$row['phone'] = $customer->getData('telephone');
		$row['mobile'] = $customer->getData('telephone');
		$row['weight'] = $weight;
		$row['payment_mode'] = $paymentMethod;
		$row['package_amount'] = $picklist['price'];
		$row['cod_amount'] = $picklist['orderprice'];
		$row['status'] = $picklist['pstatus'];
		$row['product_to_be_shipped'] = $picklist['product_name'];
		$row['shipping_client'] = 'Falcon business resources';
		$row['length'] = '0';
		$row['breadth'] = '0';
		$row['height'] = '0';
		$row['vendor_name'] = $picklist['vendor_name'];
		$row['Vendor_address'] = $picklist['address'];
		$row['Vendor_pincode'] = $picklist['vpincode'];
		$row['update_at'] = $picklist['updated_time'];
		fputcsv($output, $row);	
		}
		
	} else {
            $redirectPath = Mage::getUrl() . "courier/";
            $this->_redirectUrl( $redirectPath );
        }
	}

}