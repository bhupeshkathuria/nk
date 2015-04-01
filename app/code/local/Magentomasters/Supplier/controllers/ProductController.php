<?php class Magentomasters_Supplier_ProductController extends Mage_Core_Controller_Front_Action {
	
	public function preDispatch(){
		parent::preDispatch();	
		if(Mage::getStoreConfig('supplier/interfaceoptions/interface_enabled')=='0'){
			$redirectPath = Mage::getUrl();
			$this->_redirectUrl($redirectPath); 
		} else if(Mage::getStoreConfig('supplier/interfaceoptions/interface_stock')=='0'){
			$redirectPath = Mage::getUrl() . "supplier/order";
			$this->_redirectUrl($redirectPath); 
		}
	}
	public function indexAction() {
        $session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
        //$orderId = $this->getRequest()->getParam('orderid');
        if( $supplierId && $supplierId != "logout") {
            $this->loadLayout();
            $this->renderLayout();
        } else {
            $redirectPath = Mage::getUrl() . "supplier/";
            $this->_redirectUrl( $redirectPath );
        }
	}
	
	public function importAction() {
		$session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
        if( $supplierId && $supplierId != "logout") {
			
		$supplierId = $session->getDate('supplierId');	
		$data = $this->getRequest()->getPost();
		$handle = fopen($_FILES['import']['tmp_name'], "r");
		$i=0;
		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
			if($i>0){
				if(!$data['2'] || $data['2']==0){ 
			
					$stockData = array(
					  'qty' => $data['2'],
					  'is_in_stock' => 0
					);
				
				} else {
					
					$stockData = array(
					  'qty' => $data['2'],
					  'is_in_stock' => 1
					);
				}
				$productId = Mage::getModel('catalog/product')->getResource()->getIdBySku($data['1']);
				//$productId = $data['id'];
			 	$product = Mage::getModel('catalog/product' )->load($productId);
				//$product = Mage::getModel('catalog/product' )->loadByAttribute('sku',$data['1']);
				 
				
				//$stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
				$product->setStockData($stockData);
			 	$product->save();
				
			}
			$i++;
		}
		}
		$redirectPath = Mage::getUrl() . "supplier/product/";
        $this->_redirectUrl( $redirectPath );
		
	}
	
	public function exportAction() {
		
		$session = Mage::getSingleton('core/session');
		$supplierId = $session->getData('supplierId');
		$supplier =  Mage::getModel('supplier/supplier')->getSupplierById($supplierId);
		$suppliername = $supplier['name'];
		
		$attribute = Mage::getModel('supplier/supplier')->getSupplierOptionsId($suppliername);
		$collection = Mage::getModel('catalog/product')
                    ->getCollection()
                    ->addFieldToFilter(array(
   						 array('attribute'=>'supplier','eq'=>$attribute['option_id'])
						))
					->addAttributeToSelect('*');
		
		$session = Mage::getSingleton('core/session');
		$supplierId = $session->getData('supplierId');
		$supplier =  Mage::getModel('supplier/supplier')->getSupplierById($supplierId);
		$suppliername = $supplier['name'];
		$attribute = Mage::getModel('supplier/supplier')->getSupplierOptionsId($suppliername);
		$productModel = Mage::getModel('catalog/product');
		$attr = $productModel->getResource()->getAttribute("supplier");
	
		if ($attr->usesSource()) {
    		$color_id = $attr->getSource()->getOptionId($attribute['value']);
		}

		if($color_id == $attribute['option_id']){
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=Supplier_products.csv');
		$output = fopen('php://output', 'w');
		fputcsv($output, array('name', 'sku', 'qty'));
		foreach($collection as $_product){
		
		$row= array();

		$_product->getResource()->getAttribute('supplier')->getFrontend()->getValue($_product);

		$attributeSetModel = Mage::getModel("eav/entity_attribute_set");
		$attributeSetModel->load($_product->getAttributeSetId());
		$attributeSetName  = $attributeSetModel->getAttributeSetName();


		$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
		$manageStock = $stock->getManageStock();
		$qty = $stock->getQty();
		$qty = round($qty);
		
		$row['product_name'] = $_product->getName();
        $row['product_sku'] = $_product->getSku();
        $row['product_qty'] = $qty;
 		fputcsv($output, $row);	
		}
		
	}
	}
	
	public function saveAction(){
		
		$session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
        //$orderId = $this->getRequest()->getParam('orderid');
        if( $supplierId && $supplierId != "logout") {
            
			
			 $data = $this->getRequest()->getPost();
			 
			 if($data['id']){
				 
				if(!$data['qty'] || $data['qty']==0){ 
			
					$stockData = array(
					  'qty' => $data['qty'],
					  'is_in_stock' => 0
					);
				
				} else {
					
					$stockData = array(
					  'qty' => $data['qty'],
					  'is_in_stock' => 1
					);
				}
					
			 	$productId = $data['id'];
			 	$product = Mage::getModel('catalog/product' )->load($productId);
			 	$product->setStockData($stockData);
			 	$product->save();
				
				echo "Product updated.";
				
			 }
			
        } else {
            $redirectPath = Mage::getUrl() . "supplier/";
            $this->_redirectUrl( $redirectPath );
        }
	
	}
	
	public function inventoryAction(){
		
		$session = Mage::getSingleton('core/session');
        $supplierId = $session->getData('supplierId');
        //$orderId = $this->getRequest()->getParam('orderid');
        if( $supplierId && $supplierId != "logout") {
            
			
			$count = 0;

			 $file = fopen($_FILES['import']['tmp_name'], "r");;
			 while (($line = fgetcsv($file)) !== FALSE) { 
			 
			 if ($count == 0) {
			 foreach ($line as $key=>$value) {
			 $cols[$value] = $key;
			 } 
			 } 
			 
			 $count++;
			 
			 if ($count == 1) continue;
			 
			 #Convert the lines to cols 
			 if ($count > 0) { 
			 foreach($cols as $col=>$value) {
			 unset(${$col});
			 ${$col} = $line[$value];
			 } 
			 }
			 
			 // Check if SKU exists
			 $product = Mage::getModel('catalog/product')->loadByAttribute('sku',$sku); 
			
			 if ( $product ) {
			
			 $productId = $product->getId();
			 $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
			 $stockItemId = $stockItem->getId();
			 $stock = array();
			 
			 if (!$stockItemId) {
			 $stockItem->setData('product_id', $product->getId());
			 $stockItem->setData('stock_id', 1); 
			 } else {
			 $stock = $stockItem->getData();
			 }
			 
			 foreach($cols as $col=>$value) {
			 $stock[$col] = $line[$value];
			 } 
			 
			 foreach($stock as $field => $value) {
			 $stockItem->setData($field, $value?$value:0);
			 }
			 
			 
			 
			 $stockItem->save();
			
			 unset($stockItem);
			 unset($product);
			 }
			 
			 echo "<br />Stock updated $sku";
			 
			 }
			 fclose($file);
			 
				
			
			
        } else {
            $redirectPath = Mage::getUrl() . "supplier/";
            $this->_redirectUrl( $redirectPath );
        }
	
	}
	
}