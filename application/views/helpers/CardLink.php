<?php

class HM_View_Helper_CardLink extends HM_View_Helper_Abstract
{

    public function cardLink($url = '', $title = null, $type = 'icon', $className = 'pcard', $relName = 'pcard', $iconType = false)
    {
        if (null == $title) $title = _('Карточка');
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/lib/jquery/jquery-ui.lightdialog.js'));
        $this->view->url   = $url;
        $this->view->title = $title;
        $this->view->type  = $type;
        $this->view->iconType  = $iconType;
        $this->view->cls   = is_array($className) ? $className : array( $className );
        $this->view->rel   = $relName;

        return $this->view->render('cardlink.tpl');
    }
}