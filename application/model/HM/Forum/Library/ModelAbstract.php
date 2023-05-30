<?php

/**
 * @author Mike
 * @version 0.5
 * @package HM_Forum
 * @copyright Hypermethod
 * 
 * @property HM_Forum_Library_Flags $flags
 */
abstract class HM_Forum_Library_ModelAbstract extends HM_Model_Abstract implements HM_Forum_Library_Constants{
    
    /**
     * @var Zend_Controller_Router_Abstract
     */
    protected $_router;
    
    /**
     * @var string Название роута использующегося для построения url
     */
    protected $_routeName;
    
    public function __construct($data){
        parent::__construct($data);
        $this->_router = Zend_Controller_Front::getInstance()->getRouter();
        $this->_routeName = $this->_router->getCurrentRouteName();
    }
    
    public function __get($key){
        // Перехватываем обращение к флагам
        if($key == 'flags' && isset($this->_data['flags'])){
            if(is_numeric($this->_data['flags'])){
                $this->_data['flags'] = new HM_Forum_Library_Flags(static::$flagsPattern, $this->_data['flags']);
            }
            return $this->_data['flags'];
        }
        
        return parent::__get($key);
    }
    
    /**
     * Кодирует флаги в соответствии со статической сущностью модели $flagsPattern являющейся массивом
     * 
     * @param array $flags заданные флаги
     * @return int закодированные флаги
     */
    static public function flagsEncode(array $flags){
        return HM_Forum_Library_Flags::encode(static::$flagsPattern, $flags);
    }
    
    /**
     * Время создания записи в формате заданном для dateTime в HM_Model_Abstract
     * 
     * @return string 
     */
    public function createdDateTime(){
        return $this->dateTime($this->_data['created']);
    }
    
    public function getServiceName(){
        return 'Forum';
    }

    public function setRouteName($name)
    {
        $this->_routeName = $name;
    }
    
}