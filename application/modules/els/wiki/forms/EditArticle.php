<?php

class HM_Form_EditArticle extends HM_Form
{
    protected $disableLinks;
    
    public function __construct($disableLinks = false)
    {
        $this->disableLinks = $disableLinks;
        parent::__construct();
    }

    public function init()
    {
        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAction($this->getView()->url(array('module' => 'wiki', 'controller' => 'index', 'action' => 'edit')));

        $this->addElement('hidden', 'id');
        $this->addElement('hidden', 'title');
        
        $this->addElement(new HM_Form_Element_WikiEditor('body', array(
            'connectorUrl' => $this->getView()->url(array(
                'module' => 'storage',
                'controller' => 'index',
                'action' => 'elfinder',
                'subject' => $this->getView()->subjectName,
                'subject_id' => $this->getView()->subjectId
            )),
            'Filters'      => array('HtmlSanitize'),
            'lang'         => Zend_Registry::get('config')->wysiwyg->params->lang,
            'disableLinks' => $this->disableLinks
        )));
            
        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));
        
        $this->addElement('hidden', 'cancelUrl', array(
            'required' => false,
            'value' => $this->getView()->url(array('module' => 'wiki', 'controller' => 'index', 'action' => 'index'))
        ));
        
        parent::init(); // required!
    }
}
