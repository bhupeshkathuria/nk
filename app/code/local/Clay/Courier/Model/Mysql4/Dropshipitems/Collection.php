<?php

class Magentomasters_Courier_Model_Mysql4_Dropshipitems_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('courier/dropshipitems');
    }
}