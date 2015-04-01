<?php

class ES_ActiveReviewStars_Helper_Data extends Mage_Core_Helper_Abstract {

  /**
   * Check does active review rating enabled
   * 
   * @return boolean
   */
  public function isEnabled() {
    return Mage::getStoreConfig('ActiveReviewStars/general/enabled');
  }

  /**
   * Get active review star mode
   * 
   * @return int (1 - All selected ratings in table (full mode), 2 - Show only dinamic star icons without labels (single mode) (if selected only one rate parameter))
   */
  public function getReviewStarMode() {
    $adminValue = Mage::getStoreConfig('ActiveReviewStars/general/review_star_mode');

    // Check does exist more then on rating enabled
    if ($this->getRatings() && count($this->getRatings()) > 1) {
      return 1;
    }

    return $adminValue;
  }

  /**
   * Show or hide rating label, only works in single mode
   * 
   * @return boolean
   */
  public function showRatingLabel() {
    if ($this->getReviewStarMode() == 2) {
      return Mage::getStoreConfig('ActiveReviewStars/general/show_label');
    }
    return true;
  }

  /**
   * Get enabled ratings for review
   * 
   * @return type
   */
  protected function getRatings() {
    $ratingCollection = Mage::getModel('rating/rating')
            ->getResourceCollection()
            ->addEntityFilter('product')
            ->setPositionOrder()
            ->addRatingPerStoreName(Mage::app()->getStore()->getId())
            ->setStoreFilter(Mage::app()->getStore()->getId())
            ->load()
            ->addOptionToItems();
    return $ratingCollection;
  }

}