<?php
/**
 * 
 *
 * @author Rocky
 * @copyright Copyright (c) 2013 Rocky.
 */
class Rocky_Newcustomercoupon_Helper_Data extends Mage_Core_Helper_Abstract {

    //put your code here

    public function validatorCoupon($rule,$code) {
        $coupon = Mage::getModel('salesrule/coupon');
        $coupon->load($code, 'code');
        if ($coupon->getId()) {
            if ($coupon->getUsageLimit() && $coupon->getTimesUsed() >= $coupon->getUsageLimit()) {
                return false;
            }
            $customerId = $this->getSession()->getCustomerId();
            if ($customerId && $coupon->getUsagePerCustomer()) {
                $couponUsage = new Varien_Object();
                Mage::getResourceModel('salesrule/coupon_usage')->loadByCustomerCoupon(
                        $couponUsage, $customerId, $coupon->getId());
                if ($couponUsage->getCouponId() &&
                        $couponUsage->getTimesUsed() >= $coupon->getUsagePerCustomer()
                ) {
                    return false;
                }
            }
        }
        
        $ruleId = $rule->getId();
        if ($ruleId && $rule->getUsesPerCustomer()) {
            $customerId     = $this->getSession()->getCustomerId();
            $ruleCustomer   = Mage::getModel('salesrule/rule_customer');
            $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
            if ($ruleCustomer->getId()) {
                if ($ruleCustomer->getTimesUsed() >= $rule->getUsesPerCustomer()) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    public function getSession(){
        return Mage::getSingleton('customer/session');
    }

}

?>
