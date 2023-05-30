<?php
class HM_Form_News extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        //$this->setAttrib('onSubmit', "select_list_select_all('list2');");
        $this->setName('news');
        $this->setAction($this->getView()->url(array('module' => 'news', 'controller' => 'index', 'action' => 'new')));

        $this->addElement('hidden', 'cancelUrl', array(
            'required' => false,
            'value' => $this->getView()->url(array('module' => 'news', 'controller' => 'index', 'action' => 'index'))
        ));

        $this->addElement('hidden', 'id', array(
            'required' => false,
            'Filters' => array(
                'Int'
            )
        ));

        $this->addElement('hidden', 'subject_name', array(
            'required' => false,
            'filters' => array(
                'StripTags'
            )
        ));

        $this->addElement('hidden', 'subject_id', array(
            'required' => false,
            'filters' => array(
                'Int'
            )
        ));


        $this->addElement('textarea', 'announce', array(
            'Label' => _('Анонс новости'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',255,3)
            ),
            'Rows' => 5
        ));

        $this->addElement($this->getDefaultWysiwygElementName(), 'message', array(
            'Label' => _('Полный текст новости'),
            'Required' => true,
            'Validators' => array(
                array('StringLength',4096,3)
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

        //$this->getElement('message')->addFilter(new HM_Filter_Utf8());

		$this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        $this->addDisplayGroup(
            array(
                'id',
                'cancelUrl',
                'subject_name',
                'subject_id',
                'announce',
                'message',
                'submit'
            ),
            'newsGroup',
            array('legend' => _('Новость'))
        );

        parent::init(); // required!
	}

}