<?php
trait HenryHayes_Merge_Model_Merge_Trait_EavEntityType
{
    use HenryHayes_Merge_Model_Merge_Trait_Entity;
    
   /**
    * Stores Mage_Eav_Model_Entity_Type object, set by setter from $_entityTypeCode if not set.
    * 
    * @var Mage_Eav_Model_Entity_Type
    */
   protected $_entityType;

    /**
     * Sets the $this->_entityType property to Mage_Eav_Model_Entity_Type instance.
     *
     * @param \Mage_Eav_Model_Entity_Type $entityType
     * @return $this
     */
    public function setEntityType(\Mage_Eav_Model_Entity_Type $entityType)
    {
        
        $this->addMessage('Setting: ' . get_class($entityType));
        
        $this->_entityType = $entityType;

        return $this;
    }

    /**
     * Retrieve current Mage_Eav_Model_Entity_Type object.
     *
     * @see https://www.php.net/manual/en/language.oop5.traits.php#language.oop5.traits.properties
     * @throws \Mage_Eav_Exception
     * @return \Mage_Eav_Model_Entity_Type
     */
    public function getEntityType()
    {
        if (false == ($this->_entityType instanceof \Mage_Eav_Model_Entity_Type) && $this->getEntity()) {

            /** @var Mage_Eav_Model_Config $config */
            $config = Mage::getSingleton('eav/config');
            
            $this->_entityType = $config->getEntityType($this->getEntity()->getEntityType());
        }
        
        if (false == ($this->_entityType instanceof \Mage_Eav_Model_Entity_Type)) {
            
            $message = sprintf(
                "Must be set before it can be retrieved using '%s'. Entity type found was '%s'.",
                __METHOD__,
                get_class($this->_entityType)
            );
            throw new \Mage_Eav_Exception($message);
        }
        
        return $this->_entityType;
    }
}

