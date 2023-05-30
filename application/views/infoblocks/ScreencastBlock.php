<?php
require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
class HM_View_Infoblock_ScreencastBlock extends HM_View_Infoblock_ScreenForm
{
    protected $id = 'screencast';

    public function screencastBlock($title = null, $attribs = null, $options = null)
    {
        $this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/screencasts/style.css');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/screencasts/script.js');

        $this->view->screencasts = array(
            '',
            '01_login' => _('Вход в систему'),
            '02_create_subject' => _('Cоздание учебного курса'),
            '03_assign_teachers_students' => _('Назначение преподавателей и слушателей'),
            '04_import_module' => _('Импорт готового учебного модуля'),
            '05_create_schedule' => _('Cоздание плана занятий'),
            '06_create_quiz' => _('Cоздание тестов'),
            '07_create_assignment' => _('Cоздание заданий'),
            '08_service_addition_in_courses' => _('Подготовка и проведение вебинаров'),
            '10_results and grading' => _('Анализ результатов обучения и выставление оценок'),
        );

        $content = $this->view->render('screencastBlock.tpl');
        if ($title == null) return $content;
        return parent::screenForm($title, $content, $attribs);
    }
}