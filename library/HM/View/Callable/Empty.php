<?php
class HM_View_Callable_Empty implements HM_View_Callable_Interface {
    protected $view;

    public static function defaultData()
    {
        return array('attrs' => array());
    }
    
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
        return $this;
    }

    public function call($path, &$current, &$all, array $meta = array())
    {
        $data = HM_View_Callable_Empty::defaultData();
        if (0 === @substr_compare($meta['type'], "title", -strlen("title"))) {
            if (is_array($current)) {
                $data['title'] = $current['title'];
            } else {
                $data['title'] = $current;
            }
            if (!isset($data['title'])) {
                $data['title'] = '';
            }
        }
        return $data;
    }
}