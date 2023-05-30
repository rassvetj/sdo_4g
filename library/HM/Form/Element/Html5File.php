<?php
class HM_Form_Element_Html5File extends Zend_Form_Element_File
{
    public $helper = 'html5File';

    public function __construct($spec, $options = null)
    {
        $this->setTransferAdapter(new HM_File_Transfer_Adapter_Flash(array('name' => $spec)));
        parent::__construct($spec, $options);
    }
    
    public function setValue($value)
    {
        $this->_value = $value;
        return $this;
    }
    
    // удалить старые файлы, которые были помечены на удаление
    // для этого ему нужно передать список старых файлов ($populatedFiles)
    public function updatePopulated($populatedFiles = array(), $doUnlink = true)
    {
        $adapter = $this->getTransferAdapter();
        return $adapter->updatePopulated($populatedFiles, $doUnlink);
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