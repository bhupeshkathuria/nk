<?php
class Clay_Salesreport_Block_Categorywise extends Clay_Courier_Block_Abstract{
	/**
     * Return month's totals
     *
     * @return mixed
     */
    public function getOrders()
    {
       	$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item');
		$table2 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_payment');
		$table3 = Mage::getSingleton('core/resource')->getTableName('catalog_category_product');
		$post = $this->getRequest()->getPost();
		
		if($post['from'] || $post['to']){
				$from = date("Y-m-d H:i:s", strtotime($post['from']));
    	    	$to = date("Y-m-d H:i:s", strtotime($post['to']));
				$fromDate = date('Y-m-d', strtotime($from));
				$toDate = date('Y-m-d', strtotime($to));
				$search = "`main_table`.`created_at` BETWEEN '".$fromDate."' AND '".$toDate."' AND ";
				
		} else if($this->getRequest()->getParam('from') && $this->getRequest()->getParam('to')) {
			
			$fromDate = date('Y-m-d H:i:s', strtotime($this->getRequest()->getParam('from')));
			$toDate = date('Y-m-d H:i:s', strtotime($this->getRequest()->getParam('to')));
			
			$search = "`main_table`.`created_at` BETWEEN '".$fromDate."' AND '".$toDate."' AND ";
		}
		
		$sql = "SELECT `main_table`.`sku`, `main_table`.`qty_ordered`,`main_table`.`product_id`,
				 count(main_table.order_id) AS `totalordered`				 
				 FROM ".$table." AS `main_table` WHERE ".$search." `main_table`.`parent_item_id` IS NULL 
				 GROUP BY `main_table`.`sku` ORDER BY totalordered DESC";
	
		$result = $connect->query( $sql );
		return $result->fetchAll();
		
    }
	
	public function getCategoryOrders($category_id)
    {
       	$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item');
		$table2 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_payment');
		$table3 = Mage::getSingleton('core/resource')->getTableName('catalog_category_product');
		$post = $this->getRequest()->getPost();
		
		if($post['from'] || $post['to']){
				$from = date("Y-m-d 00:00:00", strtotime($post['from']));
    	    	$to = date("Y-m-d 24:00:00", strtotime($post['to']));
				$fromDate = date('Y-m-d', strtotime($from));
				$toDate = date('Y-m-d', strtotime($to));
				$search = "`main_table`.`created_at` BETWEEN '".$fromDate."' AND '".$toDate."' AND ";
				
		} else if($this->getRequest()->getParam('from') && $this->getRequest()->getParam('to')) {
			
			$fromDate = date('Y-m-d H:i:s', strtotime($this->getRequest()->getParam('from')));
			$toDate = date('Y-m-d H:i:s', strtotime($this->getRequest()->getParam('to')));
			
			$search = "`main_table`.`created_at` BETWEEN '".$fromDate."' AND '".$toDate."' AND ";
		}
		
		$sql = "SELECT `main_table`.`sku`, `main_table`.`qty_ordered`,`main_table`.`product_id`,
				 count(main_table.order_id) AS `totalordered`				 
				 FROM ".$table." AS `main_table` INNER JOIN ".$table3." AS `cat_product` ON `main_table`.`product_id` = `cat_product`.`product_id` WHERE ".$search." `cat_product`.`category_id` = '".$category_id."' and `main_table`.`parent_item_id` IS NULL GROUP BY `main_table`.`sku` ORDER BY totalordered DESC";
		
		$result = $connect->query( $sql );
		return $result->fetchAll();
		
    }
	
	function getCategoryName($id,$index){
			$product = Mage::getModel('catalog/product')->load($id);
			$cats = $product->getCategoryIds();
				if($cats[3]){
					if($cats[0] == 2){
						$i = $index +1;
						$_cat = Mage::getModel('catalog/category')->load($cats[$i]);
					}
					else{
						$_cat = Mage::getModel('catalog/category')->load($cats[$index]);
					}
				} else {
					
					if($cats[0] == 2){
						$i = $index +1;
						$_cat = Mage::getModel('catalog/category')->load($cats[$i]);
					}
					else{
						$_cat = Mage::getModel('catalog/category')->load($cats[$index]);
					}
					//$_cat = Mage::getModel('catalog/category')->load($cats[$index]);
				}
		
		return $_cat->getId();
	}
	
