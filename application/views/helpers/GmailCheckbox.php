<?php
class HM_View_Helper_GmailCheckbox extends HM_View_Helper_Abstract
{
    public function gmailCheckbox($name = "checkbox", $id = "checkbox", $options = null)
    {
        $this->view->name = $name;
        $this->view->id = $id;
        $this->view->options = $options;
        return $this->view->render('gmailcheckbox.tpl');    
    }
}