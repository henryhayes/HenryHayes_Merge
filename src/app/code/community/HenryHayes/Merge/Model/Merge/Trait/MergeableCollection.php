<?php
trait HenryHayes_Merge_Model_Merge_Trait_MergeableCollection
{
   
    /**
     * The collection of model entities to merge into the entity to keep.
     * 
     * @var Varien_Data_Collection_Db
     */
    protected $_mergableCollection;
    
    /**
     * Sets the mergeable collection from ID array.
     * 
     * @param array $mergeableArray
     * @return $this
     */
    public function setMergeableCollectionFromIdArray(array $mergeableArray)
    {
        if (false == ($this->_mergableCollection instanceof Varien_Data_Collection_Db)) {
            
            $collection = Mage::getModel($this->_entityName)->getCollection()
                ->addFieldToFilter($this->getEntity()->getIdFieldName(), ['in' => $mergeableArray]);
            
            $this->setMergeableCollection($collection);
        }
        
        return $this;
    }
    
    /**
     * Sets the mergeable collection.
     * 
     * @param Varien_Data_Collection_Db $mergeableCollection
     * @return $this
     */
    public function setMergeableCollection(Varien_Data_Collection_Db $mergeableCollection)
    {
        $this->_mergableCollection = $mergeableCollection;
        
        return $this;
    }
    
    /**
     * Gets the collection to merge or 
     * 
     * @return Varien_Data_Collection_Db
     */
    public function getMergeableCollection()
    {
        return $this->_mergableCollection;
    }
}
