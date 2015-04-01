<?php class Clay_Salesreport_Model_Categorywise extends Mage_Core_Model_Abstract {

	public function getShipments($order)
    {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment');
		$query = "SELECT * FROM ".$table." WHERE order_id=".$order;
		$result = $connect->query( $query );
        return $result->fetchAll();
    }
	
	public function getOrderIdByShippingId($shippingid)
    {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment');
		$query = "SELECT * FROM ".$table." WHERE entity_id=".$shippingid;
		$result = $connect->query( $query );
       return $result->fetch();
	}
	
	public function getShipment($parentid)
    {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment');
		$query = "SELECT * FROM ".$table." WHERE entity_id=".$parentid;
        $result = $connect->query( $query );
        return $result->fetch();
    }
	
	public function getShipmentItems($parentid,$productIds)
    {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment_item');
		$query = "SELECT * FROM $table WHERE parent_id=$parentid AND product_id IN ($productIds)";
		$result = $connect->query($query);
        return $result->fetchAll();
    }
		
	public function getTrackings($parentid)
    {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment_track');
		$query = "SELECT * FROM ".$table." WHERE parent_id=".$parentid;
		$result = $connect->query( $query );
        return $result->fetchAll();
    }

	public function checkOrderAuth($orderId){
		$items = $this->getCartItemsBySupplier($orderId);
		foreach($items as $item){
			if($item->getProductId()){
				return true;
			} else {
				return false;
			}
		}
	}

	public function getCartItemsBySupplier($orderId){
		$order = Mage::getModel('salesreport/order')->load($orderId);	
		$items = $order->getAllItems();
		$status = array(1,5);	
		
		$collection = Mage::getModel('supplier/dropshipitems')->getCollection();
		$collection->addFieldToSelect('product_id');
		$collection->addFieldToFilter('order_id',$orderId);
		$collection->addFieldToFilter('status', array('in' => $status));
		
		$product_list = $collection->getData();
		$products = array();
			
		foreach($product_list as $product){
			$products[] = $product['product_id'];
		}
		
		foreach ($items as $itemid => $item) {				
			if(!in_array($item->getProductId(),$products)){
				unset($items[$itemid]);
			} 
		}  
		return $items;
	}
	
	
	public function getOrdersCAT()
    {
       	$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item');
		$table2 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_payment');
		$table3 = Mage::getSingleton('core/resource')->getTableName('catalog_category_product');
		/*$post = $this->getRequest()->getPost();
		
		if($post['from'] || $post['to']){
				$from = date("Y-m-d 00:00:00", strtotime($post['from']));
    	    	$to = date("Y-m-d 24:00:00", strtotime($post['to']));
				$fromDate = date('Y-m-d', strtotime($from));
				$toDate = date('Y-m-d', strtotime($to));
				$search = "`main_table`.`created_at` BETWEEN '".$fromDate."' AND '".$toDate."' AND ";
				
		} else if($this->getRequest()->getParam('from') && $this->getRequest()->getParam('to')) {
			
			$fromDate = date('Y-m-d', strtotime($this->getRequest()->getParam('from')));
			$toDate = date('Y-m-d', strtotime($this->getRequest()->getParam('to')));
			
			$search = "`main_table`.`created_at` BETWEEN '".$fromDate."' AND '".$toDate."' AND ";
		}*/
		
		$sql = "SELECT `main_table`.`sku`, `main_table`.`qty_ordered`,`main_table`.`product_id`,
				 SUM(main_table.qty_ordered) AS `totalordered`				 
				 FROM ".$table." AS `main_table` WHERE ".$search." `main_table`.`parent_item_id` IS NULL 
				 GROUP BY `main_table`.`sku` ORDER BY totalordered DESC";
		
		$result = $connect->query( $sql );
		return $result->fetchAll();
		
    }
	
	function getCategoryName($id){
			$product = Mage::getModel('catalog/product')->load($id);
			$cats = $product->getCategoryIds();
				if($cats[3]){
					
				$_cat = Mage::getModel('catalog/category')->load($cats[3]);
				
				} else {
					
					$_cat = Mage::getModel('catalog/category')->load($cats[2]);
				}
		
		return $_cat->getId();
	}
	
	function getCategoryByName($id){
				$_cat = Mage::getModel('catalog/category')->load($id);
		return $_cat->getName();
	}
	
	
	
	function getCategoryByProductAjax($pid,$cid){
			$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
			$table = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item');
			$table2 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_payment');
			$table3 = Mage::getSingleton('core/resource')->getTableName('catalog_category_product');
			$table4 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
			$ids = "('" . implode( "','", $pid ) . "')" ;
			
			$cids = "('" . implode( "','", $cid ) . "')" ;
			/*$post = $this->getRequest()->getPost();
			if($post['from'] || $post['to']){
				$from = date("Y-m-d 00:00:00", strtotime($post['from']));
    	    	$to = date("Y-m-d 24:00:00", strtotime($post['to']));
				$fromDate = date('Y-m-d', strtotime($from));
				$toDate = date('Y-m-d', strtotime($to));
				$search = "`main_table`.`created_at` BETWEEN '".$fromDate."' AND '".$toDate."' AND ";
				
		   }  else if($this->getRequest()->getParam('from') && $this->getRequest()->getParam('to')) {
			
			$fromDate = date('Y-m-d', strtotime($this->getRequest()->getParam('from')));
			$toDate = date('Y-m-d', strtotime($this->getRequest()->getParam('to')));
			
			$search = "`main_table`.`created_at` BETWEEN '".$fromDate."' AND '".$toDate."' AND ";
		}*/
			
			
			
			$sql = "SELECT  `main_table`.`sku`, `main_table`.`qty_ordered`, `cat_product`.`category_id`,
				 SUM(main_table.qty_ordered) AS `week1`,
				 SUM(IF(p.`method` = 'cashondelivery',1,0)) AS totalCOD,
				 SUM(IF(p.`method` = 'payucheckout_shared',1,0)) AS totalPrePaid,
				 SUM(CASE WHEN p.`method` = 'cashondelivery' THEN base_amount_ordered ELSE 0 END) AS CODTotal,
				 SUM(CASE WHEN p.`method` = 'payucheckout_shared' THEN base_amount_ordered ELSE 0 END) AS PrePaidTotal,
				 SUM(o.`total_qty_ordered`) AS Transactions,
				 SUM(o.`base_grand_total`) as total,
				 SUM( IF( o.`status` = 'canceled', 1, 0 ) ) AS 'Canceled',
				 SUM( IF( o.`status` = 'closed', 1, 0 ) ) AS 'Closed',
				 SUM( IF( o.`status` = 'complete', 1, 0 ) ) AS 'Complete',
				 SUM( IF( o.`status` = 'processing', 1, 0 ) ) AS 'Processing',
				 SUM( IF( o.`status` = 'returns', 1, 0 ) ) AS 'Returns',
				 SUM( IF( o.`status` = 'readypickup', 1, 0 ) ) AS 'Readyforpickup',
				 SUM( IF( o.`status` = 'pending', 1, 0 ) ) AS 'Pending',
				 SUM( IF( o.`status` = 'handed_courier ', 1, 0 ) ) AS 'HandedtoCourier',
				 SUM( IF( o.`status` = 'holded ', 1, 0 ) ) AS 'Hold'				 
				 FROM ".$table." AS `main_table` INNER JOIN ".$table3." AS `cat_product` ON `main_table`.`product_id` = `cat_product`.`product_id` INNER JOIN ".$table2." AS p ON p.entity_id = `main_table`.`order_id` INNER JOIN ".$table4." AS o ON `main_table`.`order_id` = `o`.`entity_id` WHERE ".$search." `cat_product`.`category_id` IN ".$cids." and `cat_product`.`product_id` IN ".$ids." GROUP BY `cat_product`.`category_id` ORDER BY `week1`	DESC";
				 
				 $result = $connect->query( $sql );
		return $result->fetchAll();	
		
	}
	
	function getAjaxPieChart(){
			$orders = $this->getOrdersCAT();
			
			foreach($orders as $order){
				
				$catId[] = $this->getCategoryName($order['product_id']);
				$pid[] = $order['product_id'];	
			}
			
			$catOrders = $this->getCategoryByProductAjax($pid,$catId);
			$rows = array();
			
			foreach($catOrders as $catOrder):
				$this->getCategoryByName($catOrder['category_id']);
				round($catOrder['week1'],0);
				
				$row[0] = $this->getCategoryByName($catOrder['category_id']);
				$row[1] = round($catOrder['week1'],0);;
				array_push($rows,$row);
			endforeach;
			print json_encode($rows, JSON_NUMERIC_CHECK);
	}

	

}