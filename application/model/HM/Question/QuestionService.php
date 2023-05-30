<?php
class HM_Question_QuestionService extends HM_Service_Abstract
{


    //HM_Test_List_ListModel


    public function getTypes($type = HM_Test_TestModel::TYPE_TEST){
    	switch ($type) {
    		case HM_Test_TestModel::TYPE_POLL:
    			$types = array(
                    HM_Question_QuestionModel::TYPE_ONE => _('одиночный выбор'),
                    HM_Question_QuestionModel::TYPE_MULTIPLE => _('множественный выбор'),
                    HM_Question_QuestionModel::TYPE_FREE => _('свободный ответ'),
                );
    			break;

    		default:
    			$types = array(
                    HM_Question_QuestionModel::TYPE_ONE => _('одиночный выбор'),
                    HM_Question_QuestionModel::TYPE_MULTIPLE => _('множественный выбор'),
                    HM_Question_QuestionModel::TYPE_CONFORMITY => _('соответствие'),
                    HM_Question_QuestionModel::TYPE_SORT => _('упорядочивание'),
                    HM_Question_QuestionModel::TYPE_CLASS => _('классификация'),
                    HM_Question_QuestionModel::TYPE_FORM => _('заполнение формы'),
                    HM_Question_QuestionModel::TYPE_FILLINGAPS => _('заполнение пропусков'),
                    HM_Question_QuestionModel::TYPE_MAP => _('выбор области на изображении'),
                    HM_Question_QuestionModel::TYPE_IMAGE => _('выбор изображения'),
                    HM_Question_QuestionModel::TYPE_FREE => _('свободный ответ'),
/*        			HM_Question_QuestionModel::TYPE_ATTACH => _('Прикрепленный файл'),
                    HM_Question_QuestionModel::TYPE_OBJECT => _('Внешний объект'),
                    HM_Question_QuestionModel::TYPE_TRAINING => _('Тренажер'),*/
        );
    		break;
    	}
        return $types;
    }

    public function insert($data)
    {
        if (!isset($data['kod'])) {
            // todo: refactoring
            require_once(APPLICATION_PATH.'/../public/unmanaged/test.inc.php');
            $data['kod'] = newQuestion((int) $data['subject_id']);
        }
        $data['created_by'] = $this->getService('User')->getCurrentUserId();
        $data['last'] = time();

        unset($data['subject_id']);
        return parent::insert($data);
    }

    public function update($data)
    {
        $data['last'] = time();
        return parent::update($data);
    }

    public function delete($data){


        // Сначала удаляем этот вопрос из тестов
        $GLOBALS['brtag']='~~';

        $tests = $this->getService('Test')->fetchAll(array('data LIKE ?' => '%'.$data.'%'));

        if(count($tests) > 0){

            foreach($tests as $value){

                $temp = explode( $GLOBALS['brtag'],$value->data);

                foreach($temp as $key => $val){
                    if($data == $val){
                        unset($temp[$key]);
                        break;
                    }
                }

                $this->getService('Test')->update(
                    array(
                        'tid' => $value->tid,
                        'data' => implode($GLOBALS['brtag'], $temp)
                    )
                );


            }

        }

        $this->getService('TestQuestion')->deleteBy(
            $this->quoteInto('kod = ?', $data)
        );

        // Потом просто удаляем
        parent::delete($data);

    }

    public function getUneditableTypes() {
        $types = $this->getTypes();
        $denied = array($types[HM_Question_QuestionModel::TYPE_FILLINGAPS]);
        return $denied;
    }

//[che 09.06.2014 #16965]
    public function getPopulatedFiles($kod)
    {
        $populatedFiles = array();
        $files = $this->getService('QuestionFiles')->fetchAll("kod = '{$kod}'");
        foreach($files as $file)
        {
            $files_service = $this->getService('Files');
            $file_s = $files_service->fetchAll("file_id = '{$file->file_id}'");

            $populatedFiles[] = new HM_File_FileModel(array(
                'id' => $file->file_id,
                'displayName' => $file_s[0]->name,
                'path' => $files_service->getPath($file->file_id),
                'url' => Zend_Registry::get('view')->url(array('module' => 'file', 'controller' => 'get', 'action' => 'file', 'file_id' => $file->file_id))));

        }                                  

        return $populatedFiles;
    }
//
    public function getFiles($kod, $oldSchool = true){

        if (!$oldSchool) {
            return $this->getService('Files')->fetchAllJoinInner('QuestionFiles', $this->quoteInto('QuestionFiles.kod = ?', $kod), 'file_id');
        }

        $select = $this->getSelect();
        $select->from(array('f' => 'file'))->where('kod = ?', $kod);
        $fetch = $select->query()->fetchAll();

        return $fetch;
    }

