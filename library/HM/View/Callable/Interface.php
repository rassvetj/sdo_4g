<?php
interface HM_View_Callable_Interface {
    public function call($path, &$current, &$all, array $meta = array());
    public function setView(Zend_View_Interface $view);
}