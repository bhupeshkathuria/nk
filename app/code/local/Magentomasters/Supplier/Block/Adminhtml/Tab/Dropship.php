<?php class Magentomasters_Supplier_Block_Adminhtml_Tab_Dropship extends Mage_Adminhtml_Block_Sales_Order_Abstract implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
 public function __construct()
    {
        parent::__construct();
		$this->setId('order_dropshipments');
        $this->setTemplate('supplier/order/tab/view.phtml');
    }
    
    public function getOrder()
    {
        return Mage::registry('current_order');
    }
	
	public function getConnection()
	{
		return Mage::getSingleton('core/resource')->getConnection('catalog_write');
	}
	
	public function getDropshippersOrderedItems()
    { 
		//$order = $this->getOrder();
       	//$order = Mage::getModel('sales/order')->load($order->getId());
       	//$items = $order->getItemsCollection(array(Mage_Catalog_Model_Product_Type::TYPE_SIMPLE));
        //return $items;
    }
	
	public function getSuppliers(){
		$suppliers = Mage::getModel('supplier/supplier')->getCollection();
		return $suppliers;
	}
	
	public function getDroppedItems(){
		$collection = Mage::getModel('supplier/dropshipitems')->getCollection()->addFieldToFilter('order_id',$this->getRequest()->getParam('order_id'));
        return $collection;
	}
	
	public function getIsDropped($item){
		return Mage::getModel('supplier/dropshipitems')->getIsDropped($item);
	}
	
	public function getStatus($value){
		$status = array(
                       '1'     => Mage::helper('supplier')->__('Pending'),
                        '2'     => Mage::helper('supplier')->__('Scheduled'),
                        '3'     => Mage::helper('supplier')->__('Canceled'),
                        '4'     => Mage::helper('supplier')->__('Refunded'),
                        '5'     => Mage::helper('supplier')->__('Completed'), 
                );
		return $status[$value]; 
	}
	
	public function getMethod($value){
		$method = array(
                        '0'     => Mage::helper('supplier')->__('None'),
						'1'     => Mage::helper('supplier')->__('Email'),
                        '2'     => Mage::helper('supplier')->__('XML'),
						'3'     => Mage::helper('supplier')->__('FTP XML'),
                );
		return $method[$value]; 
	}
	    
    public function getSource()
    {
        return $this->getOrder();
    }

    
    public function getTabLabel()
    {
        return Mage::helper('sales')->__('Dropship');
    }

    public function getTabTitle()
    {
        return Mage::helper('sales')->__('Dropship');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
    
    public function getFormUrl()
    {
        return $this->getUrl('supplier/adminhtml_dropshipments/dropshipform', array('order_id'=> $this->getRequest()->getParam('order_id')));
    }    
    
}

