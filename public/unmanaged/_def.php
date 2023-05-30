<?
define ("TARGET_TOP", 1);
define ("TARGET_THIS", 2);

#
# Добавление слешей перед выводом строки внутри JS функции
#   $br - заменять ли переходы строк на "\n"
#

//    global $info_block;
// функции для определения в какое место вставлять инфо блок

function intBlockType( $info ){
  switch( $info ){
//    case "": $i=1; break;
    case "-~-": $i=1; break;
    case "-~about~-": $i=1; break;
    case "-~home~-": $i=2; break;
    case "-~help~-": $i=3; break;
    case "-~reg~-": $i=4; break;
    case "-~courses~-": $i=5; break;
    case "-~lib~-": $i=6; break;
    case "-~faq~-": $i=7; break;
      default: $i=0; break;
  }
  return( $i );
}

function textBlockType( $info ){
  switch( $info ){
    case "-~-": $i="info"; break;
    case "-~about~-": $i=_("О СЕРВЕРЕ"); break;
    case "-~home~-": $i=_("СТАРТОВАЯ"); break;
    case "-~help~-": $i="HELP"; break;
    case "-~reg~-": $i=_("РЕГИСТРАЦИЯ"); break;
    case "-~courses~-": $i=_("КУРСЫ"); break;
    case "-~lib~-": $i=_("БИБЛИОТЕКА"); break;
    case "-~faq~-": $i="FAQ"; break;
    default: $i=0; break;
  }
  return( $i );
}

function addjs($msg,$br=0) {
   $msg=str_replace("\\","\\\\",$msg);
   $msg=str_replace("\"","\\\"",$msg);
   $msg=str_replace("'","\\'",$msg);
   if ($br) {
      $msg=str_replace("\r","",$msg);
      $msg=str_replace("\n","\\n",$msg);
   }
   return $msg;
}



#
# Логирование вывода текста программы в файл
#
function myob($fn="___myob_temp.html") {
   ob_start("__myob_end");
   $GLOBALS['myobfilename']=realpath($fn);
}

function __myob_end($buf) {
   $f=@fopen($GLOBALS['myobfilename'],"w+");
   @fputs($f,"<h1>".date("d/m/Y H:i:s")." ".md5(microtime())."</h1>\n\n\n$buf<br><br>\n\n<xmp>".mypr($GLOBALS));
   @fclose($f);
   return $buf;
}


#
# Аналог print_r, для вывода в return структуры
#
function mypr(&$x,$print=0) {
   if (!$print) return _mypr(&$x);
   echo "<xmp>"._mypr(&$x)."</xmp>";
}

function _mypr(&$x,$tab="",$name="",$ravno="=") {

   $maxlen=200; // какой максимальной длины выводить строки

   static $cnt=0;
   if ($cnt++>500) return "";
   if ($name==="GLOBALS" && is_array($x)) return "{$tab}GLOBALS => ...\n";
   $q="";
   $tab2="$tab   ";
   if ($name!=="") $name.=" ";
   if (is_array($x)) {
      $q.="{$tab}{$name}[array:".count($x)."]\n";
      if (count($x)==0) return $q;
      $q.="{$tab}(\n";
      $keys=array_keys($x);
      for ($i=0; $i<count($keys); $i++) {
         $q.=_mypr(&$x[$keys[$i]],$tab2,$keys[$i],"=>");
      }
      $q.="{$tab})\n";
      return $q;
   }
   if (is_object($x)) {
      $q.="{$tab}{$name}object...\n";
      return $q;
   }
   if (is_null($x)) {
      $q.="{$tab}{$name} $ravno NULL\n";
      return $q;
   }
   if (is_float($x) && is_nan($x)) {
      $q.="{$tab}{$name} $ravno NAN (not a number)\n";
      return $q;
   }
   if (is_string($x)) {
      $q.="{$tab}{$name}[string:".strlen($x)."] $ravno ";
      if (strlen($x)>$maxlen) $q.=preg_replace("![\r\n]!s","\\n",substr($x,0,500))."...\n";
      else $q.=preg_replace("![\r\n]!s","\\n",$x)."\n";
      return $q;
   }
   if (is_bool($x)) {
      if ($x)
         $q.="{$tab}{$name}[bool] $ravno TRUE\n";
      else
         $q.="{$tab}{$name}[bool] $ravno FALSE\n";
      return $q;
   }
   if (is_double($x) || is_int($x) || is_float($x) || is_infinite($x) || is_long($x)) {
      $q.="{$tab}{$name}[".gettype($x)."] $ravno $x\n";
      return $q;
   }
   return $q;
}


#
# Привести имя аплоденного файла в приличный вид из английских букв
#
function normal_filename($ss) {
   return substr(to_translit(substr($ss,0,210)),0,210);
}

#
# Строку в транслит
#
function to_translit($ss) {
    $ss=str_replace(
       array('Ш', 'Щ',  'Ж', 'Я', 'Ч', 'Ю', 'Ё', 'ш', 'щ',  'ж', 'я', 'ч', 'ю', 'ё', 'Й','Ц','У','К','Е','Н','Г','З','Х','Ъ','Ф','Ы','В','А','П','Р','О','Л','Д','Э','С','М','И','Т','Ь','Б','й','ц','у','к','е','н','г','з','х','ъ','ф','ы','в','а','п','р','о','л','д','э','с','м','и','т','ь','б',' '),
       array('SH','SCH','ZH','YA','CH','YU','YO','sh','sch','zh','ya','ch','yu','yo', 'J', 'C', 'U', 'K', 'E', 'N', 'G', 'Z', 'H', '_', 'F', 'Y', 'V', 'A', 'P', 'R', 'O', 'L', 'D', 'E', 'S', 'M', 'I', 'T', '_', 'B', 'j', 'c', 'u', 'k', 'e', 'n', 'g', 'z', 'h', '_', 'f', 'y', 'v', 'a', 'p', 'r', 'o', 'l', 'd', 'e', 's', 'm', 'i', 't', '_', 'b', '_'),
       $ss);
    $ss = str_replace($russtr1, $russtr2, $ss);
    return $ss;

   // TRASH
   $ss=str_replace(
      array('Ш', 'Щ',  'Ж', 'Я', 'Ч', 'Ю', 'Ё', 'ш', 'щ',  'ж', 'я', 'ч', 'ю', 'ё', ),
      array('SH','SCH','ZH','YA','CH','YU','YO','sh','sch','zh','ya','ch','yu','yo',),
      $ss);
   $ss = str_replace($russtr1, $russtr2, $ss);
   $russtr1="JCUKENGZH_FYVAPROLDESMIT_Bjcukengzh_fyvaproldesmit_b_";
   $russtr2="ЙЦУКЕНГЗХЪФЫВАПРОЛДЭСМИТЬБйцукенгзхъфывапролдэсмитьб ";
   $ss=strtr($ss,$russtr2,$russtr1);

   return $ss;
}


