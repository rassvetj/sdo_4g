<?php

class HM_View_Helper_LightDialogLink extends HM_View_Helper_Abstract
{

    public function lightDialogLink($url = '', $text = '')
    {
        $this->view->url   = $url;
        $this->view->text = $text;

        return $this->view->render('lightdialoglink.tpl');
    }
}