    public function updateTask($lessonId, $userId, $taskVariant = null){
        $lesson = $this->getOne($this->getService('Lesson')->find($lessonId));

        if($lesson->typeID != HM_Event_EventModel::TYPE_TASK){
            return true;
        }
        if($lesson){
            $params         = $lesson->getParams();
            $testId         = $lesson->getModuleId();
            $test           = $this->getOne($this->getService('Test')->find($testId));
            $ids            = explode(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $test->data);
            $questionList   = $this->getService('Question')->fetchAll(array('kod IN(?)' => $ids));

            if (count($questionList)) {
                $amount = count($questionList);
                $date = new HM_Date();
                $interviews = $this->getService('Interview')->fetchAll($this->getService('Interview')->quoteInto(array('to_whom = ? ',' AND lesson_id = ? '),array($userId,$lessonId)));
                $answers = $interviews->getList('type');
                if(count($interviews)){
                    if((null === $taskVariant) && $params['assign_type'] == HM_Lesson_Task_TaskModel::ASSIGN_TYPE_RANDOM){
                        $variant = mt_rand(0, $amount - 1);
                        if(count($answers) > 1){
                            foreach($questionList as $key=>$question){
                                foreach($interviews as $interview){
                                    if($question->kod == $interview->question_id)
                                    $variant = $key;
                                }
                            }
                        }
                    }else{
//[che 3.06.2014 #16963]
//                      $variant = $taskVariant; // - не корректно, т.к. в массиве далее индекс - порядковый, а не код вопроса
                        foreach($questionList as $key=>$question)
                        {
                            if($question->kod == $taskVariant)
                                $variant = $key;
                        }
//
                    }
                    if($questionList[$variant]){
                        $interview = $this->getService('Interview')->updateWhere(
                            array(
                                'title' => $questionList[$variant]->qtema,
                                'question_id' => $questionList[$variant]->kod,
                                'message' => $questionList[$variant]->qdata,
                                'date' => $date->toString(),
                            ),
                            array($this->getService('Interview')->quoteInto(array('to_whom = ? ',' AND lesson_id = ? ', ' AND type = ?'),array($userId,$lessonId, HM_Interview_InterviewModel::MESSAGE_TYPE_TASK)))
                        );
                        foreach($interviews as $interview){
                            $files = $this->getService('Question')->getFiles($questionList[$variant]->kod);
                            if(count($files) > 0){
                                foreach($files as $fileOne){
                                    $interviewFile = $this->getService('InterviewFile')->fetchAll($this->quoteInto(array('interview_id = ?'),array($interview->interview_id)));
                                    if(count($interviewFile))
                                        $this->getService('InterviewFile')->delete($this->quoteInto(array('interview_id = ?'), array($interview->interview_id)));
                                    $file = $this->getService('Files')->addFileFromBinary($fileOne['fdata'], $fileOne['fname']);
                                    $this->getService('InterviewFile')->insert(array('interview_id' => $interview->interview_id,'file_id' => $file->file_id));
                                }
                            }
                        }
                    }
                    else{
                        $this->getService('Interview')->deleteBy(array($this->getService('Interview')->quoteInto(array('to_whom = ? ',' AND lesson_id = ? '),array($userId, $lessonId))));
                    }
                }
                return false;
            }

        }
    }

    /**
     * Переназначает варианты задания слушателям
     * @param $lesson
     * @param $studentForUpdates
     * @param $taskUserVars
     */
    public function updateTasks($lesson, $studentForUpdates, $taskUserVars)
    {
         foreach ($studentForUpdates as $studentID) {

             if (!isset($taskUserVars[$studentID])){
                 $this->updateTask($lesson->SHEID,$studentID);
                 continue;
             }

             $inteviews = $this->getService('Interview')->fetchAll(array('lesson_id = ?' => $lesson->SHEID, '( user_id = ' . $studentID .' OR to_whom = ' . $studentID . ')'));

             if (count($inteviews)) {

                 $itwItem = $this->getOne($inteviews);

                 if ($itwItem->question_id != $taskUserVars[$studentID]) {

                     // если диалог для другого варианта, то он удаляется
                     $where = $this->quoteInto(
                         array('lesson_id = ?', ' AND ( user_id = ?', ' OR to_whom = ?)'),
                         array($lesson->SHEID, $studentID, $studentID)
                     );

                     $this->getService('Interview')->deleteBy($where);

                 } else {
                     // Обновляем текст задания и добавляем новые прикрепленные файлы
                     $this->updateTask($lesson->SHEID,$studentID, $taskUserVars[$studentID]);
                     // если диалоги для указанного варианта, то переход к слудующему студенту
                     continue;

                 }
             }

             // создаётся новый диалог
            $this->createTask($lesson->SHEID, $studentID, $taskUserVars[$studentID]);

         }
    }

