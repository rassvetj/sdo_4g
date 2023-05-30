<?php
class HM_View_Callable_Escape extends HM_View_Callable_Empty {
    public function call($path, &$current, &$all, array $meta = array())
    {
        $data = parent::call($path, $current, $all, $meta);
        if (isset($data['title'])) {
            $data['title'] = $this->view->escape($data['title']);
        }
        return $data;
    }
}