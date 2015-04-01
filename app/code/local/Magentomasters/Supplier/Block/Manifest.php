<?php
class Magentomasters_Supplier_Block_Manifest extends Magentomasters_Supplier_Block_Abstract{

    public function getOrders() {
        $session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
		$status = array(1,2,3,4,5,6,7);
		$cstatus = Mage::app()->getRequest()->getParam('s');
		
		$collection = Mage::getModel('supplier/dropshipitems')->getCollection()->addFieldToSelect('order_id')->addFieldToFilter('supplier_id',$supplierId)->addFieldToFilter('status',$cstatus);
		
		$collection->getSelect()->group('order_id');
	 	$collection->getSelect()->distinct(true);
		$order_list = $collection->getData();
        $limit = $this->getCurrentLimit();
        $filter = $this->getRequest()->getParam('status');
        $p = $this->getCurrentPage();

        $orders = Mage::getResourceModel('sales/order_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $order_list))
				->addAttributeToSort('entity_id', 'DESC')
                ->setPageSize($limit)
                ->setPage($p, $limit);
				
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

}