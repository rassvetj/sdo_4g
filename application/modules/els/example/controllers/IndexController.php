<?php

class Example_IndexController extends HM_Controller_Action_Crud
{
    public function init()
    {
        $this->_helper->ContextSwitch()->setAutoJsonSerialization(true)->addActionContext('get', 'json')->initContext('json');

        parent::init();
    }

    const SCHEDULE_TYPE_STUDENT = 0x01;
    const SCHEDULE_TYPE_TEACHER = 0x02;

    public function getAction()
    {
        $from = $this->_getParam('from');
        $to   = $this->_getParam('to');

        $scheduleInfo = $this->getScheduleInfo();

        $data = $this->getData($from, $to, $scheduleInfo['type'], $scheduleInfo['id']);

        $this->view->assign(array(
            'from' => $from,
            'to'   => $to,
            'data' => $data
        ));
    }

    protected function getData($fromDate, $toDate, $type, $id)
    {
        $url = 'http://dist.urfu.ru/timetable_viewer/timetable_export/?dateFrom='.$fromDate.'&dateTo='.$toDate;

        switch ($type) {
            case self::SCHEDULE_TYPE_STUDENT:
                $url .= '&grpName='.urlencode($id);
                break;
            case self::SCHEDULE_TYPE_TEACHER:
                $url .= '&snils='.urlencode($id);
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_USERPWD => 'timetable_export:3pn7nye9',
            CURLOPT_RETURNTRANSFER => true
        ));

        $data = curl_exec($ch);

        curl_close($ch);

        $xml = new SimpleXMLElement($data);
        $pairs = $xml->children();


        $data = array();

        foreach ($pairs as $pair) {
            $pair = (array) $pair;

            foreach ($pair as &$pairAttr) {
                if ($pairAttr instanceof SimpleXMLElement) {
                    $pairAttr = (array) $pairAttr;
                }
            }
            $data[] = (array) $pair;
        }

        return $data;
    }

    protected function getScheduleInfo()
    {
        if (false) {
            return array(
                'type' => self::SCHEDULE_TYPE_STUDENT,
                'id' => 'ЭНЗ-130301с-НЕд'
            );
        } else {
            return array(
                'type' => self::SCHEDULE_TYPE_TEACHER,
                'id' => '01618773656'
            );
        }
    }

    public function indexAction()
    {
        $from = date('d.m.Y', strtotime('monday this week'));
        $to   = date('d.m.Y', strtotime('sunday this week'));

        // временно
        $from = '03.02.2014';
        $to   = '09.02.2014';

        $scheduleInfo = $this->getScheduleInfo();

        $data = $this->getData($from, $to, $scheduleInfo['type'], $scheduleInfo['id']);

        $view = $this->view;

        $view->assign(array(
            'type' => $scheduleInfo['type'],
            'from' => $from,
            'to'   => $to,
            'data' => $data,
            'url' => $view->url(array(
                'module' => 'example',
                'controller' => 'index',
                'action' => 'get'
            ))
        ));

        $this->_helper->getHelper('layout')->disableLayout();

        Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');

    }
}