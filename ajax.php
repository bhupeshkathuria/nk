<?php
require_once('./app/Mage.php'); // ABSOLUTH PATH TO MAGE
umask(0);
Mage::app ();

Mage::getSingleton('core/session', array('name'=>'frontend'));   // GET THE SESSION

$simbol= Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol();  // GET THE CURRENCY SIMBOL
$store=Mage::app()->getStore()->getCode();  // GET THE STORE CODE

$cart = Mage::getSingleton('checkout/cart'); //->getItemsCount();   

$ajtem=$_REQUEST['sku'];    // THIS IS THE ITEM ID
$items = $cart->getItems();

foreach ($items as $item) {  

   if($item->getData('sku')==$ajtem){  // IS THIS THE ITEM WE ARE CHANGING? IF IT IS:
   	   $item->setQty($_REQUEST['qty']); // UPDATE ONLY THE QTY, NOTHING ELSE!
   	   $cart->save();  // SAVE
   	   Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
	   echo json_encode(array('price'=>number_format($item->getPriceInclTax() * $_REQUEST['qty'],0),'sub'=>number_format(Mage::getSingleton('checkout/session')->getQuote()->getSubtotal(),0),'grand'=>number_format(Mage::getSingleton('checkout/session')->getQuote()->getGrandTotal(),0)));
   	   break;
   }
	
}

// THE REST IS updatTotalG FUNCTION WHICH IS CALLED AFTER AJAX IS COMPLETED 
// (UPDATE THE TOTALS)
/*echo '<script type="text/javascript">';
echo 'function updateTotalG(){';
echo 'jQuery("#sveUkupno,#sveUkupno1").html(\'';
echo '<strong><span class="price">';
//echo 'JQuery(\'#sveUkupno\').html("<strong><span class="price">';
if($store=='default')  echo '<span class="WebRupee"> Rs. </span>';
echo number_format(Mage::getSingleton('checkout/session')->getQuote()->getGrandTotal(),2);
//echo $simbol . ' </span></strong>");';
if($store=='hr')  echo ' '.$simbol;
echo " </span></strong>');";
echo '}   </script>';*/