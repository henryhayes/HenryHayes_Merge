<?php 
abstract class HenryHayes_Merge_Model_Merge_Abstract
{
    use HenryHayes_Merge_Model_Merge_Trait_Entity;
    use HenryHayes_Merge_Model_Merge_Trait_MergeableCollection;
    
    /**
     * Merge if destination is empty.
     */
   const  MERGE_CONDITION_EMPTY = 'empty';
   const  MERGE_CONDITION_FORCE = 'force';
   const  MERGE_CONDITION_APPEND = 'append';
   
   /**
    * Lookup array with valid merge conditions.
    * 
    * @var array
    */
   protected $_mergeConditions = [
       self::MERGE_CONDITION_EMPTY,
       self::MERGE_CONDITION_FORCE,
       self::MERGE_CONDITION_APPEND
   ];
   
   /**
    * This is the service locator name for the entity we're expecting.
    * 
    * @var string
    */
   protected $_entityName = 'customer/customer';
   
   /**
    * The <fieldsets>/<merge_entities>/<customer> path, for example.
    * 
    * @todo use the $_entityType->getEntityTypeCode() functionality for this purpose.
    * @var string
    */
   protected $_node = 'customer';
   
   /**
    * An array of messages.
    * 
    * If $this->_passedSelfTest == false, these will be likely be error messages.
    * 
    * @var array
    */
   protected $_messages = [];
   
   /**
    * Stores the boolean result of the self-test.
    * 
    * Defaults to false.
    * 
    * @var bool
    */
   protected $_passedSelfTest = false;
   
   /**
    * Stores a boolean of whether there is an error.
    * 
    * Defaults to false.
    * 
    * @var bool
    */
   protected $_isError = false;
    
    /**
     * Array of merge fields.
     * 
     * @var array
     */
    protected $_mergeFields = [];
    
