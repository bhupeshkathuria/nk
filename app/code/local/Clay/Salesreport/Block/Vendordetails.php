<?php
class Clay_Salesreport_Block_Vendordetails extends Clay_Salesreport_Block_Abstract{
	
	
	public function getOrders() {
        $status = array(5);
		$suppliername = $this->getRequest()->getParam('supplier_name');
		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		$table1 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_item');
		$table2 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
		
		//$sql = "SELECT s.`order_item_id`,s.order_number, s.`supplier_name`,s.supplier_paid,s.supplier_payment_status, i.*, i.product_id  FROM ".$table." AS s INNER JOIN  ".$table1." as i ON s.`order_id` = i.`order_id` WHERE s.`status` = 5 and i.product_type = 'simple' and s.supplier_name = '".$suppliername."'";
		$sql = "SELECT  items.order_id AS orderid,
		items.item_id AS itemid,
		items.created_at AS orderdate,
		items.product_type,
		items.sku AS itemcode,
		items.name AS itemname,
		items.price_incl_tax AS itemprice,
		items.tax_amount AS itemtax,
		items.discount_amount AS discount,
		items.qty_ordered AS qty_ordered,
		items.qty_shipped AS qty_shipped,
		items.discount_amount AS discount_amount,
		items.price AS itemprice,
		items.order_id AS order_id,
		address.price AS dropshiprice,
		address.order_number AS order_number,
		address.supplier_paid AS supplier_paid,
		address.supplier_payment_status AS supplier_payment_status,
		address.supplier_name AS supplier_name,
		address.codremit AS codremit
FROM ".$table2." AS orders 
        JOIN ".$table1." AS items 
          ON items.order_id = orders.entity_id 
        LEFT JOIN ".$table." AS address
          ON items.item_id = address.order_item_id
WHERE 
         address.`status` = 5 and orders.state = 'complete' and address.supplier_name = '".$suppliername."'";

       $result = $connect->query( $sql );
		return $result->fetchAll();
    }


		public function getItemsBySupplier($supplierId,$orderId){
			$order = Mage::getModel('sales/order')->load($orderId);
			$items = $order->getAllItems();
			$status = array(5);
			
			$collection = Mage::getModel('supplier/dropshipitems')->getCollection();
			$collection->addFieldToSelect('product_id');
			$collection->addFieldToSelect('price');
			$collection->addFieldToSelect('qty');
			$collection->addFieldToFilter('supplier_name',$supplierId);
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
}