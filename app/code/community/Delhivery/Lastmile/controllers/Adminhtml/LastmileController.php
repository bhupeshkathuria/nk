<?php
/**
 * Delhivery
 * @category   Delhivery
 * @package    Delhivery_Lastmile
 * @copyright  Copyright (c) 2014 Delhivery. (http://www.delhivery.com)
 * @license    Creative Commons Licence (CCL)
 * @purpose    Waybills admin controller actions   
 */
class Delhivery_Lastmile_Adminhtml_LastmileController extends Mage_Adminhtml_Controller_Action {

    /**
     * Y coordinate
     *
     * @var int
     */
    public $y;

    /**
     * Zend PDF object
     *
     * @var Zend_Pdf
     */
    protected $_pdf;
     /**
     * Init Action to specify active menu and breadcrumb of the module
     */
    protected function _initAction() {
	     $this->loadLayout()
                ->_setActiveMenu('lastmile/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }
     /**
     * Function to render waybill layout block
     */
    public function indexAction() {
        $this->_initAction()
                ->renderLayout();
    }
     /**
     * Loads default grid view of admin module
     */ 	
	public function gridAction()
	{
	$this->loadLayout();
	$this->getResponse()->setBody(
	$this->getLayout()->createBlock('lastmile/adminhtml_lastmile_grid')->toHtml());
	}
     /**
     * Function to fetch more waybills for the current client
     */
	public function fetchAction() {
		$model = Mage::getModel('lastmile/lastmile');
		$unsed = $model->countUnsed();
		if($unsed > 50){
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('lastmile')->__('More than 50 AWB still available to use'));
			$this->_redirect('*/*/');
		}
		else{
		$apiurl =  Mage::getStoreConfig('carriers/dlastmile/awb_url');
		$cl     =  Mage::getStoreConfig('carriers/dlastmile/clientid');
		$token = Mage::getStoreConfig('carriers/dlastmile/licensekey');
		if($apiurl && $token && $cl)
		{
			$path = $apiurl.'json/?token='.$token.'&count=50&cl='.urlencode($cl);
			mage::log($path);	
			$retValue = Mage::helper('lastmile')->Executecurl($path,'','');
			$codes = json_decode($retValue);
			mage::log($codes);
			$awbs = explode(',',$codes);
			mage::log($awbs);
			if(sizeof($awbs))
			{	
				foreach ($awbs as $awb) {		   
				   $data = array();
				   $data['awb'] = $awb;
				   $data['state'] = 2;
				   $model->setData($data);
				   $model->save();  
				}
			}
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('lastmile')->__('AWB Downloaded Successfully'));
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('lastmile')->__('Please add valid License Key, Client ID and Gateway URL in plugin configuration'));
		}		
		$this->_redirect('*/*/');
		}
    }
     /**
     * Function to Update current waybill status from Delhivery Server
     */		
	public function syncstatusAction() {
		$model = Mage::getModel('lastmile/lastmile');
		$apiurl =  Mage::getStoreConfig('carriers/dlastmile/gateway_url');
		$token =   Mage::getStoreConfig('carriers/dlastmile/licensekey');		
		if($apiurl && $token)
		{
			$waybills = $model->findAwbToUpdate();
			if(count($waybills)){ //No update to perform if count is zero
				$awbs = '';
				foreach($waybills as $waybill){
					if(is_array($waybill)){
					   $awbs .= $waybill['awb'].',';        
					}
				}
				mage::log("Status Updated for these waybills $awbs");
				$path = $apiurl.'api/packages/json/?verbose=0&token='.$token.'&waybill='.$awbs;	
				$retValue = Mage::helper('lastmile')->Executecurl($path,'','');
				$statusupdates = json_decode($retValue);
				foreach ($statusupdates->ShipmentData as $item) {			   		   
				   $lmawb = Mage::getModel('lastmile/lastmile')->loadByAwb($item->Shipment->AWB);
				   $model = Mage::getModel('lastmile/lastmile');
				   $data = array();
				   $data['awb'] = $item->Shipment->AWB;
				   $data['status'] = preg_replace('/\s+/', '', $item->Shipment->Status->Status);		   
				   $model->setData($data)->setId($lmawb)->save();	
				}
			}
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('lastmile')->__(count($waybills).' Waybill(s) Updated Successfully'));
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('lastmile')->__('Please add valid License Key and Gateway URL in plugin configuration'));
		}		
		$this->_redirect('*/*/');
    }	
     /**
     * Function to submit manifest about new waybills to Delhivery Server
     */		
	public function manifestAction() {
		$model = Mage::getModel('lastmile/lastmile');		
        $waybills = $this->getRequest()->getParam('lastmile');
		mage::log("Manifest submitted for these waybills $waybills");		
		$token = Mage::getStoreConfig('carriers/dlastmile/licensekey');
		$clientid = Mage::getStoreConfig('carriers/dlastmile/clientid');
		$url =  Mage::getStoreConfig('carriers/dlastmile/gateway_url');
		if($clientid && $token && $url)
		{
		$token = "$token"; // replace this with your token key
		$url .= "cmu/push/json/?token=".$token;
		mage::log($url);
		$succsscount = 0;
		$failcount = 0;
		$msg = '';
		foreach ($waybills as $waybill) {
				$model = Mage::getModel('lastmile/lastmile')->load($waybill);
                if($model->status == 'Assigned'): // Submit only if status is assigned
				mage::log("Submitting Only Assigned");
				$order = Mage::getModel('sales/order')->load($model->orderid);
				$address = Mage::getModel('sales/order_address')->load($order->shipping_address_id);				 
				$products = $order->getAllItems();
				$params = array(); // this will contain request meta and the package feed
				$package_data = array(); // package data feed
				$shipments = array();
				$methodcode = ($order->getPayment()->getMethodInstance()->getCode() == 'cashondelivery' ) ? "COD" :"Pre-Paid";
				$codamount = ($order->getPayment()->getMethodInstance()->getCode() == 'cashondelivery' ) ? $order->getGrandTotal() : "00.00";
				$ordered_items = $order->getAllItems();
				foreach($ordered_items as $item){
				$item_desc .= $item->getName(); 
				}
				/////////////start: building the package feed/////////////////////
				$shipment = array();
				$shipment['client'] = $clientid;
				$shipment['name'] = $address->getName(); // consignee name
				$shipment['order'] = $order->increment_id; // client order number
				$shipment['products_desc'] = $item_desc;
				$shipment['order_date'] = $order->updated_at; // ISO Format
				$shipment['payment_mode'] = $methodcode;
				$shipment['total_amount'] = $order->getGrandTotal(); // in INR
				$shipment['cod_amount'] = $codamount; // amount to be collected, required for COD
				$shipment['add'] = $address->getStreetFull(); // consignee address
				$shipment['city'] = $address->getCity();
				if($address->getRegion())
				$shipment['state'] = $address->getRegion();
				$shipment['waybill'] = $model->awb;
				$shipment['country'] = $address->getCountry();
				if($address->getTelephone())
				$shipment['phone'] = $address->getTelephone();
				$shipment['pin'] = $address->getPostcode();
				
				$shipments = array($shipment);
				
				$package_data['shipments'] = $shipments;
				mage::log($package_data);
				/////////////end: building the package feed/////////////////////
				$params['format'] = 'json';
				$params['data'] = json_encode($package_data);
				mage::log($url);
				$result = Mage::helper('lastmile')->Executecurl($url,'post',$params);
				$result = json_decode($result);
				mage::log($result);
				if($result->success):
					$model->setData('status','InTransit')->save();
					$msg .= "$model->awb Submitted Successfully<br />";
					$succsscount++;	
				else:
					$msg .= "$model->awb Failed to submit. Remark: ".$result->packages[0]->remarks."<br />";
					$failcount++;
				endif;
				else:
					$msg .= "$model->awb already submitted<br />";
				endif;
        }

        $msg .= "<br />$succsscount Waybills submited successfully. $failcount Waybills Failed";
        Mage::getSingleton('adminhtml/session')->addSuccess($msg);
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('lastmile')->__('Please add valid License Key and Gateway URL in plugin configuration'));
		}		
		$this->_redirect('*/*/index');
	}
     /**
     * Function to export grid rows in CSV format
     */
    public function exportCsvAction() {
        $fileName = 'lastmile.csv';
        $content = $this->getLayout()->createBlock('lastmile/adminhtml_lastmile_grid')
                        ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }
     /**
     * Function to export grid rows in XML format
     */
    public function exportXmlAction() {
        $fileName = 'lastmile.xml';
        $content = $this->getLayout()->createBlock('lastmile/adminhtml_lastmile_grid')
                        ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }
     /**
     * Function to get next unsed waybill for a client and send it to ajax request
     */
	public function getAWBAction()
       {
		  $msg = '';
		  $post = $this->getRequest();
		  
			if (empty($post)) {
				$msg = "PinCode is not serviceable by Delhivery.";
			}
			else
			{
				$zipcode = $post->getParam('zipcode');
				$orderid = $post->getParam('orderid');
				$order = Mage::getModel('sales/order')->load($orderid);
				$payment_method_code = $order->getPayment()->getMethodInstance()->getCode();				
				try{				
				$resourceid = Mage::getModel('lastmile/pincode')->loadByPin($zipcode);
				$zipdeatils = Mage::getModel('lastmile/pincode')->load($resourceid);
				
				if((!$resourceid ) || ( $zipdeatils->pre_paid != 'Y') || ($payment_method_code == 'cashondelivery' && $zipdeatils->cod != 'Y'))
				   $msg = "Order PinCode is not serviceable by Delhivery.";
				else
				{
					$tableName = Mage::getSingleton('core/resource')->getTableName('lastmile/lastmile');
					$query="SELECT * FROM $tableName WHERE state = 2 ORDER BY lastmile_id ASC LIMIT 1";
					mage::log($query);
					mage::log("Zipcode servicesable");
					$data  = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($query);
					mage::log($data);					
					if(!count($data) || $data[0]['awb'] == '')
					{
						$msg = 'AWB number is not available. Please download more AWB';
					}
					else
					{
						$query="UPDATE $tableName SET state = 3  WHERE lastmile_id = ".$data[0]['lastmile_id'];
						Mage::getSingleton('core/resource')->getConnection('core_write')->query($query);
					}
				}
				}
				catch (Exception $e) 
				{
					$msg = $e->getMessage();
				}
			}
			if($msg)
			{
				$data = array();
				$data[0]['awb'] = $msg;
				mage::log("AWB Found and sending via ajax");
			}
			  $output = array();
			  $output['resp'] = $data;
			  $json = json_encode($output);
			  $this->getResponse()
				   ->clearHeaders()
				   ->setHeader('Content-Type', 'application/json')
				   ->setBody($json);
	}	
     /**
     * Function to set temp waybill status at the time of adding tracking
     */
	  public function setAWBStatusAction()
	  {
		 $tableName = Mage::getSingleton('core/resource')->getTableName('lastmile/lastmile');
		 try{
			 $query="UPDATE $tableName SET state = 2  WHERE state = 3";
			 Mage::getSingleton('core/resource')->getConnection('core_write')->query($query);
			}
			catch (Exception $e) 
			{
					$msg = $e->getMessage();
			}				 
	  }
	  
     /**
     * Function to print shipping label for selected waybills
     */	  
	public function shippinglabelAction(){
		   
	$waybills = $this->getRequest()->getParam('lastmile');
	
	mage::log("Shipping Label Printed for these waybills $waybills");
	$flag = false;
	if (!empty($waybills)) {
		$labelperpage = 1;
		$totalpages = sizeof($waybills)/$labelperpage;   			
        $pdf = new Zend_Pdf();
        $style = new Zend_Pdf_Style();		
		for ($page_index = 0; $page_index <= $totalpages; $page_index++)
        {
			$page = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4);
			$pdf->pages[] = $page;
		}
		$pagecounter = 0;
		$i=0; $y=830;
		foreach ($waybills as $waybill) {			
			$awb = Mage::getModel('lastmile/lastmile')->load($waybill);
			if($awb->state==2)
			continue;
			$i++;
			// check if next page;
			if($i%$labelperpage == 0)
			{
			$pagecounter++; // Set to use new page
			$y = 830; // Set position for first label on new page
			}
			//$pdf->pages[$pagecounter];
			$shipments = Mage::getResourceModel('sales/order_shipment_collection')->setOrderFilter($awb->orderid)->load();
			if ($shipments->getSize()) {
				$flag = true;
				//$pdf = $this->getPdf($awb->awb,$shipment);
				foreach ($shipments as $shipment) {
 					Mage::getModel('lastmile/shippinglabel')->getContent($pdf->pages[$pagecounter], $shipment->getStore(), $awb->awb, $shipment->getOrder(),$y);
				}			
				
			}
			// Set position for the next label on same page
			$y = $y-190;
						
		}
		if ($flag) {
			return $this->_prepareDownloadResponse(
				'shippinglabel'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(),
				'application/pdf'
			);
		} else {
			$this->_getSession()->addError($this->__('There are no printable shipping labels related to selected waybills.'));
			$this->_redirect('*/*/');
		}
	}
	$this->_redirect('*/*/');
}
    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
		
}