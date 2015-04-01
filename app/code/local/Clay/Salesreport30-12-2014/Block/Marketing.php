<?php
class Clay_Salesreport_Block_Marketing extends Clay_Courier_Block_Abstract{
	/**
     * Return month's totals
     *
     * @return mixed
     */
    public function getOrders()
    {
       	$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
		$table2 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_payment');
		
		
		
			$post = $this->getRequest()->getPost();
			
			if($post['from'] || $post['to']){
				$from = date("Y-m-d 00:00:00", strtotime($post['from']));
    	    	$to = date("Y-m-d 24:00:00", strtotime($post['to']));	
			} else {
				$from = $this->getStartMonth();
    			$to = $this->getCurrentDate();
			}
			
	   	$fromDate = date('Y-m-d', strtotime($from));
		$toDate = date('Y-m-d', strtotime($to));
		
		$sql = "SELECT DATE_FORMAT(o.created_at, '%Y-%m-%d') AS Orderdate,
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
				 SUM( IF( o.`status` = 'holded ', 1, 0 ) ) AS 'Hold',
				 SUM(IF(p.`method` = 'cashondelivery',1,0)) AS totalCOD,
				 SUM(IF(p.`method` = 'payucheckout_shared',1,0)) AS totalPrePaid,
				 SUM(CASE WHEN o.`status` = 'complete' THEN base_amount_ordered ELSE 0 END) AS CompleteTotal,
				 SUM(CASE WHEN o.`status` = 'processing' THEN base_amount_ordered ELSE 0 END) AS ProcessingTotal,
				 SUM(CASE WHEN o.`status` = 'canceled' THEN base_amount_ordered ELSE 0 END) AS CancelTotal,
				 SUM(CASE WHEN o.`status` = 'returns' THEN base_amount_ordered ELSE 0 END) AS ReturnsTotal,
				 SUM(CASE WHEN o.`status` = 'Readyforpickup' THEN base_amount_ordered ELSE 0 END) AS ReadyPcikupTotal,
				 SUM(CASE WHEN o.`status` = 'Pending' THEN base_amount_ordered ELSE 0 END) AS PendingTotal,
				 SUM(CASE WHEN o.`status` = 'HandedtoCourier' THEN base_amount_ordered ELSE 0 END) AS HandedCourierTotal,
				 SUM(CASE WHEN o.`status` = 'holded' THEN base_amount_ordered ELSE 0 END) AS HoldTotal,
				 SUM(CASE WHEN p.`method` = 'cashondelivery' THEN base_amount_ordered ELSE 0 END) AS CODTotal,
				 SUM(CASE WHEN p.`method` = 'payucheckout_shared' THEN base_amount_ordered ELSE 0 END) AS PrePaidTotal
 			  FROM ".$table." AS o INNER JOIN ".$table2." AS p ON o.entity_id = p.parent_id WHERE o.created_at BETWEEN '".$fromDate."' AND '".$toDate."' GROUP BY DATE_FORMAT(o.created_at, '%Y-%m-%d') ORDER BY Orderdate DESC";
		
		$result = $connect->query( $sql );
		return $result->fetchAll();
		
    }
	
	public function getMarketingSpend($date){
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('marketing_spend');
		$sql = "SELECT `spend` FROM ".$table." WHERE `spend_date` = '".$date."'";	
		$result = $connect->query( $sql );
		return $result->fetch();
	}
	
	public function getTotalMarketingSpend($date){
		$month = date("m",strtotime($date));
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('marketing_spend');
		$sql = "SELECT SUM(`spend`) AS totalSpend FROM ".$table." WHERE MONTH(`spend_date`) = '".$month."'";	
		$result = $connect->query( $sql );
		return $result->fetch();
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