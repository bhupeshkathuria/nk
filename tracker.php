<?php
require_once('app/Mage.php');
Mage::app();
$status = array('pending','complete');

//$orderPayment = new Mage_Sales_Model_Order();
$conn = Mage::getSingleton('core/resource')->getConnection('core_read');
$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');

$comment = 'Please find the shipping tracking code below:';
$isCustomerNotified = false;	

function getEcomTracking($track_number){
		$url = 'http://eepl.ecomexpress.in/track_me/api/mawb/?awb='.$track_number.'&order=&username=falcon&password=fa80lcom312';
		$xmlDoc = new DOMDocument();
		$xmlDoc->load($url);
		$x = $xmlDoc->documentElement;
		foreach ($x->childNodes AS $item) {
 			$result[] = $item->nodeValue;
		}
		return $result; 
	}
	
		
		//print_r($x);
$orders = Mage::getModel('sales/order')->getCollection()
			->addFieldToFilter('status', 'handed_courier')
			;
			foreach ($orders as $order) {
				$orderStatus = $order->getStatus();
				if(!in_array($orderStatus,$status)):
				$shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
				->setOrderFilter($order)
				->load();
				
				
				foreach ($shipmentCollection as $shipment){

					foreach($shipment->getAllTracks() as $tracknum)
					{
						 
						$carrier = $tracknum->getData('carrier_code');
						
						//$result = substr($tracknum->getNumber(), 0, 2);
						//if($result == 30){
							
						if($carrier == 'ecom'){
						//$model = Mage::getModel('lastmile/dlastmile')->getDtdcInfo($tracknum->getNumber());
						$model = getEcomTracking($tracknum->getNumber());
						
						if($check == 'Delivered')
						{
							//echo 'UPDATE '.$table.' SET `status` = 5 WHERE `awbno` = "'.$tracknum->getNumber().'"';
							//die;
							if($order->canInvoice()){
								
								$conn->query('UPDATE '.$table.' SET `status` = 5 WHERE `awbno` = "'.$tracknum->getNumber().'"');	
							}
							try {
								
									if(!$order->canInvoice())
									{
									Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
									}
									  
									$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
									  
									if (!$invoice->getTotalQty()) {
									Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
									}
									  $payment_method_code = $order->getPayment()->getMethodInstance()->getCode();
									if($payment_method_code == 'cashondelivery'){
										$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
									} else {
										$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);	
									}
									$invoice->register();
									$transactionSave = Mage::getModel('core/resource_transaction')
									->addObject($invoice)
									->addObject($invoice->getOrder());
									  
									$transactionSave->save();
									}
									catch (Mage_Core_Exception $e) {
										echo $e;
										
									}
							
						}
					
						if($check == 'Returned')
						{
						if($tracknum->getNumber()){
						$orderTrack = Mage::getModel('sales/order')->load($tracknum->getData('order_id'));
						
						$orderTrack->setState('returns', 'returns', $comment, $isCustomerNotified);
						$orderTrack->save();
						$conn->query('UPDATE '.$table.' SET `status` = 8 WHERE `order_number` = '.$orderTrack->getData('increment_id').'');
						}
						}
				//}
						}
					}
				}
				endif;
			}
	
?>
