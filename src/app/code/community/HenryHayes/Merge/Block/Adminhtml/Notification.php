<?php
class HenryHayes_Merge_Block_Adminhtml_Notification extends Mage_Adminhtml_Block_Template
{
    public function hasError()
    {
        return (false === $this->_getMergeModel()->getPassedSelfTest());
    }
    
    /**
     * Gets messages as array.
     * 
     * @return array
     */
    public function getMessages()
    {
        return $this->_getMergeModel()->getMessages();
    }
    
    /**
     * Returns a boolean result of whether there are messages in the array.
     * 
     * @return bool
     */
    public function hasMessages()
    {
        return $this->_getMergeModel()->hasMessages();
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