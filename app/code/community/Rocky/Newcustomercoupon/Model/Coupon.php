<?php
/**
 * 
 *
 * @author Rocky
 * @copyright Copyright (c) 2013 Rocky.
 */
class Rocky_Newcustomercoupon_Model_Coupon extends Mage_Core_Model_Abstract {
    //put your code here
    
    public function __construct() {
        parent::__construct();
        $this->_init('newcustomercoupon/coupon');
    }
}

?>
