<?php
class HM_View_Helper_Score extends HM_View_Helper_Abstract
{
    const MODE_DEFAULT = 'default'; // в рамочке, readonly 
    const MODE_FORSTUDENT = 'forstudent'; // в рамочке, writeable 
    const MODE_MARKSHEET = 'marksheet'; // в таблице
    
    public function score($params = null)
    {
        if (is_null($params) || (is_array($params) && sizeof($params) == 0)) return;
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/css/content-modules/score.css'));
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/application/marksheet/index/index/score.js'));

        static $scaleValuesInserted;
        
        if (!$scaleValuesInserted) {
         
          $this->view->headScript()->appendScript('
              window.hm      = window.hm || {};
              window.hm.dict = window.hm.dict || {};
              window.hm.dict.scaleValues = {
                BINARY: {
                  ON:  '.HM_Scale_Value_ValueModel::VALUE_BINARY_ON.',
                  NA:  '.HM_Scale_Value_ValueModel::VALUE_NA.'
                },
                TERNARY: {
                  ON:  '.HM_Scale_Value_ValueModel::VALUE_TERNARY_ON.',
                  OFF: '.HM_Scale_Value_ValueModel::VALUE_TERNARY_OFF.',
                  NA:  '.HM_Scale_Value_ValueModel::VALUE_NA.'
                }
              };
          ');
          
          $scaleValuesInserted = true;
        }

        $service = Zend_Registry::get('serviceContainer');
        if($service->getService('Acl')->inheritsRole($service->getService('User')->getCurrentUserRole(),HM_Role_RoleModelAbstract::ROLE_TUTOR)){
            $this->view->allowTutors = $service->getService('Lesson')->getOne($service->getService('Lesson')->find($params['lesson_id']))->allowTutors;
        }

        $view = $this->view;

        $view->score = ($params['score'] == intval($params['score'])) ? intval($params['score']) : round(floatval($params['score']), 2);
        $view->tabindex = $params['tabindex'];
        $view->userId = $params['user_id'];
        $view->lessonId = $params['lesson_id'];
        $view->mark_type = $params['mark_type'] ? $params['mark_type'] : 0;

        if (empty($params['scale_id'])) $params['scale_id'] = HM_Scale_ScaleModel::TYPE_CONTINUOUS;
        if (empty($params['mode'])) $params['mode'] = self::MODE_DEFAULT;
        return $this->view->render("score/{$params['scale_id']}/{$params['mode']}.tpl");
    }
}