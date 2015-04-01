<?php
class Chand_Skiping_Block_Onepage_Review extends Mage_Checkout_Block_Onepage_Abstract
{
    protected function _construct()
    {
        $this->getCheckout()->setStepData('review', array(
            'label'     => Mage::helper('checkout')->__('Order Review'),
            'is_show'   => $this->isShow()
        ));
        parent::_construct();

        $this->getQuote()->collectTotals()->save();
    }
public function isShow()
    {
        return false;
    }
}

?>