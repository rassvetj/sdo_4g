<?php
class HM_Form_Report extends HM_Form
{
	public function init()
	{
        $this->setMethod(Zend_Form::METHOD_POST);
        //$this->setAttrib('enctype', 'multipart/form-data');
        $this->setName('report');


        $this->addElement($this->getDefaultWysiwygElementName(), 'template_report_header', array(
            'Label' => _('Заголовок'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 4000, 0),
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

        $this->addElement($this->getDefaultWysiwygElementName(), 'template_report_footer', array(
            'Label' => _('Подвал'),
            'Required' => true,
            'Validators' => array(
                array('StringLength', 4000, 0),
            ),
            'Filters' => array('HtmlSanitizeRich'),
        ));

        $this->addDisplayGroup(
            array(
                'template_report_header',
                'template_report_footer'
            ),
            'orderGroup',
            array('legend' => _('Общие'))
        );

        $this->addElement('Submit', 'submit', array('Label' => _('Сохранить')));

        parent::init(); // required!
	}

}