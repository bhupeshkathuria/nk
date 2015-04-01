<?php
require_once 'app/Mage.php';
Mage::app();

		$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
		$table = Mage::getSingleton('core/resource')->getTableName('supplier_dropship_items');
		$table2 = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
			
		echo $sql = "SELECT COUNT(entity_id) AS countOrder, sum(price) AS price,d.status as dstatus, s.status from ".$table." AS d INNER JOIN ".$table2." s ON d.order_id = s.entity_id WHERE d.supplier_id = '9' and s.status = '".$status."' GROUP BY s.status";
		$result = $connect->query( $sql );
		 $resultOut = $result->fetch();
		 
		 print_r($resultOut);
		echo date('Y-m-d H:i:s');