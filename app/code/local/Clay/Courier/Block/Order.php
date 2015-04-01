<?php
ini_set('memory_limit', '-1');
class Clay_Courier_Block_Order extends Clay_Courier_Block_Abstract{

	public function getAllManifests($courierId) {
		
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table1 = Mage::getSingleton('core/resource')->getTableName('supplier_manifest');
		$table2 = Mage::getSingleton('core/resource')->getTableName('supplier_users');
		
		
		$sql = "SELECT sm.*,su.surname FROM ".$table1." sm INNER JOIN ".$table2." su ON sm.supplier_id = su.id WHERE courier_id = '".$courierId."' and date_time IS NOT NULL";
		
		$result = $connect->query( $sql );
		return $result->fetchAll();
	
	
	}
    public function getOrders() {
        $session = Mage::getSingleton('core/session');
        $courierId = $session->getData('courierId');
		$status = array(1,2,3,4,5,6,7);
		$cstatus = Mage::app()->getRequest()->getParam('s');
		
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		$table2 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
			
		$sql = "SELECT  SUM(d.price) AS totalorderprice, SUM(d.qty) AS totalorderqty,d.dropship_id, d.order_id,d.order_number,order_item_id,d.supplier_id,d.courier_id,d.awbno,d.product_id,d.product_name,d.sku,d.qty,d.cost, d.price,d.status as dstatus,d.method,d.date,s.status,s.updated_at from ".$table." AS d INNER JOIN ".$table2." s ON d.order_id = s.entity_id WHERE d.courier_id = ".$courierId." and d.status = '".$cstatus."' GROUP BY d.order_number ";
		$result = $connect->query( $sql );
		 		
        return $result->fetchAll();
    }
	
	
	
        

	public function getOrderStatus(){
		
		$orderStatus = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
		return $orderStatus;
	}
	
    public function getPageUrl($page=1){
        $limit = $this->getCurrentLimit();
        $currentUrl = $this->getCurrentUrl();
        return $this->getBaseUrl() . "courier/order/index" . '/limit/' . $limit . '/p/' . $page;
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
        return Mage::registry("courier_order_filter");
    }
	
	public function getOrderByStatus($supplierid, $status){
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		$table2 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
			
		$sql = "SELECT COUNT(entity_id) AS countOrder, sum(price) AS price,d.order_id,d.status as dstatus, s.status from ".$table." AS d INNER JOIN ".$table2." s ON d.order_id = s.entity_id WHERE d.courier_id = ".$supplierid." and s.status = '".$status."' GROUP BY s.status";
		$result = $connect->query( $sql );
		 return $result->fetch();
	}
	
	public function getCustomStatus(){
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('sales_order_status');		
		$sql = "SELECT * FROM ".$table." ORDER BY sort";
		$result = $connect->query( $sql );
		 return $result->fetchAll();
	}
	
	public function getCustomSku($orderid,$supplier){
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');		
		$sql = "SELECT sku,product_name,qty,price,status FROM ".$table." WHERE order_item_id = ".$orderid." and supplier_id = ".$supplier."";
		$result = $connect->query( $sql );
		return $result->fetch();			
	}
	
	public function getSupplierInfo($sid){
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_users');		
		$sql = "SELECT * FROM ".$table." WHERE id = ".$sid."";
		$result = $connect->query( $sql );
		return $result->fetch();			
	}
	
}