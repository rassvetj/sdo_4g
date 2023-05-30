<?php
class Programm_IndexController extends HM_Controller_Action
{
    private $_programmId = 0;
    private $_programm = null;

    public function init()
    {
        $this->_programmId = (int) $this->_getParam('programm_id' , 0);

        $this->_programm = $this->getOne($this->getService('Programm')->find($this->_programmId));

        $this->getService('Unmanaged')->setSubHeader($this->_programm->name);

        parent::init();
    }

    public function indexAction()
    {
         $subjects = $this->getService('Subject')->fetchAll(
             $this->quoteInto('base = ?', HM_Subject_SubjectModel::BASETYPE_PRACTICE),
             'name'
         );

        $sessions = $this->getService('Subject')->fetchAll(
            $this->quoteInto('base = ?', HM_Subject_SubjectModel::BASETYPE_SESSION),
            'name'
        );

        $collection = $this->getService('ProgrammEvent')->fetchAll(
            $this->quoteInto(
                array('programm_id = ?', ' AND type = ?'),
                array($this->_programmId, HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT)
            )
        );

        $events = array();
        if (count($collection)) {
            $events = $this->getService('Subject')->fetchAll(
                $this->quoteInto('subid IN (?)', $collection->getList('item_id'))
            );
        }

        $users = $this->getService('Programm')->getProgrammUsers($this->_programmId);
        if (count($users)) {
            $this->view->message = sprintf(_('Внимание! По данной программе уже обучается %s чел.'), count($users)); //._(' человек <br>Удаление не элективного курса уничтожит набранные по нему результаты обучения.<br> Элективный курс у пользователя не удаляется, если он на него подписан.<br> Перевод курса со статуса "Элективный" в "Обязательный" автоматически подпишет всех слушателей программы на этот курс.<br> Перевод курса со статуса "Обязательный" в "Элективный" оставит подписанными всех слушателей программы на этом курсе.');
        }


        $this->view->page = 0;
        $this->view->events = $events;
        $this->view->collection = $collection;
        $this->view->subjects = $subjects;
        $this->view->sessions = $sessions;
        $this->view->programmId = $this->_programmId;
    }

    public function assignAction()
    {
        if ($this->isAjaxRequest()) {
            $this->getHelper('viewRenderer')->setNoRender();

            $ids = $this->_getParam('course_id', array());
            $isElectives = $this->_getParam('idElective', array());


            $oldSubjects = $this->getService('Programm')->getSubjects($this->_programmId);
            $oldIds = array ('Elektive' => array(), 'noElektive' => array());
            $newIds = array ('Elektive' => array(), 'noElektive' => array());
            if ($oldSubjects) {
                foreach ($oldSubjects as $oldSubject) {
                    if ($oldSubject->isElective) {
                        $oldIds['Elektive'][] =  $oldSubject->item_id;
                    } else {
                        $oldIds['noElektive'][] =  $oldSubject->item_id;
                    }
                }
            }

            if (count($ids)) {
                foreach($ids as $key=>$id) {

                    if ($isElectives[$key]) {
                        $newIds['Elektive'][] =  $id;
                    } else {
                        $newIds['noElektive'][] =  $id;
                    }

                    $this->getService('Programm')->assignSubject($this->_programmId, $id, $isElectives[$key]);
                }
            }

            $addIds = array_diff($newIds['noElektive'], $oldIds['noElektive']);
            $removeIds = array_diff($oldIds['noElektive'], $newIds['noElektive']);
            /* обновляем список курсов для пользователей программы, возвращаем МИДы слушателей для которых обновили курсы*/
            $usersIds = $this->getService('Programm')->updateCoursesForUsers($this->_programmId, $addIds, $removeIds);

            /* обновляем список курсов на группах */
            $this->getService('Programm')->updateCoursesForGroups($this->_programmId, $newIds, $oldIds);


            /* Удаляем связь программа курс */
            if (count($ids)) {
                $this->getService('ProgrammEvent')->deleteBy(
                    $this->quoteInto(
                        array('programm_id = ?', ' AND type = ?', ' AND item_id NOT IN (?)'),
                        array($this->_programmId, HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT, $ids)
                    )
                );
            } else {
                /* Удаляем все курсы */
                $this->getService('ProgrammEvent')->deleteBy(
                    $this->quoteInto(
                        array('programm_id = ?', ' AND type = ?'),
                        array($this->_programmId, HM_Programm_Event_EventModel::EVENT_TYPE_SUBJECT)
                    )
                );
            }

        } else {
            $this->_redirector->gotoSimple('index');
        }
    }

    public function unassignAction()
    {
        $events = explode(',', $this->_getParam('postMassIds_grid', ''));

        if (count($events)) {
            foreach($events as $eventId) {
                $this->getService('ProgrammEvent')->delete($eventId);
            }
        }

        $this->_redirector->gotoSimple('index', 'index', 'programm', array('programm_id' => $this->_programmId));
    }
}