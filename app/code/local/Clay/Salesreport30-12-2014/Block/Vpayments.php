<?php
class Clay_Salesreport_Block_Vpayments extends Clay_Salesreport_Block_Abstract{
	/**
     * Return month's totals
     *
     * @return mixed
     */
	 
	
	 
	 public function getCustomSku($orderid){
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		$table1 = Mage::getSingleton('core/resource')->getTableName('supplier_users');		
		$sql = "SELECT d.id,s.surname FROM ".$table." AS d INNER JOIN ".$table1." AS s ON d.supplier_name = s.name WHERE d.order_number = '$orderid'";
		$result = $connect->query( $sql );
		return $result->fetch();			
	}
	 
	 public function getCustomAttributes($attributeCode, $pID){
		$status = array(0,1);
		$products = Mage::getResourceModel('catalog/product_collection')
						->addAttributeToFilter('sku', array('eq' => $pID))
						->addAttributeToFilter($attributeCode, array('notnull' => true))
						->addAttributeToFilter($attributeCode, array('neq' => ''))
						//->addAttributeToFilter('visibility', array('in'=> $status))
						->addAttributeToSelect($attributeCode);
		
		$usedAttributeValues = array_unique($products->getColumnValues($attributeCode));	
		
		//print_r($usedAttributeValues);
		
		return 	$usedAttributeValues[0];	
	}
	
	
	public function getPaymentMade($attributeCode, $vendor){
		$products = Mage::getResourceModel('catalog/product_collection')
						->addAttributeToFilter('supplier', array('eq' => $vendor))
						->addAttributeToFilter($attributeCode, array('notnull' => true))
						->addAttributeToFilter($attributeCode, array('neq' => ''))
						->addAttributeToSelect($attributeCode);
		
		$usedAttributeValues = array_unique($products->getColumnValues($attributeCode));	
		
		//print_r($usedAttributeValues);
		if($usedAttributeValues[0]!= 0 && $usedAttributeValues[0]!="")
		return 	$usedAttributeValues[0];	
	}
	
    public function getOrders()
    {
			$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		$table1 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item');
		$table2 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');

		$sql = "SELECT  items.order_id AS orderid,
		items.item_id AS itemid,
		items.created_at AS orderdate,
		items.product_type,
		GROUP_CONCAT(items.order_id) AS orderid,
		items.name AS itemname,
		items.qty_ordered AS qty_ordered,
		items.discount_amount AS discount_amount,
		items.price AS itemprice,
		count(items.order_id) AS totalOrder,
		SUM(address.price) AS dropshiprice,
		address.order_number AS order_number,
		address.supplier_payment_status AS supplier_payment_status,
		address.supplier_name AS supplier_name
FROM ".$table2." AS orders 
        JOIN ".$table1." AS items 
          ON items.order_id = orders.entity_id 
        LEFT JOIN ".$table." AS address
          ON items.item_id = address.order_item_id
WHERE address.`status` = 5 and orders.state = 'complete' GROUP BY address.supplier_name";		
    
		$result = $connect->query( $sql );
		return $result->fetchAll();
   
    }
	
	
	public function getGrandTotalBySupplier($supplierId,$order_Id){
		
		$orderIds = explode(",",$order_Id);
		$price = array();
		foreach($orderIds as $orderId){
		$order = Mage::getModel('sales/order')->load($orderId);	
		$items = $order->getAllVisibleItems();
		$status = array(5);
			
		
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
			else{
				$price[] = $item->getPrice() * $item->getQtyOrdered();
			}
		}  
		}
		return array_sum($price);
	}
	
	function getSupplierProduct($supplier){
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		$table1 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item');
		$table2 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
		
		//$sql = "SELECT product_id AS pid, qty AS pQty FROM ".$table." WHERE supplier_name = '".$supplier."' and status = '5'";
		$sql = "SELECT  items.order_id AS orderid,
		items.item_id AS itemid,
		items.created_at AS orderdate,
		items.product_type,
		items.name AS itemname,
		items.qty_ordered AS qty_ordered,
		items.sku AS itemcode,
		address.supplier_name AS supplier_name
FROM ".$table2." AS orders 
        JOIN ".$table1." AS items 
          ON items.order_id = orders.entity_id 
        LEFT JOIN ".$table." AS address
          ON items.item_id = address.order_item_id
WHERE address.`status` = 5 and supplier_name = '".$supplier."'";
		 $result = $connect->query( $sql );
		return $result->fetchAll();
	}
	
	
	public function getSupplierOrders($supplier)
    {
			$post = $this->getRequest()->getPost();
			if($post['from'] || $post['to']){
				$from = date("Y-m-d 00:00:00", strtotime($post['from']));
    	    	$to = date("Y-m-d 00:00:00", strtotime($post['to']));	
			}
		$status = array(5);
		$collection = Mage::getModel('supplier/dropshipitems')->getCollection()->addFieldToSelect('product_id');
		$collection->addFieldToFilter('supplier_id', array('eq'=>$supplier));
		if($from && $to)
		$collection->addFieldToFilter('date', array('from'=>$from, 'to'=>$to));
	 	$collection->getSelect()->distinct(false);
		//$collection->addAttributeToSort('order_id', 'DESC');
		$order_list = $collection->getData();
        return $order_list;
   
    }
	
	
	
	
	public function getOrdersByMonth()
    {
       //	$fromDate = date('Y-m-d',strtotime("-0 days"));
		//$toDate = date('Y-m-d',strtotime("-0 days"));
		
			$post = $this->getRequest()->getPost();
			if($post['from'] || $post['to']){
				$from = date("Y-m-d 00:00:00", strtotime($post['from']));
    	    	$to = date("Y-m-d 00:00:00", strtotime($post['to']));	
			}
			else{
				$from = $this->getStartMonth();
    	    	$to = $this->getCurrentDate();
			}

		$status = array(1,5);
		$collection1 = Mage::getModel('supplier/dropshipitems')->getCollection()->addFieldToSelect('order_id');
		//$collection1->getSelect()->group('order_id');
	 	$collection1->getSelect()->distinct(true);
		$order_list1 = $collection1->getData();
        //$limit = $this->getCurrentLimit();
        //$filter = $this->getRequest()->getParam('status');
       

        $orders1 = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                //->addAttributeToFilter('entity_id', array('in' => $order_list1))
				 ->addAttributeToFilter('created_at', array('from'=>$from, 'to'=>$to))
				->addAttributeToSort('entity_id', 'DESC');
               
       
        return $orders1;
   
    }
	
	public function getBestsellerProducts()
    {
        $storeId = (int) Mage::app()->getStore()->getId();
 
        // Date
        $date = new Zend_Date();
       	$toDate = $date->setDay(1)->getDate()->get('Y-MM-dd');
        $fromDate = $date->subMonth(1)->getDate()->get('Y-MM-dd');
 
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addStoreFilter()
            ->addPriceData()
            ->addTaxPercents()
            ->addUrlRewrite()
            ->setPageSize(6);
 
        $collection->getSelect()
            ->joinLeft(
                array('aggregation' => $collection->getResource()->getTable('sales/bestsellers_aggregated_monthly')),
                "e.entity_id = aggregation.product_id AND aggregation.store_id={$storeId} AND aggregation.period BETWEEN '{$fromDate}' AND '{$toDate}'",
                array('SUM(aggregation.qty_ordered) AS sold_quantity')
            )
            ->group('e.entity_id')
            ->order(array('sold_quantity DESC'));
 
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
 
        return $collection;
    }
	
 
    
	
	public function getCurrentDate()
    {
        $date = date('Y-m-d 24:00:00');
        return (string)$date;
    }
 
    /**
     * Return first day for current date
     *
     * @return string
     */
	 
	 
	 
    public function getStartMonth()
    {
        $startCurrentMonth = date('Y').'-'.date('m').'-01  00:00:00';
        return (string)$startCurrentMonth;
    }
    /**
     * Return current date
     *
     * @return string
     */
     
