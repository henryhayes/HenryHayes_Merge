<?php
trait HenryHayes_Merge_Model_Merge_Trait_Entity
{
    /**
     * The entity everything will merge into.
     * 
     * @var Mage_Core_Model_Abstract
     */
    protected $_entity;
    
    /**
     * Sets the entity that we intend to merge all other records into.
     * 
     * @param Mage_Core_Model_Abstract $entity
     * @return $this;
     */
    final public function setEntity(Mage_Core_Model_Abstract $entity)
    {
        $this->_entity = $entity;
        
        return $this;
    }
    
    /**
     * Sets the entity that we intend to merge all other recods into.
     * 
     * @param Mage_Core_Model_Abstract $entity
     * @return Mage_Core_Model_Abstract
     */
    final public function getEntity()
    {
        if (false == ($this->_entity instanceof Mage_Core_Model_Abstract)) {
            
            $message = sprintf("Method '%s' cannot be called without property being set.", __METHOD__);
            Mage::throwException($message);
        }
        
        return $this->_entity;
    }
}

