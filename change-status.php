<?php
require_once('app/Mage.php');
Mage::app();
$status = array('pending','complete');
$orderPayment = new Mage_Sales_Model_Order();
$conn = Mage::getSingleton('core/resource')->getConnection('core_read');
$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');

$comment = 'Please find the shipping tracking code below:';
$isCustomerNotified = false;
$method = '';
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
						if($carrier == 'dlastmile'){
						$id = $order->getId();
						
						$model = Mage::getModel('lastmile/dlastmile')->getTrackingInfo($tracknum->getNumber());
						
						if(strip_tags($model->getData('status')) == 'Delivered')
						{
							$payment_method_code = $order->getPayment()->getMethodInstance()->getCode();

									if($payment_method_code == 'cashondelivery'){
										$method = CAPTURE_OFFLINE;
									} else {
										$method = CAPTURE_ONLINE;	
									}
							
							if($order->canInvoice()){
								
								$conn->query('UPDATE '.$table.' SET `status` = 5 WHERE `awbno` = '.$model->getData('tracking').'');	
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
									  
									$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::$method);
									//Or you can use
									//$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
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
						}
						if(strip_tags($model->getData('status')) == 'Returned')
						{
						if($tracknum->getNumber() == $model->getData('tracking')){
						$orderTrack = Mage::getModel('sales/order')->load($tracknum->getData('order_id'));
						
						$orderTrack->setState('returns', 'returns', $comment, $isCustomerNotified);
						$orderTrack->save();
						$conn->query('UPDATE '.$table.' SET `status` = 8 WHERE `order_number` = '.$orderTrack->getData('increment_id').'');
						}
						}
				//}
					}
				}
				endif;
			}
			
?>