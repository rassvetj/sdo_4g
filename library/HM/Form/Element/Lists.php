<?php

class HM_Form_Element_Lists extends Zend_Form_Element {
    
    public $list1Name;
    public $list1Title;
    public $list1Options;
    public $list2Name;
    public $list2Title;
    public $list2Options;

    public function render(Zend_View_Interface $view = null) {
        if (null == $view) {
            $view = $this->getView();
        }

        $content = $view->lists(
            $this->getName(),
            array('title' => $this->list1Title, 'name' => $this->list1Name, 'options' => $this->list1Options),
            array('title' => $this->list2Title, 'name' => $this->list2Name, 'options' => $this->list2Options)
        );
        foreach ($this->getDecorators() as $decorator) {
            $decorator->setElement($this);
            $content = $decorator->render($content);
        }

        return $content;

    }
}