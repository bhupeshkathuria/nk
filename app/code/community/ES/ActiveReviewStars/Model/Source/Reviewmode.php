<?php

class ES_ActiveReviewStars_Model_Source_Reviewmode {

  public function toOptionArray() {
    return array(
        array('value' => 1, 'label' => Mage::helper('ActiveReviewStars')->__('Full Mode')),
        array('value' => 2, 'label' => Mage::helper('ActiveReviewStars')->__('Single Mode')),
    );
  }

}