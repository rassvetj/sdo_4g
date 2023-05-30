<?php
class HM_View_Helper_JQLists extends HM_View_Helper_Abstract
{

    public function jQLists($name, $list1Options, $list2Options, $filter)
    {

        $this->view->name = $name;
        $this->view->list1 = $list1Options;
        $this->view->list2 = $list2Options;
        $this->view->filter = $filter;
        return $this->view->render( 'jqlists.tpl' );
    }
}