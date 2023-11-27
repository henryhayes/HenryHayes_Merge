<?php 
class HenryHayes_Merge_Model_Merge_Customer extends HenryHayes_Merge_Model_Merge_Abstract
{
    /**
     * This is the service locator name for the entity we're expecting.
     * 
     * @var string
     */
    protected $_entityName = 'customer/customer';
    
    public function _mergeAddresses()
    {
        $entity = $this->getEntity();
        $mergeableCollection = $this->getMergeableCollection();
        
        /** @var Mage_Customer_Model_Customer $toMerge */
        foreach ($mergeableCollection as $toMerge) {
            
            /** @var Mage_Customer_Model_Address $toMergeAddress */
            foreach ($toMerge->getAddresses() as $toMergeAddress) {
                
                $entity->addAddress($this->_duplicateCustomerAddress($toMergeAddress));
            }
        }
        
        return $this;
    }
    
    /**
     * This will be improved to use the Mage_Sales_Model_Quote::merge method.
     * 
     * @see https://magento.stackexchange.com/questions/84117/programmatically-assign-customer-to-a-quote
     * @See https://magento.stackexchange.com/questions/14056/how-quotes-work-in-magento
     * @todo properly merge quotes using Mage_Sales_Model_Quote::merge
     * @param Mage_Core_Model_Abstract $customer
     * @param array $mergeIds
     * @return $this
     */
    protected function _mergeSalesQuotes(Mage_Core_Model_Abstract $customer, array $mergeIds)
    {
        return $this;
    }
    
    /**
     * Makes a copy of the customer's address object.
     * 
     * @param Mage_Customer_Model_Address $address
     * @return type
     */
    protected function _duplicateCustomerAddress(Mage_Customer_Model_Address $address)
    {
        $new = Mage::getModel('customer/address');
        
        $this->getCoreHelper()->copyFieldset('customer_address', 'to_customer_address', $address, $new);
        
        return $new;
    }


    /**
     * Assigns the new new customer to all sales orders.
     * 
     * @param Mage_Core_Model_Abstract $customer
     * @param array $mergeIds
     * @return $this
     */
    protected function _mergeSalesOrders(Mage_Core_Model_Abstract $customer, array $mergeIds)
    {
        $orderCollection = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('customer_id', array('in' => $mergeIds));

        foreach($orderCollection as $order) {

            /** @var Mage_Sales_Model_Order $order */
            $order->setCustomerId($customer->getId());
            $order->setCustomerEmail($customer->getEmail());
            $order->setCustomerGroupId($customer->getGroupId());
            
            // Billing address
            
            if ($order->hasBillingAddressId()) {
                
                /** @var Mage_Sales_Model_Order_Address $billing */
                $billing = $order->getBillingAddress();
                $billing->setCustomerId($customer->getId());
                    
                $this->getTransactionHandler()->addObject($billing);
            }
            
            // Shipping address
            
            if ($order->hasShippingAddressId()) {
                
                /** @var Mage_Sales_Model_Order_Address $shipping */
                $shipping = $order->getShippingAddress();
                $shipping->setCustomerId($customer->getId());
                    
                $this->getTransactionHandler()->addObject($shipping);
            }
            
            // Shipments
            
            if ($order->hasShipments()) {
                foreach ($order->getShipmentsCollection() as $shipment) {
                    /** @var Mage_Sales_Model_Order_Shipment $shipment */
                    $shipment->setCustomerId($customer->getId());
                    
                    $this->getTransactionHandler()->addObject($shipment);
                }
            }
            
            $this->getTransactionHandler()->addObject($order);
            
            // Grid record.
            
            if ($grid = Mage::getModel('sales/order_grid')->load($order->getId())) {
                
                $grid->setCustomerId($customer->getId());
                $this->getTransactionHandler()->addObject($grid);
            }
        }
        
        return $this;
    }
}
