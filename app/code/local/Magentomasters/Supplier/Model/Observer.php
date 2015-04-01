<?php

class Magentomasters_Supplier_Model_Observer {

	public function logging($value){
		$settings = $this->settings(null);
		if ($settings['logging']== '1'){
			Mage::log($value, null, "Ultimate_Dropship.log",true);
		}
	}
	
	private function _getProcessor(){
		return Mage::getModel('supplier/processor');
	}

	public function settings($store_id){
		$supplierModel = Mage::getModel('supplier/supplier');
		$settings = $supplierModel->getSupplierSettings($store_id);
		return $settings;
	}

	public function invoice($observer){	
		$this->logging('Start Auto Invoice Dropshipment');
		$eventData = $observer->getEvent()->getData();
		$eventName = $eventData['name'];
		if ($eventName == 'sales_order_invoice_pay'){
			$invoice = $observer->getEvent()->getInvoice();
			$order = $invoice->getOrder();
			$settings = $this->settings($order->getStoreId());
			if($settings['method'] == 'invoice' || $settings['method'] == 'invoicemanual'){
				$orderEntityid = $order->getEntity_id();
				$check = Mage::getModel('supplier/dropshipitems')->getCollection()->addFieldToFilter('order_id',$orderEntityid)->count();
				// Only Continue when there is no dropshipment allready
				if(!$check){
					$this->_getProcessor()->dropship($order,"invoice",null);
				}
			}
		}
	}
	
	public function ordercreate($observer){
		$order = $observer->getEvent()->getOrder();	
		$settings = $this->settings($order->getStoreId());
		if($settings['method'] == 'ordercreate'){
			$this->logging('Start Ordercreate Dropshipment');
			$orderEntityid = $order->getEntity_id();
			$check =  Mage::getModel('supplier/dropshipitems')->getCollection()->addFieldToFilter('order_id',$orderEntityid)->count();
			// Check if order is not allready dropped
			if(!$check){
				$this->_getProcessor()->dropship($order,"ordercreate",null);	
			}
		}
	}
	
	public function ordersave($observer){
		$order = $observer->getEvent()->getOrder();	
		$settings = $this->settings($order->getStoreId());
		if($settings['method'] == 'orderstatus'){
			$this->logging('Start Orderstatus Dropshipment');
			$orderEntityid = $order->getEntity_id();
			$check =  Mage::getModel('supplier/dropshipitems')->getCollection()->addFieldToFilter('order_id',$orderEntityid)->count();
			// Check if order is not allready dropped
			if(!$check){
				$dropStatusses = Mage::getStoreConfig('supplier/suppconfig/droporderstatus');
				$dropStatusses = explode(',', $dropStatusses);
				if(in_array($order->getStatus(), $dropStatusses)){
					$this->_getProcessor()->dropship($order,"orderstatus",null);	
				}
			}
		}
	}
	
	public function manual($order_id){
		$this->logging('Start Manual Dropshipment');
		$this->logging($order_id);
		$order = Mage::getModel('sales/order')->load($order_id);
		$settings = $this->settings($order->getStoreId());
		if ($settings['method'] != 'invoice'){	
			$this->_getProcessor()->dropship($order,"manual",null);
		}
	}
	
	public function cron($order_id,$supplierList){
		$this->logging('Start Cron Dropshipment');
		$order = Mage::getModel('sales/order')->load($order_id);
		$this->_getProcessor()->dropship($order,"cron",$supplierList);
	}
	
	public function form($order_id,$supplierList){
		$this->logging('Start Form Dropshipment');
		$order = Mage::getModel('sales/order')->load($order_id);
		$this->_getProcessor()->dropship($order,"form",$supplierList);
	}

	private function freshOrder($order_id){
		$order = Mage::getModel('sales/order')->load($order_id);		
		return $order;
	}

