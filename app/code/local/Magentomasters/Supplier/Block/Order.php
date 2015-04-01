	<?php
class Magentomasters_Supplier_Block_Order extends Magentomasters_Supplier_Block_Abstract{

    public function getAllManifests($supplierId) {
		
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_manifest');
		
		$sql = "SELECT * FROM ".$table." WHERE supplier_id = '".$supplierId."' and date_time IS NOT NULL";
		
		$result = $connect->query( $sql );
		return $result->fetchAll();
	
	
	}
	public function getOrders($post = '') {
        $session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
		$status = array(1,3,4,5,6,7,8,9,10);
		$cstatus = Mage::app()->getRequest()->getParam('s');
		
		$collection = Mage::getModel('supplier/dropshipitems')->getCollection()->addFieldToSelect('order_id')->addFieldToFilter('supplier_id',$supplierId)->addFieldToFilter('status',$cstatus);
		
		$collection->getSelect()->group('order_id');
	 	$collection->getSelect()->distinct(true);
		$order_list = $collection->getData();
        //$limit = $this->getCurrentLimit();
		$limit = 200;
        $filter = $this->getRequest()->getParam('status');
        $p = $this->getCurrentPage();

		if($post != ''){
		$from = date("Y-m-d 00:00:00", strtotime($post['from']));
		$to = date("Y-m-d 23:59:59", strtotime($post['to']));
        $orders = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $order_list))
				->addAttributeToFilter('updated_at', array('gt' => $from,'lt' => $to))
				->addAttributeToSort('entity_id', 'DESC')
                ->setPageSize($limit)
                ->setPage($p, $limit);
		}
		else{
		$orders = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $order_list))
				->addAttributeToSort('entity_id', 'DESC')
                ->setPageSize($limit)
                ->setPage($p, $limit);
		}
	      if ($filter) {
            foreach ($orders as $key => $order) {
				                if ($filter=="Shipped" && $this->canShip($order->getEntityId())) {
                    $orders->removeItemByKey($key);
                } elseif($filter=="Waiting" && !$this->canShip($order->getEntityId())) {
                	$orders->removeItemByKey($key);
                }
            }
            Mage::register("supplier_order_filter",$filter);
        }
        Mage::register("order_page_count", array("item_count"=>$orders->getSize(),"last_page"=>$orders->getLastPageNumber()));
        return $orders;
    }















	public function getOrderStatus(){
		
		$orderStatus = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
		return $orderStatus;
	}
	
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
	
	public function getOrderByStatus($supplierid, $status, $post = ''){
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		$appendQuery ='';
		if($post['from'] !=''){
			$from = date("Y-m-d 00:00:00", strtotime($post['from']));
			$appendQuery .= " and updated_at >= '".$from."' ";		
		}
		if($post['to'] !=''){
			$to = date("Y-m-d 23:59:59", strtotime($post['to']));
			$appendQuery .= " and updated_at <= '".$to."' ";		
		}
		if($post['courier']!=''){
			$appendQuery .= " and d.courier_id='".$post['courier']."' ";
		}
		$table2 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
			
		$sql = "SELECT COUNT(entity_id) AS countOrder, SUM(s.base_grand_total) AS price,d.status as dstatus, s.status from ".$table." AS d INNER JOIN ".$table2." s ON d.order_id = s.entity_id WHERE d.supplier_id = ".$supplierid." ".$appendQuery." and s.status = '".$status."' GROUP BY s.status";
		
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
	
	public function getCourierList(){
		
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('courier_user');		
		$sql = "SELECT id,name,surname FROM ".$table." ORDER BY surname";
		$result = $connect->query( $sql );
		 return $result->fetchAll();
	}
	
	public function getCustomSku($orderid){
		
		$session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		$table1 = Mage::getSingleton('core/resource')->getTableName('supplier_users');		
		echo $sql = "SELECT d.id,d.sku,d.product_id,d.product_name,d.courier_id,d.awbno,d.qty,d.price,d.status FROM ".$table." AS d INNER JOIN ".$table1." AS s ON d.supplier_name = s.name WHERE d.order_number = '".$orderid."' and s.id=".$supplierId." and awbno !=''";
		$result = $connect->query( $sql );
		return $result->fetch();			
	}
	
	public function getCourierName($id){
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('courier_user');		
		$sql = "SELECT surname FROM ".$table." WHERE id = '".$id."'";
		
		$result = $connect->query( $sql );
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
	
	public function getAwb($id,$tableField = ''){
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName($tableField['table']);	
		if($id){	
		$sql = "SELECT $tableField[field] as lastmile_id FROM ".$table." WHERE awb = '".$id."'";
		$result = $connect->query( $sql );
		return $result->fetch();
		}
		
		
	}
	
	function getTotalQty($order,$supplier){
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		
		$sql = "SELECT sum(`qty`) as totalQty, sum(`price`) as totalPrice FROM ".$table." WHERE order_number = ".$order." and supplier_id = ".$supplier."";
		$result = $connect->query( $sql );
		return $result->fetch();
	}
	
}