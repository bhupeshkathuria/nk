<?php class Magentomasters_Supplier_Model_Order extends Mage_Core_Model_Abstract {

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
	public function loadawb($awb,$cTableField){
		
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName($cTableField['table']);
		$table2 = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		$query = "SELECT c.state as state,c.awb as awb,s.order_id as orderid FROM $table c INNER JOIN $table2 s ON c.awb = s.awbno WHERE c.".$cTableField['field']." = '$awb'";
		
		$result = $connect->query($query);
        return $result->fetch();
	
	}
	public function getCourierTableName($id){
		$tableField = array();
		if($id == '2' || $id == '4'){
			$tableField['field'] = 'lastmile_id';
			$tableField['table'] = 'delhivery_lm_awb';
			
		}
		else if($id == '3'){
			$tableField['field'] = 'id';
			$tableField['table'] = 'dtdc_awb';
			
		}
		else if($id == '5'){
			$tableField['field'] = 'id';
			$tableField['table'] = 'ecom_awb';
			
		}
		return $tableField;
	}	
	public function getTrackings($parentid)
    {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('sales_flat_shipment_track');
		$query = "SELECT * FROM ".$table." WHERE parent_id=".$parentid;
		$result = $connect->query( $query );
        return $result->fetchAll();
    }

	public function checkOrderAuth($supplierId,$orderId){
		$items = $this->getCartItemsBySupplier($supplierId,$orderId);
		
		foreach($items as $item){
			if($item->getProductId()){
				return true;
			} else {
				return false;
			}
		}
	}

	public function getCartItemsBySupplier($supplierId,$orderId){
		$order = Mage::getModel('sales/order')->load($orderId);	
		$items = $order->getAllItems();
		$status = array(1,2,3,4,5,6,7,8);	
		
		$collection = Mage::getModel('supplier/dropshipitems')->getCollection();
		$collection->addFieldToSelect('product_id');
		$collection->addFieldToFilter('supplier_id',$supplierId);
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
	
	public function getCourierName($id){
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('courier_user');		
		$sql = "SELECT surname FROM ".$table." WHERE id = ".$id."";
		//$result = $connect->query( $sql );
		return $connect->fetchOne($sql);
	}
	
	
	public function getCartItemsBySupplierInvoice($supplierId,$orderId){
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);	
		$items = $order->getAllItems();
		$status = array(1,2,3,4,5,6,7,8);	
		
		$collection = Mage::getModel('supplier/dropshipitems')->getCollection();
		$collection->addFieldToSelect('product_id');
		$collection->addFieldToFilter('supplier_id',$supplierId);
		$collection->addFieldToFilter('order_number',$orderId);
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
		//print_r($items);
		return $items;
	}
	
	public function getconfigPrice($item_id){
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item');
		$sql = "SELECT p.base_row_total as price FROM ".$table." p INNER JOIN ".$table." c on p.item_id = c.parent_item_id WHERE c.item_id = '$item_id'";
		
		$result = $connect->query( $sql );
		return $result->fetchAll();		 
	}
	
	public function picklistOrders($supplierId, $status){
		$qryFrom = '';
		$qryTo = '';
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		$table2 = Mage::getSingleton('core/resource')->getTableName('courier_user');
		$table3 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');	
		
		$query = "SELECT SUM(sdi.price) as orderprice,GROUP_CONCAT(sdi.sku SEPARATOR ', ') AS sku ,SUM(sdi.qty) as qty, sdi.order_number as order_number, sdi.awbno as awb, sfo.status as pstatus, sdi.product_name as product_name, sdi.price as price, cu.surname as courier_name,sfo.updated_at as updated_time,sfo.created_at as created_time FROM ".$table." sdi INNER JOIN ".$table2." cu ON sdi.courier_id = cu.id INNER JOIN ".$table3." sfo ON sdi.order_number = sfo.increment_id WHERE supplier_id=".$supplierId." and sdi.status IN (".implode(",",$status).") GROUP BY order_number ORDER BY order_number DESC";
		
		//$query = "SELECT SUM(sdi.price) as orderprice,SUM(sdi.qty) as orderqty,sdi.order_number as order_number, sdi.awbno as awb, sdi.product_name as product_name, sdi.price as price, sdi.sku as sku, su.surname as vendor_name,su.address1 as address FROM ".$table." sdi INNER JOIN ".$table2." su ON sdi.supplier_id = su.id WHERE courier_id=".$courierId." and status = 3 GROUP BY order_number ORDER BY order_number DESC ";
	
		
	
		$result = $connect->query( $query );
       return $result->fetchAll();
	}
	
	public function getCartItemsBySupplierManifest($supplierId,$orderIds){
		$allItems =array();
		foreach($orderIds as $orderId){
			$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
			$items = $order->getAllItems();
			$status = array(1,2,3,4,5,6,7,8);	
			$collection = Mage::getModel('supplier/dropshipitems')->getCollection();
			$collection->addFieldToSelect('product_id');
			$collection->addFieldToFilter('supplier_id',$supplierId);
			$collection->addFieldToFilter('order_number',$orderId);
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
			$allItems[$orderId]=$items;
 
		}
		
		return $allItems;
	}

}