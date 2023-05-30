<?php
class HM_Form_Fact extends HM_Form
{
	public function init()
	{
        $resourceId = $this->getParam('resource_id', 0);

        $this->setMethod(Zend_Form::METHOD_POST);
        $this->setAttrib('enctype', 'multipart/form-data');
        //$this->setAttrib('onSubmit', "select_list_select_all('list2');");
        $this->setName('resource');

        $this->addElement('hidden', 'cancelUrl', array(
            'Required' => false,
            'Value' => $this->getView()->url(array('action' => 'index'))
        ));

        $this->addElement('hidden', 'interesting_facts_id', array(
            'Required' => true,
            'Validators' => array('Int'),
            'Filters' => array('Int')
        ));

        $this->addElement('text', 'title', array(
            'Label' => _('Название'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 255, 1)
            ),
            'Filters' => array(
                'StripTags'
            )
        ));

        $this->addElement('select', 'status', array(
                'label' => _('Статус'),
                'required' => false,
                'filters' => array('Int'),
                'multiOptions' => array(HM_Infoblock_Fact_FactModel::STATUS_UNPUBLISHED => _('Не опубликован'), HM_Infoblock_Fact_FactModel::STATUS_PUBLISHED => _('Опубликован'))
            )
        );
        
	      

        $this->addElement($this->getDefaultWysiwygElementName(), 'text', array(
            'Label' => _('Описание'),
            'Required' => true,
            'Validators' => array(
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

        $this->addDisplayGroup(
            array(
                'cancelUrl',
                'interesting_facts_id',
                'title',
                'status',
                'text',
                'submit'
            ),
            'factGroup',
            array('legend' => _('Факт'))
        );

        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
	}
}