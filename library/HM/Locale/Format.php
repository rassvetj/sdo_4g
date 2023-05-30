<?php

class HM_Locale_Format extends Zend_Locale_Format
{

    public static function getDateFormat($locale = null)
    {
        if ($locale == null) {
            $locale = Zend_Locale::findLocale();
        }
        return Zend_Locale_Format::getDateFormat($locale);
    }

}
