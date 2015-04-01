<?php
require_once('app/Mage.php');
Mage::app();
$status = array('pending','complete');

//$orderPayment = new Mage_Sales_Model_Order();


$comment = 'Please find the shipping tracking code below:';
$isCustomerNotified = false;	

function getEcom($doc){
$comment = 'Please find the shipping tracking code below:';
$isCustomerNotified = false;
$conn = Mage::getSingleton('core/resource')->getConnection('core_read');
$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		
$url = 'http://eepl.ecomexpress.in/track_me/api/mawb/?awb='.$doc.'&username=falcon&password=fa80lcom312';

$xml = simplexml_load_file($url);
	$lactivity = "";
	foreach($xml->children() as $ecom){
		
		if($ecom->field[10]=='Delivered / Closed'){
			$conn->query('UPDATE '.$table.' SET `status` = 5 WHERE `awbno` = '.$ecom->field[0].'');	
			$orderComplete = Mage::getModel("sales/order")->loadByIncrementId($ecom->field[1]);
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
		else if($ecom->field[10]=='Returned'){
			//$conn->query('UPDATE '.$table.' SET `status` = 5 WHERE `awbno` = '.$ecom->field[0].'');	
			$orderTrack = Mage::getModel('sales/order')->loadByIncrementId($ecom->field[1]);					
			$orderTrack->setState('returns', 'returns', $comment, $isCustomerNotified);
			$orderTrack->save();
			$conn->query('UPDATE '.$table.' SET `status` = 8 WHERE `order_number` = '.$ecom->field[1].'');	
		}
	}
}



	$orders = Mage::getModel('sales/order')->getCollection()
			->addFieldToFilter('status', 'handed_courier');
			$trackNum = array();
			foreach ($orders as $order) {
				$orderStatus = $order->getStatus();
				if($orderStatus == 'handed_courier'):
				if($order->getTracksCollection()->getFirstItem()->getData('carrier_code') == 'ecom'):
				$trackNum = $order->getTracksCollection()->getFirstItem()->getData('track_number');
				getEcom($trackNum);
				endif;
				endif;
			}

//getEcom($t);
?>
