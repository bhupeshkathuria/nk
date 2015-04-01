<?php

class Lema21_CustomExport_Service_GenerateCSV {

    private $_orderIds;
    private $_collectionOrders;
    private $_contentCSV;

    public function __construct($ordersId) {
        $this->_orderIds = $ordersId;
    }

    private function _loadOrderObjects()
    {
        $this->_collectionOrders = array();

        foreach($this->_orderIds as $id) {
            $instance = Mage::getModel("sales/order")->load($id);
            array_push($this->_collectionOrders, $instance);
        }
    }

	public function getCustomSku($orderid){
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		$table1 = Mage::getSingleton('core/resource')->getTableName('supplier_users');		
		$sql = "SELECT d.id,s.surname FROM ".$table." AS d INNER JOIN ".$table1." AS s ON d.supplier_name = s.name WHERE d.order_number = '$orderid'";
		$result = $connect->query( $sql );
		return $result->fetch();			
	}
	

    private function _prepareData($templateLine)
    {
			$this->_contentCSV = "";
        	
			$lineItem1 .= "Order ID,";
			$lineItem1 .= "Order Date,";
			$lineItem1 .= "Status,";
			$lineItem1 .= "SKUCode,";
			$lineItem1 .= "Product Details,";
			$lineItem1 .= "Qty,";
			$lineItem1 .= "Category,";
			$lineItem1 .= "Payment Method,";
			$lineItem1 .= "Customer Name,";
			$lineItem1 .= "Pincode,";
			$lineItem1 .= "City,";
			$lineItem1 .= "G.T Purchase (Transfer Price),";
			$lineItem1 .= "Sale,";
			
			$lineItem1 .= "Gross Margin,";
			$lineItem1 .= "Delivered Date,";
			$lineItem1 .= "Vendor Details,";
			$lineItem1 .= "Payment to Venndor,";
			$lineItem1 .= "Payment Due Date,";
			
		
		$this->_contentCSV .=$lineItem1 ."\n";
		

        //iterate on the orders selected
        foreach($this->_collectionOrders as $order) {
			
			
			//print_r($order);
			//die;
            $lineItem = "";
            // iterate on the itens in template
			$order1 = Mage::getModel("sales/order")->loadByIncrementId($order->getData('increment_id')); 
			$ordered_items = $order1->getAllItems();
            foreach($templateLine as $t) {
                // order.increment_id => $order->getData("increment_id");
                // getAttributeByCode($attribute, $order)
                $item = "";
                list($object, $attribute) = explode(".", $t);

                switch($object) {

                    case "order":
                        $item = $order->getData($attribute);
                    break;
					case "product":
                        foreach($ordered_items as $item1){     //item detail
						$item = $item1->getSku(); 
						}
                    break;
					
					case "product1":
                        foreach($ordered_items as $item2){     //item detail
						$item = $item2->getName(); 
						}
                    break;
					
					case "product2":
                        foreach($ordered_items as $item3){     //item detail
						$item = $item3->getQtyOrdered(); 
						}
                    break;
					
					case "product3":
                        foreach($ordered_items as $item4){     //item detail
							
							$product = Mage::getModel('catalog/product')->load($item4->getItemId());
							$categoryIds = $product->getCategoryIds();
							$categoryName = '';
						if (isset($categoryIds[2])){
							$category = Mage::getModel('catalog/category')->setStoreId(Mage::app()->getStore()->getId())->load($categoryIds[2]);
							$item = $categoryName['category'] = $category->getName();
						}
							
						}
                    break;
					
					case "product4":
                        foreach($ordered_items as $item5){     //item detail
							
							//$product1 = Mage::getModel('catalog/product')->load($item5->getItemId());
							//$item = $product1->getResource()->getAttribute('payment_to_be_made');
							
							 $_product = Mage::getModel("catalog/product")->load($item5->getItemId());
    						 $item = $_product->getPaymentToBeMade();
														
						}
                    break;
					
					case "product5":
                       
							if ($order->hasInvoices()) {
								$item = ($order->getData('total_paid'))* ($order->getData('total_qty_ordered'));
							} else {
								$item = ($order->getData('total_due'))* ($order->getData('total_qty_ordered'));
							}
						
                    break;
					
					case "product6":
                       
							$item = '';
						
                    break;
					
					case "product7":
                       
							if ($order->hasInvoices()) {
								$invIncrementIDs = array();
								foreach ($order->getInvoiceCollection() as $inv) {
									$item = $inv->getCreatedAt();
								}
							}

						
                    break;
					
					
					case "product8":
                       
							$sku = $this->getCustomSku($order->getData('increment_id'));
							$item = $sku['surname'];
							
													
                    break;
					
					case "product9":
							$item ='';
                    break;
					
					case "product10":
							if ($order->hasInvoices()) {
								$invIncrementIDs = array();
								foreach ($order->getInvoiceCollection() as $inv) {
									$date = $inv->getCreatedAt();
									$dateDue = date_create($date);
									date_add($dateDue,date_interval_create_from_date_string("15 days"));
									
									$item = date_format($dateDue,"Y-m-d");
								}
							}
                    break;
					
                    case "customer":
							$au = $order->getData('increment_id');
							$order = Mage::getModel("sales/order")->loadByIncrementId($au); //load order by order id
							$billing_address = $order->getBillingAddress();
                            $item = trim($billing_address->getName());
                    break;
					
					case "zipcode":
							$au = $order->getData('increment_id');
							$order = Mage::getModel("sales/order")->loadByIncrementId($au); //load order by order id
							$billing_address = $order->getBillingAddress();
                            $item = trim($billing_address->getPostcode());
                    break;
					
					case "city":
							$au = $order->getData('increment_id');
							$order = Mage::getModel("sales/order")->loadByIncrementId($au); //load order by order id
							$billing_address = $order->getBillingAddress();
                            $item = trim($billing_address->getCity());
                    break;
					 case "payment":
							$au = $order->getData('increment_id');
							$order = new Mage_Sales_Model_Order();
							$order->loadByIncrementId($au);
							
							$item = $order->getPayment()->getMethodInstance()->getTitle();
							
					 break;	
                }

                $lineItem.="{$item},";
            }

            // endline
            $this->_contentCSV .=$lineItem ."\n";
        }
    }
    
    public function call()
    {
        $this->_loadOrderObjects();
        
        $templateLine = Mage::helper("custom_export")->loadTemplate();

        $this->_prepareData($templateLine);

        return $this->_contentCSV;
    }

}