<?php
/*
* Copyright (c) 2014 www.magebuzz.com
*/
class Magebuzz_Testimonial_Block_Adminhtml_Testimonial_Renderer_Image extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{


public function render(Varien_Object $row)
    {
        return $this->_getValue($row);
    }
   protected function _getValue(Varien_Object $row) {
       
            $html = '<img ';
            $html .= 'id="' . $this->getColumn()->getId() . '" ';
            $html .= 'width="80" ';
            $html .= 'height="80" ';
            $html .= 'src="' . Mage::getBaseUrl("media") .'magebuzz/avatar/' . $row->getData($this->getColumn()->getIndex()) . '"';
            $html .= 'class="grid-image ' . $this->getColumn()->getInlineCss() . '"/>';
   
        

        return $html;
    }
}