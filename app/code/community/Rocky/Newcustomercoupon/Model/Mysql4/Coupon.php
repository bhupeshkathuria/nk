<?php
/**
 * 
 *
 * @author Rocky
 * @copyright Copyright (c) 2013 Rocky.
 */
class Rocky_Newcustomercoupon_Model_Mysql4_Coupon extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('newcustomercoupon/coupon', 'coupon_id');
    }
}

?>