#
# Альтернатива для getenv("HTTP_HOST") - вырезает порт, если он там есть.
# Порт будет, если он не "80".
#
function gethttphost() {
   $host=preg_replace("!:[0-9]+$!","",$_SERVER["HTTP_HOST"]);
   return $host;
}


#
# Сортировка 2х мерного массива по любому полю
#
function sortarray(&$arr,$col,$desc,$kods = false) {
   $buf=array();
   $out=array();
   foreach ($arr as $k=>$v) {
      $buf[$k] = ($kods) ? intval(str_replace("-", "", $v[$col])) : $v[$col];
   }
   if ($desc) arsort($buf); else asort($buf);
   foreach ($buf as $k=>$v) {
      $out[]=$arr[$k];
   }
   $arr=$out;
}


#
# WorlWrap + HtmlSpecialChars в одном флаконе .-)
#
function htmlwordwrap($text,$num,$br) {
   $text=str_replace("\x01","",$text);
   $text=wordwrap($text,$num," \x01[wbr]\x01 ",1);
   $text="<nobr>".html($text)."</nobr>";
   $text=str_replace(" \x01[wbr]\x01 ","<wbr>",$text);
   return $text;
}

#
# Из кода вопроса получить первое число - номер курса
#
function kodintval($kod) {
   $kod=trim($kod);
   if (preg_match("!^([0-9]{1,10})-!",$kod,$ok)) {
      return intval($ok[1]);
   }
   return 0;
}
function cid2kod ($kod) {return kodintval($kod);}
function kod2cid ($kod) {return kodintval($kod);}
function getcid ($kod) {return kodintval($kod);}

#
# Проверить валидность кода вопроса исходя из номера курса и проверяемого кода
#
function validkod($cid,$kod) {
   if (preg_match("![^a-zA-Z0-9*?_-]!",$v)) return false;
   if (strlen($kod)>255) return false;
   $x="$cid-";
   if (substr($kod,0,strlen($x))!=$x) return false;
   return true;
}


#
# Обрезать строку на $length байт, если она длинная, и приписать "..." в конец.
# Если строка не длиннее $length, то вернуть без изменений.
#
function strbig($str,$length) {
   if (strlen($str)>$length)
      return substr($str,0,$length)."...";
   return $str;
}


#
# Провека и обновления временного файла $fn для организации cron-вызовов
# раз в $update секунд.
#    $fn - имя временного файла (с правами на запись!)
#    $update - как часто вызывать (сек)
# Возврат:
#    1 - пора делать cron операции
#    0 - еще не пора
#   -1 - ошибка (нет прав открыть файлы)
#
function cron_update($fn,$update) {
   if (!file_exists($fn)) {
      if (!@touch($fn)) {
         echo _("не могу создать")." <b>".basename($fn)."</b>, "._("проверьте права на файлы")." (w+)<br>";
         return -1;
      }
   }
   else {
      if (time()-filemtime($fn)<$update) {return 0;}
   }
   $f=@fopen($fn,"r+");
   if (!$f) {
      echo _("не могу открыть")." <b>".basename($fn)."</b>, "._("проверьте права на файлы")." (r+)<br>";
      return -1;
   }
   if (!flock($f,6)) {return;}
   $text=fgets($f,100);
   if (time()-intval($text)<$update) {return 0;}
   fseek($f,0,SEEK_SET);
   fwrite($f,time()."    ");
   fclose($f);
   return 1;
}


#
# Прошедшее время за $t секунд
#
function duration ($t) {
   $s="";
   $t=doubleval($t);
   $a=$t/(60*60*24);
   if (intval($a)>0) {
      if (intval($a)==$a) return intval($a)." ".slovo($a,_("день"));
      $s.=intval($a)._("дн.")." ";
      if (intval($a)%(60*60*24)==60*60*24) return intval($a)." ".slovo($a,_("день"));
   }
   $a=$t/(60*60);
   if (intval($a)>0) {
      if ($s=="" && intval($a)==$a) return intval($a)." ".slovo($a,_("час"));
      $s.=sprintf("%02d",(intval($a)%24))._("ч:");
   }
   $a=$t/60;
   if (intval($a)>0) {
      if ($s=="" && intval($a)==$a) return intval($a)." ".slovo($a,_("минута"));
      $s.=sprintf("%02d",(intval($a))%60)._("м:");
   }
   //$s.=sprintf("%02d",(intval($t/60)%60))."м:";
   if ($s=="") return $s.sprintf("%02d",($t%60))." ".slovo($t%60,_("секунда"));
   $s.=sprintf("%02d",($t%60))._("с");
   return $s;
}
function slovo($a,$w) {
   $a=intval($a);
   // глюки окончаний в русском языке N1 :)
   $buf[_("день")]=array(_("дня"),_("дней"));
   $buf[_("час")]=array(_("часа"),_("часов"));
   $buf[_("минута")]=array(_("минуты"),_("минут"));
   $buf[_("секунда")]=array(_("секунды"),_("секунд"));
   // глюки окончаний в русском языке N2
   $buf[_("секунду")]=array(_("секунды"),_("секунд"));
   // глюки окончаний в русском языке N3
   $buf[_("секунды")]=array(_("секунд"),_("секунд"));
   $buf[_("байта")]=array(_("байт"),_("байт"));
   $buf[_("сообщения")]=array(_("сообщений"),_("сообщений"));
   $buf[_("смайлика")]=array(_("смайликов"),_("смайликов"));
   $b=$a%10;
   if ($a>=11 && $a<=20) return $buf[$w][1];
   if ($b>=2 && $b<=4) return $buf[$w][0];
   if ($b==1) return $w;
   return $buf[$w][1];
}




#
# Получить по номеру курса его название
#
function cid2title($cid) {

   $rq="SELECT * FROM Courses WHERE cid='".intval($cid)."'";
   $res=sql($rq,"cid2title($cid)");
   if (sqlrows($res)==0) return "";
   if(dbdriver == "oci8")
   	$name=sqlres($res,0,'TITLE');
   else
   	$name=sqlres($res,0,'Title');
   sqlfree($res);
   return $name;
}

#
# Получить по номеру теста его название
#
function tid2title($tid) {
   $rq="SELECT * FROM test WHERE tid=".intval($tid);
   $res=sql($rq,"tid2title($tid)");
   if (sqlrows($res)==0) return "";
   $name=sqlres($res,0,'title');
   sqlfree($res);
   return $name;
}


