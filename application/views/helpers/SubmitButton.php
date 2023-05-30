<?php
class HM_View_Helper_SubmitButton extends Zend_View_Helper_Abstract
{
    public function submitButton($name = 'ok')
    {
        return okbutton($name);
    }
}