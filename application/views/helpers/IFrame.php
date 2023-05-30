<?php
class HM_View_Helper_IFrame extends HM_View_Helper_Abstract
{
    public function iFrame($url, $options = null)
    {
        $this->view->url = $url;

        return $this->view->render( 'iframe.tpl' );
    }
}