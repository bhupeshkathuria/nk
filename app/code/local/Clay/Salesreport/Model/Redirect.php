<?php
class Clay_Salesreport_Model_Redirect extends Mage_Core_Model_Abstract {
    public function checkCourierInfo( $username, $password ) {
        $connect = Mage::getSingleton( 'core/resource' )->getConnection('core_read');
        $query = "
            SELECT id
            FROM " . Mage::getSingleton('core/resource')->getTableName('salesreport_user') . "
            WHERE (username='$username') and (password='$password')
        ";
		
        $result = $connect->query($query);
        $id = $result->fetch();
        return $id;
    }
}