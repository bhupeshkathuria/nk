<?php

require_once "../app/Mage.php"; 
 Mage::app('default');
  extract($_REQUEST);
   $del_pin = $_GET['p'];
   
   /**
     * Get the resource model
     */
    $resource = Mage::getSingleton('core/resource');
     
    /**
     * Retrieve the read connection
     */
    $readConnection = $resource->getConnection('core_read');
 
    /**
     * Retrieve our table name
     */
    $table = $resource->getTableName('catalog/product');
     
    /**
     * Set the product ID
     */
     
	  $query = "SELECT city, state FROM `service_pincodes` WHERE  pincode=".$del_pin." limit 1";
     
    /**
     * Execute the query and store the result in $sku
     */
    $results = $readConnection->fetchAll($query);
     
    /**
     * Print the SKU to the screen
     */
    echo json_encode($results[0]);
?>