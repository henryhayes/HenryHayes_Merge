<?php 
class HenryHayes_Merge_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Is allowed.
     * 
     * @return bool
     */
    public function isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/actions/merge');
    }
    
    /**
     * If the merge model passed the self test, we're good to go!
     * 
     * @return type
     */
    public function isAvailable()
    {
        return $this->_getMergeModel()->getPassedSelfTest();
    }
    
    /**
     * Gets the merge model.
     * 
     * @return HenryHayes_Merge_Model_Merge_Customer
     */
    protected function _getMergeModel()
    {
        return Mage::getSingleton('merge/merge_customer');
    }
}