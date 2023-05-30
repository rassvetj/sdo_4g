<?php
class HM_Form_Element_FcbkComplete extends Zend_Form_Element_Text
{
    protected $options;

    protected $_isArray = true;
    // public $helper = 'fcbkComplete';
    public function __construct($spec, $options = null)
    {
        $this->options = $options;
        parent::__construct($spec, $options);
        $this->setIsArray(true);
    }
    
    public function render(Zend_View_Interface $view = null)
    {
        if (null == $view) {
            $view = $this->getView();
        }
        
        $content = $view->fcbkComplete($this->getName(), $this->_value, $this->options);
        foreach ($this->getDecorators() as $decorator) {
            if (!$decorator instanceof Zend_Form_Decorator_ViewHelper) {
                $decorator->setElement($this);
                $content = $decorator->render($content);
            }
        }
        
        return $content;
    }
}