<?php

class Infoblock_SessionCalendarController extends HM_Controller_Action_Rest
{

    public function getAction()
    {

        $currentMonth = $this->_getParam('month');

        $result = array();

        $dayCount = date('t', strtotime($currentMonth));

        $from = new HM_Date($currentMonth . '-01', 'y-M-d');
        $to   = new HM_Date($currentMonth . '-' . $dayCount, 'y-M-d');
        //pr('begin LIKE "' . date('Y', $from->getTimestamp()) . '-' . date('m', $from->getTimestamp()) . '-%" OR end LIKE "' . date('Y', $from->getTimestamp()) . '-' . date('m', $from->getTimestamp()) . '-%"');
        /*$sessions = $this->getService('Subject')->fetchAll(
            array(
                'base = ?' => HM_Subject_SubjectModel::BASETYPE_SESSION,
                //'integration_type = ?' => HM_Subject_SubjectModel::INTEGRATION_TYPE_NONE,
              //  '((period = '
              //     .
              //     HM_Subject_SubjectModel::PERIOD_DATES . ' AND ((begin LIKE "' . date('Y', $from->getTimestamp()) . '-' . date('m', $from->getTimestamp()) . '-%" OR end LIKE "' . date('Y', $from->getTimestamp()). '-' . date('m', $from->getTimestamp()) . '-%") OR (begin < ' . date('Y-m-d', $from->getTimestamp()). ' AND end > ' . date('Y-m-d', $to->getTimestamp()). ') )))'

            )
        );*/

        $where = $this->getService('Subject')->quoteInto(
            array(
                'base = ?',
                ' AND begin <= ?',
                ' AND end >= ?'
            ),
            array(
                HM_Subject_SubjectModel::BASETYPE_SESSION,
                $this->getService('User')->getDateTime($to->setHour(23)->setMinute(59)->setSecond(59)->getTimestamp()),
                $this->getService('User')->getDateTime($from->setHour(0)->setMinute(0)->setSecond(0)->getTimestamp())
            ));
        $sessions = $this->getService('Subject')->fetchAllManyToMany('User', 'Teacher', $where);

        //pr($sessions); exit;

       // pr( HM_Subject_SubjectModel::PERIOD_DATES . ' AND ((begin LIKE "' . date('Y', $from->getTimestamp()) . '-' . date('m', $from->getTimestamp()) . '-%" OR end LIKE "' . date('Y', $from->getTimestamp()). '-' . date('m', $from->getTimestamp()) . '-%") OR (begin < ' . date('Y-m-d', $from->getTimestamp()). ' AND end > ' . date('Y-m-d', $to->getTimestamp()). ') )))'); exit;
        $teachers = array();

        foreach($sessions as $session){
            //$tTeachers = $this->getService('Subject')->getAssignedTeachers($session->subid);
            $tTeachers = $session->teachers;

            if (!count($tTeachers)) continue;

            $fios = array();
            foreach($tTeachers as $teacher){
                $fios[] = $teacher->getName();
            }

            $teachers[$session->subid] = implode(', ', $fios);
        }


        foreach($sessions as $session){
            $studentsCount = $this->getService('Student')->countAll('CID = ' . $session->subid);

            $dateFrom = strtotime($session->begin);
            $dateTo = strtotime($session->end);

            for($i = 1; $i <= $dayCount; $i++){
                $currDate = new HM_Date( $currentMonth . '-' . $i, 'y-M-d');

                if(($currDate->getTimestamp() >= $dateFrom && $currDate->getTimestamp() <= $dateTo)){

                    $begin = new HM_Date(strtotime($session->begin));
                    $end = new HM_Date(strtotime($session->end));
                    //list($begin, $_null) = explode(' ', $begin->getDate());
                    //list($end, $_null) = explode(' ', $end->getDate());
                    $begin = $begin->get(Zend_Date::DATE_MEDIUM);
                    $end   = $end->get(Zend_Date::DATE_MEDIUM);
                    $result[] = array(
                        'date'  => strtotime( $currentMonth . '-' . $i),
                        'this_month_start' => ($from->getTimestamp() <= $dateFrom && $to->getTimestamp() >= $dateFrom)? 'y' : 'n',
                        'title' => $session->name,
                        'url'   => $this->view->url(array('module' => 'subject', 'controller' => 'index', 'action' => 'card', 'subject_id' => $session->subid), null, true),
                        'attendees' => $studentsCount,
                        'description' =>
                            _('Преподаватели') . ': ' .  ($teachers[$session->subid] ? $teachers[$session->subid] : _('Нет')) . ' <br/>' .

                            _('Дата начала') . ': ' .  ($begin) . ' <br/>' .
                            _('Дата окончания') . ': ' .  ($end) . ' <br/>' .
                            _('Количество участников') . ': ' .  ($studentsCount) . ' <br/>'
                    );
                }
            }


        }

        //pr($result); exit;
        $this->view->assign($result);
    }

    public function indexAction()
    {

        $this->getAction();

    }


}