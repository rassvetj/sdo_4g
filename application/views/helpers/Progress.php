<?php
class HM_View_Helper_Progress extends ZendX_JQuery_View_Helper_UiWidget
{
    public function progress($percent, $size = "normal")
    {
        $id = $this->view->id('progressbar');
        
        $percent = max(0, $percent);
        $percent = min(100, $percent);

        $this->jquery->addOnLoad("$('#{$id}').progressbar({ value: {$percent} });");

        return "<div id=\"{$id}\" class=\"ui-progressbar progressbar-{$size}\"></div>";
    }
}