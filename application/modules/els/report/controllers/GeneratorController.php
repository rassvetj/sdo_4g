<?php
class Report_GeneratorController extends HM_Controller_Action_Report
{

    public function init()
    {
        parent::init();
        $this->getService('Unmanaged')->setSubHeader(_('Создание отчёта'));
    }

    public function constructAction()
    {
        $report_id = $this->_getParam('report_id', false);

        if ($report = $this->getService('Report')->getOne($this->getService('Report')->find($report_id))) {

            $this->getService('Unmanaged')->setSubHeader($report->name);

            $config = new HM_Report_Config();
            if (!$report->domain || !$config->getDomain($report->domain)) {
                $this->_flashMessenger->addMessage(array('message' => _('Выберите область отчёта'), 'type' => HM_Notification_NotificationModel::TYPE_ERROR));
                $this->_redirector->gotoSimple('index', 'generator', 'report', array());
            }

            $this->view->name = $report->name;
            $this->view->dataFields = ( strlen($report->fields) )? unserialize($report->fields) : array();
            $this->view->reportId = $report->report_id;
            $this->view->domain = $report->domain;
            $this->view->fields = $config->getDomain($report->domain);
        }
    }

    public function gridAction()
    {

/*
        $fields = array(
            array(
                'field' => '	StudyGeneral.Person.personId',
                'options' => array(
                    'aggregation' => 'count',
                    'hiden' => 0,
                    'input' => 0,
                ),
                'title' => '№ пользователя; Количество'
            ),
            array(
                'field' => 'StudyGeneral.Graduated.graduatedSubjectMark',
                'options' => array(
                    'function' => 'notempty',
                    'hiden' => 0,
                    'input' => 0,
                ),
                'title' => 'Итоговая оценка; Выставлена'
            ),


        );*/

        $fields = $this->_getParam('fields', $fields);
        if (is_array($fields) && count($fields)) {
            Zend_Registry::get('session_namespace_default')->report['generator']['fields'] = $fields;
            $this->getRequest()->setParam('gridmod', 'ajax');
        } else {
            if (isset(Zend_Registry::get('session_namespace_default')->report['generator']['fields'])) {
                $fields = Zend_Registry::get('session_namespace_default')->report['generator']['fields'];
            }
        }

        $config = new HM_Report_Config();
        $report = new HM_Report();
        $report->setConfig($config);
        $report->setFields($fields);

        $grid = $report->getGrid($this);
        $this->view->grid = $grid->deploy();
    }

    public function saveAction()
    {

        $this->_helper->ContextSwitch()
                      ->setAutoJsonSerialization(true)
                      ->addActionContext('save', 'json')
                      ->initContext('json');

        $reportId = $this->_getParam('report_id', 0);
        $fields = $this->_getParam('fields', array());


        if ($this->isAjaxRequest()) {
            if ( $reportId && count($fields) ) {
                foreach($fields as $index => $field) {
                    if (isset($field['title'])) {
                        $fields[$index]['title'] = iconv('UTF-8', Zend_Registry::get('config')->charset, $field['title']);
                    }
                }
                $report = $this->getService('Report')->update(array('report_id' => $reportId,'fields' => serialize($fields)));
                $this->view->report_id = $report->report_id;
            }
            // convert field names
        }

    }
}