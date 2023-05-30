<?php

class HM_Form_Element_Submitbutton extends Zend_Form_Element {
    public function render(Zend_View_Interface $view = null) {
        return okbutton();
        //return "<div style='float: right;' class='button ok'><a href='javascript:void(0);' onclick='return eLS.utils.form.submit(this);'>&nbsp;&nbsp;&nbsp;OK&nbsp;&nbsp;&nbsp;</a></div><input type='submit' name='$name' value='".html($title)."' {$html} class='submit' style='display: none;' /><div class='clear-both'></div>";
    }
}