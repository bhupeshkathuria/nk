<?php

/**
 * 
 *
 * @author Rocky
 * @copyright Copyright (c) 2013 Rocky.
 */
class Rocky_Newcustomercoupon_Model_Observer extends Mage_Core_Model_Abstract {

    
	
	public function customerSaveAfter($observer)
    {
        //Array of customer data
        $customerData = $observer->getCustomer()->getData();
			
			if (!$observer->getCustomer()->getOrigData()) {
            //customer is new, otherwise it's an edit
            $modelcollection = Mage::getModel('salesrule/rule')->getCollection();
            $newCollection =array();
            $newCollection = $modelcollection->getData();
			
			
			$connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
			$table1 = Mage::getSingleton('core/resource')->getTableName('cp_coupon');
			$query1 = "SELECT coupon FROM ".$table1."";
			$result1 = $connect->query( $query1 );
		    $couponCode1 =  $result1->fetchAll();
			
			$table = Mage::getSingleton('core/resource')->getTableName('salesrule_coupon');
			$query = "SELECT coupon_id,rule_id,code FROM ".$table." where code not in(SELECT coupon FROM ".$table1.") ORDER BY coupon_id LIMIT 1";

			$result = $connect->query( $query );
		    $couponCode =  $result->fetch();
			if($couponCode['code']){
			$insQuery = "INSERT INTO ".$table1."(`coupon_id`,`coupon`,`customer_id`) VALUES('".$couponCode['coupon_id']."','".$couponCode['code']."', '".$customerData['entity_id']."')";
			$connect->query( $insQuery );
            $ruleName = $newCollection[0]['name'];
            $rule_id=   $newCollection[0]['rule_id'];
            $promocode = $couponCode['code'];
       

           // if($rule_id==1 && $ruleName=="RegisteredUserPromocode"){ // if in case not required remove this condition ...

                $emailTemplate  = Mage::getModel('core/email_template')
                    ->loadDefault('notify_new_customer1');

                $emailTemplate
                    ->setSenderName('NetaKart')
                    ->setSenderEmail('support@netakart.com')
                    ->setTemplateSubject('Promo Code');
                // $data = $observer->getCustomer()->getData(); */
                $emailTemplateVariables = array();

                $emailTemplateVariables['username']= $customerData['firstname'].' '.$customerData['lastname'];
                $emailTemplateVariables['customer_email']   = $customerData['email'];
                $emailTemplateVariables['promo_code'] = $promocode;
                $emailTemplate->send($customerData['email'],$customerData['firstname'].' '.$customerData['lastname'], $emailTemplateVariables);
            //} 
        }
		
		}
    }
	
	
	/*
    public function newCustomerCouponGenerator($observer) {

        $customer = $observer->getEvent()->getCustomer();
        if (Mage::getStoreConfig('newcustomercoupon/general/enable')) {
            $id = (int) Mage::getStoreConfig('newcustomercoupon/general/ruleid');
            $rule = Mage::getModel('salesrule/rule')->load($id);
            if ($rule->getId()) {
                $generator = $rule->getCouponMassGenerator();
                $data = array(
                    'rule_id' => $rule->getId(),
                    'length' => Mage::getStoreConfig('promo/auto_generated_coupon_codes/length'),
                    'format' => is_numeric(Mage::getStoreConfig('promo/auto_generated_coupon_codes/format')) ? 'alphanum' : Mage::getStoreConfig('promo/auto_generated_coupon_codes/format'),
                    'prefix' => Mage::getStoreConfig('promo/auto_generated_coupon_codes/prefix'),
                    'suffix' => Mage::getStoreConfig('promo/auto_generated_coupon_codes/suffix'),
                    'dash' => Mage::getStoreConfig('promo/auto_generated_coupon_codes/dash'),
                    'qty' => 1
                );
                $generator->setData($data);
                $maxAttempts = Mage_SalesRule_Model_Coupon_Massgenerator::MAX_GENERATE_ATTEMPTS;
                $attempt = 0;
                do {
                    if ($attempt >= $maxAttempts) {
                        Mage::throwException(Mage::helper('salesrule')->__('Unable to create requested Coupon Qty. Please check settings and try again.'));
                    }
                    $code = $generator->generateCode();
                    $attempt++;
                } while ($generator->getResource()->exists($code));
            }
        }
        if ($code) {

            $expirationDate = $generator->getToDate();
            if ($expirationDate instanceof Zend_Date) {
                $expirationDate = $expirationDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
            }
            $now = $generator->getResource()->formatDate(
                    Mage::getSingleton('core/date')->gmtTimestamp()
            );
            try {
                $coupon = Mage::getModel('salesrule/coupon');
                $coupon->setId(null)
                        ->setRuleId($generator->getRuleId())
                        ->setUsageLimit($generator->getUsesPerCoupon())
                        ->setUsagePerCustomer($generator->getUsesPerCustomer())
                        ->setExpirationDate($expirationDate)
                        ->setCreatedAt($now)
                        ->setType(Mage_SalesRule_Helper_Coupon::COUPON_TYPE_SPECIFIC_AUTOGENERATED)
                        ->setCode($code)
                        ->save();
                
                $customerCoupon = Mage::getModel('newcustomercoupon/coupon');
                $customerCoupon->setId(null)
                        ->setCoupon($code)
                        ->setCustomerId($customer->getId())
                        ->save();
            } catch (Exception $e) {
                
            }
        }
    }
    */
    public function afterOutput($observer) {

        $block = $observer->getEvent()->getBlock();
        $transport = $observer->getEvent()->getTransport();
        if (empty($transport)) {
            return $this;
        }
        if ($block->getBlockAlias() == 'top' && $block->getChild('newcustomercoupon_info')) {
            $html = $transport->getHtml();
            $st_html = $block->getChildHtml('newcustomercoupon_info');

            if (strpos($html, $st_html) === false) {
                $html = $st_html . $html;
            }

            $transport->setHtml($html);
        }

        return $this;
    }

}

?>
