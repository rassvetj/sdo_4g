<?php
class Marksheet_IndexController extends HM_Controller_Action
{
    protected $service     = 'Subject';
    protected $idParamName = 'subject_id';
    protected $idFieldName = 'subid';
    protected $id          = 0;

    public function indexAction()
    {

        $fromDate = $this->_getParam('from', '');
        $toDate   = $this->_getParam('to', '');
        $group    = $this->_getParam('groupname', null);

        $this->_setParam('to', null);
        $this->_setParam('from', null);
        $this->_setParam('groupname', null);

        $fromDate = str_replace('_', '.', $fromDate);
        $toDate = str_replace('_', '.', $toDate);


       $courseId = $this->id = (int) $this->_getParam('subject_id', 0);
       if ( $this->_request->isPost() ) {
           if (($fromDate == '' || $toDate == '')) {
               $fromDate = null;
               $toDate   = null;
           }

           if (!$fromDate && !$toDate && !$group) {
               Zend_Registry::get('session_namespace_default')->marksheetFilter = null;
           } else {
               Zend_Registry::get('session_namespace_default')->marksheetFilter[$courseId] = array( 'to'    => $toDate,
                                                                                                    'from'  => $fromDate,
                                                                                                    'group' => $group);
           }

       } else {
           $dates = Zend_Registry::get('session_namespace_default')->marksheetFilter[$courseId];
           $fromDate = $dates['from'];
           $toDate   = $dates['to'];
           $group    = $dates['group'];
       }

       /*if(($fromDate == '' || $toDate == '') && $this->_request->isPost()){
            $fromDate = null;
            $toDate = null;
            Zend_Registry::get('session_namespace_default')->marksheetFilter = null;
        }elseif($fromDate == '' || $toDate == ''){
            $dates = Zend_Registry::get('session_namespace_default')->marksheetFilter[$courseId];
            $fromDate = $dates['from'];
            $toDate = $dates['to'];
        }else{
            Zend_Registry::get('session_namespace_default')->marksheetFilter[$courseId] = array('to' => $toDate, 'from' => $fromDate);
            $dates = Zend_Registry::get('session_namespace_default')->marksheetFilter[$courseId];
        }*/
        $this->view->dates = $dates;

        $subject                       = $this->getOne($this->getService('Subject')->find($courseId));
        $groups                        = $this->getService('Group')->fetchAll(array('cid = ?' => $courseId));
      //  $this->view->groupname         = array(0=>_('-Все-')) + ((count($groups))? $groups->getList('gid','name') : array());

        $studygroups                   = $this->getService('StudyGroupCourse')->getCourseGroups($courseId);

        $this->view->groupname         = array();

        if (count($studygroups)) {
            $this->view->groupname[] =  _('-Группы-');
            foreach ($studygroups as $studygroup) {
                $this->view->groupname['sg_'.$studygroup->group_id] = $studygroup->name;
            }
        }

        if (count($groups)) {
            $this->view->groupname[] =  _('-Подгруппы-');
            foreach ($groups as $item) {
                $this->view->groupname['s_'.$item->gid] = $item->name;
            }
        }


        $this->view->current_groupname = $group;
        $this->view->dates = array('from' => $fromDate, 'to' => $toDate);

        $this->view->setExtended(
            array(
                'subjectName' => $this->service,
                'subjectId' => $this->id,
                'subjectIdParamName' => $this->idParamName,
                'subjectIdFieldName' => $this->idFieldName,
                'subject' => $subject
            )
        );
        
        $score = $this->getService('Lesson')->getUsersScore($courseId, $fromDate, $toDate, $group);
        $this->view->score = $score;
        $this->view->subjectId = $courseId;

    }

    public function setScoreAction()
    {
        $scores = $this->_getParam('score');
        $courseId = $this->_getParam('subject_id', 0);

        $isTutor = $this->getService('Acl')->inheritsRole(
            $this->getService('User')->getCurrentUserRole(),
            HM_Role_RoleModelAbstract::ROLE_TUTOR
        );
        

        if ($scores && !empty($scores) && is_array($scores))
        {
            foreach ($scores as $id => $score)
            {
                list($user_id, $lesson_id) = explode("_", $id);
                $allowTutors = array();
                if(is_numeric($lesson_id)){
                    $allowTutors = $this->getService('Lesson')->fetchAll(
                        $this->getService('Lesson')->quoteInto(
                            'allowTutors = 1 AND SHEID = ?',
                            array($lesson_id)
                        )
                    )->current();
                    $allowTutors = $allowTutors->allowTutors;
                }
                if(!$isTutor || ($isTutor && $allowTutors)){
                    if (null === $score || '' === $score)
                        $score = -1;
                    $this->_setScore($id, $score, $courseId);
                }
            }
        }
        else
        {
            $id = $this->_getParam('id');
            $score = $this->_getParam('score', -1);
            $this->_setScore($id, $score, $courseId);
        }

        echo count($scores);
        exit;
    }

    private function _setScore($id, $score, $courseId)
    {
        $score = iconv('UTF-8', Zend_Registry::get('config')->charset, $score);
        list($pkey, $skey) = explode("_", $id);

        $this->getService('LessonAssign')->setUserScore($pkey, $skey, $score, $courseId);
    }
    
