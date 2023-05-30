<?php
class HM_Form_Blog extends HM_Form
{
    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setName('blog');
        $this->setAction($this->getView()->url(array('module' => 'blog', 'controller' => 'index', 'action' => 'new')));

        $this->addElement('hidden', 'cancelUrl', array(
            'required' => false,
            'value' => $this->getView()->url(array('module' => 'blog', 'controller' => 'index', 'action' => 'index'))
        ));

        $this->addElement('hidden', 'id', array(
            'required' => false,
            'Filters' => array(
                'Int'
            )
        ));
        
        $this->addElement('text', 'title', array(
            'Label' => _('Название записи'),
            'Required' => true,
            'Filters' => array('StripTags'),
            'Validators' => array(
                array(
                    'validator' => 'StringLength',
                    'options' => array('max' => 255, 'min' => 3)
            ))
        ));

     $this->addElement($this->getDefaultWysiwygElementName(), 'body', array(
            'Label' => _('Полный текст записи'),
            'Required' => true,
            'Validators' => array(
                array(
                    'validator' => 'StringLength',
                    'options' => array('min' => 3)
            )),
            'Filters' => array('HtmlSanitizeRich'),
            'connectorUrl' => $this->getView()->url(array(
                'module' => 'storage',
                'controller' => 'index',
                'action' => 'elfinder',
                'subject' => $this->getView()->subjectName,
                'subject_id' => $this->getView()->subjectId
            )),
            'toolbar' => 'hmToolbarMidi',
            'fmAllow' => true
        ));


        $this->addElement(new HM_Form_Element_FcbkComplete('tags', array(
                'Label' => _('Метки'),
                'json_url' => $this->getView()->url(array('module' => 'blog', 'controller' => 'index', 'action' => 'tags'))
            )
        ));


        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        $this->addDisplayGroup(
            array(
                'id',
                'title',
                'body',
                'tags',
                'cancelUrl',
                'submit'
            ),
            'blogGroup',
            array('legend' => _('Запись'))
        );

        parent::init(); // required!
	}

}