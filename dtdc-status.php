<?php
require_once('app/Mage.php');
Mage::app();
$status = array('pending','complete');

//$orderPayment = new Mage_Sales_Model_Order();


function getDtdc($trackNum,$refno){
	$conn = Mage::getSingleton('core/resource')->getConnection('core_read');
	$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
	$comment = 'Please find the shipping tracking code below:';
	$isCustomerNotified = false;	
	$url = 'http://webxpress.cloudapp.net/DMS_DOTZOT/services/cust_ws_ver2.asmx/ConsignmentTrackEvents_Details_New?userName=instauser&password=insta2013&clientId=INSTACOM&DOCNO='.$trackNum.'';
	$dt = array();
	$dtR = array();
	$xml = simplexml_load_file($url);
	foreach($xml->children() as $dtdc){
		//print_r($dtdc);
		if($dtdc->TRACKING_CODE == 'D'){
			$dt[] = $dtdc[0];
		}
		elseif($dtdc->TRACKING_CODE == 'R'){
			$dtR[] = $dtdc[0];
		}
		
					
	}
	
	foreach($dt as $d){
		if($d->TRACKING_CODE == 'D'){
			echo $refno.'---'.$d->DOCKNO.'---'.$d->TRACKING_CODE."<br>";
			$conn->query('UPDATE '.$table.' SET `status` = 5 WHERE `awbno` = "'.$d->DOCKNO.'"');	
			$orderComplete = Mage::getModel("sales/order")->loadByIncrementId($refno);
			try {
					if(!$orderComplete->canInvoice())
					{
					Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
					}
					 
					$invoice = Mage::getModel('sales/service_order', $orderComplete)->prepareInvoice();
					 
					if (!$invoice->getTotalQty()) {
					Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
					}
					 
					$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
					$invoice->register();
					$transactionSave = Mage::getModel('core/resource_transaction')
					->addObject($invoice)
					->addObject($invoice->getOrder());
					 
					$transactionSave->save();
					}
					catch (Mage_Core_Exception $e) {
					 
					}
		}
		
	}
	
	foreach($dtR as $dr){
		
	if($dr->TRACKING_CODE == 'R'){
			echo $refno.'---'.$dr->DOCKNO.'---'.$dr->TRACKING_CODE."<br>";
			$orderTrack = Mage::getModel('sales/order')->loadByIncrementId($refno);					
			$orderTrack->setState('returns', 'returns', $comment, $isCustomerNotified);
			$orderTrack->save();
			$conn->query('UPDATE '.$table.' SET `status` = 8 WHERE `order_number` = '.$refno.'');	
		}
	}
}

//echo getDtdc($trackNum,$refno);
	
			$orders = Mage::getModel('sales/order')->getCollection()
			->addFieldToFilter('status', 'handed_courier');
			foreach ($orders as $order) {
				$orderStatus = $order->getStatus();
				if($orderStatus == 'handed_courier'):
					if($order->getTracksCollection()->getFirstItem()->getData('carrier_code') == 'dtdc'):
						$trackNum = $order->getTracksCollection()->getFirstItem()->getData('track_number');	
						getDtdc($trackNum,$order->getData('increment_id'));
					endif;
				endif;
			}


?>
