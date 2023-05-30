<?php

/**
 * Primitive implementing  of Iterator, ArrayAccess and Countable interfaces
 *
 * @author tutrinov
 */
class HM_Collection_Primitive implements Iterator, ArrayAccess, Countable {
    
    protected $_raw = array();
    protected $eventDispatcher = null;
    
    public function __construct() {
        $this->setEventDispatcher(Zend_Registry::get("serviceContainer")->getService('EventDispatcher'));
    }
    
    /**
     * 
     * @return sfEventDispatcher
     */
    public function getEventDispatcher() {
        return $this->eventDispatcher;
    }

    /**
     * Define event dispatcher
     * @param sfEventDispatcher $eventDispatcher
     */
    public function setEventDispatcher(sfEventDispatcher $eventDispatcher) {
        $this->eventDispatcher = $eventDispatcher;
    }

    
    public function add($object, $key = null) {
        if (null !== $key) {
            $this->_raw[$key] = $object;
            return;
        }
        $this->_raw[] = $object;
        return;
    }
    
    public function offsetExists($offset) {
        return array_key_exists($offset, $this->_raw);
    }

    public function offsetGet($offset) {
        if ($this->offsetExists($offset)) {
            return $this->_raw[$offset];
        }
        return null;
    }

    public function offsetSet($offset, $value) {
        $this->_raw[$offset] = $value;
        return;
    }

    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->_raw[$offset]);
            return true;
        }
        return false;
    }

    public function count() {
        return sizeof($this->_raw);
    }

    public function current() {
        return current($this->_raw);
    }

    public function key() {
        return key($this->_raw);
    }

    public function next() {
        return next($this->_raw);
    }

    public function rewind() {
        return reset($this->_raw);
    }

    public function valid() {
        return (is_array($this->_raw) && array_key_exists(key($this->_raw), $this->_raw));
    }

    
}