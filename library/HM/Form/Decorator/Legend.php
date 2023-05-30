<?php
class HM_Form_Decorator_Legend extends Zend_Form_Decorator_Abstract {

    protected $_tag = '';

    public function render($content) {
        // Получаем объект элемента к которому применяется декоратор
        $element = $this->getElement();

        // Проверяем объект вида зарегистрированного для формы
        if (null === $element->getView()) {
            return $content;
        }
        
        return "<tr><th colspan=99>".$element->getLegend()."</th></tr>".$content;
    }
}