    public function createTask($lessonId, $userId, $taskVariant = null)
    {
        $lesson = $this->getOne($this->getService('Lesson')->find($lessonId));

        if($lesson->typeID != HM_Event_EventModel::TYPE_TASK && 
                $lesson->tool != HM_Event_EventModel::TYPE_TASK &&
				$lesson->typeID != HM_Event_EventModel::TYPE_LANGUAGE && 
                $lesson->tool	!= HM_Event_EventModel::TYPE_LANGUAGE
        ){
            return false;
        }

        if ( $lesson ) {
            $params       = $lesson->getParams();
            $testId       = $lesson->getModuleId();
            $test         = $this->getOne($this->getService('Test')->find($testId));
            $ids          = explode(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $test->data);
            $questionList = $this->getService('Question')->fetchAll(array('kod IN(?)' => $ids));

            if (count($questionList)) {
                if ( isset($params['assign_type'])&& $taskVariant && $params['assign_type'] == HM_Lesson_Task_TaskModel::ASSIGN_TYPE_MANUAL ) { // ручное назначение вариантов
                    foreach ($questionList as $key => $variant) {
                        if ($variant->kod == $taskVariant) {
                            $rand = $key;
                            break;
                        }
                    }
                } else { // случайное назначение вариантов
                    $amount = count($questionList);
                    // Рандомно определяем вариант челу
                    $rand = mt_rand(0, $amount - 1);
                }
                
                if (isset($questionList[$rand])) {
                    $date = new HM_Date();
                    
                    $interviews_count = $this->getService('Interview')->countAll(
                        $this->getService('Interview')->quoteInto(array('to_whom = ? ',' AND lesson_id = ? '), array($userId, $lessonId))
                    );
                    $data = array(
                                'user_id' => 0,
                                'to_whom' => $userId,
                                'lesson_id' => $lessonId,
                                'title' => $questionList[$rand]->qtema,
                                'question_id' => $questionList[$rand]->kod,
                                'type' => HM_Interview_InterviewModel::MESSAGE_TYPE_TASK,
                                'message' => $questionList[$rand]->qdata,
                                'date' => $date->toString(),
                                'interview_hash' => mt_rand(999999, 999999999)
                            );
                    if($interviews_count == 0){
                        $interview = $this->getService('Interview')->insert($data);

                        $files = $this->getService('Question')->getFiles($questionList[$rand]->kod);
                        if(count($files) > 0){
                            foreach($files as $fileOne){
                                $file = $this->getService('Files')->addFileFromBinary($fileOne['fdata'], $fileOne['fname']);
                                $this->getService('InterviewFile')->insert(array('interview_id' => $interview->interview_id, 'file_id' => $file->file_id));
                            }
                        }
                    }
                    else{
                        $interview = $this->getService('Interview')->update($data);
                    }
                }
            }
        }
    }

    public function copy($questionId, $subjectId = null)
    {
        $question = $this->getOne($this->find($questionId));
        if ($question) {
            $kod = $question->kod;
            $parts = explode('-', $question->kod);
            $question->subject_id = $parts[0];
            if (null !== $subjectId) {
                $question->subject_id = $subjectId;
            }
            unset($question->kod);
            unset($question->id);
            $newQuestion = $this->insert($question->getValues());
            
            $files = $this->getService('Question')->getFiles($kod);
            if(count($files) > 0){
                foreach($files as $fileOne){
                    $fileOne['kod'] = $newQuestion->kod;
                    try {
                        $this->getService('QuestionFile')->insert($fileOne);
                    } catch (Exception $e) {
                        //для mssql
                        $fileOne['fdata'] = array(
                            $fileOne['fdata'],
                            SQLSRV_PARAM_IN,
                            SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY),
                            SQLSRV_SQLTYPE_VARBINARY('max')
                        );
                        $this->getService('QuestionFile')->insert($fileOne);
                    }
                }
            }
            
            return $newQuestion;
        }

