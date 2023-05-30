<?php
class HM_Classifier_Import_Manager
{
    protected $_existing = array();
    protected $_existingIds = array();

    protected $_inserts = array();
    protected $_updates = array();
    protected $_deletes = array();

    const CACHE_NAME = 'HM_Classifier_Import_Manager';

    protected $_type = 0;

    private $_restoredFromCache = false;

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    private function _init()
    {
        $this->setType();
        $classifiers = $this->getService('Classifier')->fetchAll(
            $this->getService('Classifier')->quoteInto('type = ?', $this->_type)
        );

        if (count($classifiers)) {
            foreach($classifiers as $classifier) {
                $id = md5($classifier->name);
                $this->_existingIds[$id] = $classifier->classifier_id;
                $this->_existing[$classifier->classifier_id] = $classifier;
            }
        }
    }

    public function getInsertsCount()
    {
        return count($this->_inserts);
    }

    public function getUpdatesCount()
    {
        return count($this->_updates);
    }

    public function getDeletesCount()
    {
        return count($this->_deletes);
    }

    public function getCount()
    {
        return $this->getInsertsCount() + $this->getUpdatesCount();
    }

    public function getInserts()
    {
        return $this->_inserts;
    }

    public function getUpdates()
    {
        return $this->_updates;
    }

    public function getDeletes()
    {
        return $this->_deletes;
    }

    public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'inserts' => $this->_inserts,
                 'updates' => $this->_updates,
                 'deletes' => $this->_deletes
            ),
            self::CACHE_NAME
        );
    }

    public function restoreFromCache()
    {
        if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->_inserts = $actions['inserts'];
            $this->_updates = $actions['updates'];
            $this->_deletes = $actions['deletes'];
            $this->_restoredFromCache = true;
            return true;
        }

        return false;
    }

    public function init($items)
    {
        $this->_init();

        if ($this->_restoredFromCache) {
            return true;
        }

        if (count($items)) {
            foreach($items as $item) {
                if (strlen($item->name)) {
                    $id = md5($item->name);
                    $item->type = $this->_type;
                    $this->_inserts[$id] = $item;
                }
            }
        }

        if (count($this->_existing)) {
            $this->_deletes = $this->_existing;
        }

        $this->saveToCache();
    }

    public function setType()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $this->_type = $request->getParam('type', 0);

    }

    public function import()
    {
        $classifiersCache = array();

        $this->setType();
        if (count($this->_inserts)) {
            $this->getService('Classifier')->deleteByType($this->_type);

            foreach($this->_inserts as $id => $insert) {

                $parentId = isset($classifiersCache[$insert->parent]) ? $classifiersCache[$insert->parent] : null;
                $classifier = $this->getService('Classifier')->insert($insert->getValues(array('name', 'type')), $parentId);
                if ($classifier) {
                    $classifiersCache[$insert->name] = $classifier->classifier_id;
                }
            }
        }

    }

}