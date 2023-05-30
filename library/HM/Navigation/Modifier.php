<?php
class HM_Navigation_Modifier implements HM_Navigation_Modifier_Interface
{
    protected $_function = null;

    public function __construct($function)
    {
        $this->_function = $function;
    }

    public function process(HM_Navigation $container)
    {
        return call_user_func($this->_function, $container);
    }
}