	public function processOrder($orderId,$trigger){
		
		$this->logging('processOrder');
		
		$order = $this->freshOrder($orderId);
		
		$settings = $this->settings($order->getStoreId());
		
		################### for courier tracking code #####################
		$tbl_dtdc_awb = Mage::getSingleton('core/resource')->getTableName('dtdc_awb'); //$table1
		$tbl_delhivery_awb = Mage::getSingleton('core/resource')->getTableName('delhivery_awb'); //$table2
		$tbl_delhivery_lm_awb = Mage::getSingleton('core/resource')->getTableName('delhivery_lm_awb'); //$table3
		$tbl_ecom_awb = Mage::getSingleton('core/resource')->getTableName('ecom_awb');
		$tbl_supplier_dropship_items = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items'); //$table
		
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		
		$postcode = $this->getPostalCode($orderId);
		$courierAwb = $this->getCourierAwb($postcode['postcode'], $orderId);
		$courier = $this->checkCourier($postcode['postcode']);
		$awb = $courierAwb['awb'];
		//$courier = 2;
		if($courier !='0'){
		$updateD = "UPDATE $tbl_supplier_dropship_items SET courier_id = '$courier', awbno = '$awb' WHERE order_id = '$orderId'";
		$connect->query($updateD);
		}
		if($courier == 5){
			$dateC = date ("Y-m-d H:m:s");
			$update = "UPDATE $tbl_ecom_awb SET order_id = '$orderId', state = '1', status = 'used', created_time = '$dateC', updated_time = '$dateC' WHERE awb = '$awb'";
			$connect->query($update);
		}
		else if($courier == 4){
			$dateC = date ("Y-m-d H:m:s");
			$update1 = "UPDATE $tbl_delhivery_lm_awb SET state = '1',orderid = '$orderId',status = 'Assigned',created_time = '$dateC',update_time = '$dateC' WHERE awb = '$awb'";
			$connect->query($update1);
		
		} else if($courier == 3){
			$dateC = date ("Y-m-d H:m:s");
			$update = "UPDATE $tbl_dtdc_awb SET order_id = '$orderId', state = '1', status = 'used', created_time = '$dateC', updated_time = '$dateC' WHERE awb = '$awb'";
			$connect->query($update);
			}
		
		###################### end tracking ##############################
		// Ship Order
		if ($settings['shipping']=='1' and $courier !='0' and $awb!=''){
			
			$this->logging('shipping');
		
			if($order->canShip()){
			
				$convertor   = Mage::getModel('sales/convert_order');
				$shipment    = $convertor->toShipment($order);
	
				foreach ($order->getAllItems() as $orderItem)
				{
					if (!$orderItem->getQtyToShip())
					{
						continue;
					}
					if ($orderItem->getIsVirtual())
					{
						continue;
					}
					$item = $convertor->itemToShipmentItem($orderItem);
					$qty = $orderItem->getQtyToShip();
					$item->setQty($qty);
					$shipment->addItem($item);
				}
	
				$shipment->register();
	
				$shipment->setEmailSent($sendEmails);
	
				$shipment->getOrder()->setIsInProcess(true);
       			$transactionSave = Mage::getModel('core/resource_transaction')
				->addObject($shipment)
				->addObject($shipment->getOrder())
				->save();
				
				if ($sendEmails) $shipment->sendEmail($sendEmails, '');
				$isModified = true;
								
			}
			
			$entityid = $this->getShippingId($orderId);
			$entityidA = $entityid['entity_id'];
			$qtyA = $entityid['total_qty'];
			
			$tbl_sales_flat_shipment_track = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment_track');
			
			if($courier == 4){
				$title = 'Delhivery Lastmile';
				$carrier = 'dlastmile';	
			}
			else if($courier == 3){
				$title = 'DTDC';
				$carrier = 'dtdc';	
			}
			else if($courier == 5){
				$title = 'ECOM EXPRESS';
				$carrier = 'ecom';	
			}
			
			$insert1 = "INSERT INTO ".$tbl_sales_flat_shipment_track." (parent_id,weight,qty,order_id,track_number,description,title,carrier_code,created_at,updated_at) VALUES('$entityidA','NULL','$qtyA','$orderId','$awb','NULL','$title','$carrier','$dateC','$dateC')";
			$connect->query($insert1);
		
		}
				
		// Invoice Order Standard switched of		
		if ($settings['invoice']== '1' && $trigger=="manual"){
			$this->logging($settings['invoice']);
			$this->logging('invoice');	
			$order = $this->freshOrder($orderId);
			if($order->canInvoice()){ 
				$invoice = $order->prepareInvoice();
		
				$invoice->register();
				Mage::getModel('core/resource_transaction')
				   ->addObject($invoice)
				   ->addObject($invoice->getOrder())
				   ->save();
		
				$invoice->sendEmail(true, '');
			}
		}
		
		// Complete Order	
		if ($settings['complete']=='1'){
			
			$this->logging('complete');
			
			$order = $this->freshOrder($orderId);
		
			if (!$order->canInvoice() && ($order->getStatus() !== 'complete' && $order->getStatus() !== 'canceled' && $order->getStatus() !== 'closed'))
			{
			$order->setStatus(Mage_Sales_Model_Order::STATE_COMPLETE);
			$order->save();
			$isModified = true;
			}
		
		}
	}
	
