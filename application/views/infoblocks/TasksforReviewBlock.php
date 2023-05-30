<?php

require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
/*
	Виджет 	ЗАДАНИЯ НА ПРОВЕРКУ (№task#12882)
		перечень учебных курсов, доступных данному пользователю с ролью «преподаватель». 
		Под каждым курсов - перечень занятий с типом "Задание". 
		Если в курсе нет заданий - он вообще не попадает в список. 
*/

class HM_View_Infoblock_TasksForReviewBlock extends HM_View_Infoblock_ScreenForm {

    protected $id = 'tasksForReviewBlock';

    /**
     * Получение учебных курсов     
     * @author Elena.Mirzoyan
     */
    public function TasksForReviewBlock($title = null, $attribs = null, $options = null) {
    try {
    
        $currentUserId = (int) $this->getService('User')->getCurrentUserId();
        $select = $this->getService('User')->getSelect();
        if ($this->getService('Acl')->inheritsRole($this->getService('User')->getCurrentUserRole(), HM_Role_RoleModelAbstract::ROLE_TEACHER)) {
                $eventTypesSubselect = $this->getService('User')->getSelect()->from('events', array('event_id'=>'-(event_id)'))
                    ->where('tool = ?', HM_Event_EventModel::TYPE_TASK)->query()->fetchAll();
                if(!count($eventTypesSubselect)){
                    $eventTypesSubselect = array(-1);
                }

        		$select->from(array('s' => 'subjects'), array(
                 	'subname' => 's.name',
        			'subid' => 's.subid',
        			'sheid' => 'schedule.SHEID',
        			'schetitle' => 'schedule.title'
        		))
                ->joinInner('Teachers', 's.subid = Teachers.CID', array())
                ->joinInner('schedule', 's.subid = schedule.CID', array())
                ->where('Teachers.MID = ?', $currentUserId)


                ->where($this->getService('User')->quoteInto(
                    array('schedule.typeID = ? OR ','schedule.typeID in (?)'),
                    array(HM_Event_EventModel::TYPE_TASK, $eventTypesSubselect)
                    ))
                ;
                $rows = $select->query()->fetchAll();
                if (count($rows)) { // Проверяем наличие учебных курсов 

                    foreach ($rows as $row) 
                        $subids[$row['subid']] = 1;
                    $courses = $this->getService('Subject')->fetchAll("subid in (".implode(',', array_keys($subids)).")");
                    foreach($courses as $c) {
                        if($c->isExpired())
                            $coursesExpired[$c->subid] = 1;
                    }

                    foreach ($rows as $row) {
                        if(isset($coursesExpired[$row['subid']])) continue;

                        $type = array();
                        
                        $select    = $this->getService('Lesson')->getSelect();
                        $subSelect = clone $select;
                        
                        $case = '(CASE WHEN(to_whom=0) THEN user_id ELSE to_whom END)';
                        $subSelect->from(
                            array('ri' => 'interview'),
                            array(
                                'real_user_id' => new Zend_Db_Expr($case),
                                'real_interview_id' => new Zend_Db_Expr('MAX(ri.interview_id)'),
                                
                            )
                        );
                        $subSelect->where('ri.lesson_id = ?', $row['sheid']);
                        $subSelect->group(new Zend_Db_Expr($case));
                        
                        $select->from(
                            array('s' => 'scheduleID'),
                            array(
                                'count_' => 'count(i.type)',
                                'i.type'
                            )
                        );
                        $select->joinInner(array('ss' => $subSelect), 's.MID = ss.real_user_id', array());
                        $select->joinInner(array('i' => 'interview'), 'i.interview_id = ss.real_interview_id', array());
                        $select->where('SHEID = ?', $row['sheid']);
                        $select->group('i.type');
                        
                        $stmt = $select->query();
                        $stmt->execute();
                        $rows = $stmt->fetchAll();


                        $type = array();
                        
                        foreach($rows as $value){
                            $type[$value['type']] = $value['count_'];
	                	}

//                        $interview_sql = sql("SELECT i.type  FROM  scheduleID  AS s INNER JOIN ("
//                                . "SELECT (CASE WHEN(to_whom=0) THEN user_id ELSE to_whom END) AS real_user_id, MAX(ri.interview_id) AS real_interview_id "
//                                . "FROM `interview` AS ri "
//                                . "WHERE (ri.lesson_id ='" . $row['sheid'] . "') "
//                                . "GROUP BY (CASE WHEN(to_whom=0) THEN user_id ELSE to_whom END)"
//                                . ") AS `ss` ON s.MID = ss.real_user_id "
//                                . "INNER JOIN `interview` AS `i` ON i.interview_id = ss.real_interview_id "
//                                . "WHERE (SHEID = '" . $row['sheid'] . "')");
//                        while ($interview_row = sqlget($interview_sql)) {
//                            $type[$interview_row['type']] = $type[$interview_row['type']] + 1;
//                        }
                        

                        for ($i = 5; $i >= 0; $i--) {
							$type[$i]=empty($type[$i])?0:$type[$i];
						}
                	

	                	$subjects[$row['subid']]['subname']=$row['subname'];
	                	$subjects[$row['subid']]['lessons'][$row['sheid']]= array(
	                	'schetitle'=>$row['schetitle'],
	                	'task'=>$type[HM_Interview_InterviewModel::MESSAGE_TYPE_TASK],
	                	'question'=>$type[HM_Interview_InterviewModel::MESSAGE_TYPE_QUESTION],
	                	'test'=>$type[HM_Interview_InterviewModel::MESSAGE_TYPE_TEST],
	                	'answer'=>$type[HM_Interview_InterviewModel::MESSAGE_TYPE_ANSWER],
	                	'condition'=>$type[HM_Interview_InterviewModel::MESSAGE_TYPE_CONDITION],
	                	'ball'=>$type[HM_Interview_InterviewModel::MESSAGE_TYPE_BALL],
	                	'url'=>$this->view->url(array('module' => 'lesson', 'controller' => 'result', 'action' => 'index','subject_id'=>$row['subid'], 'preview' => 1,'gridmod' => '','lesson_id'=>$row['sheid'])),
	                	);
		            }
                } else {
                	// Отсутствуют данные для отображения
                	$this->view->empty = true;
                }
                $this->view->subjects = $subjects;
            } else {
        	//message that you have not enought permissions
        }

        $content = $this->view->render('TasksForReviewBlock.tpl');

        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/content-modules/schedule_table.css');
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/tasks_for_review/style.css');

        return parent::screenForm($title, $content, $attribs);
		} catch (Exception $e) {
   				 echo 'Выброшено: ',  $e->getMessage(), "\n";
			}
    }

}
