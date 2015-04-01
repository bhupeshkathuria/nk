<?php

class ES_ActiveReviewStars_Block_Review_Form extends Mage_Review_Block_Form {

  public function __construct() {
    parent::__construct();
    
    if(Mage::helper('ActiveReviewStars')->isEnabled()) {
      $this->setTemplate('es_active_review_stars/review/form.phtml');
    }
  }
  
}