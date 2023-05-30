<?php
class HM_Navigation_Modifier_Remove_Page extends HM_Navigation_Modifier
{

    private $_property = null;
    private $_value = null;

    public function __construct($findByProperty, $findByValue)
    {
        $this->_property = $findByProperty;
        $this->_value = $findByValue;

        $this->_function = array($this, 'removePage');
    }

    public function removePage(HM_Navigation $container = null)
    {
        if (null !== $container) {
            $page = $container->findBy($this->_property, $this->_value);
            if ($page) {
                $page->getParent()->removePage($page);
            }
        }
    }
}