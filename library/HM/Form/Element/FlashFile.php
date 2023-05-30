<?php
class HM_Form_Element_FlashFile extends Zend_Form_Element_File
{
    public $helper = 'flashFile';

    public function __construct($spec, $options = null)
    {
        $this->setTransferAdapter(new HM_File_Transfer_Adapter_Flash(array('name' => $spec)));
        parent::__construct($spec, $options);
    }

    public function render(Zend_View_Interface $view = null)
    {

        if ($this->_isPartialRendering) {
            return '';
        }

        if (null !== $view) {
            $this->setView($view);
        }

        $content = '';
        foreach ($this->getDecorators() as $decorator) {
            $decorator->setElement($this);
            $content = $decorator->render($content);
        }

        return $content;
    }

}