#
# Получить по логину его MID
#
function login2mid($login) {
   $rq="SELECT * FROM People WHERE login=".$GLOBALS['adodb']->Quote($login)."";
   $res=sql($rq,"login2mid($login)");
   if (sqlrows($res)==0) return "";
   $name=sqlres($res,0,'MID');
   sqlfree($res);
   return $name;
}


#
# Получить логину по MID
#
function mid2login($mid) {
   $rq="SELECT * FROM People WHERE mid='".addslashes($mid)."'";
   $res=sql($rq,"mid2login($mid)");
   if (sqlrows($res)==0) return "";
   $name=sqlres($res,0,'Login');
   sqlfree($res);
   return $name;
}

#
# Получить имя по MID
#
function mid2name($mid) {
    $name = '';
   $rq="SELECT * FROM People WHERE mid='".addslashes($mid)."'";
   $res=sql($rq,"mid2name($mid)");
    if ($row = sqlget($res)){
        $name = "{$row['LastName']} {$row['FirstName']} {$row['Patronymic']}";
    }
   sqlfree($res);
   return $name;
}


//
// функции обратимого шифрования и расшифровки
//
function encrypt($s,$pass) {
   $md=md5pass(strval($pass));
   $s=strval($s);
   
   $strLen = strlen($s);
   $seq = $pass;
   $gamma = '';
   while (strlen($gamma)<$strLen)
   {
       $seq = pack("H*",sha1($gamma.$seq.$md5));
       $gamma.=substr($seq,0,8);
   }
   
   return $s^$gamma;   
    
   $md=md5pass(strval($pass));
   $s=strval($s);
   for ($i=0; $i<strlen($s); $i++) $s[$i]=chr((ord($s[$i])+ord($pass[$i%strlen($pass)])*6)%256);
   for ($i=0; $i<strlen($s); $i++) $s[$i]=chr((ord($s[$i])+ord($md[$i%strlen($md)])*5)%256);
   return $s;
}

function decrypt($s,$pass) {
   return encrypt($s,$pass); 
    
   $md=md5pass(strval($pass));
   $s=strval($s);
   for ($i=0; $i<strlen($s); $i++) $s[$i]=chr((ord($s[$i])-ord($md[$i%strlen($md)])*5)%256);
   for ($i=0; $i<strlen($s); $i++) $s[$i]=chr((ord($s[$i])-ord($pass[$i%strlen($pass)])*6)%256);
   return $s;
}

// вспомогательная функция
function md5pass($pass) {
$s=substr(md5($pass."G#@$J(HN@#$g"),18,6).
   substr(md5($pass."EoxnDEnbsp6a"),3,6).
   substr(md5($pass."*9Ednw3wb_3="),23,6).
   substr(md5($pass."!:}.,/sde35)"),10,14).
   md5($pass."cn20n2").md5($pass."=x1~<S").md5($pass."23=+$");
   $c=strlen($s)/2;
   $ss="";
   for ($i=0; $i<$c; $i++) {
      $ss.=chr(hexdec(substr($s,$i*2,2)));
   }
   return $ss;
}



#
# создание справки по ссылке "[справка]" или "[?]"
#
function helpalert($msg,$name,$use_return=0) {
   $msg=str_replace("\r","",$msg);
   $msg=preg_replace("!\n +!si","\n",$msg);
   $tmp="<a href=# onclick=\"alert('".msg2js($msg)."');return false\">$name</a>";
   if ($use_return) return $tmp;
   echo $tmp;
}


#
# alert + refresh + exit
#
function exitmsg($msg,$url="",$sec=0, $constTarget = TARGET_TOP) {
   $GLOBALS['controller']->setMessage($msg, JS_GO_URL, $url, false, false, false, '_top');
   if (!$GLOBALS['controller']->enabled){
	   alert($msg);
	   if ($url!="") refresh($url, $sec, $constTarget);
	   exit;
   }
   $GLOBALS['controller']->terminate();
   exit();
}


#
# типа addslashes для строки, перед помещением в <script>alert($s)</script>
#
function msg2js($s) {
   $s=str_replace("\\","\\\\",$s);
   $s=str_replace("\"","\\\"",$s);
   $s=str_replace("'","\\'",$s);
   $s=str_replace("\r\n","\n",$s);
   $s=str_replace("\r","",$s);
   $s=str_replace("\n","\\n\\\n",$s);
   $s=preg_replace("!\n +!","\n ",$s);
   return strip_tags($s);
}

function alert($msg) {
//   if ($GLOBALS['controller']->enabled)
//   $GLOBALS['controller']->setMessage($msg);
//   else
   header("Content-Type: text/html; charset=".$GLOBALS['controller']->lang_controller->lang_current->encoding);
   echo "<script>alert(\"".msg2js($msg)."\");</script>";
}

function close_window() {
   echo "<script>window.close();</script>";
}


#
# найти в массиве элемент и вернуть его порядковый номер
#
function num_array(&$arr,$el) {
   $i=0;
   foreach ($arr as $v) {
      if ($v===$el) return $i;
      $i++;
   }
   return false;
}


#
# задать случ. числа
#
function randomize() {
   static $x;
   if (isset($x)) return;
   $x=1;
   list($usec,$sec) = explode(" ", microtime());
   mt_srand((float)$sec+((float)$usec)*1000);
//   echo (float)$sec+(float)$usec*100000;
}



#
# Вырезать из HTTP_REFERER переменную $tweek (для редиректа);
#
function gettweek() {
   if (preg_match("!tweek=([0-9]{1,12})!",$_SERVER["HTTP_REFERER"],$ok))
      return $ok[1];
   return time();
}

#
# замена exit(), см. так же err()
#
function di($err="") {
   if ($err!=="") err($err,1);
   exit($err);
}

#
# вернуть содержимое файла или пустую строку, если файла нет (+ошибки)
#
function gf($fn) {
   ob_start();
   $GLOBALS['controller']->substituteTemplate($fn);
   $f=fopen($fn,"rb");
   $err=ob_get_contents();
   ob_end_clean();
   if (!$f) {
      putlog("gf($fn) - can't open this file: $err");
      if (debug) echo $err;
      return "";
   }
   $s=fread($f,filesize($fn));
   fclose($f);
   return parse_t_blocks($s);
}

#
# замена print_r
#
function pr($array) {
   echo "<xmp>";
   if (!isset($array)) echo "pr(): value not set";
   elseif (is_object($array))  print_r($array);
   elseif (!is_array($array)) echo "pr() string: $array";
   elseif (!count($array)) echo "pr(): array empty";
   else print_r($array);
   echo "</xmp>";
}

