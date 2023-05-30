<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';

class HM_View_Infoblock_TrainingsCalendarBlock extends HM_View_Infoblock_ScreenForm
{
    protected $id = 'trainingsCalendar';
    
    public function trainingsCalendarBlock($title = null, $attribs = null, $options = null)
    {
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/css/infoblocks/trainings-calendar/style.css'));
        $this->view->headScript()->appendFile($this->view->serverUrl('/js/infoblocks/trainings-calendar/script.js'));
        $content = $this->view->render('trainingsCalendarBlock.tpl');

        return parent::screenForm($title, $content, $attribs);
    }
}