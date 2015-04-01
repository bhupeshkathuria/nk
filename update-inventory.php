<?php

 	require_once('app/Mage.php');
	Mage::setIsDeveloperMode(true);
	ini_set('display_errors', 1);
	umask(0);
 Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
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
 
 
 
 //$stockItem->save();

 unset($stockItem);
 unset($product);
 }
 
 echo "<br />Stock updated $sku";
 
 }
 fclose($file);
 
?>