function ad($s) {return addslashes($s);}

function ue($s) {return urlencode($s);}

function sl($s) {
   return strtr($s,
     "ЁЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮQWERTYUIOPASDFGHJKLZXCVBNM",
     "ёйцукенгшщзхъфывапролджэячсмитьбюqwertyuiopasdfghjklzxcvbnm");
}

#
# выполнить для переменных, записанных через пробел, функцию intval
#
function intvals($s) {
   foreach (explode(" ",$s) as $v)
      $GLOBALS[$v]=intval($GLOBALS[$v]);
}

#
# заменить в тексте "тест $переменная" на значение переменной
#
function var_replace($html) {
   $html=preg_replace("!\$([a-zA-Z0-9_]{1,20})!e","\$GLOBALS['\\1']",$html);
   return $html;
}

function refresh($url,$sec=0,$constTarget = TARGET_THIS) {
   if ($GLOBALS['controller']->isAjaxRequest()) {
       $GLOBALS['controller']->sendRefresh($url);
   }
   global $s,$PHP_SELF;
   echo "<head><META HTTP-EQUIV='Refresh' CONTENT='$sec;url=$url'></head>";
   if ($sec==0) {
                   switch ($constTarget) {
                           case TARGET_TOP:
                                   echo "<script>top.location.href=\"$url\"</script>";
                                   break;
                           case TARGET_THIS:
                                   echo "<script>location.href=\"$url\"</script>";
                                   break;
                   }
   }

}

// andy for mazafaka design
function winclose($opener_refresh = false) {
        if (!$_SESSION['boolInFrame']){
            echo "<script>window.close();";
		    if ($opener_refresh) echo "opener.location.reload();";
               echo "</script>";
        }
}

function location($url,$msg="<html>") {
   global $s,$PHP_SELF;
//   echo $url;
   header("Location: $url");
   echo $msg;
}

function jslocation($url) {
   global $s,$PHP_SELF;
   echo "<script>top.location.href=\"$url\"</script>";
}

function html($x="") { return htmlspecialchars(strval($x)); }



/////////////////////////////// SQL /////////////////////////////////////
//#######################################################################
//#######################################################################
//#######################################################################
//require_once("adodb_func.php");

//fn
/*
function myconnect() {
   global $sqlconnect;
   $sock=@mysql_connect(dbhost,dbuser,dbpass);
   if ($sock==false) die("</table></table></table><table height=95% width=100%><tr><td align=center><Table border=0 bgcolor=#997766><tr><td><Table border=0 bgcolor=#ffffff><tr><td><Table border=0 bgcolor=#997766><tr><td><Table border=0 bgcolor=#ffffff><tr><td align=center><b><font style='font-size:10pt;'>Извините, сервер временно не работает.   </font></b></td></tr></table></td></tr></table></td></tr></table></td></tr></table></td></tr></table>");
   if (@mysql_select_db(dbbase,$sock)==false) die("error# не веpное имя базы данных");
}

function sql($a,$err="",$free=0) {
// данная "хитрая" проверка с помощью static позволит нам подконнектиться
// к SQL базе только тогда, когда это надо и если это надо. Другими словами
// коннект состоится при первом вызове sql(), а при повторных - нет.
   static $connect;
   if ($connect<>1) {
      myconnect();
      $connect=1;
   }
   if ($err=="") {
      if ($free) {
         sqlfree($res);
         return false;
      }
      return @sql($a);
   }
   $res=@sql($a);
   if (!$res) {
      sqlerror($a,$err);
      exit();
   }
   if ($free) {
      sqlfree($res);
      return false;
   }
   return $res;
}


// кол-во строк:
function sqlrows($a) { return @sqlrows($a); }
// получить ячейку из ответа:
function sqlres($a,$b,$c) { return @mysql_result($a,$b,$c); }
// получить целую строку ОЧЕРЕДНУЮ из ответа:
function sqlget($a) { return @sqlget($a); }
// выполнить запрос и вернуть самую первую ячейку - надо в тех случаях, когда
// sql-запрос выбирает только одно значение из базы данных
function sqlval($a) { return @sqlget(sql($a)); }
// после того, как идентификатор sql-запроса стал не нужен, надо как можно
// скорее уничтожить его (ту память, на которую ссылается идентификатор).
// Это похоже на закрытие файла.
function sqlvalue($a,$err) {
   $res=sql($a,$err);
   if (sqlrows($res)==0) $val=false;
   else $val=sqlres($res,0,0);
   sqlfree($res);
   return $val;
}

function sqlfree($a) { return @mysql_free_result($a); }
// получить последнее значение, автоматом проставленное в колонке
// с AUTO_INCREMENT - если такое имеется, и если его надо получить
function sqllast() { return @sqllast(); }

function sqlerror($zapros="", $info="") {
   putlog("SQLERROR | $zapros | $info");
   exit("</table></table></table></table><hr><h3>************ $info ***********<br><br>
   SQL ERROR..... N".mysql_errno().": ".mysql_error()."</h3><pre>$zapros</pre><hr>");
}

function sqldupl() {
// Если при вставке значенея в колонку УНИКАЛЬНАЯ оно там уже было,
// функция sql() вернет false, а эта - true. Если sql() вернет правду,
// то эта вернет false, что будет означать успешную вставку.
// Вызывать без параметров после sql-запроса.
   if (eregi("Duplicate entry",mysql_error())) return true;
   return false;
}



//function sqlf($a,$err="") {
// выполнить запрос и сразу очистить память (для не SELECT запросов)
//   $res=sql($a,$err,1);
//   if (!$res) return false;
//   sqlfree($res);
//   return true;
//}

// проверяет наличие таблицы в БД
function showTableFields( $table ){
// вывводит все поля твблицы
  $fields = mysql_list_fields( dbbase, $table );
  $columns = mysql_num_fields($fields);

   for ($i = 0; $i < $columns; $i++) {
     echo "<LI>".mysql_field_name($fields, $i);
     echo " ( ".mysql_field_type($fields, $i)." ) [".mysql_field_flags($fields, $i)." ]<BR>";
//        $len   = mysql_field_len($result, $i);

   }
  return( $res );
}

function showTables( $mode ){
   // выводит все таблицы БД
  $result = mysql_list_tables( dbbase );

    if (!$result) {
        echo "DB Error, could not list tables\n";
        echo 'MySQL Error: ' . mysql_error();
        exit;
    }

    while ($row = mysql_fetch_row($result)) {
        echo "<B>Table: $row[0]</B> lines=".rowsCount( $row[0] )."<BR>";
        if( $mode ) showTableFields( $row[0] );
    }

  mysql_free_result($result);

  return( $res );
}   */
//#########################################################################
//#########################################################################
//#########################################################################