	public function getNextId()
	{
     	$connect = Mage::getSingleton('core/resource')->getConnection('core_read');   
		$table = Mage::getSingleton('core/resource')->getTableName('core_config_data');
		
		$select = "SELECT * FROM ". $table . " WHERE path='supplier/suppconfig/number'";
		$selectresult = $connect->query($select);
		$check = $selectresult->fetch();
		$last = $check['value'];

        if ($last) {
           	$next = $last+1;
			$query = "UPDATE ". $table . " SET value=".$next." WHERE path='supplier/suppconfig/number'";		   
        } else {
			$last = "100000000";
			$next = $last+1;
			$query = "INSERT INTO ". $table . " (scope,scope_id,path,value) VALUES ('default','0','supplier/suppconfig/number','" . $next . "')";
		}
		
		$connect->query($query);
		
		$this->logging("Next Dropship Id" . $next);
		
        return $next;
    }
	
	public function getPostalCode($id){
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $tbl_order_address = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_address');
		$tbl_neta_pincode = Mage::getSingleton('core/resource')->getTableName('netakart_pincode');
		
		$query = "SELECT postcode from ".$tbl_order_address." ad INNER JOIN ".$tbl_neta_pincode." pin ON pin.pincode = ad.postcode where ad.parent_id = '$id' group by parent_id order by priority limit 1";
		$result = $connect->query( $query );
        return $result->fetch();
	}
	
	
	public function checkCourier($pin){
		
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        //$table = Mage::getSingleton('core/resource')->getTableName('delhivery_lm_pincode');
		//$table1 = Mage::getSingleton('core/resource')->getTableName('dtdc_pincode');
		$table = Mage::getSingleton('core/resource')->getTableName('netakart_pincode');
		//$sql = "SELECT count(*) as total FROM $table WHERE  pin='".$pin."'";
	 	$sql = "SELECT pincode,courier_id FROM $table WHERE  pincode='".$pin."'";
		$result = $connect->query( $sql );
		$cResult = $result->fetch();
		 if($cResult['pincode'] > 0 and $cResult['pincode']){
			 $resultR = $connect->query( $sql );
			 $cResultA = $cResult['courier_id'];
		 } else {
			 //$sql1 = "SELECT count(*) AS total FROM $table1 WHERE  pincode=$pin";
			 //$result1 = $connect->query( $sql1 );
			 $cResultA = 0;
		 }
		return $cResultA;
	}
	
	
	public function getCourierAwb($pin, $orderid){
		
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('redexpress_awb');
		$table1 = Mage::getSingleton('core/resource')->getTableName('dtdc_awb');
		
		$table2 = Mage::getSingleton('core/resource')->getTableName('delhivery_awb');
		$ecom_awb = Mage::getSingleton('core/resource')->getTableName('ecom_awb');
		$table4 = Mage::getSingleton('core/resource')->getTableName('delhivery_lm_awb');
		
		$checkCourier = $this->checkCourier($pin); 
		
		$order = new Mage_Sales_Model_Order();
		$order->load($orderid);
		
		$payment_method = $order->getPayment()->getMethodInstance()->getCode();
		if($payment_method == 'cashondelivery')
		{
			$paymentType= 'COD';	
		}
		else{
			$paymentType= 'prepaid';
		}
		if($checkCourier == 5){
			$query = "SELECT awb from ".$ecom_awb." WHERE  state = '0' and `payment_method` = '".$paymentType."' ORDER BY `id` ASC LIMIT 1";
		}
		else if($checkCourier == 4){
		
		$query = "SELECT awb from ".$table4." WHERE  state = 2 AND awb like '30%' ORDER BY RAND() LIMIT 1";
		}
		else {
			$query = "SELECT awb from ".$table1." WHERE  state = '0' and `payment_method` = '".$paymentType."' ORDER BY `id` ASC LIMIT 1";
		}
		
		$result = $connect->query( $query );
        return $result->fetch();
	}
	
