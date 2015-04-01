<?php
class Clay_Operations_Block_Order extends Clay_Courier_Block_Abstract{
	/**
     * Return month's totals
     *
     * @return mixed
     */
    public function getOrders()
    {
        $item = $this->_prepareCollection();
        $total = $item->getTotalIncomeAmount() - $item->getTotalRefundedAmount();
        return (string)Mage::helper('core')->currency($total, true, false);
    }
 
    /**
     * Get report month's amount totals
     * @return mixed
     */
    protected function _prepareCollection()
    {
        $aggregatedColumns = array('total_income_amount'=>'sum(total_income_amount)',
            'total_refunded_amount'=>'sum(total_refunded_amount)');
 
        $totalsCollection = Mage::getResourceModel('sales/report_order_collection')
            ->setPeriod('month')
            ->setDateRange($this->getStartMonth(), $this->getCurrentDate())
            ->addStoreFilter(1)
            ->setAggregatedColumns($aggregatedColumns)
            ->addOrderStatusFilter(null)
            ->isTotals(true);
 
        foreach ($totalsCollection as $item) {
            return $item;
            break;
        }
    }
 
    /**
     * Return current date
     *
     * @return string
     */
     
/**
     * Return current day
     *
     * @return string
     */
public function getCurrentDate()
    {
        $date = date('Y-m-d');
        return (string)$date;
    }
 
    /**
     * Return first day for current date
     *
     * @return string
     */
    public function getStartMonth()
    {
        $startCurrentMonth = date('Y').'-'.date('m').'-01';
        return (string)$startCurrentMonth;
    }
}