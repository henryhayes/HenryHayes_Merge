<?php
class HenryHayes_Merge_Adminhtml_MergeController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Is allowed.
     * 
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/actions/merge');
    }

    public function indexAction()
    {
        die('merge controller allowed.');
    }
    
    public function customerAction()
    {
        $customersIds = $this->getRequest()->getParam('customer');
        
        if (!is_array($customersIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select customer(s).'));
        } else {
            try {
                foreach ($customersIds as $customerId) {
                    
                    $customer = Mage::getModel('customer/customer')->load($customerId);
                    $customer->setSectorId($this->getRequest()->getParam('sector_id'));
                    $customer->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were updated.', count($customersIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        
        $this->_redirect('adminhtml/customer/');
    }
}