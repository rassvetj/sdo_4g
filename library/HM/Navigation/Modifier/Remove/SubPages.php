<?php
class HM_Navigation_Modifier_Remove_SubPages extends HM_Navigation_Modifier
{

    private $_property = null;
    private $_value = null;

    public function __construct($findByProperty, $findByValue)
    {
        $this->_property = $findByProperty;
        $this->_value = $findByValue;

        $this->_function = array($this, 'removeSubPages');
    }

    public function removeSubPages(HM_Navigation $container = null)
    {
        if (null !== $container) {
            $page = $container->findBy($this->_property, $this->_value);
            if ($page) {
                if ($page->pages) {
                    foreach($page->pages as $subPage) {
                        $subPage->getParent()->removePage($subPage);
                    }
                }
            }
        }
    }
}