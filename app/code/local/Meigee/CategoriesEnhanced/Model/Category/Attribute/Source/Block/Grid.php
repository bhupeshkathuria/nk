<?php
class Meigee_CategoriesEnhanced_Model_Category_Attribute_Source_Block_Grid extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{	
	public function getAllOptions()
	{
		if (!$this->_options)
		{
			$this->_options = array(
				array('value' => '',		'label' => 'Use Default Config'),
				array('value' => 'grid_large',		'label' => 'Large'),
		        array('value' => 'grid_standard',	'label' => 'Standard'),
                array('value' => 'grid_small',		'label' => 'Small')
			);
        }
		return $this->_options;
    }
}
