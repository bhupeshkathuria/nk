<?php 
/**
 * Magento
 *
 * @author    Meigeeteam http://www.meaigeeteam.com <nick@meaigeeteam.com>
 * @copyright Copyright (C) 2010 - 2012 Meigeeteam
 *
 */
class Meigee_ThemeOptionsBlacknwhite_Model_Grid
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'grid_large', 'label'=>Mage::helper('ThemeOptionsBlacknwhite')->__('Large')),
            array('value'=>'grid_standard', 'label'=>Mage::helper('ThemeOptionsBlacknwhite')->__('Standard')),
            array('value'=>'grid_small', 'label'=>Mage::helper('ThemeOptionsBlacknwhite')->__('Small'))          
        );
    }

}