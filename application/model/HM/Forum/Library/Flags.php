<?php

class HM_Forum_Library_Flags{
    
    protected $_pattern;
    protected $_data = array();
    
    public function __construct(array $pattern, $data = null){        
        switch(true){
            case is_null($data):
                foreach(array_keys($pattern) as $key) $this->_data[$key] = false;
                break;
                
            case is_array($data):
                $this->_data = $data;
                break;
            
            case is_numeric($data):
                $this->_data = self::decode($pattern, $data);
                break;
            
            default: throw new HM_Exception('The datatype is invalid');
        }
        
        $this->_pattern = $pattern;
    }
    
    public function __get($key){
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }
    
    public function __set($key, $value){
        if(isset($this->_pattern[$key])) $this->_data[$key] = (bool) $value;
        else throw new HM_Exception('Uncnown flag');
    }
    
    public function __isset($key){
        return isset($this->_data[$key]);
    }
    
    /**
     * Получить флаги в закодированном виде
     * 
     * @return integer 
     */
    public function getEncoded(){
        return self::encode($this->_pattern, $this->_data);
    }
    
    /**
     * Декодирует integer в массив флагов по шаблону
     * integer -> array('flag1' => true, 'flag2' => false)
     * По шаблону вида: array('flagName1' => (int) flagNm1)
     * 
     * @param array $pattern шаблон кодирования
     * @param integer $encoded
     * @return array
     */
    static public function decode(array $pattern, $encoded){
        $encoded = (int) $encoded;
        $decoded = array();
        $bits = strlen(decbin($encoded));
        for($bit = 0; $bit < $bits; ++$bit){
            if($encoded & 1 << $bit) $decoded[] = $bit;
        }
        
        $decoded = array_flip($decoded);
        $options = array();
        foreach($pattern as $option => $value)$options[$option] = isset($decoded[$value]);
        
        return $options;
    }
    
    /**
     * Кодирует массив флагов в integer
     * array('flag1' => true, 'flag2' => false) -> integer
     * По шаблону вида: array('flagName1' => (int) flagNm1)
     * 
     * @param array $pattern шаблон кодирования
     * @param array $options массив флагов
     * @return integer
     */
    static public function encode(array $pattern, array $options){
        $encoded = 0;
        foreach($pattern as $key => $value){
            if(isset($options[$key]) && $options[$key] === true) $encoded+= 1 << $value;
        }
        
        return $encoded;
    }
    
}