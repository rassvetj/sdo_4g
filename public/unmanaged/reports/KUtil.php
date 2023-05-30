<?php
/********************************************************************************************
*  Developed by Kovalenko Pavel (kovalenko_pavel@mail.ru)
*  for KEngine library
*  class KUtil - 
*  version 1.5
********************************************************************************************/

/*function pr($arg) {
   echo "<xmp>";
   if (!isset($arg)) echo "pr(): value not set";
   elseif (is_object($arg)) echo print_r($arg);
   elseif (!is_array($arg)) echo "pr() string: '$arg'";
   elseif (!count($arg)) echo "pr(): array empty";
   else print_r($arg);
   echo "</xmp>";
} */

function vd($arg) {
   echo "<xmp>";
   var_dump($arg);
   echo "</xmp>";
}

function redirect($url) {
   global $page;
   $page->save();
   if (!headers_sent()) {
      header("Location: ".$url);
   }else{
      echo "<head><META HTTP-EQUIV='Refresh' CONTENT='0;url=".$url."'></head>";
      echo "<body><script>location.href=\"".$url."\"</script>";
      echo "<a href=\"".$url."\">Please click here for process</a></body>";
   }
   exit();
}

function cat_redirect($link='')
{
    global $category;
    if(isset($category))
        redirect('?cat='.$category->getCat().$link);
    else
        redirect('?cat=home'.$link);
}

function stack_trace()
{
    if(function_exists('debug_backtrace')){
        $stack=debug_backtrace();
        echo "Stack trace: <br>";
        foreach($stack as $c){
            echo "<b>".basename($c['file'])."</b> : ".$c['class']." : ".$c['function']." : <b>".$c['line']."</b> <br>";
        }
    }
}

function getCurURL()
{
    if( isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' )
        $prot='https';
    else
        $prot='http';
    return $prot."://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];;
}

function getFileList($path,$ext='',$sort=true)
{
    $f=array();
    if ($dir = @opendir($path)) {
        while (($file = readdir($dir)) !== false) {
            if(!is_file($path.$file))continue;
            if(isset($ext) && $ext!=''){
                $pi=pathinfo($file);
                if($pi['extension']!=$ext)
                    continue;
            }
            $f[]=$file;
        }  
        closedir($dir);
    }
    if($sort)sort($f);
    return $f;
}

function bugFormat($text)
{
    $ar=explode('|',$text);
    $text="<font color='red'><b> ".$ar[1]." </b></font> : ".$ar[2];
    if(isset($ar[3]))
        $text.=" : <b><font color='blue'>".$ar[3]."</font></b>";
    return $text."<br>\n";
}

function bugReport($text,$obj='')
{
    if(function_exists('debug_backtrace')){
        $stack=debug_backtrace();
        $stack_str="<b>Stack trace</b>: <br>\n";
        foreach($stack as $c){
            $stack_str.="<b>".basename($c['file'])."</b> : ".$c['class']." : ".$c['function']." : <b>".$c['line']."</b> <br>\n";
        }
    }
    global $log;
    if(isset($log)){
	    include_once(_K_DIR_ENGINE.'KLog.php');
	    KLog::write_line($text);
	    $text=bugFormat($text);
	    $msg=$text."<br>\n";
	    $msg.=$stack_str."<br><br>\n";
	}
    /*
    if(is_object($obj)){
        $msg.="<b>Error object content </b><br><pre>\n";
        $msg.=var_export($obj,TRUE)."</pre>";
    }*/
    $msg.="<b>Content of \$_SERVER </b><br>\n<table>\n";
    foreach($_SERVER as $key=>$val){
        $msg.="<tr><td align='right' valign='top'>$key : </td><td>".htmlspecialchars((string)$val)."</td></tr>\n";
    }
    $msg.="</table>";

    $msg.="<b>Content of \$_GET </b><br>\n<table>\n";
    foreach($_GET as $key=>$val){
        $msg.="<tr><td align='right' valign='top'>$key : </td><td>".htmlspecialchars((string)$val)."</td></tr>\n";
    }
    $msg.="</table>";

    $msg.="<b>dContent ofЗначения \$_POST </b><br>\n<table>\n";
    foreach($_POST as $key=>$val){
        $msg.="<tr><td align='right' valign='top'>$key : </td><td>".htmlspecialchars((string)$val)."</td></tr>\n";
    }
    $msg.="</table>";
    $msg.="        ";

    if(defined(_K_BUG_REPORT)){
	    mail(_K_BUG_REPORT,"KEngine bug report",$msg,
    	    "Content-type: text/html; charset=windows-1251\r\n"     
	        ."Reply-To: <"._K_DEF_RETURN.">\r\n"
    	    ."Return-Path: <"._K_BUG_REPORT.">\r\n"
        	);
	}

/*
    include_once(_K_DIR_ENGINE.'KDirectMail.php');
    KDirectMail::send(
        "pavel",
        _K_BUG_REPORT,
        _K_DEF_RETURN,
        "KEngine bug report",
        "Content-type: text/html; charset=windows-1251\r\n".
        "Reply-To: <"._K_DEF_RETURN.">\r\n".
        "Return-Path: <"._K_DEF_RETURN.">\r\n"
        ,
        $msg
    );
*/
    if(_K_SHOW_ERRORS=='yes'){
        echo $text;
        stack_trace();
    }
    if(_K_DIE_ON_ERROR=='yes') die;
}

function setNoCahe()
{
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-cache");
    header("Cache-Control: post-check=0, pre-check=0");
    header("Pragma: no-cache");
}

function send_file($fileName,$type='image/png')
{
    header('Content-type: '.$type);
    $f=fopen($fileName,"rb");    
    while(!feof($f)){
        echo fread($f,1024);
    }
    fclose($f);
    
}

?>