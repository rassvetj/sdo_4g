<?php

require_once APPLICATION_PATH . "/modules/els/lesson/controllers/ListController.php";

class Feedback_PollController extends Lesson_ListController
{
    protected $_formName = 'HM_Form_Poll';
    protected $_module = 'feedback';
    protected $_controller = 'poll';
    
    
    
    public function indexAction()
    {
        
        $subjectId = (int) $this->_getParam('subject_id', 0);

        $select = $this->getService('Lesson')->getSelect();
        $select->from(array('l' => 'lessons'),
                    array(
                        'lesson_id' => 'l.SHEID',
                        'l.SHEID',
                        'l.title',
                        'l.typeID',
                        'l.begin',
                        'l.end',
                        'l.timetype',
                        'l.condition',
                        'l.cond_sheid',
                        'l.cond_mark',
                        'l.cond_progress',
                        'l.cond_avgbal',
                        'l.cond_sumbal'
                    )
                )
                ->joinLeft(array('s' => 'scheduleID'), 'l.SHEID = s.SHEID', array())
                ->joinLeft(array('p' => 'People'), 's.MID = p.MID', array('mids' => new Zend_Db_Expr("GROUP_CONCAT(p.MID)")))
                ->where('CID = ?', $subjectId)
                ->where('typeID IN (?)', array_keys(HM_Event_EventModel::getExcludedTypes()))
                ->group(
                    array(
                    	'l.SHEID', 
                    	'l.title',
                        'l.typeID',
                        'l.begin',
                        'l.end',
                        'l.timetype',
                        'l.condition',
                        'l.cond_sheid',
                        'l.cond_mark',
                        'l.cond_progress',
                        'l.cond_avgbal',
                        'l.cond_sumbal'
                    )
                );

        $grid = $this->getGrid($select,
            array(
                'SHEID' => array('hidden' => true),
                'lesson_id' => array('hidden' => true),
                'title' => array('title' => _('Название')),
                'typeID' => array('title' => _('Тип')),
                'begin' => array('title' => _('Ограничение по времени')),
                'mids' => array(
                    'title' => _('Назначен'),
                    'callback' => array(
                        'function' => array($this, 'updateFios'),
                        'params' => array('{{mids}}')
                    )                        
                ),
                'condition' =>  array('hidden' => true),
                'end' => array('hidden' => true),
                'timetype' => array('hidden' => true),
                'cond_sheid' => array('hidden' => true),
                'cond_mark' => array('hidden' => true),
                'cond_avgbal' => array('hidden' => true),
                'cond_sumbal' => array('hidden' => true),
                'cond_progress' => array('hidden' => true) 
            ),
            array(
                'title' => null,
                'typeID' => array('values' => HM_Event_EventModel::getExcludedTypes()),
                'begin' => array('render' => 'DateTimeStamp'),
                'condition' => array('values' => array('0' => _('Нет условия'), '1' => _('Есть условие'))),
            	//'fios' => null,
            )

        );


        $grid->addAction(
            array('module' => 'lesson', 'controller' => 'result', 'action' => 'index'),
            array('lesson_id'),
            _('Просмотр результатов')
        );
        
        $grid->addAction(array(
            'module' => 'feedback',
            'controller' => 'poll',
            'action' => 'edit'
        ),
            array('lesson_id'),
            $this->view->icon('edit')
        );

        $grid->addAction(array(
            'module' => 'feedback',
            'controller' => 'poll',
            'action' => 'delete'
        ),
            array('lesson_id'),
            $this->view->icon('delete')
        );

        $grid->addMassAction(array('action' => 'delete-by'), _('Удалить'), _('Вы подтверждаете удаление отмеченных занятий?'));


        $grid->updateColumn('typeID',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'getTypeString'),
                    'params' => array('{{typeID}}')
                )
            )
        );

        $grid->updateColumn('begin',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'getDateTimeString'),
                    'params' => array('{{begin}}', '{{end}}', '{{timetype}}')
                )
            )
        );
        
        $grid->updateColumn('title',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'updateName'),
                    'params' => array('{{title}}', '{{lesson_id}}')
                )
            )
        );
        
        $grid->updateColumn('condition',
            array(
                'callback' =>
                array(
                    'function' => array($this, 'getConditionString'),
                    'params' => array('{{cond_sheid}}', '{{cond_mark}}', '{{cond_progress}}', '{{cond_avgbal}}', '{{cond_sumbal}}')
                )
            )
        );

        
        $this->view->gridAjaxRequest = $this->isGridAjaxRequest();
        $this->view->grid = $grid->deploy();

        //Zend_Session::namespaceUnset('multiform');    
        /*
        $subjectId = (int) $this->_getParam('subject_id', 0);
        $paginator = $this->getService('Lesson')->getPaginator(
           'CID = '.$subjectId.' AND timetype = 0',
           'begin'
        );

        $this->view->paginator = $paginator;
         *
         */
    }
    
    
   protected function assignStudents($lessonId, $students){

       $lesson = $this->getOne($this->getService('Lesson')->find($lessonId));
       if (is_array($students) && count($students)) {
           $lesson->getService()->assignStudents($lessonId, $students);
       } else {
           $this->getService('LessonAssign')->deleteBy($this->getService('LessonAssign')->quoteInto('SHEID = ? AND MID > 0', $lessonId));
           $this->getService('LessonDeanPollAssign')->deleteBy($this->getService('LessonAssign')->quoteInto('lesson_id = ?', $lessonId));
       }
       /*
       $lesson = $this->getService('Lesson')->getOne($this->getService('Lesson')->find($lessonId));
       $subjectId = $lesson->CID;

       switch($lesson->typeID){
           case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_TEACHER:
               $students = $this->getService('Subject')->getAssignedTeachers($subjectId);
               $students = array_keys($students->getList('MID', 'LastName'));
           case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_STUDENT:
               parent::assignStudents($lessonId, $students);
               break;
           case HM_Event_EventModel::TYPE_DEAN_POLL_FOR_LEADER:
               $users = $this->getService('Subject')->getAssignedUsers($subjectId);
               
               $leaders = array();

               foreach($users as $user){
                   if(in_array($user->MID, $students)){
                       $leaders[$user->mid_head][] = $user->MID;
                   }
               }
               
               
               unset($leaders[0]);

               parent::assignStudents($lessonId, array_keys($leaders));
               
               foreach($leaders as $leader => $students){
                   $this->getService('LessonDeanPollAssign')->assignStudents($lessonId, $students, $leader);
                   
               }
               break;
           default:
               
               break;
       }

*/
    }
    
    
    public function getTypeString($typeId)
    {
        $types = HM_Event_EventModel::getExcludedTypes();
        if (isset($types[$typeId])) {
            return $types[$typeId];
        }
    }
    
    public function updateName($field, $id){
    //lesson/execute/index/lesson_id/89/subject_id/92
        return '<a href="' . $this->view->url(array('module' => 'lesson', 'controller' => 'execute', 'action' => 'index', 'lesson_id' =>$id, 'subject_id' => $this->_getParam('subject_id'))). '">'. $field.'</a>';
    }
    

    
    public function updateFios($mids) 
    {
        // @todo: вообще-то нехорошо делать это для каждой строчки грида; но строчек бывает не более 3х
        $mids = explode(',', $mids);
        if (count($mids)) {
            $users = $this->getService('User')->fetchAll(array('MID IN (?)' => $mids), 'LastName');
            
            $count = count($users);
            $result = ($count > 1) ? array('<p class="total">' . sprintf(_n('пользователь plural', '%s пользователь', $count), $count) . '</p>') : array();
            foreach($users as $user){
                $name = $user->getName();
                $result[] = "<p>{$name}</p>";
            }
            
            if($result) return implode('',$result);
        }
        return _('Нет');
    }
}