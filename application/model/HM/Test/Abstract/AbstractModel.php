<?php

class HM_Test_Abstract_AbstractModel extends HM_Model_Abstract
{
    const QUESTION_SEPARATOR = '~~';    

    const STATUS_PUBLISHED = 1;
    const STATUS_UNPUBLISHED = 0;
    
    //TYPE
    const LOCALE_TYPE_LOCAL  = 0;
    const LOCALE_TYPE_GLOBAL = 1;

    static public function getStatuses()
    {
        return array(
            self::STATUS_UNPUBLISHED => _('Не опубликован'),
            self::STATUS_PUBLISHED => _('Опубликован')
        );
    }
    
    static public function getLocaleStatuses()
    {
        return array(
            self::LOCALE_TYPE_LOCAL => _('Учебный курс'),
            self::LOCALE_TYPE_GLOBAL => _('База знаний')
        );
    }
    
    

    public function getQuestionsIds()
    {
        if (strlen($this->data)) {
            return explode(self::QUESTION_SEPARATOR, $this->data);
        }
        return array();
    }

    public function isQuestionExists($questionId)
    {
        return in_array($questionId, $this->getQuestionsIds());
    }
    
    public function getTestType()
    {
        return HM_Test_TestModel::TYPE_TEST;
    }
    
    public function addQuestionsIds(array $questionsIds)
    {
        $questions = $this->getQuestionsIds();
        $questions = array_merge($questions, $questionsIds);
        $this->data = join(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $questions);
        		
		$this->questions = count($questions);
    }

    public function removeQuestionsIds(array $questionsIds)
    {
        $questions = $this->getQuestionsIds();

        if (count($questionsIds)) {
            foreach($questionsIds as $questionId) {
                if (($key = array_search($questionId, $questions)) !== false) {
                    unset($questions[$key]);
                }
            }
        }

        $this->data = join(HM_Test_Abstract_AbstractModel::QUESTION_SEPARATOR, $questions);
        $this->questions = count($questions);
		
    }

    public function getName(){
        return $this->title;        
    }
    
    public function getFreeModeUrlParam()
    {
        return array(
                	'module' => 'test', 
                	'controller' => 'abstract', 
                	'action' => 'view', 
                	'test_id' => $this->test_id
                );
    }
    
    public function getFreeModeAllUrlParam()
    {
        return array(
                	'module' => 'test', 
                	'controller' => 'abstract', 
                	'action' => 'index'
                );
    }

}