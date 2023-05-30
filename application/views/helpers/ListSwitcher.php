<?php
class HM_View_Helper_ListSwitcher extends Zend_View_Helper_Abstract
{
    public function listSwitcher($modes, $urlArr, $selected)
    {
        $plushkas = array();
        foreach ($modes as $key => $title) {
            $urlArr['list-switcher'] = $key;
            $url = $this->view->url($urlArr);
    		$plushkas[] = sprintf(
    			'<div class="%s %s" onClick="%s;%s;%s">%s</div>',
    			$key,
    			($selected == $key) ? '_u_selected' : '',
    			"top.location.href = '{$url}'",
    			"$('._grid_gridswitcher div').removeClass('_u_selected')",
    			"$(this).addClass('_u_selected')",
    			$title
    		);
        }
        return sprintf(
        	'<div class="_grid_gridswitcher"><div class="_d_title">%s</div>%s</div>',
        	_('Выводить в списке:'),
            implode($plushkas)
        );
    }
}