<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_CarouselBlock extends HM_View_Infoblock_ScreenForm
{
    const LIMIT = 250;
    
    protected $id = 'carousel';

    public function carouselBlock($title = null, $attribs = null, $options = null)
    {
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/carousel/style.css');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/carousel/script.js');
        
        //@todo: не все подряд курсы, а только текущие 
        $subjectmates = array();        
        if (count($subjects = Zend_Registry::get('serviceContainer')->getService('Student')->getSubjects())) {
        
            $subjectIds = $subjects->getList('subid', 'name');
            // здесь нужно проверить - не слишком ли много однокурсников и если слишком, добавить дополнительный селект с курсом
            $students = Zend_Registry::get('serviceContainer')->getService('Student')->fetchAllDependence('User', array('CID IN (?)' => array_keys($subjectIds)));
            foreach ($students as $student) {
                if (!count($student->users) || ($student->MID == Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUserId())) {
                    continue;
                } else {
                    $user = $student->users->current();
                }
                if (!isset($subjectmates[$student->MID])) {
                    $subjectmates[$student->MID] = array(
                        'user' => $user,
                        'subjects' => array($subjectIds[$student->CID])
                    );
                    if (count($subjectmates) > self::LIMIT) {
                        $this->view->error = _('Слишком много однокурсников для отображения в виджете');
                    }
                } else {
                    $subjectmates[$student->MID]['subjects'][] = $subjectIds[$student->CID];
                }
            }
        }

        $this->view->subjectmates = $subjectmates;
        $content = $this->view->render('carouselBlock.tpl');

        return parent::screenForm($title, $content, $attribs);        
    }
}