/**
     * Return current day
     *
     * @return string
     */
  public function getPageUrl($page=1){
        $limit = $this->getCurrentLimit();
        $currentUrl = $this->getCurrentUrl();
        return $this->getBaseUrl() . "supplier/order/index" . '/limit/' . $limit . '/p/' . $page;
    }


    public function getCurrentUrl(){
        $urlRequest = Mage::app()->getFrontController()->getRequest();
        $urlPart = $urlRequest->getServer('ORIG_PATH_INFO');
        if(is_null($urlPart))
        {
            $urlPart = $urlRequest->getServer('PATH_INFO');
        }
        $urlPart = substr($urlPart, 1 );
        
        return $this->getUrl($urlPart);

    }


    public function getAvailableOrderLimit() {
        $mode = 'list';
        $perPageConfigKey = 'catalog/frontend/' . $mode . '_per_page_values';
        $perPageValues = (string)Mage::getStoreConfig($perPageConfigKey);
        $perPageValues = explode(',', $perPageValues);
        $perPageValues = array_combine($perPageValues, $perPageValues);
        if (Mage::getStoreConfigFlag('catalog/frontend/list_allow_all')) {
            return ($perPageValues + array('all'=>$this->__('All')));
        } else {
            return $perPageValues;
        }
    }


    public function getCurrentLimit(){
        $limit = $this->getRequest()->getParam('limit');
        if (!isset ($limit)) { $limit = $this->getDefaultLimit();}
        return $limit;
    }


    public function getDefaultLimit(){
        $defaultLimit = Mage::getStoreConfig('catalog/frontend/list_per_page');
        return $defaultLimit;
    }

    
    public function getCurrentPage(){
        $defaultPage = 1;
        $page = $this->getRequest()->getParam('p');
        
        if (!isset ($page)) { $page = $defaultPage;}
        return $page;
    }

    public function getFilterValues(){
        return array(
            "" => "",
            "Shipped" => "Shipped",
            "Waiting" => "Waiting"
        );
    }

    public function getCurrentFilter(){
        return Mage::registry("supplier_order_filter");
    }
	
	public function getSuppliear($name){
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table1 = Mage::getSingleton('core/resource')->getTableName('supplier_users');		
		$sql = "SELECT surname FROM ".$table1." where name = '$name'";
		$result = $connect->query( $sql );
		return $result->fetch();
    }
	
	public function getTotalPrice($name){
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table1 = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');		
		$sql = "SELECT sum(`price`) as grandtotal, sum(`qty`) as totalQty FROM ".$table1." where supplier_name = '$name' and status = 5";
		$result = $connect->query( $sql );
		return $result->fetch();
    }
	
	
	public function getTotalQty(){
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table1 = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');		
		$sql = "SELECT sum(`price`) as grandtotal, sum(`qty`) as totalQty FROM ".$table1." where status = 5";
		$result = $connect->query( $sql );
		return $result->fetch();
    }
	
	
	public function getOrdersBySupplier(){
		$orders = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('entity_id')
				->addFieldToFilter('status', 'complete')
				->addAttributeToSort('entity_id', 'DESC');
		$order = $orders->getData();		
		return $order;	
	}
	

}