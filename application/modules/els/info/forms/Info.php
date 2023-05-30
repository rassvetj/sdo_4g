<?php
/**
 * Форма для редактирования конфиг-файлов Skillsoft курсов
 *
 */
class HM_Form_Info extends HM_Form
{
    //public $status;

    public function init()
    {

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('info');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->baseUrl('info/list/')
        ));

        $this->addElement('hidden', 
        				  'nID', 
                          array('Required' => true,
                                'Validators' => array('Int'),
                                'Filters' => array('Int')));
        $this->addElement('checkbox', 
        				  'show', 
                          array('label' => _('Опубликован'),
            			  'value' => true));
        $this->addElement(
            'text', 
            'Title', 
            array(
                'Label' => _('Название'),
                'Required' => true,
                'Validators' => array(
                    array(
                        'validator' => 'StringLength',
                        'options' => array('max' => 255, 'min' => 3)
                    )
                ),
                'Filters' => array('StripTags')
            )
        );
        $this->addElement(
            'text', 
            'Title_translation', 
            array(
                'Label' => _('Название').(' (en)'),
                'Required' => false,
                'Validators' => array(
                    array(
                        'validator' => 'StringLength',
                        'options' => array('max' => 255, 'min' => 3)
                    )
                ),
                'Filters' => array('StripTags')
            )
        );        
        $this->addElement($this->getDefaultWysiwygElementName(), 'message', array(
            'Label' => _('Содержание'),
            'Required' => true,
            'Validators' => array(),
//            'Filters' => array('HtmlSanitizeRich'),
            'connectorUrl' => $this->getView()->url(array(
                'module' => 'storage',
                'controller' => 'index',
                'action' => 'elfinder'
            )),
            'toolbar' => 'hmToolbarMaxi',
            'fmAllow' => true
        ));
		
        // Add new Wysiwyg Textarea for english translation for Infoblock
        $this->addElement($this->getDefaultWysiwygElementName(), 'translation', array(
            'Label' => _('Перевод'),
            //'Filters' => array('HtmlSanitizeRich'),
            'Required' => false,
            'connectorUrl' => $this->getView()->url(array(
                'module' => 'storage',
                'controller' => 'index',
                'action' => 'elfinder'
            )),
            'toolbar' => 'hmToolbarMaxi',
            'fmAllow' => true
        )
        );		

        $this->addElement(new HM_Form_Element_FcbkComplete('resource_id', array(
                'Label' => _('Ресурс'),
                'Description' => _('Используйте знак # для указания ID ресурса'),
                'json_url' => $this->getView()->url(array('module' => 'resource', 'controller' => 'index', 'action' => 'resources-list')),
                'value' => array(),
                'newel' => false,
                'height' => 1,
                'maxitimes' => 1,
                'Filters' => array()
            )
        ));
        
        $this->addElement('Submit', 'submit', array(
            'Label' => _('Сохранить')));

        $this->addDisplayGroup(array('cancelUrl',
                                    'nID',
                                    'show',
                                    'Title',
									'Title_translation', 
                                    'message',
									'translation',
                                    'resource_id',
                                    'submit'),
            				   'resourceGroup',
                               array('legend' => ''));
        
        parent::init(); // required!
    }


}