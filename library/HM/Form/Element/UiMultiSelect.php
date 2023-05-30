<?php

class HM_Form_Element_UiMultiSelect extends ZendX_JQuery_Form_Element_UiWidget
{
    public $multiple = 'multiple';
    protected $_isArray = true;

    public $helper = "uiMultiSelect";
}