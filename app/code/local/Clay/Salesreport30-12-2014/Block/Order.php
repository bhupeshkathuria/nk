<?php
class Clay_Salesreport_Block_Order extends Clay_Courier_Block_Abstract{
	/**
     * Return month's totals
     *
     * @return mixed
     */
    public function getOrders()
    {
       //	$fromDate = date('Y-m-d',strtotime("-0 days"));
		//$toDate = date('Y-m-d',strtotime("-0 days"));
		
			ini_set('memory_limit', '-1');
			
			$post = $this->getRequest()->getPost();
			if($post['from'] || $post['to']){
				$from = date("Y-m-d 00:00:00", strtotime($post['from']));
    	    	$to = date("Y-m-d 24:00:00", strtotime($post['to']));	
			} else {
				//$from = date("Y-m-d 00:00:00", strtotime('0 day'));
        		//$to = date("Y-m-d 24:00:00", strtotime('0 day'));
				$from = date('Y-m-d 00:00:00', time());
				$to = date('Y-m-d 24:00:00',  time());
				
			}
			

		$status = array(1,5);
		$collection = Mage::getModel('supplier/dropshipitems')->getCollection()->addFieldToSelect('order_id');
		//$collection->getSelect()->group('order_id');
	 	$collection->getSelect()->distinct(true);
		$order_list = $collection->getData();
        //$limit = $this->getCurrentLimit();
        //$filter = $this->getRequest()->getParam('status');
       

        $orders = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('created_at', array('from'=>$from, 'to'=>$to))
				->addAttributeToSort('entity_id', 'DESC');
               
       
        return $orders;
   
    }
	
	
	
	
	
	
	public function getOrdersByMonth()
    {
      			$from = $this->getStartMonth();
    	    	$to = $this->getCurrentDate();
		
		
		/*
		//////////////////////////////////
		
			$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
			$table = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item');
			$table2 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_payment');
			$table3 = Mage::getSingleton('core/resource')->getTableName('catalog_category_product');
			$table4 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
		
			$search = " WHERE `main_table`.`created_at` BETWEEN '".$from."' AND '".$to."' ";
		
			
			
			
			$sql = "SELECT  count(`main_table`.`qty_ordered`) AS TotalProducts,
				 SUM(IF(p.`method` = 'cashondelivery',1,0)) AS totalCOD,
				 SUM(IF(p.`method` = 'payucheckout_shared',1,0)) AS totalPrePaid,
				 SUM(CASE WHEN p.`method` = 'cashondelivery' THEN base_amount_ordered ELSE 0 END) AS CODTotal,
				 SUM(CASE WHEN p.`method` = 'payucheckout_shared' THEN base_amount_ordered ELSE 0 END) AS PrePaidTotal,
				 SUM(CASE WHEN o.`status` = 'canceled' THEN base_amount_ordered ELSE 0 END) AS CancelTotal,
				 SUM(CASE WHEN o.`status` = 'complete' THEN base_amount_ordered ELSE 0 END) AS CompleteTotal,
				 SUM(CASE WHEN o.`status` = 'processing' THEN base_amount_ordered ELSE 0 END) AS ProcessingTotal,
				 SUM(CASE WHEN o.`status` = 'returns' THEN base_amount_ordered ELSE 0 END) AS ReturnsTotal,
				 SUM(CASE WHEN o.`status` = 'readypickup' THEN base_amount_ordered ELSE 0 END) AS ReadyPcikupTotal,
				 SUM(CASE WHEN o.`status` = 'pending' THEN base_amount_ordered ELSE 0 END) AS PendingTotal,
				 SUM(CASE WHEN o.`status` = 'handed_courier' THEN base_amount_ordered ELSE 0 END) AS HandedCourierTotal,
				 SUM(CASE WHEN o.`status` = 'holded' THEN base_amount_ordered ELSE 0 END) AS HoldTotal,
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
				 FROM ".$table." AS `main_table` INNER JOIN ".$table3." AS `cat_product` ON `main_table`.`product_id` = `cat_product`.`product_id` INNER JOIN ".$table2." AS p ON p.entity_id = `main_table`.`order_id` INNER JOIN ".$table4." AS o ON `main_table`.`order_id` = `o`.`entity_id` ".$search." ORDER BY `week1`	DESC";
		echo $sql;
		die;
		*/
		////////////////////////////////
		
        
		$orders1 = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                //->addAttributeToFilter('entity_id', array('in' => $order_list1))
				 ->addAttributeToFilter('created_at', array('from'=>$from, 'to'=>$to))
				->addAttributeToSort('entity_id', 'DESC');
        return $orders1;
		 $result = $connect->query( $sql );
		return $result->fetchAll();	
   
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
	
 	
	public function getAllOrders()
    {
		ini_set('memory_limit', '-1');	
		$post = $this->getRequest()->getPost();
			if($post['from'] || $post['to']){
				$from = date("Y-m-d 00:00:00", strtotime($post['from']));
    	    	$to = date("Y-m-d 24:00:00", strtotime($post['to']));	
			$orders = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
				->addAttributeToFilter('created_at', array('from'=>$from, 'to'=>$to))
				->addAttributeToSort('entity_id', 'DESC');
			}
			else{
			$orders = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
				->addAttributeToSort('entity_id', 'DESC');	
			}
		
   		return $orders;
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