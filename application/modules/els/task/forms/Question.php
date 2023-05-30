<?php
class HM_Form_Question extends HM_Form
{
//[che 09.06.2014 #16965]
    public function setDefaults(array $defaults)
    {
        parent::setDefaults($defaults);

        $populatedFiles = $this->getService('Question')->getPopulatedFiles($defaults['kod']);

        $files = $this->getElement('files');
        $files->setValue($populatedFiles);

        return $this;
    }
//
	public function init()
	{

        $subjectId = (int) $this->getParam('subject_id', 0);
        $taskId = (int) $this->getParam('task_id', 0);

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('task-question');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('action' => 'task', 'controller' => 'list', 'module' => 'question', 'subject_id' => $subjectId, 'task_id' => $taskId))
        ));

        $this->addElement('hidden', 'kod', array(
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 0)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement('text', 'qtema', array(
            'Required' => false,
            'Label' => _('Название'),
            'Validators' => array(
                array('StringLength', 255, 0)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));
		
        $this->addElement('text', 'qtema_translation', array(
            'Required' => false,
            'Label' => _('Перевод (en)'),
            'Validators' => array(
                array('StringLength', 255, 0)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));		

        $this->addElement($this->getDefaultWysiwygElementName(), 'qdata', array(
            'Label' => _('Формулировка варианта задания'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 4000, 1)
            ),
            //'Filters' => array('HtmlSanitizeRich'),
            'toolbar' => 'hmToolbarMidi',
            'rows' => 3
        ));
		

        $this->addElement($this->getDefaultWysiwygElementName(), 'qdata_translation', array(
            'Label' => _('Формулировка варианта задания (перевод)'),
            'Required' => false,
            'Validators' => array(
                array('StringLength', 4000, 1)
            ),
            //'Filters' => array('HtmlSanitizeRich'),
            'toolbar' => 'hmToolbarMidi',
            'rows' => 3
        ));		


        // эти атрибуты никак не обрабатываются, поэтому сделаем hidden
        $this->addElement('hidden', 'timetoanswer');
        $this->addElement('hidden', 'url');
        $this->addElement('hidden', 'balmin');
        $this->addElement('hidden', 'balmax');
/*
        $this->addElement('hidden', 'qtema', array(
            'Label' => _('Тема'),
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 0)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement('text', 'timetoanswer', array(
            'Label' => _('Время на решение в минутах'),
            'Description' => _('0 - без ограничений'),
            'Required' => false,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            ),
            'Value' => 0
        ));

        $this->addElement('text', 'url', array(
            'Label' => _('Ссылка'),
            'Required' => false,
            'Validators' => array(
                array('StringLength', 255, 0)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement('text', 'balmin', array(
            'Label' => _('Диапазон баллов (от)'),
            'Required' => false,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            ),
            'Value' => 0
        ));

        $this->addElement('text', 'balmax', array(
            'Label' => _('Диапазон баллов (до)'),
            'Required' => false,
            'Validators' => array(
                'Int'
            ),
            'Filters' => array(
                'Int'
            ),
            'Value' => 1
        ));
*/
        $this->addElement($this->getDefaultFileElementName(), 'files',
            array(
                 'Label'      => _('Файлы'),
                 'Required'   => false,
                 'Filters'    => array('StripTags'),
                 'Destination' => realpath(Zend_Registry::get('config')->path->upload->tasks),
                 'file_size_limit' => 0,
                 //'file_types' => '*.zip',
                 'file_upload_limit' => 100,
            )
        );
		
        $this->addElement($this->getDefaultFileElementName(), 'files_en',
            array(
                 'Label'      => _('Файлы (en)'),
                 'Required'   => false,
                 'Filters'    => array('StripTags'),
                 'Destination' => realpath(Zend_Registry::get('config')->path->upload->tasks),
                 'file_size_limit' => 0,
                 //'file_types' => '*.zip',
                 'file_upload_limit' => 100,
            )
        );		

        $fields = array(
            'cancelUrl',
            'kod',
            'qtema',
			'qtema_translation',
            'qdata',
            'qdata_translation',
			'timetoanswer',
            'url',
            'balmin',
            'balmax',
            'files',
			'files_en'
        );

        $this->addDisplayGroup(
            $fields,
            'mainGroup',
            array('legend' => _('Общие свойства'))
        );

		$this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
	}

}