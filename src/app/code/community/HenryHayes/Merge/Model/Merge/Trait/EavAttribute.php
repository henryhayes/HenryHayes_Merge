<?php
trait HenryHayes_Merge_Model_Merge_Trait_EavAttribute
{
    use HenryHayes_Merge_Model_Merge_Trait_EavEntityType;
    
    /**
     * 
     * @param string $attributeCode
     * @return \Mage_Eav_Model_Attribute
     */
    public function getEavAttribute($attributeCode)
    {
        /** @var Mage_Eav_Model_Config $config */
        $config = Mage::getSingleton('eav/config');
        
        return $config->getAttribute($this->getEntityType(), $attributeCode);
    }
    
    /**
     * 
     * @param string $attributeCode
     * @return bool
     */
    public function checkEavAttributeExists($attributeCode)
    {
        $attributeModel = $this->getEavAttribute($attributeCode);
        // getEntityAttribute
        return ($attributeModel->getId());
    }
}

