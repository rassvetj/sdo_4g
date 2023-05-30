<?php

class HM_Form_Decorator_Button extends Zend_Form_Decorator_Abstract
{

    public $url = null;

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    /**
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

        $separator = $this->getSeparator();
        $placement = $this->getPlacement();

        $options = $this->getOptions();
        if (!$options['url'] && !$options['onClick']) {
            $button = new Zend_Form_Element_Button(
                'prev',
                array(
                    'Label' => $options['label'],
                    'onClick' => sprintf("if ($('#cancelUrl')) {
                                            window.location.href = $('#cancelUrl').val();
                                         }  return false;")
                )
            );
        } else {
            $button = new Zend_Form_Element_Button(
                'cancel',
                array(
                    'Label' => $options['label'],
                    'onClick' => isset($options['onClick']) ? $options['onClick'] : sprintf("window.location.href = '".$options['url']."'")
                )
            );
        }
        $button->setDecorators(array('ViewHelper'));

        switch ($placement) {
            case self::APPEND:
                return $content . $separator . $button->render($view);
            case self::PREPEND:
                return $button->render($view) . $separator . $content;
        }
    }
}