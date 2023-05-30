<?php

class HM_Form_Decorator_CancelButton extends Zend_Form_Decorator_Abstract
{

    public $cancelLabel = null;
    public $cancelUrl = null;

    public function setCancelLabel($label)
    {
        $this->cancelLabel = $label;
    }

    public function getCancelLabel()
    {
        return $this->cancelLabel;
    }

    public function setCancelUrl($url)
    {
        $this->cancelUrl = $url;
    }

    public function getCancelUrl()
    {
        return $this->cancelUrl;
    }

    /**
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

        $separator = $this->getSeparator();
        $placement = $this->getPlacement();

        $options = $this->getOptions();

        $cancelButton = new Zend_Form_Element_Submit(
            'button',
            array(
                'Label' => $options['cancelLabel'],
                'onClick' => sprintf("window.location.href = '%s'; return false;", $options['cancelUrl'])
            )
        );
        $cancelButton->setDecorators(array('ViewHelper'));

        switch ($placement) {
            case self::APPEND:
                return $content . $separator . $cancelButton->render($view);
            case self::PREPEND:
                return $cancelButton->render($view) . $separator . $content;
        }
    }
}