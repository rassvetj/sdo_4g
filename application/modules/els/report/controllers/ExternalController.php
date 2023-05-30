<?php
class Report_ExternalController extends HM_Controller_Action_Crud
{
    public function indexAction()
    {
	}
	
	public function listAction()
    {
		$this->getService('Unmanaged')->setHeader(_('Список отчетов'));
		
		$this->view->reports = array(
			array(
				'url' => $this->view->url(array('module' => 'report', 'controller' => 'assign-student', 'action' => 'index'), 'default', true),
				'name' => 'Отчет о назначении студентов',
			),
			array(
				'url' => $this->view->url(array('module' => 'report', 'controller' => 'debt-subject', 'action' => 'index'), 'default', true),
				'name' => 'Отчет о продленных сессиях',
			),
			array(
				'url' => $this->view->url(array('module' => 'report', 'controller' => 'timetable', 'action' => 'index'), 'default', true),
				'name' => 'Отчет о заполнении расписания',
			),
			array(
				'url' => $this->view->url(array('module' => 'report', 'controller' => 'debt-student', 'action' => 'index'), 'default', true),
				'name' => 'Отчет об академических долгах студента',
			),
			array(
				'url' => $this->view->url(array('module' => 'report', 'controller' => 'journal', 'action' => 'index'), 'default', true),
				'name' => 'Отчет по журналу',
			),
		);
	}
	
	
    
}