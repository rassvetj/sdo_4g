<?php
abstract class HM_Quiz_PersistentModel_Abstract extends HM_Model_Abstract
{
    protected $_model = array();
    protected $_results = array();    
    protected $_memoResults = array();    
    
    protected $_items = array();
    protected $_currentItem = 0;
        
    public function getItems()
    {
        return $this->_items;
    }
    
    public function setItems($items)
    {
        $this->_items = $items;
        return $this;
    }
    
    public function getCurrentItem()
    {
        return $this->_currentItem;
    }
    
    public function setCurrentItem($itemId)
    {
        $this->_currentItem = $itemId;
        return $this;
    }
    
    public function getResults()
    {
        return $this->_results;
    }
    
    public function getMemoResults()
    {
        return $this->_memoResults;
    }
    
    public function setResults($itemId, $results)
    {
        $this->_results[$itemId] = $results;
        return $this;
    }
    
    public function setMemoResults($memoResults)
    {
        $this->_memoResults = $memoResults;
        return $this;
    }
    
    public function getModel()
    {
        return $this->_model;
    }
    
}