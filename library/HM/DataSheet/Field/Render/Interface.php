<?php
interface HM_DataSheet_Field_Render_Interface
{
    public function __construct($value, $options, $hId, $vId);
    public function render();
}