function void($str)
{
        return $str;
}


// rooms - набор аудиторий
//  rid , name, volume, type, description, status, ips

// tracks - программы обучения
//  tid , name, type, description, status,


// tracks_links - программы обучения
//  tid , cid, step, sgrid?,
/*
rid ( int ) [ ]
name ( blob ) [blob ]
volume ( int ) [ ]
type ( blob ) [blob ]
status ( int ) [ ]
description ( blob ) [blob ]
*/

function rowsCount( $table ){
  if ( ! strchr($table,"-") ){
   $r=sql("SELECT * FROM `$table`","ERRcount");
   $i=0;
    while( $rr=sqlget($r) ){
      $i++;
   }
    sqlfree( $r );
  }else $i=-1;
  return( $i );
}


function upgradeTable( $table ){

 if( $table =="Students" ){
    $res=sql("SELECT * FROM $table", "ERR - update students");
    $i=0;
    while( $r=sqlget($res) ){
      $sts[$i]['MID']=$r['MID'];
      $sts[$i]['CID']=$r['CID'];
      $sts[$i]['SID']=$r['SID'];
      $sts[$i]['cgid']=$r['cgid'];
      $sts[$i]['Registered']=$r['Registered'];
    }
    if( $res ){
      $rq="DROP TABLE ".$table;
      $Result = sql( $rq );

      $rq="CREATE TABLE ".$table." (
             SID int UNSIGNED DEFAULT '0' NOT NULL auto_increment,
             CID int NOT NULL , MID int NOT NULL , cgid int NOT NULL , Registered int NOT NULL ,
           PRIMARY KEY(`SID`) )";
      $res=sql( $rq,"ERR upgrading ");
      if( $res ){
         foreach( $sts as $st ){
           $rq="INSERT INTO $table ( SID, MID, CID, cgid, Registered ) VALUES (".$st['SID'].",".$st['MID'].",".$st['CID'].",".$st['cgid'].",".$st['Registered'].")";
           $res=sql( $rq,"ERR upgrading ");
        }
      }
    }
 }
 if( $table == "Courses" ){
      $rq="ALTER TABLE $table ADD longtime int";
//      $res=sql( $rq,"ERR upgrading $table");
      $rq="ALTER TABLE $table ADD did int"; // ссылка на депертамент (кто владеет курсом)
      $res=sql( $rq,"ERR upgrading $table");
 }

  if( $table == "EventTools" ){
      $rq="ALTER TABLE $table ADD type int";
      $res=sql( $rq,"ERR upgrading $table");
 }

 if( $table == "schedule" ){
//      $rq="ALTER TABLE $table ADD timetype int";
//      $res=sql( $rq,"ERR upgrading $table");
//      $rq="ALTER TABLE $table ADD startday int";
//      $res=sql( $rq,"ERR upgrading $table");
//      $rq="ALTER TABLE $table ADD stopday int";
//      $res=sql( $rq,"ERR upgrading $table");
      $rq="ALTER TABLE $table ADD room int";
      $res=sql( $rq,"ERR upgrading $table");
 }
 if( $table == "groupname" ){
      $rq="ALTER TABLE $table ADD info BLOB";
      $res=sql( $rq,"ERR upgrading $table");
 }
 echo "<H1> $table > $res</H1>";
 return( $res );
}

function createTable( $table ){
 // создает таблицу


 $s="DROP TABLE $table";
 $Result = sql( $s );
                      //         primary_key auto_increment not_null
 if( $table =="tracks" ){
   $s="CREATE TABLE $table( trid int UNSIGNED DEFAULT '0' NOT NULL auto_increment,
                            name VARCHAR(255), id VARCHAR(255), volume int, status int, type int,
                            owner VARCHAR(255), totalcost int, currency int, description TEXT,
                            PRIMARY KEY(`trid`))";
   $Result = sql( $s );

   $s="DROP TABLE ".$table."2course";
  $Result = sql( $s );

  $s="CREATE TABLE ".$table."2course( trid int , cid int, level int, name VARCHAR(255) )";
  $Result = sql( $s );


 }

 if( $table =="tracks2mid" ){

  $s="CREATE TABLE ".$table."( trmid int UNSIGNED DEFAULT '0' NOT NULL auto_increment,
                               trid int , mid int, level int,
                               started int, changed int, stoped int, status int, sign int, info TEXT,
                               PRIMARY KEY(`trmid`) )";
  $Result = sql( $s );


 }
 if( $table =="person" ){  // проводки денег

  $s="CREATE TABLE ".$table."( ppid int UNSIGNED DEFAULT '0' NOT NULL auto_increment,
                               mid int, date int, place int, category int, position int, department int,
                               PRIMARY KEY(`ppid`) )"; //;13:32 02.10.2004
  $Result = sql( $s );


 }

 if( $table =="money" ){  // проводки денег

  $s="CREATE TABLE ".$table."( moneyid int UNSIGNED DEFAULT '0' NOT NULL auto_increment,
                               sum int, mid int, trid int, date int, type int, sign int, info VARCHAR(255),
                               PRIMARY KEY(`moneyid`) )";
  $Result = sql( $s );


 }

 if( $table =="periods" ){  // перечень пар

  $s="CREATE TABLE ".$table."( lid int UNSIGNED DEFAULT '0' NOT NULL auto_increment,
                               starttime int, stoptime int, name VARCHAR( 255 ),
                               PRIMARY KEY(`lid`) )";
  $Result = sql( $s );


 }

 if( $table =="departments" ){  // перечень подразделений (кафедр)

  $s="CREATE TABLE ".$table."( did int UNSIGNED DEFAULT '0' NOT NULL auto_increment,
                               name VARCHAR( 255 ), color VARCHAR( 255 ), mid int UNSIGNED DEFAULT '0' NOT NULL, info BLOB,
                               PRIMARY KEY(`did`) )";
  $Result = sql( $s );


 }

 if( $table =="rooms" ){
                                                                 //
   $s="CREATE TABLE $table( rid int UNSIGNED DEFAULT '0' NOT NULL auto_increment,
                            name VARCHAR(255), volume int, status int, type int, description TEXT,
       PRIMARY KEY(`rid`) ) ";
   $Result = sql( $s );
   $s="DROP TABLE ".$table."2course";
  $Result = sql( $s );

  $s="CREATE TABLE ".$table."2course( rid int , cid int )";
  $Result = sql( $s );

 }
 if( $table =="organizations" ){
                                                                 //
   $s="CREATE TABLE $table( oid int UNSIGNED DEFAULT '0' NOT NULL auto_increment,
                            title VARCHAR(255), cid int,
                            root_ref int, level int, next_ref int, prev_ref int, mod_ref int,
                            status int, vol1 int, vol2 int, metadata TEXT,
       PRIMARY KEY(`oid`), KEY(`prev_ref`) ) ";
   $Result = sql( $s );

 }
 echo "<H1> $table !$Result!</H1>";
 if(!$Result) return FALSE;
}

