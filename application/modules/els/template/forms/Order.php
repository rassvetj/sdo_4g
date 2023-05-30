<?php
class HM_Form_Order extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('order');


        $this->addElement($this->getDefaultWysiwygElementName(), 'template_order_header', array(
            'Label' => _('Заголовок'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 4000, 0),
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

        $this->addElement($this->getDefaultWysiwygElementName(), 'template_order_text', array(
            'Label' => _('Текст приказа'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 4000, 0),
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

        $this->addElement($this->getDefaultWysiwygElementName(), 'template_order_footer', array(
            'Label' => _('Подвал'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 4000, 0),
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

        $this->addDisplayGroup(
            array(
                'template_order_header',
                'template_order_text',
                'template_order_footer'
            ),
            'orderGroup',
            array('legend' => _('Общие'))
        );

        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
	}

}