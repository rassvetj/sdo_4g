<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_CourseStatusBlock extends HM_View_Infoblock_ScreenForm
{

    protected $id = 'coursestatusblock';
    
    public function courseStatusBlock($title = null, $attribs = null, $options = null)
    {
        if (!isset($options['course']) && isset($options['courseId'])) {
            $options['course'] = Zend_Registry::get('serviceContainer')->getService('Course')->getOne(
                Zend_Registry::get('serviceContainer')->getService('Course')->find($options['courseId'])
            );
        }
        $this->view->course = $options['course'];
        $content = $this->view->render('courseStatusBlock.tpl');

        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
    }
}