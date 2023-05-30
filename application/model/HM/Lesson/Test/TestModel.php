<?php
/*
 * Тест
 */
class HM_Lesson_Test_TestModel extends HM_Lesson_LessonModel
{

    const TEST_EXECUTE_URL = 'test_start.php?mode=start&tid=%d&sheid=%d';

    public function getType()
    {
        return HM_Event_EventModel::TYPE_TEST;
    }

    public function getIcon($size = HM_Lesson_LessonModel::ICON_LARGE)
    {
        $folder = "{$size}x/";
        return Zend_Registry::get('config')->url->base . "images/events/4g/{$folder}test.png";
    }

    public function isExternalExecuting()
    {
        return true;
    }

    public function getExecuteUrl()
    {
        return Zend_Registry::get('config')->url->base.sprintf(
            self::TEST_EXECUTE_URL,
            $this->getModuleId(),
            $this->SHEID
        );
    }

    public function getResultsUrl($options = array())
    {
        $params = array(
                            'module'     => 'lesson',
                            'controller' => 'result',
                            'action'     => 'index',
                            'lesson_id' => $this->SHEID,
                            'subject_id' => $this->CID
                        );
                        
        $params = (count($options))? array_merge($params,$options) : $params;
                        
        return Zend_Registry::get('view')->baseUrl(Zend_Registry::get('view')->url($params,null,true));

    }
    
    
    public function getFreeModeUrlParam()
    {
        return array(
                	'module' => 'lesson', 
                	'controller' => 'execute',  
                	'action' => 'index', 
                	'lesson_id' => $this->SHEID
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
    

    public function isResultInTable()
    {
        return true;
    }
    
    public function isFreeModeEnabled()
    {
        return false;
    }

    public function onFinish($result)
    {
        $score = false;
        if (count($collection = $this->getService()->getService('Test')->find($this->getModuleId()))) {
            $test = $collection->current();
            switch ($this->getScale()) {
                case HM_Scale_ScaleModel::TYPE_BINARY:
                    // @todo: здесь возможна бага если $test->threshold установлен в 0 (если это вообще имеет смысл)
                    if (isset($result['score']) && isset($test->threshold) && ($result['score'] >= $test->threshold)) {
                        $score = HM_Scale_Value_ValueModel::VALUE_BINARY_ON;
                    }
                    break;
                case HM_Scale_ScaleModel::TYPE_TERNARY:
                    if (isset($result['score']) && isset($test->threshold)) {
                        if ($result['score'] >= $test->threshold) {
                            $score = HM_Scale_Value_ValueModel::VALUE_BINARY_ON;
                        } else {
                            $score = HM_Scale_Value_ValueModel::VALUE_BINARY_OFF;
                        }
                    }
                    break;
                case HM_Scale_ScaleModel::TYPE_CONTINUOUS:
                    if (isset($result['score'])) {
                        $score = $result['score'];
                    }
                    break;
            }
        }
        return $score;
    }

}