/*


*/


//
// Сообщить об ошибке, способы запуска:
//
//    err("ошибка") или err("ошбика",0)         - на экран + в лог
//    err("ошбика",1)                           - только в лог
//    err("ошибка",__FILE__,__LINE)             - на экран + в лог + координаты
//    err("ошбика",0,__FILE__,__LINE)           - на экран + в лог + координаты
//    err("ошбика",1,__FILE__,__LINE)           - только в лог + координаты
//
function err($s,$a=-987,$b=-987,$c=-987) {
   global $flog;

   $line="";
   $line2="";

   if ($a===-987) {
      $say=0;
   }
   elseif ($b===-987) {
      $say=$a;
   }
   elseif ($c===-987) {
      $say=0;
      $line="<P><hr size=1 noshade>{"._("Эта ошибка произошла в файле")." <tt><b>".basename($a)."</b></tt> "._("на строке")." <tt><b>$b</b></tt>}";
      $line2="{".basename($a).":$b} ";
   }
   else {
      $say=$a;
      $line="<P><hr size=1 noshade>{"._("Эта ошибка произошла в файле")." <tt><b>".basename($b)."</b></tt> "._("на строке")." <tt><b>$c</b></tt>}";
      $line2="{".basename($b).":$c} ";
   }

   if ($say==0)
   echo "</tD></tr></table></tD></tr></table></tD></tr></table></tD></tr></table></tD></tr></table></tD></tr></table></tD></tr></table></tD></tr></table>
<table width=100% bgcolor=#990000 border=0 cellspacing=0 cellpadding=5>
<tr><td align=center bgcolor=#FF0000><font color=white><b>"._("Детектор ошибок настройки сайта")."</td></tr>
<tr><td><font color=white>&nbsp;<br>$s$line<p></td></tr></table>
<script>alert('"._("Детектор ошибок настройки сайта.")."\\n---------------------------------------------\\n\\n".
msg2js($s)."  $line2\\n\\n---------------------------------------------".
"\\n"._("Если вы не администратор сайта, найдите его и попросите исправить")." ".
""._("ошибки. Это сообщение он может найти в лог-файле.")."');</script>";
   $log=@fopen($flog,"a+") or exit();
   fputs($log,date("d.m.Y H:i:s ").$line2.$s."\n");
   exit;
}

function putlog($msg,$fname="",$fline="") {
   $msg2="";
   if ($fname!="") $msg2=" {".basename($fname).":$fline}";
   $f=@fopen($GLOBALS['flog'],"a+") or exit();
   fputs($f,date("d.m.Y H:i:s")."| $msg$msg2\n");
   fclose($f);
}

function intest() {
   global $sess;
   $message = _("Нельзя использовать эту функцию, проходя тестирование. Сейчас вы перейдете на страницу тестирования.");
   	if (!$GLOBALS['controller']->enabled) {
      alert($message);
      echo "<script>document.location.href = 'test_vopros.php?vopros=".md5(microtime)."$sess';</script>";
   	} else {
   	    $url = 'test_vopros.php?vopros='.md5(microtime).$sess;
   	    if (isset($_SERVER['HTTP_REFERER']) && (strstr($_SERVER['HTTP_REFERER'],'course_structure_top') !== false)) {
   	        $url = $GLOBALS['sitepath'].'course_structure.php?CID='.$_SESSION['s']['cid'];
   	        if (!$_SESSION['s']['cid']) {
                $url = $GLOBALS['sitepath'].'course_structure.php?CID='.$_SESSION['s']['favorites']['cid'];
   	        }
   	        refresh($url);
   	        exit();
   	    } elseif (isset($_SERVER['HTTP_REFERER']) && (strstr($_SERVER['HTTP_REFERER'],'course_structure_toc.php') !== false) && (strstr($_SERVER['SCRIPT_NAME'], "/index.php") !== false)) {
            $url = $GLOBALS['sitepath'].'course_structure.php?CID='.$_SESSION['s']['cid'];
            refresh($url);
            exit();
   	    } elseif (isset($_SERVER['HTTP_REFERER']) && (strstr($_SERVER['HTTP_REFERER'],'course_structure_toc.php') !== false) && (strstr($_SERVER['SCRIPT_NAME'], "/courses.php4") !== false)) {
            $url = $GLOBALS['sitepath'].'course_structure.php?CID='.$_SESSION['s']['favorites']['cid'];
            refresh($url);
            exit();
   	    }

		$GLOBALS['controller']->setView('DocumentBlank');
		$GLOBALS['controller']->setMessage($message, JS_GO_URL, $url);
		$GLOBALS['controller']->terminate();
   	}
   exit;
}

function intest_noexit() {
   global $s;
   global $sess;
   if($s['perm'] == 1) {

   	$message = _("Нельзя использовать эту функцию, проходя тестирование. Сейчас вы перейдете на страницу тестирования");

   	if (!$GLOBALS['controller']->enabled) {
      alert($message);
      echo "<script>document.location.href = 'test_vopros.php?vopros=".md5(microtime)."$sess';</script>";
   	} else {
		$GLOBALS['controller']->setView('DocumentBlank');
		$GLOBALS['controller']->setMessage($message, JS_GO_URL, 'test_vopros.php?vopros='.md5(microtime).$sess);
		$GLOBALS['controller']->terminate();
   	}
   }
}

function istest() {
   global $s;
   if ($s['me'] == 1) intest();
}

function istest_noexit() {
   global $s;
   if ($s['me'] == 1) {
                   intest_noexit();
                   return true;
   } return false;
}


function sortrow($label,$text,$name,$sortname,$sortdesc,$title='сортировать по этому столбцу') {
   $url=$GLOBALS[PHP_SELF];
   if (getenv("QUERY_STRING")!="") $url.="?".getenv("QUERY_STRING");
   return "<nobr><a href=$PHP_SELF?c=sortrow&label=$label&sortname=$name&sortdesc=".
   ($sortdesc?"0":"1")."&url=".ue($url).
   " title=\"$title\">$text".
   ($sortname==$name?"<img src=images/sort_".($sortdesc?"down":"up").".gif border=0>":"").
   "</a></nobr>";
}



#
# Функции парсинга файлов или текстовых строк
#

function xparse($fn,$text="",$LOCALS=array()) {
   if (empty($text)) $text=myfile($fn);
   $text=preg_replace("!^ *(#|//).*[\r\n]*!m","",$text);
   $text=preg_replace("!^  +!m"," ",$text);
   $text=preg_replace("!<\?(.+?)\?>!e","xparse_eval('\\1',\$LOCALS)",$text);
   $text=preg_replace("!\{\\\$([a-z_][a-z0-9_]*)(\[([a-z0-9_-]+)\])?\}!ie","xparse_var('\\1','\\3',\$LOCALS,\$fn)",$text);
   return $text;
}

function xparselog($n1,$n2,$fn) {
   if ($n2) $log="\${$n1}[$n2]"; else $log="\$$n1";
   putlog("xparse error: can't found [$log] for [$fn]");
}

function xparse_var($n1,$n2,$LOCALS,$fn) {
   if ($n2=="") {
      if (isset($LOCALS[$n1])) return $LOCALS[$n1];
      if (isset($GLOBALS[$n1])) return $GLOBALS[$n1];
      xparselog($n1,$n2,$fn);
      return "{XPARSE_ERROR_ON_\${$n1}_$fn}";
   }
   if (isset($LOCALS[$n1][$n2])) return $LOCALS[$n1][$n2];
   if (isset($GLOBALS[$n1][$n2])) return $GLOBALS[$n1][$n2];
   xparselog($n1,$n2,$fn);
   return "{XPARSE_ERROR_ON_\${$n1}[$n2]_$fn}";
}

function xparse_eval($php,$LOCALS) {
   extract($LOCALS);
   if ($php[0]=="=") { $php[0]=" "; $php="return ".$php; }
   return eval($php);
}



function okbutton($title="OK", $html="", $name="ok", $onClick = '') {
    $title = @trim($title);
    if (empty($title)) {
        $title = 'OK';
    }
    return '<input class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" type="submit" name="'.htmlspecialchars($name).'" value="'.htmlspecialchars($title).'" onclick="'.htmlspecialchars($onClick).'" '.$html.'><input type="hidden" name="'.htmlspecialchars($name).'" value="'.htmlspecialchars($title).'">';
}

function button($title="OK", $html="", $name="ok", $onClick = '', $url = '') {
    $title = @trim($title);
    if (empty($title)) {
        $title = 'OK';
    }
    if (!empty($url)) {
        $onClick = ($onClick ? (trim($onClick, ';') . ';') : '') . 'document.location.href="'.$url.'"; return false;';
    }
    return '<button class="ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only" name="'.htmlspecialchars($name).'" onclick="'.htmlspecialchars($onClick).'" '.$html.'>'.htmlspecialchars($title).'</button>';
}

function backbutton() {
return " <font style='font: 8pt Verdana'>&lt;&lt; <b><a href=javascript:history.go(-1)>"._("Вернуться назад")."</a></b>
| <b><a href='/'>"._("На главную")."</a></b>
| <b><a href=javascript:window.close()>"._("Закрыть окно")."</a></b></font> ";
}

function getIcon( $type, $help="", $border=0 ){
  global $sitepath;
  $help='"'.htmlspecialchars($help).'"';
  switch( $type ){
    case "i":
    case "info":
      $c="<img title=$help src='".$sitepath."images/icons/info.gif' border=$border hspace='3'>";
      break;
    case "<":
    case "left":
      $c="<img title=$help src='".$sitepath."images/icons/left.gif' border=$border hspace='3'>";
      break;
    case ">":
    case "right":
      $c="<img title=$help src='".$sitepath."images/icons/right.gif' border=$border hspace='3'>";
      break;
    case "^":
    case "up":
      $c="<img title=$help src='".$sitepath."images/icons/up.gif'  border=$border hspace='3'>";
      break;
    case "v":
    case "down":
      $c="<img title=$help src='".$sitepath."images/icons/down.gif'  border=$border hspace='3'>";
      break;
    case "!":
    case "note":
      $c="<img title=$help src='".$sitepath."images/icons/note.gif'  border=$border hspace='3'>";
      break;
    case "edit":
      if ($help == "''") $help = "'"._("Редактировать")."'";
      $c="&#252; ";
      $c="<img title=$help alt=$help src='".$sitepath."images/icons/edit.gif' border=$border hspace='3'>";
      break;
    case "lock":
      if ($help == "''") $help = "'"._("Заблокировать")."'";
      $c="&#252; ";
      $c="<img title=$help alt=$help src='".$sitepath."images/icons/lock_.gif' border=$border hspace='3'>";
      break;
    case "unlock":
      if ($help == "''") $help = "'"._("Разблокировать")."'";
      $c="&#252; ";
      $c="<img title=$help alt=$help src='".$sitepath."images/icons/unlock_.gif' border=$border hspace='3'>";
      break;
    case "x":
    case "delete":
      if (strlen($help)<3) $help = "'"._("Удалить сообщение")."'";
      $c="&#251; ";
      $c="<img title=$help alt=$help src='".$sitepath."images/icons/delete.gif' border=$border hspace='3'>";
      break;
    case "X":
    case "delete_all":
      $c="&#251; ";
      $c="<img title=$help src='".$sitepath."images/icons/delete_all.gif' border=$border hspace='3'>";
      break;
    case "+":
      $c="<span title=$help class=webd>4</span>";
    //  $c="<img src='".$sitepath."images/icons/delete_all.gif' border=$border hspace='3'>";
      break;
    case 'change_structure_item':
      $c="<img title=$help src='".$GLOBALS['controller']->view_root->skin_url."/images/change_structure_item.gif' border=$border hspace='3'>";
      break;
    case 'add_structure_item':
      $c="<img title=$help src='".$GLOBALS['controller']->view_root->skin_url."/images/add_structure_item.gif' border=$border hspace='3'>";
      break;
    case "star":
      $c="<img title=$help src='".$sitepath."images/icons/small_star.gif'  border=$border hspace='3'>";
      break;
    case "-":
      $c="<span title=$help class=webd>6</span>";
    //  $c="<img src='".$sitepath."images/icons/delete_all.gif' border=$border hspace='3'>";
      break;
    case "import":
      $c="<img title=$help src='".$sitepath."images/icons/import.gif' border=$border hspace='3'>";
    //  $c="<img src='".$sitepath."images/icons/delete_all.gif' border=$border hspace='3'>";
      break;
    case "type1":
     $c="&#0061;"; // 1=>радио кнопки
     $c="<img title=$help src='".$sitepath."images/types/radio.gif' border=$border hspace='3'>";
      break;
    case "type2":
     $c="&#0097;"; // 2=>checkbox
     $c="<img title=$help src='".$sitepath."images/types/check.gif' border=$border hspace='3'>";
      break;
    case 'type13':
     $c="<img title=$help src='".$sitepath."images/types/class.gif' border=$border hspace='3'>";
     break;
    case 'type12':
     $c="<img title=$help src='".$sitepath."images/types/sort.gif' border=$border hspace='3'>";
     break;        
    case "type3":
     $c="&#0096;"; // 3=>соответствие
     $c="<img title=$help src='".$sitepath."images/types/core.gif' border=$border hspace='3'>";
      break;
    case "type4":
     $c="&#0039;"; // 4=>скачать
     $c="<img title=$help src='".$sitepath."images/types/attach.gif' border=$border hspace='3'>";
      break;
    case "type5":
     $c="&#0062;"; // 5=>ввести
     $c="<img title=$help src='".$sitepath."images/types/filling.gif' border=$border hspace='3'>";
      break;
    case "type6":
     $c="&#0094;"; // 6=>свободный ответ
     $c="<img title=$help src='".$sitepath."images/types/free.gif' border=$border hspace='3'>";
      break;
    case "type7":
     $c="&#0252;"; // 7=>карта ответа
     $c="<img title=$help src='".$sitepath."images/types/map.gif' border=$border hspace='3'>";
      break;
    case "type8":
     $c="&#0162;"; // 8=>радио кнопки с рисунками
     $c="<img title=$help src='".$sitepath."images/types/radiopics.gif' border=$border hspace='3'>";
      break;
    case "type9":
     $c="&#0060;"; // 9=>тестирование с помощью внешних объектов
     $c="<img title=$help src='".$sitepath."images/types/blackbox.gif' border=$border hspace='3'>";
      break;
    case "type10":
     $c="&#0061;"; //
     $c="<img title=$help src='".$sitepath."images/types/training.gif' border=$border hspace='3'>";
      break;
    case "type11":
     $c="&#0097;"; // 2=>checkbox
     $c="<img title=$help src='".$sitepath."images/types/table.gif' border=$border hspace='3'>";
      break;
    case "look":
     $c="<img title=$help src='".$sitepath."images/icons/look.gif' border=$border hspace='3'>";
      break;
    case "reviewers":
     $c="<img title=$help src='".$sitepath."images/icons/positions_type_0.gif' border=$border hspace='3'>";
      break;
    case 'ok':
       $c="<img title=$help src='".$sitepath."images/icons/ok.gif' border=$border hspace='3'>";
       break;
    case "people":
     $c="<img title=$help src='".$sitepath."images/icons/people.gif' border=$border hspace='3'>";
      break;
    case "save":
      $c="<img title=$help src='".$sitepath."images/icons/save.gif'  border=$border hspace='3'>";
      break;
    case "save_course":
      $c="<img title=$help src='".$sitepath."images/icons/save_course.gif'  border=$border hspace='3'>";
      break;
    case "import_course":
      if ($help == "''") $help = _("Импортировать курс");
      $c="<img title=$help src='".$sitepath."images/icons/import_course.gif'  border=$border hspace='3'>";
      break;
    case "register":
      $c="<img title=$help src='".$sitepath."images/icons/register.gif'  border=$border hspace='3'>";
      break;
    case "face":
     $c="<img title=$help src='".$sitepath."images/icons/face.gif' border=$border hspace='3'>";
      break;
    case "tocheck":
     $c="<img title=$help src='".$sitepath."images/icons/tocheck.gif' border=$border hspace='3'>";
      break;
    case "open":
     $c="<img title=$help src='".$sitepath."images/icons/open.gif' border=$border hspace='3'>";
      break;
    case "struct":
     $c="<img title=$help src='".$sitepath."images/icons/struct.gif' border=$border hspace='3'>";
      break;
    case "*":
     $c="<font color=red style='font-size:15px'>*</font>";
      break;
    case "send_":
     $c="
      <input type=\"image\" name=\"ok\"
      onmouseover=\"this.src='".$sitepath."images/send_.gif';\"
      onmouseout=\"this.src='".$sitepath."images/send.gif';\"
      src=\"".$sitepath."images/send.gif\" align=\"right\" alt=\"ok\" border=\"0\">";
      break;
     case "copy";
      $c = "<img src='".$sitepath."/images/icons/copy.gif' border='0'>";
     break;
     case "answer":
      if (strlen($help)<3) $help = "'"._("Ответить на сообщение")."'";
      $c="&#252; ";
      $c="<img title=$help alt=$help src='".$sitepath."images/icons/answer.gif' border=$border hspace='3'>";
     break;
     case "print";
      $c = "<img src='".$sitepath."/images/icons/print.gif' border='0'>";
     default:
//      $c=" ";
  }
  return( $c );
}

function open_button( $win, $value="to do" ){
   $extra="<input type=button name=todo value='$value'
            onclick=\"
             parent.open( $win ,
                         null,'height=400,width=450,status=no,toolbar=no,menubar=no,location=no,scrollbars=yes');\">";
   return( $extra );
}


// parse ok:
define("dima",1);

function getOption($strName)
{
        $r = sql("SELECT * FROM OPTIONS WHERE name='{$strName}'");
        if ($a = sqlget($r)) return $a['value'];
        else return false;
}

function setOption($strName, $strValue)
{
        $r = sql("SELECT * FROM OPTIONS WHERE name='{$strName}'");
        if (sqlrows($r)) {
                $r = sql("UPDATE OPTIONS SET value='{$strValue}' WHERE name='{$strName}'");
        } else {
                $r = sql("INSERT INTO OPTIONS (value, name) values ('{$strValue}', '{$strName}')");
        }
        if ($r) return true;
        else return false;
}

function checkForFrames($strict = 0) {
        global $strRedirectNoFrames;
        $perm = ($strict)?$strict:$_SESSION['s']['perm'];
        $strRedirectNoFrames = ($_SESSION['s']['mid'] && ($perm > 1)) ? "<script><!--if ((top.location.href=='http://{$_SERVER["HTTP_HOST"]}/') || ((top.location.href=='http://{$_SERVER["HTTP_HOST"]}/index.html'))) top.location.href='/index.php'--></script>" : "";
}

function detectEncoding($str)
{
    $encodings = array('UTF-8', 'Windows-1251');
    foreach($encodings as $encoding) {
        if ($str == iconv($encoding, $encoding, $str)) {
            return $encoding;
        }
    }
    return 'UTF-8';
}
?>