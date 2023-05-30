<?php
class HM_Resource_Import_Manager
{
    const CACHE_NAME = 'HM_Resource_Import_Manager';

    protected $_items = null;

    protected $_existingItems = array();
    protected $_existingIds = array();

    protected $_inserts = array();
    protected $_updates = array();

    private $_restoredFromCache = false;

    public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                'inserts' => $this->_inserts,
                'updates' => $this->_updates,
            ),
            self::CACHE_NAME
        );
    }

    public function clearCache()
    {
        return Zend_Registry::get('cache')->remove(self::CACHE_NAME);
    }
    
    public function restoreFromCache()
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->_inserts = $actions['inserts'];
            $this->_updates = $actions['updates'];
            $this->_restoredFromCache = true;
            return true;
        }

        return false;
    }

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    public function getInserts()
    {
        return $this->_inserts;
    }

    public function getUpdates()
    {
        return $this->_updates;
    }

    public function getInsertsCount()
    {
        return count($this->_inserts);
    }

    public function getUpdatesCount()
    {
        return count($this->_updates);
    }

    public function getCount()
    {
        return $this->getInsertsCount() + $this->getUpdatesCount();
    }

    protected function _init()
    {
        $items = $this->getService('Resource')->fetchAll();

        if (count($items)) {
            foreach($items as $item) {
                $this->_existingItems[$item->resource_id] = $item;
                if (strlen($item->resource_id_external)) {
                    $item->resource_id_external = trim($item->resource_id_external);
                    $this->_existingIds[$item->resource_id_external] = $item->resource_id;
                }
            }
        }

        $this->_items = &$items;
    }

    protected function _isItemExists($itemExternalId)
    {
        return isset($this->_existingIds[$itemExternalId]);
    }

    protected function _needItemUpdate($item)
    {
        $existingItem = $this->_existingItems[$this->_existingIds[$item->resource_id_external]];

        $values = $item->getValues(null, array('resource_id', 'resource_id_external', 'name', 'description'));
        if (count($values)) {
            foreach($values as $key => $value) {
                if (trim($existingItem->{$key}) != trim($value)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function init($items)
    {
        if ($this->_restoredFromCache) {
            $this->_init();
            return true;
        }

        if (count($items)) {

            $this->_init();
            foreach($items as $item) {

                $item->resource_id_external = trim($item->resource_id_external);

                if (!strlen($item->resource_id_external)) continue;

                if (!$this->_isItemExists($item->resource_id_external)) {
                    // insert new item
                    $this->_inserts[$item->resource_id_external] = $item;
                } else {
                    $existingItem = $this->_existingItems[$this->_existingIds[$item->resource_id_external]];

                    if ($this->_needItemUpdate($item)) {
                        $item->resource_id = $existingItem->resource_id;
                        $this->_updates[$item->resource_id_external] = array('source' => $existingItem, 'destination' => $item);
                    }
                }
                unset($item->active);
            }
        }

        $this->saveToCache();
    }

    public function import()
    {
        if (count($this->_inserts)) {
            $insertArr = array();
            foreach($this->_inserts as $ins){
                $this->insertNode(array('insert' => $ins, 'type' => 'new'));
            }
        }

        if (count($this->_updates)) {
            foreach($this->_updates as $id => $update)
            {
                $up = $update;
                $update = $update['destination'];
                $values = $update->getValues(HM_Resource_Csv_CsvAdapter::getStaticFields());
                $values['resource_id'] = $update->resource_id;

                $this->getService('Resource')->update($values);
                $this->getService('ClassifierLink')->deleteBy($this->getService('ClassifierLink')->quoteInto(array('item_id = ?', ' AND type = ?'), array($update->resource_id, HM_Classifier_Link_LinkModel::TYPE_RESOURCE)));
                $classifiersFields = HM_Resource_Csv_CsvAdapter::getClassifiersFields();
                foreach ($classifiersFields as $classifiersField) {
                    if (!empty($update->$classifiersField)) {
                        $classifierIds = explode(',', $update->$classifiersField);
                        foreach ($classifierIds as $classifierId) {
                            $this->getService('ClassifierLink')->insert(array(
                                'item_id' => $update->resource_id,        
                                'classifier_id' => $classifierId,        
                                'type' => HM_Classifier_Link_LinkModel::TYPE_RESOURCE,        
                            ));
                        }
                    }
                }            
            }
        }

        return true;
    }


    protected function insertNode($node)
    {
        $insert = $node['insert'];

        $values = $insert->getValues(HM_Resource_Csv_CsvAdapter::getStaticFields());
        $values['location'] = HM_Resource_ResourceModel::LOCALE_TYPE_GLOBAL;
        $values['type'] = HM_Resource_ResourceModel::TYPE_CARD;
        $item = $this->getService('Resource')->insert($values);

        if ($item) {
            
            $classifiersFields = HM_Resource_Csv_CsvAdapter::getClassifiersFields();
            foreach ($classifiersFields as $classifiersField) {
                if (!empty($insert->$classifiersField)) {
                    $classifierIds = explode(',', $insert->$classifiersField);
                    foreach ($classifierIds as $classifierId) {
                        $this->getService('ClassifierLink')->insert(array(
                            'item_id' => $item->resource_id,        
                            'classifier_id' => $classifierId,        
                            'type' => HM_Classifier_Link_LinkModel::TYPE_RESOURCE,        
                        ));
                    }
                }
            }            
            
            $insert->resource_id = $item->resource_id;
            //$this->_updates[$insert->resource_id_external] = array('destination' => $insert);
            $this->_existingIds[$insert->resource_id_external] = $insert->resource_id;
        }
    }
}