	public function getCourierAvailability($pin,$orderid){
		
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('redexpress_awb');
		$table1 = Mage::getSingleton('core/resource')->getTableName('dtdc_awb');
		
		$table2 = Mage::getSingleton('core/resource')->getTableName('delhivery_awb');
		$ecom_awb = Mage::getSingleton('core/resource')->getTableName('ecom_awb');
		$table4 = Mage::getSingleton('core/resource')->getTableName('delhivery_lm_awb');
		
		$checkCourier = $this->checkCourier($pin); 
		
		$order = new Mage_Sales_Model_Order();
		$order->load($orderid);
		
		$payment_method = $order->getPayment()->getMethodInstance()->getCode();
		if($payment_method == 'cashondelivery')
		{
			$paymentType= 'COD';	
		}
		else{
			$paymentType= 'prepaid';
		}
		if($checkCourier == 5){
			$query = "SELECT awb from ".$ecom_awb." WHERE  state = '0' and `payment_method` = '".$paymentType."' ORDER BY `id` ASC LIMIT 1";
		}
		else if($checkCourier == 4){
		
		$query = "SELECT awb from ".$table4." WHERE  state = 2 AND awb like '30%' ORDER BY RAND() LIMIT 1";
		}
		else {
			$query = "SELECT awb from ".$table1." WHERE  state = '0' and `payment_method` = '".$paymentType."' ORDER BY `id` ASC LIMIT 1";
		}
		
		//$result = $connect->query( $query );
        $check = $connect->fetchOne($query);
		if($check and $check!=''){
			return true;	
		}
		else{
			return false;	
		}
	}
	
	public function getShippingId($id){
			$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        	$table = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment');
			$query = "SELECT entity_id,total_qty FROM ".$table." WHERE order_id = ".$id."";
			$result = $connect->query( $query );
        	return $result->fetch();
	}
	
	public function saveTracking($id, $awb, $carrier, $title){
		$trackNumber=$awb;
		$carrier=$carrier;
		$title=$title;
		$orderIncrementId = $id;
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
		$shipment = $order->getShipmentsCollection()->getFirstItem();
		$shipmentIncrementId = $shipment->getIncrementId();
		
		
		$shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentIncrementId);

         /* @var $shipment Mage_Sales_Model_Order_Shipment */



         $track = Mage::getModel('sales/order_shipment_track')
                     ->setNumber($trackNumber)
                     ->setCarrierCode($carrier)
                     ->setTitle($title);

         $shipment->addTrack($track);

         try {
             $shipment->save();
         } catch (Mage_Core_Exception $e) {
             $thiss->_fault('data_invalid', $e->getMessage());
         }

