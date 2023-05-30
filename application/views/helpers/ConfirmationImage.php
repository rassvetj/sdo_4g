<?php
class HM_View_Helper_ConfirmationImage extends HM_View_Helper_Abstract
{
    public function confirmationImage($scaleId, $value)
    {
        $this->view->headLink()->appendStylesheet($this->view->serverUrl('/css/content-modules/subject.css'));

        $class = $scaleValue = '';
        if (
            (($scaleId == HM_Scale_ScaleModel::TYPE_BINARY) && ($value == HM_Scale_Value_ValueModel::VALUE_BINARY_ON)) ||
            (($scaleId == HM_Scale_ScaleModel::TYPE_TERNARY) && ($value == HM_Scale_Value_ValueModel::VALUE_TERNARY_ON))
        ) {
            $class = 'confirmation-image-bg';
            $scaleValue = '<img src="/images/content-modules/marksheet/scale-value-on.png" />';    
        } elseif ($scaleId == HM_Scale_ScaleModel::TYPE_CONTINUOUS) {
            $class = 'confirmation-image-bg';
            $value = (int) $value;
            $scaleValue = "<div class=\"scale-value-continuous\">{$value}</div>";
        }
        if (empty($class)) return '';

        $html = "<div class=\"{$class}\">{$scaleValue}</div>";
        return $html;
    }
}