	function getCategoryByName($id){
				$_cat = Mage::getModel('catalog/category')->load($id);
		return $_cat->getName();
	}
	
	
	
	function getCategoryByProduct($pid,$cid){
			$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
			$table = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item');
			$table2 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_payment');
			$table3 = Mage::getSingleton('core/resource')->getTableName('catalog_category_product');
			$table4 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
			$ids = "('" . implode( "','", $pid ) . "')" ;
			
			$cids = "('" . implode( "','", $cid ) . "')" ;
			
			//$sku = "('" . implode( "','", $skus ) . "')" ;
			$post = $this->getRequest()->getPost();
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
		}
			
			
			
			$sql = "SELECT  `main_table`.`sku`, `main_table`.`qty_ordered`, `cat_product`.`category_id`,
				 count(main_table.order_id) AS `week1`,
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
				/*$sql = "SELECT  `main_table`.`sku`, `main_table`.`qty_ordered`, `cat_product`.`category_id`,
				 count(main_table.order_id) AS `week1`,
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
				 FROM ".$table." AS `main_table` INNER JOIN ".$table3." AS `cat_product` ON `main_table`.`product_id` = `cat_product`.`product_id` INNER JOIN ".$table2." AS p ON p.entity_id = `main_table`.`order_id` INNER JOIN ".$table4." AS o ON `main_table`.`order_id` = `o`.`entity_id` WHERE ".$search." `cat_product`.`category_id` IN ".$cids." and `main_table`.`sku` IN ".$sku." GROUP BY `cat_product`.`category_id` ORDER BY `week1`	DESC";*/
				 
				 $result = $connect->query( $sql );
		return $result->fetchAll();	
		
	}
	
	
	function getProductByCategory($pid,$cid){
			$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
			$table = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item');
			$table2 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_payment');
			$table3 = Mage::getSingleton('core/resource')->getTableName('catalog_category_product');
			$table4 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
			$ids = "('" . implode( "','", $pid ) . "')" ;
			
			$cids = "('" . implode( "','", $cid ) . "')" ;
			$post = $this->getRequest()->getPost();
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
		}
			
			
			
			$sql = "SELECT  `main_table`.`sku`, `main_table`.`name`, `main_table`.`qty_ordered`, `cat_product`.`category_id`,
				 count(main_table.order_id) AS `week1`,
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
				 FROM ".$table." AS `main_table` INNER JOIN ".$table3." AS `cat_product` ON `main_table`.`product_id` = `cat_product`.`product_id` INNER JOIN ".$table2." AS p ON p.entity_id = `main_table`.`order_id` INNER JOIN ".$table4." AS o ON `main_table`.`order_id` = `o`.`entity_id` WHERE ".$search." `cat_product`.`category_id` IN ".$cids." and `cat_product`.`product_id` IN ".$ids." GROUP BY `main_table`.`sku` ORDER BY `week1`	DESC";
				 
				 $result = $connect->query( $sql );
		return $result->fetchAll();	
		
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
	
	public function getCustomAttributes($attributeCode, $pID){
		$products = Mage::getResourceModel('catalog/product_collection')
						->addAttributeToFilter('sku', array('eq' => $pID))
						->addAttributeToFilter($attributeCode, array('notnull' => true))
						->addAttributeToFilter($attributeCode, array('neq' => ''))
						->addAttributeToSelect($attributeCode);
		
		$usedAttributeValues = array_unique($products->getColumnValues($attributeCode));	
		
		//print_r($usedAttributeValues);
		
		return 	$usedAttributeValues[0];	
	}

}