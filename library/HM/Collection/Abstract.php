<?php
/**
 * Абстрактная реализация lazy initialization коллекции объектов
 */
abstract class HM_Collection_Abstract implements Iterator, ArrayAccess, Countable
{
    /**
     * Массив данных до инициализации объектов
     * @var array
     */
    protected $_raw = array();

    /**
     * Массив инициализированных объектов
     * @var array
     */
    protected $_data = array();

    protected $_position = 0;
    protected $_count = 0; 

    protected $_modelClass = array();

    protected $_sortAttr = null;
    /**
     * Массив зависимостей для одного объекта
     * @var array
     */
    protected $_dependences = array();

    public function __construct($raw = array(), $modelClass = null)
    {
        $this->_raw = $raw;
        $this->_modelClass = $modelClass;
        $this->_position = 0;
        $this->_count = 0;
    }

    protected function _createDependences($offset)
    {
        if (is_array($this->_dependences) && count($this->_dependences)) {
            $model = $this->_data[$offset];
            $collectionClass = get_class($this);
            foreach($this->_dependences as $columns => $dependence) {
                if (strlen($columns)) {
                    if (isset($dependence[$model->$columns])) {
                        foreach($dependence[$model->$columns] as $propertyName => $items) {
                            $models = new $collectionClass(array(), 'HM_Model_Abstract');
                            foreach($items as $item) {
                                if (isset($item['refClass']) && strlen($item['refClass'])) {
                                    $item['row']['modelClass'] = $item['refClass'];
                                    unset($item['refClass']);
                                    $models[count($models)] = $item['row'];
                                    //$ref = call_user_func_array(array($refClass, 'factory'), array($item['row'], $refClass));
                                    //$model->add($propertyName, $ref);
                                }
                            }
                            $model->setValue($propertyName, $models);
                        }

                        //unset($this->_dependences[$columns][$model->$columns]);
                        if (!count($this->_raw)) $this->_dependences = array();

                    }
                }
            }
        }
    }

    /**
     * Инициализируем текущий объект
     * @param  int $offset
     * @return void
     */
    protected function _createObject($offset)
    {
        if (!isset($this->_data[$offset])) {
            $modelClass = $this->getModelClass();
            if (isset($this->_raw[$offset]['modelClass'])) {
                $modelClass = $this->_raw[$offset]['modelClass'];
                unset($this->_raw[$offset]['modelClass']);
            }

            $this->_data[$offset] = call_user_func_array(array($modelClass, 'factory'), array($this->_raw[$offset], $modelClass));
            $this->_createDependences($offset);

            //unset($this->_raw[$offset]);

            //if (!count($this->_raw)) {
            $this->_raw[$offset] = null;

            if (null === $this->_raw[count($this->_raw)-1]) {
                unset($this->_dependences);
            }
        }
    }

    /**
     * Делаем reset коллекции
     * @return void
     */
    public function rewind()
    {
        $this->_position = 0;
    }

    /**
     * Возвращаем текущий элемент коллекции
     * @return HM_Model_Abstract
     */
    public function current()
    {
        $this->_createObject($this->_position);
        
        return $this->_data[$this->_position];

    }

    public function key()
    {
        return $this->_position;
    }

    public function next()
    {
        ++$this->_position;
    }

    public function valid()
    {
        return (isset($this->_raw[$this->_position]) || isset($this->_data[$this->_position]));
    }

    /**
     * Добавляем в коллекцию новый элемент с индексом $offset
     * @param  int $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $modelClass = $this->getModelClass();
        if ($value instanceof $modelClass) {
            $this->_data[$offset] = $value;
            //$this->_raw[$offset] = $value->getValues();
        } elseif(is_array($value)) {
            $this->_raw[$offset] = $value;
        }
        ++$this->_count;
    }

    /**
     * Проверка на существование элемента с индексом $offset
     * @param  int $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return (isset($this->_raw[$offset]) || isset($this->_data[$offset]));
    }

    public function offsetUnset($offset)
    {
        if (isset($this->_data[$offset]) || isset($this->_raw[$offset])) {
            --$this->_count;
        }
        unset($this->_data[$offset]);
        unset($this->_raw[$offset]);
        
        //something very dangerous, observer this
        sort($this->_raw);
        sort($this->_data);
        $this->_position = 0;
    }

    public function offsetGet($offset)
    {
        if ((!isset($this->_raw[$offset]) && !isset($this->_data[$offset]))) return null;
        $this->_createObject($offset);
        return $this->_data[$offset];
    }

    /**
     * Устанавливаем название модели по умолчанию для элементов коллекции
     * @param  string $modelClass
     * @return void
     */
    public function setModelClass($modelClass)
    {
        $this->_modelClass = $modelClass;
    }

    public function getModelClass()
    {
        return $this->_modelClass;
    }

    /**
     * Устанавливаем зависимости для текущей коллекции объектов
     * @param array $dependences
     * @return void
     */
    public function setDependences($dependences = array())
    {
        $this->_dependences = $dependences;
    }

    public function getDependences()
    {
        return $this->_dependences;
    }

    public function count()
    {
        return $this->_count;
    }

    public function isEmpty()
    {
        return empty($this->_raw) && empty($this->_data);
    }
    
    /**
     * Возвращает список из $key и $value свойств элементов коллекции
     * @param  string $key
     * @param  string $value
     * @param bool $default
     * @return array
     */
    public function getList($key, $value = null, $default = false)
    {

        if (null === $value) {
            $value = $key;
        }

        $result = array();
        if ($default) {
            $result[0] = $default;
        }
        if (count($this)) {
            foreach($this as $item) {
                $result[$item->$key] = $item->$value;
            }
        }
        return $result;
    }

    public function exists($key, $value)
    {
        if (count($this)) {
            foreach($this as $item) {
                if (isset($item->$key) && ($item->$key == $value)) {
                    return $item;
                }
            }
        }
        return false;
    }
    
    public function asArray(){
              
        return $this->_raw;
    }
    
    public function asArrayOfObjects($sort = null)
    {
        $array = array();
        foreach ($this as $item) {
            if ($pk = $item->getPrimaryKey()) {
                $array[$pk] = $item;
            } else {
                $array[] = $item;
    }
        }
        
        if ($sort && count($array) && isset($item->$sort)) {
        	$this->_sortAttr = $sort;
        	uasort($array, array('HM_Collection_Abstract', '_sortByAttribute'));
        }
        
        return $array;
    }
    
    protected function _sortByAttribute(&$item1, &$item2)
    {
    	if ($sort = $this->_sortAttr) {
    		return ($item1->$sort < $item2->$sort) ? -1 : 1;
    	}
    	return 0;
    }
    
}