        return false;
    }

    /**
     * Проверка правильности ответа на вопрос
     * @param int $questionId Номер вопроса
     * @param array $answers  Ответы
     * @return true || false
     */
    public function validate($questionId, $answers, $testId)
    {

        $session = &$_SESSION['s'];

        if(in_array($questionId, $session['curr_question_ids'])){
            $key = array_search($questionId, $session['curr_question_ids']);
            unset($session['curr_question_ids'][$key]);
            $session['curr_question_id'] = 0;
            //проверять не нужно, переходим дальше
            return -1;
        }else{
            $session['curr_question_ids'][] = $questionId;
        }

        $quest = $this->fetchAll(array('kod = ?' => $questionId))->current();

        $adata = $quest->proccessAdata();
        $msg = '';
        $valid = true;
        $rightCount = 0;
        $wrongCount = 0;



        switch($quest->qtype){
            case HM_Question_QuestionModel::TYPE_ONE:
                foreach($answers as &$answer){
                    if($answer['ANSWER_CHECKED'] == "true" && $adata == $answer['ANSWER_VAL']){
                        $rightCount++;
                        $msg = $this->getService('TestFeedback')->getFeedbackForAnswers(HM_Test_Feedback_FeedbackModel::EVENT_VALID, $answer, $quest, $testId);
                        $answer['ANSWER_FEED'] = implode('<br/>', $msg);
                    }elseif($answer['ANSWER_CHECKED'] == "true" && $adata != $answer['ANSWER_VAL']){
                        $valid = false;
                        $wrongCount++;
                        $msg = $this->getService('TestFeedback')->getFeedbackForAnswers(HM_Test_Feedback_FeedbackModel::EVENT_INVALID, $answer, $quest, $testId);
                        $answer['ANSWER_FEED'] = implode('<br/>', $msg);
                    }
                }
                break;
            case HM_Question_QuestionModel::TYPE_SORT:
            case HM_Question_QuestionModel::TYPE_CONFORMITY:
            case HM_Question_QuestionModel::TYPE_CLASS:

                $qData = $quest->proccessQdata();
                foreach($answers as &$answer){
                    $right = '';
                    $name = $answer['ANSWER_NAME'];

                    preg_match('#form\[[0-9]\]\[([0-9])\]#iU', $name, $match);
                    $answerId = intval($match[1]);

                    foreach($qData as $questArr){
                        if($questArr['question'] == $answerId){
                            $right = $questArr['group'];
                        }
                    }

                    if(urldecode($answer['ANSWER_VAL']) == $right){
                        $rightCount++;
                        $msg = $this->getService('TestFeedback')->getFeedbackForAnswers(HM_Test_Feedback_FeedbackModel::EVENT_VALID, $answer, $quest, $testId);
                        $answer['ANSWER_FEED'] = implode('<br/>', $msg);
                    }else{
                        $valid = false;
                        $wrongCount++;
                        $msg = $this->getService('TestFeedback')->getFeedbackForAnswers(HM_Test_Feedback_FeedbackModel::EVENT_INVALID, $answer, $quest, $testId);
                        $answer['ANSWER_FEED'] = implode('<br/>', $msg);
                    }
                }
                break;
            case HM_Question_QuestionModel::TYPE_FILLINGAPS:
                $qData = $quest->proccessQdata();
                $j = 1;
                foreach($answers as &$answer){
                    $right = '';
                    $name = $answer['ANSWER_NAME'];

                    preg_match('#form\[[0-9]\]\[otvet\]\[([0-9])\]#iU', $name, $match);
                    $answerId = intval($match[1]);
                    foreach($qData as $questArr){

                        if($questArr['question'] == $answerId){
                            $right = $questArr['val'];
                            if($questArr['match-case'] == 0){
                                foreach($right as &$val){
                                    $val = strtolower($val);
                                }
                                $answer['ANSWER_VAL'] = strtolower($answer['ANSWER_VAL']);
                            }
                        }
                    }

                    $x = create_function('$answer,$right', '$arrAnswer = explode(",|", $answer);
                        $flag = true;
                        foreach($right as $one){
                            if(array_search($one, $arrAnswer) === false){
                                $flag = false;
                            }
                        }
                        return $flag && (count($arrAnswer) == count($right));');

                   /* $x = function($answer, $right){
                        $arrAnswer = explode(',|', $answer);
                        $flag = true;
                        foreach($right as $one){
                            if(array_search($one, $arrAnswer) === false){
                                $flag = false;
                            }
                        }
                        return $flag && (count($arrAnswer) == count($right));
                    };*/

                    if(array_search($answer['ANSWER_VAL'],$right) !==false || $x($answer['ANSWER_VAL'],$right)){

                        $rightCount++;
                        $msg = $this->getService('TestFeedback')->getFeedbackForAnswers(HM_Test_Feedback_FeedbackModel::EVENT_VALID, $answer, $quest, $testId, $j);
                        $answer['ANSWER_FEED'] = implode('<br/>', $msg);
                    }else{

                        $valid = false;
                        $wrongCount++;
                        $msg = $this->getService('TestFeedback')->getFeedbackForAnswers(HM_Test_Feedback_FeedbackModel::EVENT_INVALID, $answer, $quest, $testId, $j);
                        $answer['ANSWER_FEED'] = implode('<br/>', $msg);
                    }
                    $j++;
                }

                break;
            case HM_Question_QuestionModel::TYPE_MULTIPLE:
                foreach($answers as &$answer){

                    if($answer['ANSWER_CHECKED'] == "true" && $adata[$answer['ANSWER_VAL']] == 1){
                        $rightCount++;
                        $msg = $this->getService('TestFeedback')->getFeedbackForAnswers(HM_Test_Feedback_FeedbackModel::EVENT_VALID, $answer, $quest, $testId);
                        $answer['ANSWER_FEED'] = implode('<br/>', $msg);
                    }elseif($answer['ANSWER_CHECKED'] == "true" && $adata[$answer['ANSWER_VAL']] == 0){
                        $valid = false;
                        $wrongCount++;
                        $msg = $this->getService('TestFeedback')->getFeedbackForAnswers(HM_Test_Feedback_FeedbackModel::EVENT_INVALID, $answer, $quest, $testId);
                        $answer['ANSWER_FEED'] = implode('<br/>', $msg);
                    }
                }
                break;
            default:
                break;
        }

        return array($valid, $rightCount/($rightCount + $wrongCount)*100);
    }


    static public function generateArrayForQuestion()
    {
        $session = $_SESSION['s'];
        $currentQuestions = $session['ckod'];

        $allQuestions = $session['akod'];

        $resArray = array();
        $resArray['TEST_ID'] = $session['tid'];

        foreach($currentQuestions as $key => $question){

            $quest = Zend_Registry::get('serviceContainer')->getService('Question')->fetchAll(array('kod = ?' => $question))->current();
            $questionArray = array();
            $questionArray['QUESTION_ID'] = $question;
            $questionArray['QUESTION_TYPE'] =  HM_Question_QuestionModel::getFeedbackType($quest->qtype);
            $questionArray['QUESTION_SUBMIT'] =  false;
            $questionArray['QUESTION_FEED'] =  '';

            $questionArray['AR_ANSWERS'] = array();

            $qdata = $quest->proccessQdata();

            foreach($qdata as $kAns => $vAns){
                $answer = array();
                $akey = array_search($question, $allQuestions);
                switch($quest->qtype){
                    case HM_Question_QuestionModel::TYPE_ONE:
                        $answer['ANSWER_NAME'] = 'form[' . $akey . '][otvet]';
                        $answer['ANSWER_VAL'] = $vAns;
                        break;
                    case HM_Question_QuestionModel::TYPE_FILLINGAPS:
                        $answer['ANSWER_NAME'] = 'form[' . $akey . '][otvet][' . $vAns['question'] . ']';
                        $answer['ANSWER_VAL'] = '';
                        break;
                    case HM_Question_QuestionModel::TYPE_CLASS:
                    case HM_Question_QuestionModel::TYPE_CONFORMITY:
                    case HM_Question_QuestionModel::TYPE_SORT:
                        $answer['ANSWER_NAME'] = 'form[' . $akey . '][' . $vAns['question'] . ']';
                        //$answer['ANSWER_VAL'] = $vAns['group'];
                        $answer['ANSWER_VAL'] = '';
                        break;
                    default:
                        $answer['ANSWER_NAME'] = 'form[' . $akey . '][' . $vAns . ']';
                        $answer['ANSWER_VAL'] = $vAns;

                }
                $answer['ANSWER_CHECKED'] = false;

                $answer['ANSWER_FEED'] = "";
                $questionArray['AR_ANSWERS'][] = $answer;
            }

            $resArray['AR_QUESTION'][] = $questionArray;


        }

         return $resArray;

    }


}