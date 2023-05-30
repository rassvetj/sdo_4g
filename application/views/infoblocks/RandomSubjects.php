<?php

require_once APPLICATION_PATH . '/views/infoblocks/ScreenForm.php';
/*

выбираем предметы, у которых классифиер_линкс такие же как у юзера - линейный вариант
выбираем классификаторы, к которым привязан юзер и всех их потомков. выбираем предметы, привязанные к этим классификаторам - рекурсивный вариант
TODO усложненный вариант с диффиринцированием курсов по принципу вкусности

*/
class HM_View_Infoblock_RandomSubjects extends HM_View_Infoblock_ScreenForm
{

	protected $id = 'randomSubjects';
	protected $class = 'randomSubjects';
	
	const CLASSIFIER_TYPE = 1; // Классификатор видов деятельности и тем обучения
	const COUNT_ITEM = 20;

	public function randomSubjects($title = null, $attribs = null, $options = null)
	{
		// линейный вариант

		$services = Zend_Registry::get('serviceContainer');
		$user_id = $services->getService('User')->getCurrentUserId();

		$this->session = new Zend_Session_Namespace('infoblock_random_subjects');
		$subjects = (is_array($this->session->subjects)) ? $this->session->subjects : array();
		
		if (!count($subjects)) {
            if ($user_id) {
                $relevant_subjects = $services->getService('ClassifierLink')->getRelevantSubjectsForUser((int) $user_id, self::CLASSIFIER_TYPE);
            } else {
                $relevant_subjects = array();
            }

            $free_subjects = (count($relevant_subjects) < self::COUNT_ITEM) ? $services->getService('Subject')->getFreeSubjects(self::COUNT_ITEM-count($relevant_subjects), $user_id) : array();
            $subjects = array_merge($relevant_subjects, $free_subjects);
		}
		
		$subject_id = array_shift($subjects);
		if ($subject_id) {
            $subjects[] = $subject_id;
		}
		$subject = $services->getService('Subject')->getOne($services->getService('Subject')->find($subject_id));

		$this->session->subjects = $subjects;
		$this->view->subject = $subject;
		$this->view->classifier_type = self::CLASSIFIER_TYPE;
		$this->view->headLink()->appendStylesheet(Zend_Registry::get('config')->url->base.'css/infoblocks/random-subjects/style.css');
        $this->view->headScript()->appendFile(Zend_Registry::get('config')->url->base.'js/infoblocks/random-subjects/script.js');

		$content = $this->view->render('randomSubjects.tpl');
        
		if ($title == null) return $content;
		return parent::screenForm($title, $content, $attribs);

	}
}