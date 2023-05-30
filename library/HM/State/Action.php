<?php
abstract class HM_State_Action
{
    const DONT_DECORATE = 2;

    protected $_restriction = array();

    protected $_params = array();

    protected $_state  = null;

    public function __construct($params, $restriction, $state)
    {
        $this->_restriction = $restriction;
        $this->_params      = $params;
        $this->_state       = $state;
    }


    /**
     * Must implemented in child classes. Return rendered action
     * @abstract
     * @param $params
     */
    abstract public function _render($params);

    /**
     *  return rendered string or false if condition wasn't checked...
     *
     * @return bool|void
     */
    public function render()
    {
        if($this->checkRestriction()){
            return $this->_render($this->_params);
        }else{
            return false;
        }
    }

    public function getState()
    {
        return $this->_state;
    }

    /**
     * Check all restrictions
     *
     * @return bool
     */
    protected function checkRestriction()
    {
        $return = true;
        foreach($this->_restriction as $class => $params){
            $stateClass = get_class($this->getState());

            $explode = explode('_', $stateClass);
            $explode[count($explode)-1] = 'Validator';
            $explode[] = ucfirst($class);

            $classValidator =  implode('_', $explode);

            if(!class_exists($classValidator)){
                $explode = explode('_', 'HM_State_Action');
                $explode[count($explode)] = 'Validator';
                $explode[] = ucfirst($class);
                $classValidator =  implode('_', $explode);
            }

            $validator = new $classValidator($this->getState());

            if($validator->validate($params) == false){
                $return = false;
            }
        }
        return $return;
    }

    /**
     * Функция определяет нужно ли заключать элемент в декораторы.
     * @author Artem Smirnov <tonakai.personal@gmail.com>
     * @date 24.01.2013
     * @return bool
     */
    public function isDecorated()
    {
        if(isset($this->_params['decorating']) && $this->_params['decorating'] == self::DONT_DECORATE)
        {
            return false;
        }
        return true;
    }

}
