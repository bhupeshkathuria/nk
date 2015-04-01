<?php
class Clay_Salesreport_Block_Order extends Clay_Courier_Block_Abstract{
	/**
     * Return month's totals
     *
     * @return mixed
     */
    public function getOrders()
    {
       	$post = $this->getRequest()->getPost();
			if($post['from'] || $post['to']){
				$from = date("Y-m-d 00:00:00", strtotime($post['from']));
    	    	$to = date("Y-m-d 24:00:00", strtotime($post['to']));	
			} else {
				$from = date('Y-m-d 00:00:00', time());
				$to = date('Y-m-d 24:00:00',  time());
				
			}
			
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
		$table1 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_payment');
		
		$sql = "SELECT status,sum(grand_total) as grand_total,
		count(sfo.entity_id) as totalStatus,
		SUM(IF(sfop.`method` = 'cashondelivery',1,0)) AS totalCOD,
		SUM(IF(sfop.`method` != 'cashondelivery',1,0)) AS totalPrePaid,
		SUM(CASE WHEN sfop.`method` = 'cashondelivery' THEN sfo.grand_total ELSE 0 END) AS CODTotal,
		SUM(CASE WHEN sfop.`method` != 'cashondelivery' THEN sfo.grand_total ELSE 0 END) AS PrepaidTotal
		 FROM ".$table." sfo INNER JOIN ".$table1." sfop ON sfo.entity_id = sfop.parent_id WHERE sfo.created_at >= '$from' and sfo.created_at <= '$to' GROUP BY status";
		$result = $connect->query( $sql );
		return $result->fetchAll();	
		/*$collection = Mage::getModel('supplier/dropshipitems')->getCollection()->addFieldToSelect('order_id');
		//$collection->getSelect()->group('order_id');
	 	$collection->getSelect()->distinct(true);
		$order_list = $collection->getData();*/
        //$limit = $this->getCurrentLimit();
        //$filter = $this->getRequest()->getParam('status');
       
        /*$orders = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('created_at', array('from'=>$from, 'to'=>$to))
				->addAttributeToSort('entity_id', 'DESC');
        */       
       
       // return $orders;
   
    }
	
	
	
	public function getMarketingSpend($from = '' ,$to = '')
    {
		$appendQuery = '';
		if($from != '' or $to != ''){
			$appendQuery = ' where spend_date >="'.date("Y-m-d",strtotime($from)).'" and spend_date <= "'.date("Y-m-d",strtotime($to)).'"';	
		}
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('marketing_spend');
		$qry = "select SUM(spend) from $table $appendQuery";
		$total = $connect->fetchOne($qry);
		return $total;
	}
	public function getOrdersByMonth()
    {
      
		$from = $this->getStartMonth();
    	$to = $this->getCurrentDate();
		
		
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
		$table1 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_payment');
		
		$sql = "SELECT status,sum(grand_total) as grand_total,
		count(sfo.entity_id) as totalStatus,
		SUM(IF(sfop.`method` = 'cashondelivery',1,0)) AS totalCOD,
		SUM(IF(sfop.`method` != 'cashondelivery',1,0)) AS totalPrePaid,
		SUM(CASE WHEN sfop.`method` = 'cashondelivery' THEN sfo.grand_total ELSE 0 END) AS CODTotal,
		SUM(CASE WHEN sfop.`method` != 'cashondelivery' THEN sfo.grand_total ELSE 0 END) AS PrepaidTotal
		 FROM ".$table." sfo INNER JOIN ".$table1." sfop ON sfo.entity_id = sfop.parent_id WHERE sfo.created_at >= '$from' and sfo.created_at <= '$to' GROUP BY status";
		 
		$result = $connect->query( $sql );
		return $result->fetchAll();	
		
		/*$orders1 = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                //->addAttributeToFilter('entity_id', array('in' => $order_list1))
				 ->addAttributeToFilter('created_at', array('from'=>$from, 'to'=>$to))
				->addAttributeToSort('entity_id', 'DESC');
        return $orders1;*/
   
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
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
		$table1 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_payment');
		$post = $this->getRequest()->getPost();
			if($post['from'] || $post['to']){
				$from = date("Y-m-d 00:00:00", strtotime($post['from']));
    	    	$to = date("Y-m-d 24:00:00", strtotime($post['to']));	
				
				$sql = "SELECT status,sum(grand_total) as grand_total,
					count(sfo.entity_id) as totalStatus,
					SUM(IF(sfop.`method` = 'cashondelivery',1,0)) AS totalCOD,
					SUM(IF(sfop.`method` != 'cashondelivery',1,0)) AS totalPrePaid,
					SUM(CASE WHEN sfop.`method` = 'cashondelivery' THEN sfo.grand_total ELSE 0 END) AS CODTotal,
					SUM(CASE WHEN sfop.`method` != 'cashondelivery' THEN sfo.grand_total ELSE 0 END) AS PrepaidTotal
					 FROM ".$table." sfo INNER JOIN ".$table1." sfop ON sfo.entity_id = sfop.parent_id WHERE sfo.created_at >= '$from' and sfo.created_at <= '$to' GROUP BY status";
		 
				
			/*$orders = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('entity_id')
				->addAttributeToSelect('grand_total')
				->addAttributeToSelect('total_qty_ordered')
				->addAttributeToSelect('status')
				->addAttributeToSelect('state')
				->addAttributeToSelect('created_at')
				->addAttributeToSelect('updated_at')
				->addAttributeToFilter('created_at', array('from'=>$from, 'to'=>$to))
				->addAttributeToSort('entity_id', 'DESC');*/
			}
			else{
			/*$orders = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
				->addAttributeToSort('entity_id', 'DESC');	*/
				$sql = "SELECT status,sum(grand_total) as grand_total,
					count(sfo.entity_id) as totalStatus,
					SUM(IF(sfop.`method` = 'cashondelivery',1,0)) AS totalCOD,
					SUM(IF(sfop.`method` != 'cashondelivery',1,0)) AS totalPrePaid,
					SUM(CASE WHEN sfop.`method` = 'cashondelivery' THEN sfo.grand_total ELSE 0 END) AS CODTotal,
					SUM(CASE WHEN sfop.`method` != 'cashondelivery' THEN sfo.grand_total ELSE 0 END) AS PrepaidTotal
					 FROM ".$table." sfo INNER JOIN ".$table1." sfop ON sfo.entity_id = sfop.parent_id WHERE GROUP BY status";
			}
			$result = $connect->query( $sql );
			return $result->fetchAll();	
		
   		//return $orders;
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
	
	public function getMonthlyPaymentMade($post=array()){
		$from = $this->getStartMonth();
    	$to = $this->getCurrentDate();
		if(!empty($post) && ($post['from'] || $post['to'])){
			$from = date("Y-m-d 00:00:00", strtotime($post['from']));
    	    $to = date("Y-m-d 24:00:00", strtotime($post['to']));	
		}
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
		$table1 = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		
		$sql = "SELECT sum(payment_to_supplier) FROM ".$table." sfo INNER JOIN ".$table1." sdi ON sfo.entity_id = sdi.order_id WHERE sfo.created_at >= '$from' and sfo.created_at <= '$to' and sfo.status ='complete'";
		//$result = $connect->query( $sql );
		return $connect->fetchOne($sql);	
	}

}