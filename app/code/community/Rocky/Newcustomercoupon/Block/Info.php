<?php
/**
 * 
 *
 * @author Rocky
 * @copyright Copyright (c) 2013 Rocky.
 */
class Rocky_Newcustomercoupon_Block_Info extends Mage_Core_Block_Template {

    //put your code here
    protected function _construct() {
        parent::_construct();

        $this->setTemplate('newcustomercoupon/info.phtml');
    }

    public function getCoupon() {
        $coupon = Mage::getModel('newcustomercoupon/coupon')->load(Mage::getSingleton('customer/session')->getCustomerId(), 'customer_id');
        $id = (int) Mage::getStoreConfig('newcustomercoupon/general/ruleid');
        $rule = Mage::getModel('salesrule/rule')->load($id);
        if(Mage::helper('newcustomercoupon')->validatorCoupon($rule,$coupon->getCoupon())){
            return $coupon->getCoupon();
        }
        return false;
    }

}

?>
