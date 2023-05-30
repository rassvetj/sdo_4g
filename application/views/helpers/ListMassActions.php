<?php
/*
 * Контейнер для групповых действий со списком; визуально похож на грид, но никакого отношения не имеет
 */
class HM_View_Helper_ListMassActions extends HM_View_Helper_Abstract
{
    public function listMassActions($options = array())
    {
    	$this->view->headLink()->appendStylesheet($this->view->serverUrl('/css/content-modules/grid.css'));
    	
    	if (isset($options['pagination'])) {
    	    list($paginator, $scrollingStyle, $partial, $params) = $options['pagination'];
    	    $this->view->pagination = $this->view->paginationControl($paginator, $scrollingStyle, $partial, $params);
    	}
    	$this->view->export = $options['export'];
    	
        return $this->view->render('list-mass-actions.tpl');
    }
}