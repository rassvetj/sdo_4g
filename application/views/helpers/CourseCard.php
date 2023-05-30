<?php
include "Card.php";
class HM_View_Helper_courseCard extends HM_View_Helper_Card
{

    public function courseCard($item, $fields, $attribs = array())
    {

        $item->developers = implode(',<br/>', Zend_registry::get('serviceContainer')->getService('Course')->getDevelopers($item->CID));

//        $fields['developers'] = _('Разработчики');
//        $fields['Description'] = _('Описание');

        foreach($fields as $key => $title){

            if (!empty($item->$key) || method_exists($item, substr($key, 0, strpos($key, '(')))) {

                if (false !== strstr($key, '(')) {
                    $param = substr($key, strpos($key, '(')+1, strpos($key, ')')-strpos($key, '(')-1);
                    $funcname = substr($key, 0, strpos($key, '('));
                    if ($param) {
                        $item->$key = call_user_func(array($item, $funcname), $param);
                    } else {
                        $item->$key = $item->$funcname();
                    }
                }

            }else{
                $item->$key = '-';
            }
        }


        $this->view->fields  = $fields;
        $this->view->item    = $item;
        if (!isset($attribs['title'])) {
            $attribs['title'] = _('Карточка');
        }
        $this->view->attribs = $attribs;
        return $this->view->render('card.tpl');
    }
}