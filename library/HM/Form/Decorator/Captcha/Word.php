<?php
class HM_Form_Decorator_Captcha_Word extends Zend_Form_Decorator_Abstract
{
    /**
     * Render captcha
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $view    = $element->getView();
        if (null === $view) {
            return $content;
        }

        $name = $element->getFullyQualifiedName();

        $hiddenName = $name . '[id]';
        $textName   = $name . '[input]';

        $label = $element->getDecorator("Label");
        if($label) {
            $label->setOption("id", $element->getId()."-input");
        }

        $placement = $this->getPlacement();
        $separator = $this->getSeparator();

        $hidden = $view->formHidden($hiddenName, $element->getValue(), $element->getAttribs());
        $text   = $view->formText($textName, '', $element->getAttribs());
        switch ($placement) {
            case 'PREPEND':
                $content = $text . $separator . $hidden. $separator . $content;
                break;
            case 'APPEND':
            default:
                $content = $content . $separator . $text . $separator . $hidden;
        }
        return $content;
    }
}
