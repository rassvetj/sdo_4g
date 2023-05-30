<?php 

class CRegistry extends ArrayObject {
    
    private static $_registryClassName = 'CRegistry';

    private static $_registry = null;
    
    public static function getInstance()
    {
        if (self::$_registry === null) {
            self::init();
        }

        return self::$_registry;
    }
    
    public static function setClassName($registryClassName = 'CRegistry')
    {
        if (self::$_registry !== null) {
            throw new Exception('Registry is already initialized');
        }

        if (!is_string($registryClassName)) {
            throw new Exception("Argument is not a class name");
        }

        self::$_registryClassName = $registryClassName;
    }   
    
    public static function setInstance(CRegistry $registry)
    {
        if (self::$_registry !== null) {
            throw new Exception('Registry is already initialized');
        }
        
        self::setClassName(get_class($registry));
        self::$_registry = $registry;
    }
    
    
    protected static function init()
    {
        self::setInstance(new self::$_registryClassName());
    }
    
    
    public static function get($index)
    {
        $instance = self::getInstance();

        if (!$instance->offsetExists($index)) {
            throw new Exception("No entry is registered for key '$index'");
        }

        return $instance->offsetGet($index);
    }
    
    
    public static function set($index, $value)
    {
        $instance = self::getInstance();
        $instance->offsetSet($index, $value);
    }

    public static function isRegistered($index)
    {
        if (self::$_registry === null) {
            return false;
        }
        return self::$_registry->offsetExists($index);
    }
    
    public function offsetExists($index)
    {
        return array_key_exists($index, $this);
    }
    
    
}

?>