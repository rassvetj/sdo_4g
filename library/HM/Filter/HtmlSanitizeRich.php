<?php
require_once 'Zend/Filter/Interface.php';
require_once 'HTMLPurifier/HTMLPurifier.auto.php';

class HM_Filter_HtmlSanitizeRich extends HM_Filter_HtmlSanitize
{
    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns the string $value
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        return parent::filter($value, true);
    }
}
