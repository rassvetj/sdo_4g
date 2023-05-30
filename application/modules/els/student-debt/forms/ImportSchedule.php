<?php
class HM_Form_ImportSchedule  extends HM_Form
{
    public function init()
	{
		$this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('import_schedule');
		$this->setAction($this->getView()->url(array('module' => 'student-debt', 'controller' => 'timetable', 'action' => 'import')));
		
				         
		$this->addElement($this->getDefaultFileElementName(), 'file', array(            
			'Label' 			=> _('Прикрепите документ'),			
			'Destination' 		=> Zend_Registry::get('config')->path->upload->temp,
            'Required' 			=> true,
            'Description' 		=> _('Для загрузки использовать файлы форматов: xls, xlsx, csv'),
            'Filters' 			=> array('StripTags'),
            //'file_size_limit' => 10485760,
            'file_types' 		=> '*.xls,*.xlsx, *.csv',
            'file_upload_limit' => 1,
			'file_sample' 		=> $this->getView()->url(array('module' => 'student-debt', 'controller' => 'timetable', 'action' => 'get-example'), null, true),
        ));	
		
		
		$this->addElement('checkbox', 'remove_old', array(
            'Label' 		=> _('Удалить все старые данные'),
            'Description' 	=> '',
            'required' 		=> false,
            'validators' 	=> array('Int'),
            'filters' 		=> array('int'),
            'value' 		=> 0
        ));
		
		
		$this->addElement('submit', 'submit', array(
            'Label' => _('Импортировать'),
        ));
		
		parent::init();
	}	
	
}