    public function setTotalScoreAction() {
        $persons = $this->_getParam('person', 0);
        $subjectId = $this->_getParam('subject_id', 0);
        
        $lessonAssignService = $this->getService('LessonAssign');
        $userService = $this->getService('User');
        
        
        $notMarked = array();
        foreach($persons as $userId => $value){
            if(!$lessonAssignService->onLessonScoreChanged($subjectId, $userId)){
                $notMarked[] = $userId;
            }
        }
        
        $this->_flashMessenger->addMessage(_('Оценки выставлены успешно!'));
        
        if(count($notMarked)){
            $users = $userService->fetchAll(array('MID IN (?)' => $notMarked));
            if (count($users)) {
                foreach($users as $user) {
                    $userName = $user->getName();
                    $this->_flashMessenger->addMessage(array(
                        'type' => HM_Notification_NotificationModel::TYPE_ERROR,
                        'message' => _("Оценки не были выставлены: ") . $userName
                    ));
                }
            }
        }
        
    }
    
    public function setCommentAction(){
        $comment = $this->_getParam('comment');
        $score = $this->_getParam('score');
        $subjectId = $this->_getParam('subject_id',0);
        $comment = iconv('UTF-8', Zend_Registry::get('config')->charset, $comment);
        foreach($score as $key => $value){
            list($pkey, $skey) = explode("_", $key);
            $this->getService('LessonAssign')->setUserComments($pkey, $skey, $comment, $subjectId);
        }
        echo 'Ok';
        exit;
    }
    
    public function graduateStudentsAction(){
        $person = $this->_getParam('person', 0);
        $courseId = $this->_getParam('subject_id', 0);
        foreach($person as $key => $value) {
            if (!$this->getService('Subject')->assignGraduated($courseId, $key)) {
                echo 'Fail';
                exit;
            }
            /*$student = $this->getOne(
                $this->getService('Student')->fetchAll(
                    array(
                    	'CID = ?' => $courseId, 
                    	'MID = ?' => $key
                    )
                )
            );
            if($student){
                $this->getService('Graduated')->insert(array('MID' => $key, 'CID' => $courseId, 'begin' => $student->time_registered));
                $this->getService('Student')->deleteBy(array('CID = ?'=> $courseId, 'MID = ?' => $key));
            }else{
               echo 'Fail';
               exit;
            } */
       }
       echo 'Ok';
       exit;
    }

    public function clearScheduleAction(){
        $schedule = $this->_getParam('schedule', 0);
        foreach($schedule as $key => $value){
            $this->getService('LessonAssign')->updateWhere(array('V_STATUS' => -1), array('SHEID = ?' => $key));
        }
        echo 'Ok';
        exit;
    }
    
    public function printAction(){
    	
		$this->_helper->layout()->disableLayout();
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
    	$courseId = $this->_getParam('subject_id', 0);
        $subject = $this->getOne($this->getService('Subject')->find($courseId));
        
        $dates = Zend_Registry::get('session_namespace_default')->marksheetFilter[$courseId];
        $score = $this->getService('Lesson')->getUsersScore($courseId,$dates['from'],$dates['to'],$dates['group']);
        
        $this->view->score = $score;
        $this->view->subjectId = $courseId;
    	
    }
    
    public function wordAction(){

		$this->_helper->layout()->disableLayout();
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
    	$subjectId = $this->id = (int) $this->_getParam('subject_id', 0);
        $subject = $this->getOne($this->getService('Subject')->find($subjectId));
        
        $dates = Zend_Registry::get('session_namespace_default')->marksheetFilter[$subjectId];
        $score = $this->getService('Lesson')->getUsersScore($subjectId,$dates['from'],$dates['to'],$dates['group']);
        
        $this->view->score = $score;
        $this->view->subjectId = $subjectId;
        $data =  $this->view->render('index/export.tpl');
        
        $doc = fopen(Zend_Registry::get('config')->path->upload->marksheets.'/'.$subjectId, 'w');
        fwrite($doc, $data);
        fclose($doc);
        
		$this->sendFile($subjectId, 'doc', $subject->name);
    	
    }
    
    public function excelAction(){
    	
		$this->_helper->layout()->disableLayout();
		Zend_Controller_Front::getInstance()->unregisterPlugin('HM_Controller_Plugin_Unmanaged');
		
    	$subjectId = $this->id = (int) $this->_getParam('subject_id', 0);
        $subject = $this->getOne($this->getService('Subject')->find($subjectId));

        $dates = Zend_Registry::get('session_namespace_default')->marksheetFilter[$subjectId];
        $score = $this->getService('Lesson')->getUsersScore($subjectId,$dates['from'],$dates['to'],$dates['group']);
        
        $this->view->score = $score;
        $this->view->subjectId = $subjectId;
        $data =  $this->view->render('index/export.tpl');
        
        $xls = fopen(Zend_Registry::get('config')->path->upload->marksheets.'/'.$subjectId, 'w');
        fwrite($xls, $data);
        fclose($xls);
        
		$this->sendFile($subjectId, 'xls', $subject->name);
    	
    }
    
    public function sendFile($subjectId, $ext = 'doc', $name = null){
    	
        if ($subjectId) {
        	$name = $name ? $name : $subjectId;
            $options = array('filename' => $name.'.'.$ext);
            
            switch(true){
            	case $ext == 'doc':
            		$contentType = 'application/word';
            		break;
            	case $ext == 'xls':
            		$contentType = 'application/excel';
            		break;
            	case strpos($this->getRequest()->getHeader('user_agent'), 'opera'):
            		$contentType = 'application/x-download';
            		break;
            	default:
            		$contentType = 'application/unknown';
            }
            
            $this->_helper->SendFile(
				Zend_Registry::get('config')->path->upload->marksheets.'/'.$subjectId,
				$contentType,
				$options
	        );
            die();
        }
        $this->_flashMessenger->addMessage(_('Файл не найден'));
		$this->_redirector->gotoSimple('index', 'index', 'default');
    	
    }
    
}

