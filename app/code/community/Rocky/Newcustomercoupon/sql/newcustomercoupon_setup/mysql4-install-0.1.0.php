<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<?php
$this->startSetup();
$this->run("
-- DROP TABLE IF EXISTS {$this->getTable('newcustomercoupon/coupon')};
CREATE TABLE IF NOT EXISTS {$this->getTable('newcustomercoupon/coupon')} (
  `coupon_id` int(10) unsigned NOT NULL auto_increment,
  `coupon` varchar(100) NOT NULL default '',
  `customer_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`coupon_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Newcustomercoupon Coupon' AUTO_INCREMENT=1 ;
    ");
$this->endSetup();
?>