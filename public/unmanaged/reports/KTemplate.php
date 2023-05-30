<?php
/********************************************************************************************
*  Developed by Kovalenko Pavel (kovalenko_pavel@mail.ru)
*  for KEngine library
*  class KTemplate - 
*  version 1.3
********************************************************************************************/

class KTemplate
{
    var $vars=array();
    var $keys=array();
    var $files=array();
    var $base=_K_DIR_TEMPLATES;

function set($name,$value='')
{
    if(is_array($name)){
        foreach($name as $relName=>$relVal){
            $this->vars[$relName]=$relVal;
            $this->keys[$relName]="{".$relName."}";
            if(_K_TEMPLATE_DEBUG=='yes')$this->_dbgMsg("set: '$relName'='$relVal'");
        }
    }else{
        $this->vars[$name]=$value;
        $this->keys[$name]="{".$name."}";
        if(_K_TEMPLATE_DEBUG=='yes')$this->_dbgMsg("set: '$name'='$value'");
    }
}

function get($name)
{       
    if(!isset($this->vars[$name]))$this->_halt("Variable '$name' doas not set");
    return $this->vars[$name];
}

function separate($name,$sep)
{
    if(empty($this->vars[$name]))return;
    $this->parse($name,$sep,true);  
}

function file($file,$handle)
{
    if(!file_exists($this->base.$file) || !is_file($this->base.$file)){
        $this->_halt("File '".$this->base.$file."' does not exists or not file");
        return false;
    }
    $this->files[$handle]=$this->base.$file;    
    return true;
}

function block($handle,$name)
{
    if(is_array($name)){
        foreach($name as $realName){
            if(!$this->_load($handle)){
                $this->_halt("block('$handle','$realName'): unable to load '$handle'");
                continue;
            }
            $str = $this->get($handle);
            $reg = "/<!--\s+BEGIN $realName\s+-->(.*)\n\s*<!--\s+END $realName\s+-->/sm";
            preg_match_all($reg, $str, $m);
            if(isset($m[1][0]))
                $this->set($realName, $m[1][0]);
            else{
                $this->_halt("block('$handle','$realName'): not found block '$realName'");          
            }
        }
    }else{
        if(!$this->_load($handle)){
            $this->_halt("block('$handle','$name'): unable to load '$handle'");
            return false;
        }
        $str = $this->get($handle);
        $reg = "/<!--\s+BEGIN $name\s+-->(.*)\n\s*<!--\s+END $name\s+-->/sm";
        preg_match_all($reg, $str, $m);
        if(isset($m[1][0]))
            $this->set($name, $m[1][0]);
        else{
            $this->_halt("block('$handle','$realName'): not found block '$name'");
        }
    }
}   

function subst($handle) {
    if (!$this->_load($handle)) {
        $this->_halt("subst: unable to load '$handle'.");
        return false;
    }
    $str = $this->get($handle);
    $str=str_replace($this->keys, $this->vars, $str);
    return trim($str);    
}

function parse($target,$handle,$append=false)
{
    if (!is_array($handle)){
        $str = $this->subst($handle);
        if ($append)
            $this->set($target, $this->get($target) . $str);
        else
            $this->set($target, $str);
    }else{
        reset($handle);
        foreach($handle as $i=>$h){
            $this->set($target, $this->subst($h));
        }
    }
}

function dateFormat($name,$format)
{
    global $loc;
    if(isset($loc))
        $this->set($name,$loc->dateF($format,$this->get($name)));
    else
        $this->set($name,date($format,$this->get($name)));
}

function dateFormat2($name1,$name2,$format)
{
    global $loc;
    $d1=$this->get($name1);
    $d2=$this->get($name2);
    if($d2==$d1 || $d2==0){
        $this->dateFormat($name1,$format);
        return;        
    }
    $md1=date('M',$d1);
    $md2=date('M',$d2);
    $yd1=date('Y',$d1);
    $yd2=date('Y',$d2);
    $str='';
    if($md1==$md2 && $yd1==$yd2){
        $str=date('d',$d1)."-".date('d',$d2)." ".$loc->dateF("F2 Y",$d1);
    }else
        $str=$loc->dateF($format,$d1)." - ".$loc->dateF($format,$d2); 

    $this->set($name1,$str);
}

function kill($name)
{
    $str=$this->get($name);
    $str = preg_replace('/{([^ \t\r\n}]+)}/', "", $str);
    $this->set($name,$str);
}
//////////////////////////////////////////////////////////////////////

function _dbgMsg($msg)
{
    $msg=strtr($msg,array('<'=>'&lt;','>'=>'>'));
    echo "<font color='green'><b>KTemplate:</b></font> $msg<br>\n";
}

function _halt($msg)
{
    bugReport(" | ".get_class($this)." | ERROR: ".$msg,$this);
}

function _load($handle)
{
    if(isset($this->vars[$handle]))
        return true;

    if(!isset($this->files[$handle])){
        $this->_halt("_load: Handle '$handle' not valid");
        return false;
    }

    $fileName = $this->files[$handle];
    $str = implode("", @file($fileName));
    if (empty($str)) {
        $this->_halt("_load: While loading $handle, $fileName does not exist or is empty.");
        return false;
    }
    $this->set($handle, $str);
    
    return true;
}

function addVal($name,$val)
{
    $this->set($name,$this->get($name).$val);
}

function separateVal($name,$val)
{
    $v=$this->get($name);
    if(!empty($v)) $this->set($name,$v.$val);
}

function parseImgList($target,$sep,$ids)
{
    include_once(_K_DIR_ENGINE.'KImage.php');
    
    $this->set($target);
    $ids=unserialize(stripslashes($ids));
    if(!is_array($ids))$ids=array();    
    foreach($ids as $id)
    {
        $this->separate($target,$sep);
        $this->addVal($target,KImage::genHTMLByID($id));
    }
}

function parseImgListVal($target,$sepVal,$ids)
{
    include_once(_K_DIR_ENGINE.'KImage.php');
    
    $this->set($target);
    $ids=unserialize(stripslashes($ids));
    if(!is_array($ids))$ids=array();    
    foreach($ids as $id)
    {
        $this->separateVal($target,$sepVal);
        $this->addVal($target,KImage::genHTMLByID($id));
    }
}


function date_sql2loc($name)
{
    global $loc;
    if(!isset($loc))
        $this->_halt("Function locDate required definition KLocale object!");
    $this->set($name,$loc->date_sql2loc($this->get($name)));
}

}
?>