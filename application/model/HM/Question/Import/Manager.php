<?php
class HM_Question_Import_Manager
{
    protected $_inserts = array();

    const CACHE_NAME = 'HM_Question_Import_Manager';

    private $_restoredFromCache = false;

    public function getService($name)
    {
        return Zend_Registry::get('serviceContainer')->getService($name);
    }

    private function _init()
    {

    }

    public function getInsertsCount()
    {
        return count($this->_inserts);
    }


    public function getCount()
    {
		return $this->getInsertsCount();
    }

    public function getInserts()
    {
        return $this->_inserts;
    }

    public function saveToCache()
    {
        return Zend_Registry::get('cache')->save(
            array(
                 'inserts' => $this->_inserts
            ),
            self::CACHE_NAME
        );
    }

    public function restoreFromCache()
    {
        
		if ($actions = Zend_Registry::get('cache')->load(self::CACHE_NAME)) {
            $this->_inserts = $actions['inserts'];
            $this->_restoredFromCache = true;
            return true;
        }

        return false;
    }

    public function init($items)
    {        
		$this->_init();

        if ($this->_restoredFromCache) {
            return true;
        }

        if (count($items)) {
            foreach($items as $item) {
                $this->_inserts[] = $item;
            }
        }

        $this->saveToCache();
    }


    public function import()
    {
        $testId = (int) Zend_Controller_Front::getInstance()->getRequest()->getParam('test_id', 0);
        $quizId = (int) Zend_Controller_Front::getInstance()->getRequest()->getParam('quiz_id', 0);

        $kods = array();
        if (count($this->_inserts)) {
            $subjectId = (int) Zend_Controller_Front::getInstance()->getRequest()->getParam('subject_id', 0);
            foreach($this->_inserts as $insert) {
                $insert->subject_id = $subjectId;

                $title = $insert->title;
                $answers = array();
                if ($quizId && count($insert->answers)) {
                    $counter = 1;
                    foreach($insert->answers as $answer) {
                        $answers[$counter++] = $answer['text'];
                    }
                }

                unset($insert->title);
                unset($insert->answers);

                $insert->balmax = 1;
                $insert->balmin = 0;
                $question = $this->getService('Question')->insert($insert->getValues());
                if ($question) {
                    $kods[$question->kod] = $question->kod;

                    // Заносим записи в quizzes_answers
                    if ($quizId && count($answers)) {
                        $this->getService('PollAnswer')->synchronize(array(
                                'quiz_id' => $quizId,
                                'question_id' => $question->kod,
                                'theme' => $question->qtema,
                                'question_title' => $title,
                                'answers' => $answers
                            )
                        );
                    }
                }
            }
        }

        if (count($kods)) {

            if ($testId) {
                $test = $this->getService('TestAbstract')->getOne(
                    $this->getService('TestAbstract')->find($testId)
                );
                if ($test) {
                    /*$questions = array();
                    if (strlen($test->data)) {
                        $questions = explode(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $test->data);
                    }
                    $questions = array_merge($questions, $kods);
                    $test->data = join(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $questions);
                    $test->questions+=count($kods);*/
                    $test->addQuestionsIds($kods);
                    $this->getService('TestAbstract')->update($test->getValues());
                }
            }

            if ($quizId) {
                $quiz = $this->getService('Poll')->getOne(
                    $this->getService('Poll')->find($quizId)
                );
                if ($quiz) {
                    /**
                    $questions = array();
                    if (strlen($quiz->data)) {
                        $questions = explode(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $quiz->data);
                    }
                    $questions = array_merge($questions, $kods);
                    $quiz->data = join(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $questions);
                    $quiz->questions+=count($kods);
                     */
                    $quiz->addQuestionsIds($kods);
                    $this->getService('Poll')->update($quiz->getValues());
                }
            }

        }

    }
	
	
	public function importTask()
    {
        $taskId = (int) Zend_Controller_Front::getInstance()->getRequest()->getParam('task_id', 0);
        
        $kods = array();
        if (count($this->_inserts)) {
            $subjectId = (int) Zend_Controller_Front::getInstance()->getRequest()->getParam('subject_id', 0);
            foreach($this->_inserts as $insert) {
                $insert->subject_id = $subjectId;
                $title = $insert->title;
							
				$question = $this->getService('Question')->insert(
					array(
						'qtype' => HM_Question_QuestionModel::TYPE_FREE,
						'qdata' => $insert->getValue('title', ''),
						'qtema' => $insert->getValue('qtema', ''),
						'qmoder' => 1,
						'balmax' => $insert->getValue('balmax', 1),
						'balmin' => $insert->getValue('balmin', 0),
						'url' => $insert->getValue('url', ''),
						'last' => time(),
						'timetoanswer' => $insert->getValue('timetoanswer', 0)
					)
				);
				
				
				
                if ($question) {
                    $kods[$question->kod] = $question->kod;
				}					
			}
		}
		
		if (count($kods)) {
            if ($taskId) {                
				$task = $this->getService('Task')->getOne(
							$this->getService('Task')->find($taskId)
						);
				
				
                if ($task) {   
					$task->addQuestionsIds($kods);
					$this->getService('Task')->update($task->getValues('test_id', 'data'));
                }
				
            }
		}	
		
	}
				
				

}