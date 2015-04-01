<?php
require_once('app/Mage.php');
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);
umask(0);
Mage::app('admin');
Mage::register('isSecureArea', 1);
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
 
set_time_limit(0);
ini_set('memory_limit','1024M');
 
/***************** UTILITY FUNCTIONS ********************/
function _getConnection($type = 'core_read'){
    return Mage::getSingleton('core/resource')->getConnection($type);
}
 
function _getTableName($tableName){
    return Mage::getSingleton('core/resource')->getTableName($tableName);
}
 
 
function _getAttributeId($attribute_code = 'price'){
    $connection = _getConnection('core_read');
    $sql = "SELECT attribute_id
                FROM " . _getTableName('eav_attribute') . "
            WHERE
                entity_type_id = ?
                AND attribute_code = ?";
    $entity_type_id = _getEntityTypeId();
    return $connection->fetchOne($sql, array($entity_type_id, $attribute_code));
}
 

function _getEntityTypeId($entity_type_code = 'catalog_product'){
    $connection = _getConnection('core_read');
    $sql        = "SELECT entity_type_id FROM " . _getTableName('eav_entity_type') . " WHERE entity_type_code = ?";
    return $connection->fetchOne($sql, array($entity_type_code));
}


function _checkIfSkuExists($sku){
    $connection = _getConnection('core_read');
    $sql        = "SELECT COUNT(*) AS count_no FROM " . _getTableName('catalog_product_entity') . " WHERE sku = ?";
    $count      = $connection->fetchOne($sql, array($sku));
    if($count > 0){
        return true;
    }else{
        return false;
    }
}
 

function _getIdFromSku($sku){
    $connection = _getConnection('core_read');
    $sql        = "SELECT entity_id FROM " . _getTableName('catalog_product_entity') . " WHERE sku = ?";
    return $connection->fetchOne($sql, array($sku));
}


function _updateStocks($data){
    $connection     = _getConnection('core_write');
    $sku            = $data[1];
    $newQty         = $data[2];
	$inStock        = $data[3];
    $productId      = _getIdFromSku($sku);
    $attributeId    = _getAttributeId();
 
    $sql            = "UPDATE " . _getTableName('cataloginventory_stock_item') . " csi,
                       " . _getTableName('cataloginventory_stock_status') . " css
                       SET
                       csi.qty = ?,
                       csi.is_in_stock = ?,
                       css.qty = ?,
                       css.stock_status = ?
                       WHERE
                       csi.product_id = ?
                       AND csi.product_id = css.product_id";
   // $isInStock      = $newQty > 0 ? 1 : 0;
   	$isInStock = $inStock;
    $stockStatus    = $newQty > 0 ? 1 : 0;
    $connection->query($sql, array($newQty, $isInStock, $newQty, $stockStatus, $productId));
}

/***************** UTILITY FUNCTIONS ********************/
 
	$handle = fopen($_FILES['import']['tmp_name'], "r");
	$i=0;
	$message = '';
	$count   = 1;
	while (($_data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		if($i == 0){
			$i++;
			continue;	
		}
		if(_checkIfSkuExists($_data[1])){
			try{
				_updateStocks($_data);
				$message .= $count . '> Success:: Qty (' . $_data[2] . ') of Sku (' . $_data[1] . ') has been updated. <br />';
	 
			}catch(Exception $e){
				$message .=  $count .'> Error:: while Upating  Qty (' . $_data[2] . ') of Sku (' . $_data[1] . ') => '.$e->getMessage().'<br />';
			}
		}else{
			$message .=  $count .'> Error:: Product with Sku (' . $_data[1] . ') does\'t exist.<br />';
		}
		$count++;
	}		
	//echo $message; 	
	//$redirectPath = "http://www.netakart.com/index.php/supplier/product/";
   // $this->_redirectUrl( $redirectPath );
   header('location:http://www.netakart.com/index.php/supplier/product/');
	?>