         return $track->getId();
	}
	
	public function saveDropshipitem($order,$supplier_id,$item,$trigger){
		
		$supplier = Mage::getModel('supplier/supplier')->load($supplier_id)->getData();
		$settings = $this->settings($order->getStoreId());
		
		$method = "0";
			
		if($supplier['email_enabled'] == 1 && $supplier['xml_enabled'] == 0){
			$method = "1";	
		} elseif ($supplier['xml_enabled'] ==1 && $supplier['email_enabled'] == 0 && $supplier['xml_ftp'] == 0){
			$method = "2";
		} elseif ($supplier['xml_enabled'] ==1 && $supplier['email_enabled'] == 0 && $supplier['xml_ftp'] == 1){
			$method = "3";
		}
		
		if($item->getParentItemId()){
			$parent = Mage::getModel('sales/order_item')->load($item->getParentItemId());
			$finalprice = $parent->getPrice() * $parent->getQtyOrdered();
			$finalcosts = $parent->getBaseCost() * $parent->getQtyOrdered();
		} else{
			$finalprice = $item->getPrice() * $item->getQtyOrdered();
			$finalcosts = $item->getBaseCost() * $item->getQtyOrdered();
		}

		$dropshipid = $this->getNextId();
		$orderEntityid = $order->getEntityId();
		$orderId = $order->getRealOrderId();
		$supplier_name = $supplier['name'];
		$supplier_id = $supplier['id'];
		$itemData = $item->getData();
		$itemId = $itemData['item_id'];	
		$productid = $item->getProductId();
		$productname = $item->getName();
		$sku = $item->getSku();	
		$payment_made = $this->getPaymentMade($sku);
		$qty = $item->getQtyOrdered();
		
		
		
		$connect = Mage::getSingleton('core/resource')->getConnection('core_read');
		
		if($supplier['schedule_enabled']==2){ $status = 2; } elseif($trigger=='cron'){ $status = 1; } else { $status=1; }
		
		$date = date ("Y-m-d H:m:s");
		$productname = mysql_escape_string($productname);
		
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		
		$insert = "INSERT INTO " . $table . " (dropship_id,order_id,order_number,order_item_id,supplier_id,courier_id,awbno,supplier_name,product_id,product_name,sku,qty,cost,price,status,method,date,payment_to_supplier)
	 	VALUES
                (                    
                    '$dropshipid',
					'$orderEntityid',
					'$orderId',
					'$itemId',
					'$supplier_id',
					'$courier',
					'$awb',
					'$supplier_name',
					'$productid',
					'$productname',
					'$sku',
					'$qty',
					'$finalcosts',
					'$finalprice',
					'$status',
					'$method',
					'$date',
					'$payment_made'
                )";	
				
			
		
		$connect->query($insert);
		
		
						
	}
	
	public function getPaymentMade($sku){
		
		$products = Mage::getResourceModel('catalog/product_collection')
						->addAttributeToFilter('sku', array('eq' => $sku))
						->addAttributeToFilter('payment_to_be_made', array('notnull' => true))
						->addAttributeToFilter('payment_to_be_made', array('neq' => ''))
						->addAttributeToSelect('payment_to_be_made');
		
		$usedAttributeValues = array_unique($products->getColumnValues('payment_to_be_made'));	
		
		//print_r($usedAttributeValues);
		
		return 	$usedAttributeValues[0];	
	}
	
	public function updateDropshipitem($order,$supplier_id,$item,$trigger){
		
		$this->logging('Update Dropship Item');
		
		$itemData = $item->getData();
		$id = $itemData['dropshipid'];	
		$date = date ("Y-m-d H:m:s");
		
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		$connect = Mage::getSingleton('core/resource')->getConnection('core_read');
		$query = "UPDATE $table SET status='1', date='$date' WHERE id='$id'";
		$this->logging($query);
		$connect->query($query);
			return true;
		
		
	}
	
	private function updateDropshipItemStatus($order_item_id,$status){
		$this->logging('Update Dropship Item Status');	
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		$connect = Mage::getSingleton('core/resource')->getConnection('core_read');
		$query = "UPDATE $table SET status='$status' WHERE order_item_id='$order_item_id'";
		$this->logging($query);
		$connect->query($query);
		
	}
	
	private function updateDropshipItemAwbno($order_item_id,$status){
		$this->logging('Update Dropship Item Status');	
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		$connect = Mage::getSingleton('core/resource')->getConnection('core_read');
		$query = "UPDATE $table SET awbno='' WHERE order_item_id='$order_item_id'";
		$this->logging($query);
		$connect->query($query);
		
	}
	
	public function updateDropshipItemComplete($order_item_id){
		$this->logging('Update Dropship Item Status');	
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		$connect = Mage::getSingleton('core/resource')->getConnection('core_read');
		$query = "UPDATE $table SET status='1' WHERE order_item_id='$order_item_id'"; 
		$this->logging($query);
		$connect->query($query);
		
	}

	public function ordercancel($observer){
		$this->logging('order cancel');	
		if (isset($observer['item'])) {
		 $item = $observer['item'];
		 $this->updateDropshipItemStatus($item->getItemId(),'6'); 
		 $this->updateDropshipItemAwbno($item->getItemId(),''); 
		 //$orderId = $item->getOrderId();
		 //$orderItemId = $item->getItemId();
		 //$this->logging($orderId);
		}	
	}
	
	public function ordercredit($observer){
		if(isset($observer['creditmemo'])) {
			 $creditmemo = $observer['creditmemo'];
			 $order = $creditmemo->getOrder();
			 $items = $creditmemo->getAllItems();
			 foreach($items as $item){
				$this->updateDropshipItemStatus($item->getOrderItemId(),'4'); 
			 	//$item->getProductId();
				//$item->getOrderItemId();
			 	$this->logging($item->getName());
			 }		
		}	
	}

	public function saveshipment($observer){
		try{
			$this->logging('save shipping');
			$shipment = $observer->getEvent()->getShipment();
			$items = $shipment->getAllItems(); 
			foreach($items as $item){
				Mage::getModel('supplier/observer')->updateDropshipItemComplete($item->getOrderItemId());
			}
		} catch(exception $e){
			$this->logging($e);
		}
	}

	
	// ADD MASS ACTION OPTION
	
	public function addDropshipoption($observer) 
    {
        $block = $observer->getEvent()->getBlock();
		if(get_class($block) =='Mage_Adminhtml_Block_Widget_Grid_Massaction'
            && $block->getRequest()->getControllerName() == 'sales_order') 
	    	{
			    $block->addItem('dropship', array(
	            'label' => 'Dropship',
	            'url' => Mage::app()->getStore()->getUrl('supplier/dropship/dropshipmass/action/dropship'),
	            ));			
	    	} 
    }
	
	// ADD DROPSHIP BUTTON
	
	public function addOrderoptions($observer) 
    {
        $block = $observer->getEvent()->getBlock();
        
		//$this->logging(get_class($block));
		
		if(get_class($block) =='Mage_Adminhtml_Block_Sales_Order_View'
            && $block->getRequest()->getControllerName() == 'sales_order') 
        {
            	
				 if (Mage::getStoreConfig('supplier/suppconfig/method') == 'manual' || Mage::getStoreConfig('supplier/suppconfig/method') == 'invoicemanual' ) {
					
					$order_id = $block->getRequest()->getParam('order_id');
					
					$settings = $this->settings(null);
					$check = Mage::getModel('supplier/dropshipitems')->getCollection()->addFieldToFilter('order_id',$order_id)->count();					
		
					if($settings['multiple']==1){ $check=''; }
					
					if(!$check){
						$block->addButton('Dropship', array(
						  'label'     => 'Dropship',
						  'url' => Mage::app()->getStore()->getUrl('supplier/dropship/dropship/'),		  
						  'onclick'   => 'setLocation(\'' . $block->getUrl('supplier/dropship/dropshipment') . '\')',
						));
					}
					
					
				 }
						
        }
    }
	
} ?>