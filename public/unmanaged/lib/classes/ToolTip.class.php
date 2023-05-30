<?
class ToolTip {
    var $tplDir = "tooltips";
    var $tplName = "tooltip.tpl";
    var $skin_url;
    var $smarty;

    function display($name, $attention = false){
        return ''; // пока не заработают с новым шаблоном
	    $this->tplName = "tooltip.tpl";
        $file = $name.'-'.$GLOBALS['controller']->user->profile_current->basic_name.'.tpl';
        if (!file_exists(DIR_HELP.'/'.$this->tplDir.'/'.$file)){
            $file = $name.'.tpl';
            if (!file_exists(DIR_HELP.'/'.$this->tplDir.'/'.$file)){
                return false;
            }
        }
        $viewObj = new View();
        $this->skin_url = $viewObj->skin_url;
        $this->smarty = new Smarty_els();

        $attention = ($attention) ? '_attention' : '';
        $this->smarty->assign("img",$this->skin_url."/images/tooltip/tooltip{$attention}.gif");
        $this->smarty->assign("url",str_replace($_SERVER['DOCUMENT_ROOT'].'/',$GLOBALS['sitepath'],DIR_HELP).'/'.$this->tplDir."/tooltipLoader.php?file=$file");
        return $this->smarty->fetch($this->tplName);
    }

    function display_variable($name, $target, $values, $attention = true){
        return ''; // пока не заработают с новым шаблоном
    	if (!is_array($values)) $values = array($values);
	    $this->tplName = "tooltip_variable.tpl";
        $file = $name.'.tpl';
        if (!file_exists(DIR_HELP.'/'.$this->tplDir.'/'.$file)){
            return false;
        }
        $viewObj = new View();
        $this->skin_url = $viewObj->skin_url;
        $this->smarty = new Smarty_els();

        $this->smarty->assign("target", $target);
        $this->smarty->assign("values", implode("', '", $values));
        $this->smarty->assign("img", ($attention) ? $this->skin_url.'/images/tooltip/tooltip_attention.gif' : $this->skin_url.'/images/tooltip/tooltip.gif');
        $this->smarty->assign("url",str_replace($_SERVER['DOCUMENT_ROOT'].'/',$GLOBALS['sitepath'],DIR_HELP).'/'.$this->tplDir."/tooltipLoader.php?file=$file");
        return $this->smarty->fetch($this->tplName);
    }
}
$tooltip = new ToolTip();
?>