    /**
     * Array of objects that have a customer_id field that have no special case.
     * 
     * @var array
     */
    protected $_modelEntities = [];
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_runSelfTest();
    }
    
    /**
     * Run the self test.
     * 
     * @return void
     */
    final protected function _runSelfTest()
    {
        $this->_passedSelfTest = true;
        
        try {
            $this->setEntity(Mage::getModel($this->_entityName));
            $this->getMergeFields();
            $this->getModelEntities();
            
        } catch (Mage_Core_Exception $e) {
            
            // Reset....
            $this->_passedSelfTest = false;
            $this->_modelEntities = [];
            $this->_mergeFields = [];
            
            $this->addMessage($e->getMessage());
        }
    }
    
    /**
     * Because this class is potentially destructive, it runs a self test at time of
     * instantiation. This method then returns the result of that test.
     * 
     * @return bool
     */
    public function getPassedSelfTest()
    {
        return $this->_passedSelfTest;
    }
    
    /**
     * Merges model with $mergeIds.
     * 
     * @param Mage_Core_Model_Abstract $modelEntity
     * @param array $mergeIds
     * 
     * @return $this
     */
    final public function merge(Mage_Core_Model_Abstract $modelEntity, array $mergeIds)
    {
        try {
            
            $this->setEntity($modelEntity);
        
            $mergeables = $this->_safetyCheck($modelEntity, $mergeIds);
            $this->setMergeableCollectionFromIdArray($mergeables);
            
            $this->_beforeMerge();
            
            $this->_merge();
            
            $this->_afterMerge();
            
            $this->getTransactionHandler()->save();
            
        } catch (Mage_Core_Exception $e) {
            
            $this->_isError = true;
            
            $this->addMessage($e->getMessage());
        }
        
        return $this;
    }
    
    protected function _beforeMerge()
    {
        return $this;
    }
    
    protected function _merge()
    {
        $this->_mergeModelEntities();
        $this->_mergeFields();
        
        return $this;
    }
    
    protected function _afterMerge()
    {
        $this->_markMergeablesAsDeleted();
        
        return $this;
    }
    
    /**
     * Checks for $customer->getId() in $mergeIds and, if found, removes it.
     * 
     * This is used on a grid and it's near impossible to detect and remove the
     * $customer->getId() from the selected $mergeIds array. So we do a safety check
     * here to be 100% sure we don't accidentally delete the customer we wanted to keep.
     * 
     * @param Mage_Core_Model_Abstract $customer
     * @param array $mergeIds
     * @return array
     */
    protected function _safetyCheck(Mage_Core_Model_Abstract $customer, array $mergeIds)
    {
        return array_diff($mergeIds, [$customer->getId()]);
    }
    
    /**
     * Assigns the new new customer to all model entities.
     * 
     * @return $this
     */
    protected function _mergeModelEntities()
    {
        $entity = $this->getEntity();
        $mergeableIds = array_keys($this->getMergeableCollection()->toOptionHash());
        
        /**
         * @var string $modelEntity     Name of model/entity.
         * @var string $column          Name of column
         */
        foreach ($this->getModelEntities() as $modelEntity => $column) {
            
            $collection = Mage::getModel($modelEntity)->getCollection()
                ->addFieldToFilter($column, array('in' => $mergeableIds));

            foreach ($collection as $object) {
                
                $object->setDataUsingMethod($column, $entity->getId());

                $this->getTransactionHandler()->addObject($object);
            }
        }
        
        return $this;
    }
    
    /**
     * Merges the fields.
     * 
     * @return $this
     */
    protected function _mergeFields()
    {
        $entity = $this->getEntity();
        $mergeableCollection = $this->getMergeableCollection();
        
        /** @var Mage_Core_Model_Abstract $mergeable */
        foreach ($mergeableCollection as $mergeable) {
            
            foreach ($this->getMergeFields() as $spec) {
                
                $field = $spec['field'];
                $condition = $spec['condition'];
                $delimiter = (array_key_exists('delimiter', $spec)) ? $spec['delimiter'] :  null;
                
                $this->_mergeFieldConditionally($mergeable, $entity, $field, $condition, $delimiter);
            }
            
            $this->getTransactionHandler()->addObject($mergeable);
        }
        
        return $this;
    }
    
    protected function _mergeFieldConditionally($source, $destination, $field, $condition, $delimiter = null)
    {
        $getter = "_mergeField" . ucfirst($condition);
        
        $this->$getter($source, $destination, $field, $delimiter);
        
        return $this;
    }
    
    /**
     * Merges the field if the $destination is empty.
     * 
     * @param Mage_Core_Model_Abstract $source
     * @param Mage_Core_Model_Abstract $destination
     * @param string $field
     * 
     * @return void
     */
    protected function _mergeFieldEmpty($source, $destination, $field)
    {
        if (empty($destination->getDataUsingMethod($field)) && !empty($source->getDataUsingMethod($field))) {
            
            $destination->setDataUsingMethod($field, $source->getDataUsingMethod($field));
        }
    }
    
    /**
     * Overwrites the information in the $destination field, unless source data is empty.
     * 
     * @param Mage_Core_Model_Abstract $source
     * @param Mage_Core_Model_Abstract $destination
     * @param string $field
     * 
     * @return void
     */
    protected function _mergeFieldForce($source, $destination, $field)
    {
        $value = $source->getDataUsingMethod($field);
        
        if (!empty($value)) {
            
            $destination->setDataUsingMethod($field, $value);
        }
    }
    
    /**
     * Appends to the $destination field, unless source data is empty.
     * 
     * @param Mage_Core_Model_Abstract $source
     * @param Mage_Core_Model_Abstract $destination
     * @param string $field
     * 
     * @return void
     */
    protected function _mergeFieldAppend($source, $destination, $field, $delimiter = null)
    {
        $existingDestinationValues = array_filter(array_map('trim', explode(',', $destination->getDataUsingMethod($field))));
        $existingSourceValues = array_filter(array_map('trim', explode(',', $source->getDataUsingMethod($field))));
        
        
        if (!empty($value)) {
            
            $append = 
            $append .= $value;
            $destination->setDataUsingMethod($field, $append);
        }
    }
    
    /**
     * 
     * @param type $source
     * @param type $destination
     */
    public function _mergeFieldStrings($source, $destination, $delimiter = ',')
    {
        $sourceValueArray = array_filter(array_map('trim', explode($delimiter, $source)));
        $dsestinationArray = array_filter(array_map('trim', explode($delimiter, $destination)));

    }
    
    /**
     * Marks the mergables for deletion then transaction deletes them - last.
     * 
     * @return $this
     */
    protected function _markMergeablesAsDeleted()
    {
        $mergeableCollection = $this->getMergeableCollection();
        
        /** @var Mage_Core_Model_Abstract $toMerge */
        foreach ($mergeableCollection as $toMerge) {
            
            $toMerge->isDeleted(true);
        }
        
        return $this;
    }
    
    /**
     * The array of magento objects.
     * 
     * @return array
     */
    public function getModelEntities()
    {
        if (count($this->_modelEntities) == 0) {
            
            $entities = Mage::getConfig()->getFieldset('merge_entities');
            
            
            /** @var Mage_Core_Model_Config_Element $entity */
            foreach ($entities->{$this->_node}->children() as $entity) {
                
                if (true === $this->getCoreHelper()->isModuleEnabled($entity->module)) {
                    $this->addModelEntity((string)$entity->model_entity, (string)$entity->column, (bool)$entity->is('is_fk'), (string)$entity->table);
                }
            }
        }
        
        return $this->_modelEntities;
    }
    
    /**
     * Sets the array of magento objects.
     * 
     * @throws Mage_Core_Exception
     * @return array
     */
    public function addModelEntity($entity, $column, $isFk = false, $tableName = '')
    {
        if($tableName != $this->getTableNameByEntity($entity)) {

            $message = "Entity '{$entity}' did not match table name '{$tableName}'.";
            Mage::throwException($message);
        }

        if(false === $this->checkEntityColumnExists($entity, $column)) {

            $message = "Entity '{$entity}' does not have a column called '{$column}'.";
            Mage::throwException($message);
        }
        
        //$this->addMessage(var_export($isFk, 1));

        $has = $this->checkEntityForeignKeyExists($entity, $column);
        if ((bool)$isFk !== $has) {
            
            $specified = (bool)($isFk) ? 'should ' : 'should NOT ';
            
            $result = (bool)($has) ? 'was ' : 'was NOT ';

            $message = "Entity '{$entity}' says there {$specified} be a foreign key for column '{$column}' but {$result} found.";
            Mage::throwException($message);
        }

        $this->_modelEntities[$entity] = $column;
        
        return $this;
    }
    
    /**
     * Returns the transaction handler.
     * 
     * @return Mage_Core_Model_Resource_Transaction
     */
    public function getTransactionHandler()
    {
        return Mage::getModel('core/resource_transaction');
    }
    
    /**
     * Gets the core helper.
     * 
     * @return Mage_Core_Helper_Data
     */
    public function getCoreHelper()
    {
        return Mage::helper('core');
    }
    
    /**
     * Gets the values from <fieldsets/>/<customer_account/><fieldname/> and looks
     * for the <merge> which must be set to "empty" or "force".
     * 
     * This approach means that any extension vendor can attach their "custom" customer attributes by
     * adding "your-custom-field" with the merge value to "empty" or "force".
     * 
     * Returns fieldname as key and condition as value, example ['firstname' => 'empty'].
     * 
     * Example for config.xml file.
     * 
     *  <global>
     *      <fieldsets>
     *           <customer_account>
     *               <firstname>
     *                   <merge>empty</merge>
     *              </firstname>
     *               <your-custom-field>
     *                   <merge>force</merge>
     *              </your-custom-field>
     *           <customer_account>
     *       <fieldsets>
     *   <global>
     * 
     * @throws Mage_Core_Exception
     * @return array
     */
    public function getMergeFields()
    {
        if (count($this->_mergeFields) == 0) {
            
            $fields = [];
            
            $customerAccount = Mage::getConfig()->getFieldset('customer_account');

            foreach ($customerAccount as $code => $node) {
                
                $code = (string) $code;

                /** @var Mage_Core_Model_Config_Element $node */
                if ($node->is('merge') && $this->checkMergeCondition((string)$node->merge, $code)) {

                    $exists = false;
                    if (($resource = $this->getEntity()->getResource()) instanceof \Mage_Eav_Model_Entity_Abstract) {
                        
                        $exists = (bool)$resource->getAttribute($code);
                        
                    } elseif (($resource = $this->getEntity()->getResource()) instanceof \Mage_Core_Model_Resource_Db_Abstract) {
                        
                        //$exists = (bool)$resource->getAttribute($code);
                    }
                    
                    if (!$exists) {
                        
                        $message = sprintf("Attribute '%s' specified in config.xml does not appear to exist in the '%s' entity.", (string)$code, $this->_entityName);
                        Mage::throwException($message);
                    }
                    
                    /**
                     * Key is field, value is condition.
                     * 
                     * Example: 'firstname' => 'empty'
                     */
                    $fields[$code] = [
                        'field' => $code,
                        'condition' => (string)$node->merge,
                    ];
                    
                    if ($node->delimiter) {
                        $this->addMessage(sprintf('Delimiter for %s is "%s"', $code, (string)$node->delimiter));
                        $fields['delimiter'] = (string)$node->delimiter;
                    }
                }

            }
            
            $this->_mergeFields = $fields;
        }
        
        return $this->_mergeFields;
    }
    
    /**
     * Checks if merge condition is valid against $this->_mergeConditions.
     * 
     * @throws Mage_Core_Exception
     * @return boolean
     */
    public function checkMergeCondition($condition, $field)
    {
        if (!in_array($condition, $this->_mergeConditions)) {

            $message = "Unknown merge condition '{$condition}' for field '{$field}' in 'customer_account' fieldset.";
            
            Mage::throwException($message);
            
            return false; // probably unnecessary.
        }
        
        return true;
    }
    
    /**
     * Checks if the entity has a valid column by name.
     * 
     * @throws Mage_Core_Exception
     * @param string $modelEntity
     * @return string
     */
    public function checkEntityColumnExists($modelEntity, $columnName)
    {
        /** @var Mage_Core_Model_Resource $resource */
        $resource = $this->getCoreResource();
        
        /** @var Magento_Db_Adapter_Pdo_Mysql $connection */
        $connection = $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_READ_RESOURCE);
        
        return $connection->tableColumnExists($resource->getTableName($modelEntity), $columnName);
    }
    
    /**
     * Checks if the entity has a valid foreign key.
     * 
     * @throws Mage_Core_Exception
     * @param string $modelEntity
     * @param string $columnName
     * @return string
     */
    public function checkEntityForeignKeyExists($modelEntity, $columnName)
    {
        /** @var Mage_Core_Model_Resource $resource */
        $resource = $this->getCoreResource();
        
        /** @var Magento_Db_Adapter_Pdo_Mysql $connection */
        $connection = $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_READ_RESOURCE);
        
        $foreignKeys = $connection->getForeignKeys($resource->getTableName($modelEntity));
        
        foreach ($foreignKeys as $keyData) {
            if ($keyData['COLUMN_NAME'] == $columnName) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Checks if the entity is valid and throws exception if not. Returns table name.
     * 
     * @throws Mage_Core_Exception
     * @param string $modelEntity
     * @return string
     */
    public function getTableNameByEntity($modelEntity)
    {
        /** @var Mage_Core_Model_Resource $resource */
        $resource = $this->getCoreResource();
        
        return $resource->getTableName($modelEntity);
    }
    
    /**
     * Proxy to Mage_Core_Model_Resource.
     * 
     * @return Mage_Core_Model_Resource
     */
    public function getCoreResource()
    {
        return Mage::getSingleton('core/resource');
    }
    
    /**
     * Adds a message to the message array.
     * 
     * @param string $message
     * @return $this
     */
    public function addMessage($message)
    {
        $this->_messages[] = $message;
        
        return $this;
    }
    
    /**
     * Gets messages as array.
     * 
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }
    
    /**
     * Returns a boolean result of whether there are messages in the array.
     * 
     * @return bool
     */
    public function hasMessages()
    {
        return (bool) count($this->getMessages());
    }
    
    /**
     * Are there any errors?
     * 
     * @return bool
     */
    public function hasError()
    {
        return ($this->_passedSelfTest || $this->_isError);
    }
}