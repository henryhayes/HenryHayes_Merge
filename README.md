# Merge Magento 1.9 / OpenMage Customers

Initiated by [this Stack Overflow question](https://magento.stackexchange.com/questions/370876/magento-1-9-openmage-how-to-merge-customers) (where I didn't get a single reply), I have written this extension for merging customers in Magento 1.9 / OpenMage.

Free to use. No support is offered.

## Compatability

Tested on:

* Magento 1.9.x (and OpenMage)

## Issues

Report issues and bugs using the [issue tool](https://github.com/henryhayes/HenryHayes_Merge/issues) as normal.

## How To Use

Out of the box, this extension deals with all tables that reference the customer table.

## How To Extend

There are two parts you need to know.

### 1. Customer Merge Fields

I have setup two possible scenarios for merging the "unwanted customer(s)" records into the "customer to keep":

 * **empty:** If the 
 * **force:** Overwrite the "customer to keep" data with the "unwanted customer(s)". I'm not 100% sure why this would be desirable, but I've made provision for it.

Both of these are set up in the `HenryHayes_Merge_Model_Merge_Customer` class as constants, and a check is done by the method `checkMergeCondition()` that ensures the XML only contains valid merge conditions for each of the XML `customer_account` fields.

I have already mapped the out-the-box Magento 1.9. / OpenMage fields.

* prefix
* firstname
* middlename
* lastname
* suffix
* email
* confirmation
* dob
* taxvat
* gender

You can add extra customer attributes in the `config.xml`. For example, I have a custom extension that's created an attribute called `sector_id`. I take care of this as follows.

```xml
<global>
    <fieldsets>
        <customer_account>
            <sector_id>
                <merge>empty</merge>
            </sector_id>
        </customer_account>
    </fieldsets>
</global>
```

### 2. Entities That Reference `customer_entity`

Find the tables in other extensions that reference the `customer_entity` table by running these two:

```mysql
SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME IN ('customer_id','ColumnB') AND TABLE_SCHEMA='[your-magento-db-name]';
SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE REFERENCED_TABLE_NAME = 'customer_entity' AND REFERENCED_COLUMN_NAME = 'entity_id' AND TABLE_SCHEMA = '[your-magento-db-name]';
```

*Note:* You will only find columns that are either called `customer_id` or are a foreign key to `customer_entity.entity_id`. You might need to be a little more creative to find any columns not called `customer_id` that are not foreign keys.

Next, you have to add these tables, models, and columns to the `global/fieldsets/merge_entities/customer` section as shown below. For example, in one of my stores, there is a table called `activity_event` that has a column called `cus_id`.

* module: This is the name of the module where the table is defined in the `config.xml` file.
* table: This is the literal name of the table. We use this to check that `table` and `model_entity` match! Very important!
* model_entity: The programmatic `module/entity` name used by the `Mage::getModel()` service manager system.
* column: The column name that references `customer_entity.entity_id`. In most tables, this is `customer_id`. But check carefully, don't take that for granted.
* is_fk: If there is a FK in the database, set this to 1, otherwise 0. For future expansion use.

```xml
<global>
    <fieldsets>
        <merge_entities>
            <customer>
                <activity_event>
                    <module>HH_Activity</module>
                    <table>activity_event</table>
                    <model_entity>hh_activity/activity_event</model_entity>
                    <column>cus_id</column>
                    <is_fk>1</is_fk>
                </activity_event>
            </customer>
        </merge_entities>
    </fieldsets>
</global>

```
