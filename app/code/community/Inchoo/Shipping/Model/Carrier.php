<?php

class Inchoo_Shipping_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{
    /**
     * Carrier's code, as defined in parent class
     *
     * @var string
     */
    protected $_code = 'inchoo_shipping';

    /**
     * Returns available shipping rates for Inchoo Shipping carrier
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        /** @var Mage_Shipping_Model_Rate_Result $result */
        $result = Mage::getModel('shipping/rate_result');

        /** @var Inchoo_Shipping_Helper_Data $expressMaxProducts */
        $expressMaxWeight = Mage::helper('inchoo_shipping')->getExpressMaxWeight();

        $expressAvailable = true;
        foreach ($request->getAllItems() as $item) {
            if ($item->getWeight() > $expressMaxWeight) {
                $expressAvailable = false;
            }
        }

        if ($expressAvailable) {
            $result->append($this->_getExpressRate());
        }
        $result->append($this->_getStandardRate());

        return $result;
    }

    /**
     * Returns Allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array(
            'standard'    =>  'Standard delivery',
            'express'     =>  'Express delivery',
        );
    }

    /**
     * Get Standard rate object
     *
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    protected function _getStandardRate()
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('large');
        $rate->setMethodTitle('Standard delivery');
        

        return $rate;
    }

    /**
     * Get Express rate object
     *
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    protected function _getExpressRate()
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->getConfigData('title'));
        $rate->setMethod('express');
        $rate->setMethodTitle('Express delivery');
        
        return $rate;
    }
    
    public function getTrackingInfo($tracking_number) {
        $tracking_result = $this->getTracking($tracking_number);

        if ($tracking_result instanceof Mage_Shipping_Model_Tracking_Result) {
            if ($trackings = $tracking_result->getAllTrackings()) {
                return $trackings[0];
            }
        } elseif (is_string($tracking_result) && !empty($tracking_result)) {
            return $tracking_result;
        }

        return false;
    }
    
    public function getTracking($tracking_number) {
        $tracking_result = Mage::getModel('shipping/tracking_result');

        $tracking_status = Mage::getModel('shipping/tracking_result_status');
        $tracking_status->setCarrier($this->_code);
        $tracking_status->setCarrierTitle($this->getConfigData('carrier_title'));
        $tracking_status->setTracking($tracking_number);
	
		
		
        $path = $this->getConfigData('dtdc_url') . '?userName='.$this->getConfigData('username').'&password='.$this->getConfigData('password').'&clientId='.$this->getConfigData('clientid').'&DOCNO='.$tracking_number;
	
		$xmlDoc = new DOMDocument();
		$xmlDoc->load($path);
		$x = $xmlDoc->documentElement;
		foreach ($x->childNodes AS $item) {
 			$status[] = $item->nodeValue;
		}
		
        $tracking_status->addData(
                array(
                    'status' => $status
                )
        );
        $tracking_result->append($tracking_status);

        return $tracking_result;
    }
}