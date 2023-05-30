<?php
/**
 * Description of BlockingMessageController
 *
 * @author slava
 */
class IndexController extends HM_Controller_Action {
    
    public function indexAction() {
        
    }
    
    public function notifiesAction() {
        /*@var $filter Es_Entity_AbstractFilter */
        $filter = $this->getService('ESFactory')->newFilter();
        $filter->setUserId((int)$this->getService('User')->getCurrentUserId());
        $ev = $this->getService('EventServerDispatcher')->trigger(
            Es_Service_Dispatcher::EVENT_PULL_NOTIFIES,
            $this,
            array('filter' => $filter)
        );
        $notifies = $ev->getReturnValue();
        $notifyTypeNames = array();
        $resp = array();
        foreach ($notifies as $index => $notify) {
            $eventType = $notify->getEventType();
            if (!array_key_exists($eventType->getId(), $resp)) {
                $resp[$eventType->getId()] = array(
                    'eventTypeName' => $eventType->getLocatedName(),
                    'eventTypeId' => $eventType->getId()
                );
            }
            $notifyType = $notify->getNotifyType();
            $notifyTypeNames[$notifyType->getLocatedName()] = $notifyType->getId();
            $resp[$eventType->getId()][$notifyType->getLocatedName()] = (int)$notify->isActive();
        }
        $resp = array_values($resp);
        $fields = array_keys(current($resp));
        reset($resp);
        
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/grid.css');
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/rgsu_style.css');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/lib/jquery/jquery.collapsorz_1.1.min.js');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/content-modules/grid.js');
        
        /*@var $grid Bvb_Grid_Deploy_Table */
        $grid = Bvb_Grid::factory('table');
        $grid->setSource(new Bvb_Grid_Source_Array($resp, $fields));
        $grid->setExport(array());
        $grid->setAjax('grid');
        $grid->setImagesUrl('/images/bvb/');
        $grid->setEscapeOutput(true);
        $grid->setAlwaysShowOrderArrows(false);
        $grid->setNumberRecordsPerPage(16);
        $grid->setColumnsHidden(array('eventTypeId'));
        
        $grid->updateColumn('eventTypeName', array(
            'title' => _('Событие в системе')
        ));
        
        foreach ($notifyTypeNames as $tIndex => $tName) {
            $grid->updateColumn($tIndex, array(
                'callback' => array(
                    'function' => array($this, 'drawNotificationSwitcher'),
                    'params' => array('{{eventTypeId}}', '{{'.$tIndex.'}}', $tName)
                )
            ));
        }
        
        $filters = new Bvb_Grid_Filters();
        $grid->addFilters($filters);
        
        $this->view->grid = $grid->deploy();
        
    }
    
    public function geteventsAction() {
        
    }
    
    public function drawNotificationSwitcher($eventTypeId, $isActive, $notifyTypeId) {
        return $this->view->partial('partials/notificationSwitcher.tpl', array(
            'eventTypeId' => $eventTypeId,
            'isActive' => $isActive,
            'notifyTypeId' => $notifyTypeId
        ));
    }
    
}

?>
