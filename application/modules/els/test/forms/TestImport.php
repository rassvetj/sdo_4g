<?php
class HM_Form_TestImport extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('import');
        //$this->setAttrib('onSubmit', 'if (confirm("'._('Данная операция удалит существующую структуру курса и все сопутствующие материалы. Продолжить?').'")) return true; return false;');

        $this->addElement('hidden', 'subject', array(
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));
        
        $this->addElement('hidden', 'testimport', array(
            'value' => 1,
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'Required' => true
        ));
        
        $this->addElement('hidden', 'send', array(
            'value' => 1,
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'Required' => true
        ));

/*        $this->addElement('hidden', 'cid', array(
            'Validators' => array('Int'),
            'Filters' => array('Int'),
            'Required' => true
        ));*/

        $formats = array(0 => _('Выберите формат курса'));
        foreach(HM_Course_CourseModel::getFormats() as $formatId => $formatTitle) {
            $formats[$formatId] = $formatTitle;
        }

        $this->addElement('hidden', 'import_type', array(
            //'Label' => _('Формат пакета'),
            'Value' => 4,
            'Validators' => array('Int', array('GreaterThan', false, array('min' => 0))),
            'Filters' => array('Int'),
            'Required' => true,
            'MultiOptions' => $formats
        ));
        
        $this->addElement('hidden', 'location', array(
            //'Label' => _('Глобальный тест'),
            'Value' => 1,
            'Validators' => array('Int', array('Between', false, array(0, 1))),
            'Filters' => array('Int'),
            'Required' => true
        ));

        $this->addElement($this->getDefaultFileElementName(), 'zipfile', array(
            'Label' => _('Файл пакета в формате zip'),
            'Validators' => array(
                array('Count', false, 1),
                array('Extension', false, 'zip')
            ),
            'Destination' => Zend_Registry::get('config')->path->upload->temp,
            'file_types' => '*.zip',
            'file_upload_limit' => 1,
            'file_size_limit' => 209715200,
            'Required' => true
        ));

        $this->addElement('hidden', 'ch_info', array(
            //'Label' => _('Переписать заголовок курса'),
        	'Value' => 0,
            'Required' => true,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            )
        ));

		$this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        $this->addDisplayGroup(
            array(
                'send',
                'cid',
                'import_type',
                'zipfile',
                'ch_info',
                'submit'
            ),
            'importGroup',
            array('legend' => _('Импорт тестов'))
        );

        parent::init(); // required!
	}

}
