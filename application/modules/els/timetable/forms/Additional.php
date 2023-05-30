<?php
class HM_Form_Additional extends HM_Form
{
	public function init()
	{
	    $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('timetable-additional');
        $this->setAction($this->getView()->url());
		$this->setAction($this->getView()->url(array('module' => 'timetable', 'controller' => 'teacher', 'action' => 'save-additional')));
		
		$this->addElement('hidden', 'timetable_id', array(
			'value' => false,
        ));
		
		$this->addElement('text', 'users', array(
			'Label' 		=> 'Присутствовало слушателей',
            'Required' 		=> true,
            'Validators' 	=> array(),
            'Filters' 		=> array('Int'),
        ));
		
		$this->addElement('text', 'file_path', array(
			'Label' 		=> 'Ссылка на запись занятия',
            'Required' 		=> true,
            'Validators' 	=> array(),
            'Filters' 		=> array('StripTags'),
        ));
		
		$this->addElement('text', 'subject_path', array(
			'Label' 		=> 'Ссылка на новость в сессии',
            'Required' 		=> true,
            'Validators' 	=> array(),
            'Filters' 		=> array('StripTags'),
        ));
		
		

        parent::init(); // required!
	}

}