<?php
class HM_Form_Manager extends HM_Form {

    public function init() {
        
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('bookshelf_manager');
		$this->setAction(
            $this->getView()->url(
                array(
                    'module'     => 'bookshelf',
                    'controller' => 'manager',
                    'action'     => 'save',
                )
            )
        );
		
		
		$groups    = array();
        $subjectId = (int)$this->getParam('subject_id', 0);
		
		if($subjectId){
			$groupCollection = $this->getService('StudyGroup')->getBySubject($subjectId);
			$groups          = $groupCollection->getList('group_id', 'name');
		}
        
		
        $this->addElement('select', 'group_id', array(
            'Label'        => _('Группа'),
            'Required'     => true,
            'multiOptions' => array('' => _('- выберите -')) + $groups,
            'Validators'   => array('Int'),
            'Filters'      => array('Int')
        ));
        
        $this->addElement($this->getDefaultFileElementName(), 'document', array(
            'Label'             => _('Загрузить файл'),
            'Destination'       => Zend_Registry::get('config')->path->upload->temp,
            'Required'          => true,
            'Description'       => _('Максимальный размер файла &ndash; 10 Mb'),
            'Filters'           => array('StripTags'),
            'file_size_limit'   => 10485760,
            'file_types'        => '*.jpg;*.png;*.gif;*.jpeg;*.pdf',
            'file_upload_limit' => 10,
        ));
		
		$this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        $this->addDisplayGroup(array(
            'group_id',
            'document',
            'submit',
        ),
            'bookshelf_manager_block_1',
            array('legend' => _(''))
        );

        

        parent::init(); // required!
    }

}