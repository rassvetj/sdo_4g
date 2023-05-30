<?require_once($_SERVER['DOCUMENT_ROOT']."/formula_calc.php");
require_once("schedule.lib.php");
// Сколько ведущиbrtagх нулей добавлять в маску вопроса, которая создается
// автоматически (до скольки цифр в числе доводить)
$test_list_null=5;

// Вид маски при автосоздании вопроса. Это строка подставится полсе номера
// курса и "минуса". После будет подставлен авто-номер ("1-" - номер курса
// и обязательный дефис после цифры).
$test_list_kod="v"; // пример: 1-v0052

// При редактировании вопроса сколько показывать полей, НЕ МЕНЕЕ скольки.
// Менее этого кол-ва строк для ввода новых или редактирования введенных
// не будет никогда
$test_list_qall=5;

// Сколько дополнительных пустых полей вариантов показать после вопроса.
// Когда заполенных вариантов больше, чем $test_list_qall + $test_list_qincr,
// то вниз автоматом добавляются пустые строки, чтобы их стало по крайне
// мере $test_list_qincr штук.
$test_list_qincr=$GLOBALS['test_list_qincr'] = NUMBER_ADDITIONAL_ROWS_IN_QUESTION;


global $s;
if($_REQUEST['sheid']) $sheid = $_REQUEST['sheid'];
elseif($s[sheid]) $sheid = $s[sheid];
elseif($lessonId) $sheid = $lessonId;
elseif($lesson_id) $sheid = $lesson_id;

//pr($_GET); exit;

if (!empty($sheid)){
	$poll_or_test = sql("SELECT schedule.typeID, test.test_id
									FROM schedule, test
									WHERE schedule.SHEID = ".$GLOBALS['adodb']->Quote($sheid)." AND test.lesson_id = ".$GLOBALS['adodb']->Quote($sheid));
	$poll_row = sqlget($poll_or_test);
	define(TEST_ID, $poll_row['test_id']);
	define(LESSON_TYPE_ID, $poll_row['typeID']);
}else{
	define(LESSON_TYPE_ID, 2048); // HARDCODE == HM_Event_EventModel::TYPE_TEST
	define(TEST_ID, (int)$_GET['tid']);
}

// HARDCODE ==> HM_Event_EventModel
switch (LESSON_TYPE_ID){
	case 2048: define(TEST_TYPE, _('Тест')); break;
	case 2053: define(TEST_TYPE, _('Опрос')); break;
	case 2054: define(TEST_TYPE, _('Задание')); break;
	case 2055: define(TEST_TYPE, _('Опрос')); break;
	case 2056: define(TEST_TYPE, _('Опрос')); break;
	case 2057: define(TEST_TYPE, _('Опрос')); break;
	default: define(TEST_TYPE, _('Тест')); break;
}

if (LESSON_TYPE_ID == 2056)
    define(QTYPE_PREFIX, 'leader');
else
    define(QTYPE_PREFIX, '');


// разделитель вопросов
$brtag="~\x03~";
// автоудаление символа
$brremove="\x03";

// список типов вопросов "template_test/<type>-v.php"
$GLOBALS['qtypes']=array(1=>1,2=>2,3=>3,12=>12,13=>13,/*4=>4,6=>6,*/5=>5,7=>7,8=>8/*,9=>9,10=>10,11=>11*/); // вернуть типы вопросов когда будут готовы view для всех типов

function getVisualChar( $k , $alt = ""){
	return( getIcon( "type$k", $alt));
	$visualchar=array(
	1=>"&#0061;", // радио кнопки
	2=>"&#0097;", // checkbox
	3=>"&#0096;", // соответствие
	4=>"&#0039;", // скачать
	5=>"&#0062;", // заполнение формы
	6=>"&#0094;", // свободный ответ
	7=>"&#0252;", // карта ответа
	8=>"&#0162;", // радио кнопки с рисунками
	9=>"&#0060;", // тестирование с помощью внешних объектов
	10=>"&#0061;", // запуск тренажера
	11=>"&#0097;", // табличный ввод
	12=>"&#0096;",
	);
	return( $visualchar[ $k ] );
}
// иконки для типов вопросов
/*$visualchar=array(
1=>"&#0061;", // радио кнопки
2=>"&#0097;", // checkbox
3=>"&#0096;", // соответствие
4=>"&#0205;", // скачать
5=>"&#0062;", // ввести
6=>"&#0094;", // свободный ответ
7=>"&#0252;", // карта ответа
8=>"&#0062;", // радио кнопки с рисунками
9=>"&#0063;", // тестирование с помощью внешних объектов
);*/


// типы файлов аттачей, порядок перечисления влияет на выбор типа файла
// Следите за уникальностью ключей массива! Ключи не изменять!! Меньше 0
// не делать.
$attachtype=array(
0=>array(_("только текст"),     "text/plain",                     array("txt","inf","log","c",
"cpp","pas","pl","h","php",
"phtml","php3","php4","conf",
"cnf","htaccess","sh"),          array("link"=>3)),
1=>array(_("HTML-файл"),        "text/html",                      array("html","htm","shtml",
"xml"),                          array("link"=>3)),
7=>array(_("документ Word"),    "application/msword",             array("doc","rft"),        array("link"=>3)),
9=>array(_("документ Excel"),   "application/vnd.ms-excel",       array("xls"),                    array("link"=>3)),
2=>array(_("GIF картинка"),     "image/gif",                      array("gif"),                    array("show"=>"[img=%NUMBER%]")),
3=>array(_("JPG картинка"),     "image/jpeg",                     array("jpg","jpeg"),             array("show"=>"[img=%NUMBER%]")),
4=>array(_("PNG картинка"),     "image/png",                      array("png",),                   array("show"=>"[img=%NUMBER%]")),
8=>array(_("BMP картинка"),     "image/bmp",                      array("bmp"),                    array("show"=>"[img=%NUMBER%]")),
6=>array(_("Flash-ролик"),      "application/x-shockwave-flash",  array("swf"),                    array("show"=>"[flash=%NUMBER%]")),
5=>array(_("другие данные"),    "application/octet-stream",       array(), array("link"=>2)),
);
// описание 4-го параметра, массива:
//    "show" => "HTML-код" (допустим макрос "%NUMBER%)
//       если в вопросе будет аттач, данная команда заставит присодинит
//       к концу текста вопроса "HTML-код" так, чтобы в результате в вопросе
//       появился новый код. Этот новый код автоматом вставит ссылку для
//       показа картинки или флеша (что именно будет показано зависит от
//       кода).
//    "link" => число:
//       1 - показать ссылку "посмотреть приложение к вопросу (в новом окне)"
//       2 - показать ссылку "сохранить приложение к вопросу"
//       3 - показать обе ссылки

// Типы файлов с картинками
$attach_images=array(2,3,4,8);

// какой из типов присвоить файлу, если выбрано "Автоопределение" и
// определить файл не удалось
$attach_unknown_type=5;


// время в секундах неактивности ученика для определения брошенности теста
$cron_testlost_time=2*24*60*60; // 2 дня

// раз в сколько секунд запускать проверку поика брошенных тестов
$cron_testlost_run=600; // 10 мин

// максимальный размер картинки в предпросмотре
$image_maxx=200;
$image_maxy=120;

// сообщения о статусе сеансов тестирования
// короткое сообщение, подробное, цвет
$GLOBALS['teststatus']=array(
0 => array(_("сейчас идет"),_("В данный момент тестирование еще не закончено"),"green"),
1 => array(_("закончен"),_("Тестирование успешно и полностью выполнено"),""),
2 => array(_("брошен")." (timeout)",_("Тестирование было брошено, учащийся покинул сервер и закрыл браузер"),"red"),
3 => array(_("прерван"),_("Тестирование было превано по команде пользователя"),"red"),
4 => array(_("досрочно завершен"),_("Тестирование было досрочно завершено, не на все вопросы отвечено"),"blue"),
5 => array(_("лимит времени"),_("Тестирование было прервано из-за лимита времени, учащийся не успел ответить на все вопросы"),"blue"),
);

#####################################################################################################


#
# CRON
#

// прошло ли 10 минут
if (cron_update($GLOBALS['wwf']."/temp/cron_test.time",$cron_testlost_run)==1) {

	// поиск брошенных тестов
	$res=sql("SELECT stid FROM loguser WHERE status=0 AND start < ".(time()-$cron_testlost_time),"errTI540");
	if (sqlrows($res)) {
		$li=array();
		while ($r=sqlget($res)) $li[]=$r[stid];
		//pr("UPDATE loguser SET status=2 WHERE stid IN (".implode(",",$li).")");
		$res2=sql("UPDATE loguser SET status=2 WHERE stid IN (".implode(",",$li).")","errTI545");
		sqlfree($res2);
	}
	sqlfree($res);
}


#
# Экранирование/кодирование кода вопроса (или любого текста) с помощью
# безопасных символов. Так любой код вопроса (или текст) преобразуется
# в строку из:
# 1) англ. букв (a-z и A-Z)
# 2) цифр (0-9)
# 3) подчеркивания (_)
# 4) минуса (-)
# После этого такой код можно использовать как имя переменной PHP или JS
#
function kod2base($kod) {
	$kod=base64_encode($kod);
	$kod=str_replace("=","",$kod);
	$kod=str_replace("+","-",$kod);
	$kod=str_replace("/","_",$kod);
	return $kod;
}

#
# РАСкодирование кода вопроса - из кода восстанавливается точный оригинальный
# код вопроса (или закодированный текст)
#
function base2kod($kod) {
	$kod=str_replace("-","+",$kod);
	$kod=str_replace("_","/",$kod);
	$kod=base64_decode($kod);
	return $kod;
}


#
# удаление из текста вопросов спец символа
#
function brremove($str) { return str_replace($GLOBALS[brremove],"",$str); }


#
# переход на новый вопрос: запись нового списка текущий вопросов
#
function test_switch() {

	global $s;

	// echo "<pre>";
	// print_r($s);
	// echo "</pre>";

	if ($s[me]!=1) err("test_switch() "._("неправильный вызов").", login=$s[login], tid=$s[tid]",__FILE__,__LINE__);
	if (count($s[ckod])!=0) {
		if (tdebug) pr($s);
		err("test_switch() "._("неправильный вызов, массив")." \$s[ckod] "._("не пуст"),__FILE__,__LINE__);
	}
	/* if(isset($s[aneed]))
	if (count($s[aneed])==0) {
	if (tdebug) pr($s);
	err("test_switch() неправильный вызов, массив \$s[aneed] пустой",__FILE__,__LINE__);
	}*/

	$s[ckod]=array();
	$i=0;
	foreach ($s[aneed] as $k=>$v) {
		if ($i>=$s[qty]&&$s[qty]) break;
		$s[ckod][]=$v;
		$i++;
		$s['test']['current_position']++;
	}

}


/*

#
# типы вопросов для vopros.php
#
function vopros_type_1($r,$tm) {

$otvet="";
$tr=gf("$tm-otvet.html");

preg_match("!(.*?)~~~!",$r[qdata],$vopros);
$vopros=$vopros[1];
preg_match_all("!~~~(.*?)~~(.*?)!",$r[qdata],$ok);
$num=0;
$kod=$r[kod];
foreach ($ok[1] as $k=>$v) {
$text=$v;
$otvet.=var_replace($tr);
$num++;
echo "<li>$text";
}

//$vopros=
//$html=var_replace(gf("$tm-main.html"));


}
*/


#
# удаляет из текста BB коды
#
function v_bbdelete($text) {

}

function v_bbparse_vopros($kod,&$vopros,$vopros_text,$number,$attach) {
	global $attachtype, $testdir;
	$text=v_bbparse($kod,$vopros_text);
	if (is_array($attach) && count($attach)) {
		$text.="<ul style='font-size: 0.7em;'>";
		if (substr($attach[0]['fname'],0,13) == 'comment_file_' || $vopros['qtype'] != 8) {
		    // аттач в заголовок выводится ТОЛЬКО ели он первый и помечен вот так, и тип вопроса с картинками
			foreach ($attach as $k=>$v) {
				$tmp=_("ИМЯ:")."        $v[fname]\n"._("РАЗМЕР:")."  $v[fsize] "._("байт");
				switch ($v[ftype]) { // по типу приложениЯ
					case 2: case 3: case 4: case 6: case 8: break;
					default: $text.="<li>"._("Приложение")." <a href='#' onclick='alert(\"".addjs($tmp,1)."\"); return false' title='$tmp'>$v[fnum]</a>: ";
				}
				$link=$attachtype[$v[ftype]][3]['link'];
				$show=$attachtype[$v[ftype]][3]['show'];
				if (is_int($link)) {
					//if ($link==1 || $link==3)
					//$text.=" [".v_bbparse($kod,"[linknew=$v[fnum]]"._("посмотреть")."[/linknew]")."] ";
					if ($link==1 || $link==2 || $link==3)
					$text.=" [".v_bbparse($kod,"[download=$v[fnum]]"._("открыть")."[/download]")."] ";
				}
				if (is_string($show)) {
					$tmp=str_replace("%NUMBER%",$v[fnum],$show);
					$text.="<P align=center>".v_bbparse($kod,$tmp)."</P>";
				}
				break;
			}
		}
		$text.="</ul>";
	}
	return $text;
}




#
# получить из поля таблицы list.qdata данные для поля "название вопроса" -
# берутся первые 90 байт из текста самого вопроса, исключая теги
#

function getTestId( $tools ){
	//   возвращает id теста

	$toolsparam=explode(";",$tools);

	if ( count($toolsparam) )
	foreach ($toolsparam as $v) {
		$tmp=explode("=",$v);
		$tp[$tmp[0]]=$tmp[1];
	}
	$tid=$tp['tests_testID'];

	return( $tid );
}

function qdata2text ($qdata,$len=90) {
	global $testtag1,$testtag2,$testtag3;
	/*echo "<pre>";
	print_r($testtag1);
	echo "</pre>";
	echo "<pre>";
	print_r($testtag2);
	echo "</pre>";
	echo "<pre>";
	print_r($testtag3);
	echo "</pre>";*/


	//echo "before: ".$qdata."<br>";

	//echo "GLOBALS[brtag]: ".$GLOBALS[brtag];

	//echo "<pre>before:";
	//print_r($qdata);
	//echo "</pre>";

	$brtag="~\x03~";

	if(!(defined("LOCAL_ANSWERS_LOG_FULL") && LOCAL_ANSWERS_LOG_FULL)) {
		$qdata=explode($brtag,strip_tags($qdata));
		$qdata_translation=explode($brtag,strip_tags($qdata_translation));
	}
	else {
		$qdata=explode($brtag, $qdata);
		$qdata_translation=explode($brtag, $qdata_translation);
	}

	$qdata=$qdata[0];
	$qdata_translation=$qdata_translation[0];
	$qdata = str_replace("&nbsp;", " ", $qdata);
	$qdata_translation = str_replace("&nbsp;", " ", $qdata_translation);
	if (count($testtag1))
	$qdata=preg_replace("!\[(".implode("|",$testtag1).")[0-9=]*\]!is","",$qdata);
	$qdata_translation=preg_replace("!\[(".implode("|",$testtag1).")[0-9=]*\]!is","",$qdata_translation);
	if (count($testtag2))
	$qdata=preg_replace("!\[(".implode("|",$testtag2).")[0-9=]*\].*?\[/\\1\]!is","",$qdata);
	$qdata_translation=preg_replace("!\[(".implode("|",$testtag2).")[0-9=]*\].*?\[/\\1\]!is","",$qdata_translation);
	if (count($testtag3)) {
		$qdata=preg_replace("!\[(".implode("|",$testtag3).")[0-9=]*\]!is","",$qdata);
		$qdata_translation=preg_replace("!\[(".implode("|",$testtag3).")[0-9=]*\]!is","",$qdata_translation);
		$qdata=preg_replace("!\[/(".implode("|",$testtag3).")\]!is","",$qdata);
		$qdata_translation=preg_replace("!\[/(".implode("|",$testtag3).")\]!is","",$qdata_translation);
	}
	$qdata=preg_replace("!&#[0-9]+;!"," ",$qdata);
	$qdata_translation=preg_replace("!&#[0-9]+;!"," ",$qdata_translation);
	if (!(defined("LOCAL_ANSWERS_LOG_FULL") && LOCAL_ANSWERS_LOG_FULL)) {
		$qdata = substr(($qdata),0,$len+1);
		$qdata_translation = substr(($qdata_translation),0,$len+1);
		if (strlen($qdata)>$len) $qdata.="\x85";
		if (strlen($qdata_translation)>$len) $qdata_translation.="\x85";
	}
	return $qdata;
	return $qdata_translation;
}


// описать теги без закрывающего тега:
$testtag1=array('flash','img');
// описать теги с закрывающим тегом, внутренность вырезается:
$testtag2=array('linkopen','linknew','download');
// описать теги с закрывающим тегом, но внутренность которых вырезать не нужно:
$testtag3=array('html');

function v_bbparse($kod,$text) {
	//   $text=htmlspecialchars($text);
	$text=stripslashes($text);
	$text=preg_replace("!\[html\](.*?)\[/html\]!ies","v_bbparse_html('\\1')",$text);
	$text=preg_replace("!\[linkopen=([0-9]+)\](.*?)\[/linkopen\]!ies","v_bbparse_click('$kod','linkopen','\\1','\\2')",$text);
	$text=preg_replace("!\[linknew=([0-9]+)\](.*?)\[/linknew\]!ies","v_bbparse_click('$kod','linknew','\\1','\\2')",$text);
	$text=preg_replace("!\[download=([0-9]+)\](.*?)\[/download\]!ies","v_bbparse_click('$kod','download','\\1','\\2')",$text);
	$text=preg_replace("!\[flash=([0-9]+)\]!ies","v_bbparse_flash('$kod','flash','\\1')",$text);
	$text=preg_replace("!\[img=([0-9]+)\]!ies","v_bbparse_img('$kod','img','\\1')",$text);
	return $text;
}

function v_bbparse_html($t) {
	$t=str_replace("&lt;","<",$t);
	$t=str_replace("&gt;",">",$t);
	$t=str_replace("&quot;","\"",$t);
	$t=str_replace("&amp;","&",$t);
	return $t;
}

/*
codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"
ID="Movie" WIDTH="700" HEIGHT="500" id="1" ALIGN="">
<PARAM NAME=movie VALUE="3.swf">
<PARAM NAME=quality VALUE=high>
<PARAM NAME=bgcolor VALUE=#FFFFFF>
<EMBED src="3.swf" swLiveConnect="true"
quality=high
bgcolor=#FFFFFF  WIDTH="700" HEIGHT="500" NAME="1" ALIGN=""
TYPE="application/x-shockwave-flash" PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
*/
function v_bbparse_flash($kod,$mode,$num) {
	$url=v_attach_url($kod);
	$t="<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" ".
	"codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/".
	"swflash.cab#version=5,0,0,0\" quality='high' width='100%'  WIDTH='700' HEIGHT='500' ID='Movie' swLiveConnect='true'>".
	"<param name='movie' value='test_attach.php?mode=flash&num=$num&$url$sess' /></object>";
	return $t;
}

function v_bbparse_img($kod,$mode,$num) {
	$url=v_attach_url($kod);
	$t="<img src='".$GLOBALS['sitepath']."test_attach.php?mode=img&num=$num&$url$sess' alt='"._("Картинка")." N $num'>";
	return $t;
}

function v_bbparse_click($kod,$mode,$num,$text) {
	$add="";
	if ($mode=="linknew") $add=" target=_blank ";
	$url=v_attach_url($kod);
	$title="";
	switch ($mode) {
		case "linknew": $title="title='"._("Открыть это приложение в новом окне")."'"; break;
		case "download": $title="title='"._("Сохранить это приложение на ваш компьютер")."'"; break;
	}
	return "<a$add style=\"color: #3F5C6E;\" href='".$GLOBALS['sitepath']."test_attach.php?mode=$mode&num=$num&$url$sess' $title>$text</a>";
}

function v_attach_url($kod) {
	randomize();
	$checkrnd = mt_rand(100000,999999);
	$cryptkod=urlencode(encrypt($kod,$checkrnd.$GLOBALS[cryptpass]));
	$checkkod=md5("$kod $checkrnd $GLOBALS[cryptpass]");
	return "cryptkod=$cryptkod&checkkod=$checkkod&checkrnd=$checkrnd";
}

#
# По типу test.datype выполнить test.data нужным образом и получить список.
#    $r - массив строки анализируемого теста из таблицы test
#
# Возвращает $res SQL запроса, для прямого выбора кодов
#
function test_getkod($r) {

	switch ($r[datatype]) {

		case "1":
			$a1=array();
			$a2=array();
			foreach (explode($GLOBALS[brtag],$r[data]) as $k=>$v) {
				if (strpos($v,"%")!==false || strpos($v,"_")!==false)
				$a2[]=" kod LIKE '".ad($v)."'";
				else
				$a1[]="'".ad($v)."'";
			}
			if (count($a1)) $a3[]="kod IN (".implode(',',$a1).")";
			if (count($a2)) $a3[]="".implode(" OR ",$a2);
			if (!count($a3)) exit("Err2: error in 'data' tid=$r[tid].");
			//$rq="SELECT * FROM list WHERE ".implode(" OR ",$a3);
            if ($r['random']) {
                $rq="
              SELECT
  			  `kod`,`qtype`,`qdata`,`qtema`,`qmoder`,`adata`,`balmax`,`balmin`,`url`,`last`,`timelimit`,`weight`,`is_shuffled`,`qtema_translation`, `qdata_translation`,`
			  ABS(REPLACE(`kod`, '-', '')) as num
              FROM list WHERE ".implode(" OR ",$a3)."
              ORDER BY num";
            } else {
                $rq="
              SELECT
  			  `kod`,`qtype`,`qdata`,`qtema`,`qmoder`,`adata`,`balmax`,`balmin`,`url`,`last`,`timelimit`,`weight`,`is_shuffled`,`qtema_translation`, `qdata_translation`,
			  ABS(REPLACE(`kod`, '-', '')) as num
              FROM list WHERE ".implode(" OR ",$a3)."
              ORDER BY `ordr` ASC, num";
            }
			
            // ABS(SUBSTRING(`kod`, " . (strlen((string)$r['cid'])+2) . ", LENGTH(`kod`))) as num
			$res=sql($rq,"err4");
			break;

	}

	return $res;

}


#
# Аналогично предыдущей test_getkod, только выдает $res для обязательных вопросов.
#
function test_getneedkod($r) {
    $kods = array('0');
    if (strlen($r['data'])) {
        $kods = explode($GLOBALS['brtag'], $r['data']);
    }
	$res=sql(
        "SELECT * FROM testneed
        INNER JOIN list ON (list.kod = testneed.kod)
        WHERE tid=$r[tid] AND testneed.kod IN ('".join("','", $kods)."')"
    );
	return $res;
}



#
# Взять от sql 2 SQL запроса со всеми кодами и обязательными, объединить, перемешать и т.д.
# Вернуть готовый массив кодов для тестирования.
#
function test_evalkod($rtest, $res, $resneed) {

	$kods=array(); // снача только обяз. коды, потом весь результат
	$kods_ordered=array(); // снача только обяз. коды, потом весь результат
	$kodsall=array(); // все коды, кроме обязат.
	$kodsall_ordered=array(); // все коды, кроме обязат.
	$vkods=array(); // временный массив
	while ($r=sqlget($resneed)) {
		$kods[mt_rand(0,1000000).$r[kod]]=$r[kod];
		$kods_ordered[]=$r[kod];
		$vkods[$r[kod]]=1;
	}
	sqlfree($resneed);

	$usedQuestionsByThemes = array();

	while ($r=sqlget($res)) {
		$r['theme'] = (strlen($r['qtema'])) ? $r['qtema'] : "void";
		if (!isset($vkods[$r[kod]])) {
			$rand = mt_rand(0,1000000);
			$kodsall[$r['theme']][$rand.$r[kod]]=$r[kod];
			$kodsall_ordered[]=$r[kod];
		} else {
		    $usedQuestionsByThemes[$r['theme']][$r['kod']] = $r['kod'];
		}
	}

	sqlfree($res);
	$intCodesCount = count_recursive($kodsall) + count($kods);
	$rtest[lim] = min($intCodesCount, $rtest[lim]);
	// random
	if ($rtest[random]) {
		ksort($kods);
		foreach ($kodsall as $key => $arrTmp) {
			$arrThemeCodes[$key] = $arrTmp;
			ksort($arrThemeCodes[$key]);
		}
	}
	else {
		$arrThemeCodes = $kodsall;
	}

	/**
   * Вопросы берутся по распределению оп темам или одинаковое кол-во из кажлой темы
   */
	if ($questionsByThemes = test_getQuestionsByThemes($rtest)) {

		if ($rtest[random]) uasort($questionsByThemes, random_function);
		while(list($theme,$count) = each($questionsByThemes)) {

			if ($theme == _("Без названия")) $theme = 'void';
			if (isset($usedQuestionsByThemes[$theme])) {
			    $count = $count - count($usedQuestionsByThemes[$theme]);
			    if ($count <= 0) continue;
			}
			for($i=0; $i<$count; $i++) {
				if (count($arrThemeCodes[$theme])<=0) break;
				array_kshift($kods, $arrThemeCodes[$theme]);
			}
		}

	} else {


		if ((integer)$rtest['lim']) {
			$kods=array_slice($kods,0,$rtest[lim]);
		}

		if ((integer)$rtest['lim']) {
			while (count($kods) < $rtest[lim]) {
				$arrThemes = array_keys($arrThemeCodes);
				if($rtest[random]) uasort($arrThemes, random_function);
				foreach ($arrThemes as $theme) {
					array_kshift($kods, $arrThemeCodes[$theme]);
					//if(count($kods) >= $rtest['lim']) break;
				}
			}

		}
		else {

			$kods = ($rtest['random']) ? array_merge_custom($kods, $arrThemeCodes) : array_merge($kods_ordered, $kodsall_ordered);
		}

		if ((integer)$rtest['lim']) {
			$kods=array_slice($kods,0,$rtest[lim]);
		}

	} // questionsByThemes

	if($rtest[random]) uasort($kods, random_function);

	// окончательный random
	return $kods;

}

function random_function() {
	return rand(-1,1);

}

function array_merge_custom($arrDest, $arrSource)
{       //$arrDest = array();
	if (is_array($arrSource) && count($arrSource)) {
		foreach ($arrSource as $arrTmp) {
			$arrDest = array_merge($arrDest, $arrTmp);
		}
	}
	return $arrDest;
}

function array_kshift(&$arrDest, &$arrSource)
{
	if (is_array($arrSource) && count($arrSource)) {
		$arrKeys = array_keys($arrSource);
		$arrDest[$arrKeys[0]] = array_shift($arrSource);
	}
}

function count_recursive($arr)
{
	$i = 0;
	foreach ($arr as $item) {
		$i += count($item);
	}
	return $i;
}



#
# Обновить вопрос, параметр - готовый SQL массив, пример:
# Array
# (
#    [kod] => 1-b-4
#    [qtype] => 6
#    [qmoder] => 1
#    [balmax] => 1
#    [balmin] => 0
#    [qdata] => свободный вопрос2
#    [adata] =>
# )
#
function update_list($sqlarr) {
	global $adodb;
	$rq="UPDATE `list` SET \n";
	foreach ($sqlarr as $k=>$v) {
		if ($k=="kod") continue;
		//if ($k=="url") $v = str_replace("\\","/",$v);
		//if ($k=="qdata") $v = str_replace("\\","/",$v);
		if (dbdriver == 'oci8') {
		    switch($k) {
		        case 'balmax':
		        case 'balmin':
		            $rq.="`$k`= ".$v.",\n";
		            break;
		        default:
		          $rq.="`$k`= ".$adodb->Quote($v).",\n";
		    }
		} else {
		    $rq.="`$k`= ".$adodb->Quote($v).",\n";
		}
	}
	$rq.="
      `last`=".time()."
      WHERE `kod`= ".$adodb->Quote($sqlarr[kod]);
	$res=sql($rq,"errUPLIST1");   // echo '<pre>'; exit(var_dump( $res ));
	sqlfree($res);
	//   pr($rq);
}

#
# Наступил ли конец тестирования
#
function is_test_end() {
	global $s;
	if (count($s[aneed])!=0) return false;
	return true;
}
#
# Функция обновляет оценку по тесту, если не задана формула
#
//function update_mark()
//{
//	global $s;
//    if (( floatval($s[balmax2_true]) - floatval($s[balmin2_true]) ) == 0) {
//        $updateStatus = 0;
//    } else {
//	$updateStatus = round(100 * (floatval($s[bal]) - floatval($s[balmin2_true]))/( floatval($s[balmax2_true]) - floatval($s[balmin2_true]) ),0);
//    }
//	$rq="UPDATE scheduleID SET V_STATUS='$updateStatus' WHERE SHEID='$s[sheid]' AND MID = '$s[mid]'";
//	$res=sql($rq,"err213");
//	sqlfree($res);
//}
#
# Завершить тест, описание $status в файле dev.elearn.ru-SQL.txt
#
function result_test($status=1) {
	global $s, $adodb;

	$s[me]=2;
	$s[stop]=time();

	$log=array(
	"akod"=>$s[akod],
	"adone"=>$s[adone],
	"agood"=>$s[agood],
	"abal"=>$s[abal],
	"aotv"=>$s[aotv],
	"abalmax"=>$s[abalmax],
	"abalmin"=>$s[abalmin],
	"abalmax2"=>$s[abalmax2],
	"abalmin2"=>$s[abalmin2],
	"ainfo"=>$s[ainfo],
	);

    $mark =  toLogScore();
    if ($mark) $markUpdateStr = "mark=". $mark .",";
    else  $markUpdateStr = "";

	if ($s[moder]) $needmoder=1; else $needmoder=0;
    if (in_array(dbdriver,array("mssql","oci8")))
    $rq="UPDATE loguser SET
   mid='$s[mid]',
   cid='$s[cid]',
   tid='$s[tid]',
   balmax='" . floatval($s[balmax_true]) . "',
   balmin='" . floatval($s[balmin_true]) . "',
   balmax2='" . floatval($s[balmax2_true]) . "',
   balmin2='" . floatval($s[balmin2_true]) . "',
   bal=" . floatval($s[bal]) . ","
   . $markUpdateStr .
   "questdone=".count($s[adone]).",
   questall=".count($s[akod]).",
   qty='$s[qty]',
   free='$s[free]',
   skip='$s[skip]',
   start='$s[start]',
   stop='$s[stop]',
   fulltime='".($s[stop]-$s[start])."',
   moder='$s[moder]',
   needmoder='$needmoder',
   status='$status',
   log='0'
   WHERE stid='$s[stid]'
   ";
    else
	$rq="UPDATE loguser SET
   mid='$s[mid]',
   cid='$s[cid]',
   tid='$s[tid]',
   balmax='" . floatval($s[balmax_true]) . "',
   balmin='" . floatval($s[balmin_true]) . "',
   balmax2='" . floatval($s[balmax2_true]) . "',
   balmin2='" . floatval($s[balmin2_true]) . "',
   bal=" . floatval($s[bal]) . ","
   . $markUpdateStr .
   "questdone=".count($s[adone]).",
   questall=".count($s[akod]).",
   qty='$s[qty]',
   free='$s[free]',
   skip='$s[skip]',
   start='$s[start]',
   stop='$s[stop]',
   fulltime='".($s[stop]-$s[start])."',
   moder='$s[moder]',
   needmoder='$needmoder',
   status='$status',
   log='0'
   WHERE stid='$s[stid]'
   ";

	$res=sql($rq,"err213");
	sqlfree($res);

	if($r = serialize($log)) {
		$adodb->UpdateClob('loguser','log',$r,"stid=".$GLOBALS['adodb']->Quote($s['stid'])."");
	}

	// нужно взять формулу
	//saveAutoMark( $s );
	//pr($sq);
	//pr($log);

	return true;
}

function result_test_intermediate() {
	global $s, $adodb;

	$log=array(
	"akod"=>$s[akod],
	"adone"=>$s[adone],
	"agood"=>$s[agood],
	"abal"=>$s[abal],
	"aotv"=>$s[aotv],
	"abalmax"=>$s[abalmax],
	"abalmin"=>$s[abalmin],
	"abalmax2"=>$s[abalmax2],
	"abalmin2"=>$s[abalmin2],
	"ainfo"=>$s[ainfo],
	);

	if ($s[moder]) $needmoder=1; else $needmoder=0;

    $mark =  toLogScore();
    if ($mark) $markUpdateStr = "mark=". $mark .",";
    else  $markUpdateStr = "";

    if (in_array(dbdriver,array("mssql","oci8")))
    $rq="UPDATE loguser
        SET
        mid='$s[mid]',
        cid='$s[cid]',
        tid='$s[tid]',
        balmax='" . floatval($s[balmax_true]) . "',
        balmin='" . floatval($s[balmin_true]) . "',
        balmax2='" . floatval($s[balmax2_true]) . "',
        balmin2='" . floatval($s[balmin2_true]) . "',
        bal=$s[bal]" . "," .
        $markUpdateStr .
        "questdone='".count($s[adone])."',
        questall='".count($s[akod])."',
        qty='$s[qty]',
        free='$s[free]',
        skip='$s[skip]',
        start='$s[start]',
        stop='".$s['start']."',
        fulltime='0',
        moder='$s[moder]',
        needmoder='$needmoder',
        log='0'
        WHERE stid='$s[stid]'
    ";
    else
	$rq="UPDATE loguser
	    SET
        mid='$s[mid]',
        cid='$s[cid]',
        tid='$s[tid]',
        balmax='" . floatval($s[balmax_true]) . "',
        balmin='" . floatval($s[balmin_true]) . "',
        balmax2='" . floatval($s[balmax2_true]) . "',
        balmin2='" . floatval($s[balmin2_true]) . "',
        bal='" . floatval($s[bal]) . "'," .
        $markUpdateStr .
        "questdone='".count($s[adone])."',
        questall='".count($s[akod])."',
        qty='$s[qty]',
        free='$s[free]',
        skip='$s[skip]',
        start='$s[start]',
        stop='".$s['start']."',
        fulltime='0',
        moder='$s[moder]',
        needmoder='$needmoder',
        log='0'
        WHERE stid='$s[stid]'
    ";

	$res=sql($rq,"err213");
	sqlfree($res);

	if($r = serialize($log)) {
		$adodb->UpdateClob('loguser','log',$r,"stid='".addslashes($s['stid'])."'");
	}

	return true;
}

function showQuestionStatistic( $tid, $cid, $kl, $is_quiz = false ){

	// собирает статистику для вопроса kod
	// показывает как отвечают на вопрос с кодом kod
	// формирует масив s, содержащий данные ответы и кол-во наддых ответов по этому вопросу
	// типа q :
	//  ответ  : скоко раз был дан
	//       A : 5
	//       B : 1
	//       С : 3
	// надо выбрать все ответы по данному вопросу
	//  и посчитать коли-во каждого
	// в таблице TESTCONTENT находятся вопросы
	// QID - код вопроса
	// в таблице  ??? данные ответы
	// НАДО ВЫБРАТЬ ВОПРОСЫ ТОЛЬКО ДЛ ДАННОГО ЗАДАНИЯ

	// возьмем все вопросы задания
	//

	$brtag="~\x03~";
	if($kl==""){
		// если для всех вопросов задания
		$q = "SELECT * FROM test WHERE tid='".(int) $tid."' AND cid='".(int)$cid."'";
		$res=sql($q,"errTT71");
		if (sqlrows($res)==0)
		exitmsg(_("Такого теста нет"),"$PHP_SELF?$sess");
		$r=sqlget($res);
		sqlfree($res);
		if(strpos($r[data],"%") === false){
			$kodlist=explode($brtag,$r[data]);
		}
		else{
			$qeu_t = "SELECT kod FROM  list WHERE kod LIKE '{$cid}-%'";
			$res_t = sql($qeu_t,"errTK023");
			while($t_get = sqlget($res_t)){
				$kodlist[] = $t_get[kod];
			}
		}
		$query = "SElECT Title FROM Courses WHERE CID=$cid";
		$result = sql($query);
		$row = sqlget($result);
		$course_title = $row['Title'];

		$query = "SELECT title FROM test WHERE tid = $tid";
		$result = sql($query);
		$row = sqlget($result);
		echo "<br />";
		echo _("Курс:")." $course_title<br />";
		echo _("Занятие:")." ".getField("schedule","Title","SHEID",$GLOBALS['SHEID'])."<br />";
		echo _("Задание:")." ".$r[title]."<br />";
		//echo "<H1>$r[title]</H1>";
	}
	else {
		// если только для одного вопроса
		$kodlist[1]=$kl;
	}
	$i = 0;
	$qmoderCount = 0;
	foreach ( $kodlist as $kk=>$vv ) {
		// ДЛЯ ВОПРОСА ТЕКУЩЕГО
		$q = "SELECT logseance.stid as stid, logseance.kod, number, bal, good, vopros, otvet, text, list.qdata as qdata
                             FROM logseance, list
                             WHERE logseance.kod ='".$kodlist[$kk]."'
                             AND list.kod ='".$kodlist[$kk]."'
                             ORDER BY list.kod";
		$res=sql($q,"errTL337");



		while( $r = sqlget($res) ) {
			$ok=unserialize($r[otvet]);
			if ( count( $ok ) && is_array( $ok ) ) {
				if ($ok[moder])
				$qmoderCount++;
				//echo "ВОПРОС С ПРОВЕРКОЙ ПРЕПОДАВАТЕЛЕМ<br>";
				if (count($ok[error]))
				foreach ($ok[error] as $v) {
				    //echo _("Ошибка в ответе").": ".$v."<BR>";
				}
				if (count($ok[main]) && is_array($ok[main])) {
					foreach ($ok[main] as $k=>$v) {
						$strKey = $v.$r[kod];
						if( isset($st[$strKey][count]) ) {
							$st[$strKey][count]++;
						}
						else {
							$st[$strKey][count]=1;
							$st[$strKey][text]=str_replace(_("Выбран вариант")." N", "",$v); //echo $v;
							//$st[$strKey][text] = $v;
							$st[$strKey][kod]=$r[kod];
							if( isset($r[qdata])) {
								$qlist=explode( $brtag, $r[ qdata ]);
								$st[$strKey][title]=$qlist[0];
							}
							/* if( isset($r[qtema])) {
								exit($r[qtema]);
								
							}  */
							
							$st[$strKey][rez_false]=0;
							$st[$strKey][rez_true]=0;
						}
						if ($ok[good][$k])
						$st[$strKey][rez_true]++; // верный - нверный ответ
						else
						$st[$strKey][rez_false]++; // неверный - нверный ответ

					}
				}
			}
		}
		sqlfree($res);
	} // foreach по коду
	if ($qmoderCount>0) echo "<br>"._("ВОПРОСОВ С ПРОВЕРКОЙ ПРЕПОДАВАТЕЛЕМ:")." $qmoderCount<br>";
	// добавить поле СЛОЖНОСТЬ - и расчитать его как соотношение кол-ва прав и неправл ответов на вопрос
	$kod="";
	$strPrevCode = "";
	echo "<h2>" . _('Статистика ответов на вопрос') . "</h2><br><table width=100% class=main cellspacing=0 style='color:rgb(51, 51, 51);font-size:11px'>
         <TR>
         <TH>"._("вопрос")."</TH><TH>"._("всего ответов")."</TH>";
	if(!$is_quiz) {
		echo "<TH>"._("верных")."</TH><TH>"._("неверных")."</TH>";
	}
	echo "<TH>"._("ответы")."</TH></TR>\n";
	if (is_array($st)) {
		foreach( $st as $k => $v) {
			echo "<tr>\n";
			$intRowspan = getRowspan($st, $v['kod']);
			if ($v['kod'] !== $strPrevCode) {
				$strSyle = ($cnt++%2) ? "style='background-color:#EFEFEF;'" : "";
				echo "<TD {$strSyle} rowspan='{$intRowspan}'><B>".stripslashes(prepareGeshi($v[title]))."</B></TD>\n";
				$strPrevCode = $v['kod'];
			}
			echo "<td {$strSyle}><B>".$st[$k][count]."</B></td>\n";
			if(!$is_quiz) {

				echo "<TD {$strSyle}>".$st[$k][rez_true]."</TD>\n<TD {$strSyle}>".$st[$k][rez_false]."</td>";
			}

			echo "\n<TD {$strSyle}><FONT SIZE=-1>".$st[$k][text]."</FONT></td>\n</tr>\n";
		}
	} else {
		echo "<tr><td colspan='5' align='center'>"._("по данному вопросу нет статистики (еще никто не отвечал)")."</td></tr>\n";
	}
	echo "</table>\n";
}

function showTaskStatistic( $tid, $cid ){
	//  для заданиф выводит статистику всех вопросов
	echo "TID=$tid CID=$cid<br>";
	$s="STATISTIC<BR>";
	intvals("tid cid");
	//  if (!isset($s[tkurs][$cid])) exit("HackDetect: нет прав редактировать данное задание");
	$s[$ss][cid]=$cid;
	$s[$ss][tid]=$tid;

	$obz=array();
	$res=sql("SELECT * FROM testneed WHERE tid=$tid","errTT919");
	while ($r=sqlget($res)) {
		$obz[$r[kod]]=1;
	}
	sqlfree($res);

	$res=sql("SELECT * FROM test WHERE tid=$tid AND cid=$cid","errTT71");
	if (sqlrows($res)==0) exitmsg(_("Такого теста нет"),"$PHP_SELF?$sess");
	$r=sqlget($res);
	sqlfree($res);
	$isedit=0;
	if ($r[cid]==$r[cidowner]) $isedit=1;

	$s[$ss][tid_title]=$r[title];
	echo "R=".$r[data]."<BR>" ; // в поле дата находятся маски вопросов из этого задания

	//exit;
	$brtag="~\x03~";
	$kodlist=explode($brtag,$r[data]); // разбивает на вопросы
	$kodlist=implode("\r\n",$kodlist); // формирует список кодов вопросов с переводом сроки
	$kodlist=str_replace("%","*",$kodlist);
	$kodlist=str_replace("_","?",$kodlist);

	//echo show_tb();

	echo  $kodlist;
	//
	// Основная таблица
	//
	echo "
   <form action=$PHP_SELF method=post>$sessf
   <input type=hidden name=c value='edit_post'>
   <input type=hidden name=tid value=\"$tid\">
   <input type=hidden name=cid value=\"$cid\">";
	echo "<table width=100% class=main cellspacing=0>
   <tr>
   <td colspan=10 class='th3' align='right'>"._("список вопросов")." &nbsp;</td>
   </tr>
   <tr>
   ".($s[usermode]?"<th width=20%>".
	sortrow(2,_("код"),"kod",$s[$ss][ttorder],$s[$ss][ttdesc])."</th>":"")."
   <th width=4%>обязат.</th>
   <th width=40%>".sortrow(2,_("вопрос"),"vopros",$s[$ss][ttorder],$s[$ss][ttdesc])."</th>
   <th width=5%>".sortrow(2,_("тип"),"type",$s[$ss][ttorder],$s[$ss][ttdesc])."</th>
   <th width=8%>".sortrow(2,_("изменен"),"last",$s[$ss][ttorder],$s[$ss][ttdesc])."</th>
   <th width=8%>"._("Команды")."</th>
   </tr>";


	if (trim($r[data])=="") $mylist=array();
	else {
		$mylist=str_replace("%","*",$r[data]);
		$mylist=str_replace("_","?",$mylist);
		$mylist=explode($brtag,$mylist);
	}

	if (!count($mylist)) {
		echo "<tr><td colspan=100 align=center>"._("в это задание еще не включено ни одного вопроса!")."</td></tr>";
	}
	else {

		//
		// Цикл основной таблицы - подготовка массива
		//
		$show=array();

		foreach ($mylist as $v) {
			$good=0;
			$kod="";
			if (strpos($v,"?")===false && strpos($v,"*")===false) {
				// возьмем все вопросы с кодом для этого теста
				$sqlarr=sqlval("SELECT * FROM list WHERE kod=".$GLOBALS['adodb']->Quote($v)."","errTT490");
				$kod=$sqlarr[kod];
				if (!is_array($sqlarr)) {
					$showvopros=_("Вопрос не найден в базе данных");
					$showlast=0;
					$showtype=-2;
					$kod=$v;
					//$showtype="&nbsp;";
				}
				else {
					include_once("template_test/$sqlarr[qtype]-v.php");
					$func='v_sql2php_'.$sqlarr[qtype];
					$vopros=$func($sqlarr);
					$showvopros=wordwrap(qdata2text($sqlarr[qdata]),20," ",1);
					$showlast=$sqlarr[last];
					//$showlast=date("d-m-Y",$sqlarr[last]);
					$showtype=$sqlarr[qtype];
					//$showtype=$visualchar[$sqlarr[qtype]];
					$showtypehelp=$GLOBALS['v_edit_'.$sqlarr[qtype]]['title'];
					$good=1;
				}
			}
			else {
				$showvopros="- "._("выборка вопросов по коду вопроса")." -";
				$showlast=0;
				$showtype=-1;
				$kod=$v;
				//$showtype="<font face=system>n/a</font>";
			}
			$show[]=array(
			"kod"=>$kod,
			"vopros"=>$showvopros,
			"last"=>$showlast,
			"type"=>$showtype,
			"typehelp"=>$showtypehelp,
			"good"=>$good,
			"qtema"=>$sqlarr[qtema],
			);
		}

		sortarray($show,$s[$ss][ttorder],$s[$ss][ttdesc]);

		//
		// Цикл основной таблицы - показ на экране
		//
		foreach ($show as $k=>$v) {
			$k=$v[kod];

			echo "<tr>";
			if ($s[usermode]) echo "<td align=left width=20%>".htmlwordwrap($k,7,"\n")."</td>";

			// обязательнный ли вопрос
			echo "<td width=7%><input style='width:100%' type=checkbox name='obz[]' value=\"".html($k)."\" ".(isset($obz[$k])?"checked":"")."></td>";

			// текст вопроса
			echo "<td width=38% align=left><a href=\"#\" target='preview' ".
			"onclick=\"wopen('test_vopros.php?kod=".ue($k)."&cid=$cid&mode=2$asess','preview'); return false;\">".
			html($v[vopros]);
			if ($v[qtema]!="") echo "<br><b>&lt;</b>"._("Тема")."<b>&gt;</b>: $v[qtema]";
			echo "</a>";
			echo "</td>";

			// тип вопроса:
			echo "<td width=5% align=center style='cursor:hand' title='".$v[type]."************".$v[typehelp]."'>
              +++<span class=sym>!!!".getVisualChar( intval($v[type]) )."</span>---</td>";
			//            (!empty( $v[type]) ? getVisualChar( $v[type] ):"")

			// изменен:
			echo "<td width=8% nowrap style='font: 8pt Tahoma'>".
			($v[last]?date("d-m-Y",$v[last]):"")."</td>";

			// команды
			echo "<td width=8% align=center>";
			//   ссылка: править
			echo "</td>";

			echo "</tr>";
		}

	}
	echo "<tr>
   <td nowrap colspan='50' class=shedadd1>
   <table width=100% border=0 cellspacing=0 cellpadding=0><tr>
   <td nowrap><a href=\"javascript:wopen('test_test.php?c=start&tid=$r[tid]&teachertest=1$asess','schedul')\">"._("Выполнить задание")."</a></td>
   </tr></table>
   </td></tr></table><P>";



	echo "
   <table cellspacing=\"0\"  cellpadding=\"0\" border=0 width=\"100%\">
   <tr><td align=\"right\" valign=\"top\"><input type=\"image\" name=\"ok\"
   onmouseover=\"this.src='".$sitepath."images/send_.gif';\"
   onmouseout=\"this.src='".$sitepath."images/send.gif';\"
   src=\"".$sitepath."images/send.gif\" align=\"right\" border=\"0\"></td>
   </tr></table>
   </form>
   ";



	echo show_tb();
	exit;


	return($s);
}

/* Возвращаем в лог полученную оценку по данной поппытке прохождения теста */
function toLogScore() {
    // нехорошая фукнция работает с глобальной переменной $s
    // на основе данных из GLOBAL s вычисляет оценку и сохраняет ее в БД для занятия sheid
    global $s;
    $tid=$s[tid];
    $sheid = $s[sheid];
    if( $sheid <= 0 ) return false;
    $q = "
        SELECT schedule.end as end, scheduleID.toolParams,vedomost, schedule.params, subjects.access_mode
        FROM schedule
        INNER JOIN scheduleID ON (schedule.SHEID = scheduleID.SHEID)
        INNER JOIN subjects ON (schedule.CID = subjects.subid)
        WHERE scheduleID.SHEID=".$sheid;
    $rrr1=sql($q,"errFM50"); // фОРМИРУЕМ ВСЕ ФОРМУЛЫ ДЛЯ ЭТОГО КУРСА
    while ($r1 = sqlget($rrr1)) {
        $par=$r1['params'];
        $end = $r1['end'];
    }
    sqlfree($rrr1);
    $formula_id=getIntVal($par,"formula_id=");
    $penaltyFormula_id = getIntVal($par,'formula_penalty_id=');

    // если не используется формула, в onFinish надо передать процент
    $balmax_by_stid = get_maxbal_by_stid($s['stid']);
    $score = 0;
    if ($balmax_by_stid - $s[balmin]) {
        $score = intval(($s[bal] - $s[balmin]) * 100 / ($balmax_by_stid - $s[balmin]));
    }

    if ($formula_id > 0 && $s[cid] > 0) {
        $res=sql("SELECT * FROM formula WHERE (CID='{$s[cid]}' OR CID='0') AND type=1 AND id=$formula_id","errFM50"); // фОРМИРУЕМ ВСЕ ФОРМУЛЫ ДЛЯ ЭТОГО КУРСА
        while ($r = sqlget($res)){
            $formula=$r[formula];
        }
        sqlfree($res);

        $mark=viewFormula( $formula,$text,$s[balmin],$balmax_by_stid,$s[bal] );
        if ($penaltyFormula_id) {
            $days = (int) ((strtotime($end)-time())/60/60/24);
            $penaltyFormula = getPenaltyFormula($penaltyFormula_id);
            $penalty = viewPenaltyFormula($penaltyFormula,$days);
            if ($penalty) $mark = round($mark*$penalty,2);
        }
        $score = $mark;
    }


    return round($score,2);
}


function saveAutoMark($void = "") {
   // нехорошая фукнция работает с глобальной переменной $s
   // на основе данных из GLOBAL s вычисляет оценку и сохраняет ее в БД для занятия sheid
   global $s;
   $tid=$s[tid];
   $sheid = $s[sheid];
    if( $sheid <= 0 ) return("-");
    $q = "
        SELECT schedule.end as end, scheduleID.toolParams,vedomost, schedule.params, subjects.access_mode,
            schedule.timetype as timetype, scheduleID.endRelative as endRelative, subjects.mark_type as mark_type
        FROM schedule
        INNER JOIN scheduleID ON (schedule.SHEID = scheduleID.SHEID)
        INNER JOIN subjects ON (schedule.CID = subjects.subid)
        WHERE scheduleID.SHEID=".$sheid."
        AND scheduleID.MID=".$s[mid];
    $rrr1=sql($q,"errFM50"); // фОРМИРУЕМ ВСЕ ФОРМУЛЫ ДЛЯ ЭТОГО КУРСА
    while ($r1 = sqlget($rrr1)) {
        $par=$r1['params'];
        $end = $r1['end'];
        $timetype = $r1['timetype'];
        $endRelative = $r1['endRelative'];
        $mark_type = $r1['mark_type'];
    }
    sqlfree($rrr1);
    $formula_id=getIntVal($par,"formula_id=");
    $penaltyFormula_id = getIntVal($par,'formula_penalty_id=');
    $mark="-";

    // если не используется формула, в onFinish надо передать процент
    $balmax_by_stid = get_maxbal_by_stid($s['stid']);
    $score = 0;
    if ($balmax_by_stid - $s[balmin]) {
        $score = round(($s[bal] - $s[balmin]) * 100 / ($balmax_by_stid - $s[balmin]), 2);
    }

    if ($formula_id > 0 || $mark_type == 1/*HM_Mark_StrategyFactory::MARK_BRS*/) {

        if($mark_type == 1/*HM_Mark_StrategyFactory::MARK_BRS*/){
            $mark = round(100 * (floatval($s[bal]) - floatval($s[balmin2_true]))/( floatval($s[balmax2_true]) - floatval($s[balmin2_true]) ),2);
        } else {
            $res=sql("SELECT * FROM formula WHERE (CID='{$s[cid]}' OR CID='0') AND type=1 AND id=$formula_id","errFM50"); // фОРМИРУЕМ ВСЕ ФОРМУЛЫ ДЛЯ ЭТОГО КУРСА
            while ($r = sqlget($res)){
                $formula=$r[formula];
            }
            sqlfree($res);
            $mark=viewFormula( $formula,$text,$s['balmin'],$balmax_by_stid,$s['bal'] );
        }
        if ($penaltyFormula_id) {
            if ($timetype == 1){
                $end = $endRelative;
            }
            if(strtotime($end) < time()){
                $days = (int) ceil(((time()-strtotime($end))/60/60/24));
                if($days > 0){
                    $penaltyFormula = getPenaltyFormula($penaltyFormula_id);
                    $penalty = viewPenaltyFormula($penaltyFormula,$days);
                    if ($penalty) $mark = round($mark*$penalty,2);
                }
            }
        }

    //     echo "<script>alert('".$balmax_by_stid."');</script>";
    //     $mark=viewFormula( $formula,$text,$s[balmin],$s[balmax],$s[bal] );

         // получаем предыдущее значение V_STATUS если такое есть
         $existResultSql  = "SELECT * FROM `scheduleID`  WHERE MID='".$s[mid]."' AND SHEID='".$sheid."'";
         $existResult     = sql($existResultSql);
         $existResultData = sqlget($existResult);

         // получаем порог прохождения теста
         $testSql         = "SELECT * FROM test WHERE lesson_id=" . $sheid;
         $testResult      = sql($testSql);
         $testData        = sqlget($testResult);
         $testThreshold   = (isset($testData['threshold'])) ? (int) $testData['threshold'] : 100;

         // если текущий вариант успешнее предыдущего
         if ($existResultData && ($mark >= $existResultData['V_STATUS'])) {
            // убрали тесты из своб.доступа, V_DONE более не имеет смысла
            $doneStatus = 2; //($mark >= $testThreshold)? 2 : 1;
            $score = $actualMark = ($mark >= $existResultData['V_STATUS']) ? $mark : $existResultData['V_STATUS'];
    // этот код в onFinish
    //         $sql        = "UPDATE `scheduleID` SET `V_STATUS`='".$actualMark."', `V_DONE`='" . $doneStatus . "' WHERE MID='".$s[mid]."' AND SHEID='".$sheid."'";
    //         $res        = sql($sql,"errSAVE_MARK");
         }
   }
//    else
//		update_mark();
   
    define('HARDCODE_WITHOUT_SESSION', true);
    $locale = $GLOBALS['locale']; // теряется значение переменной locale, а оно нужно в cmdBootstraping
    require_once "../../application/cmd/cmdBootstraping.php";
    $services = Zend_Registry::get('serviceContainer');
    if (count($collection = $services->getService('Lesson')->find($sheid))) {
        $services->getService('LessonAssign')->onLessonFinish($collection->current(), array(
            'score' => $score,
        ), true);
    }

   return( $mark );
}

function getIntVal( $s, $val ){
	ereg("[[:print:]]*$val([0-9]+)[[:print:]]*",$s, $r );
	return ( intval($r[1]) );
}


function newQuestion( $cid ){
	try {

	$id_base=sqlvalue("SELECT autoindex FROM conf_cid WHERE cid='".(int) $cid."'","errTL883");

	// нумерацию начинаем с 1 !!!
	if (!(integer)$id_base) {

		$id_base=1;
		$res=sql("INSERT INTO conf_cid (cid, autoindex) values ('{$cid}', 1)");

		sqlfree($res);

	}

	$ok=0;
	$incr=1;
	$id=$id_base;
	for ($i=0; $i<100; $i++) {
		$testkod="$cid-".$test_list_kod.sprintf("%0{$test_list_null}d",$id);
		$cnt=sqlvalue("SELECT COUNT(*) FROM list WHERE kod=".$GLOBALS['adodb']->Quote($testkod)."","errTL837");
		if ($cnt) $id=$id_base+$i*ceil(doubleval($i/5))+mt_rand(0,1+ceil(doubleval($i/5)));
		else {
			$ok=1;
			break;
		}
	}
	
	$res=sql("UPDATE conf_cid SET autoindex=".($id+1)." WHERE cid = $cid","errTL849");
	if ($ok==0) {
		exitmsg(_("Извините, невозможно автоматически сгенерировать ни одного нового номера, т.к. все они оказываются занятыми!"),"$PHP_SELF?$sess");
	}
	
	$kod=$testkod;
	return( $kod ) ;
	
	} catch (Exception $e) {				
		//echo 'Exception: ',  $e->getMessage(), "\n";		
	}
}


//
// Форма: добавить новый вопрос (откроется в новом окне)
//
function html_new_question($cid,$tid,$adding=1,$show=0) {
    require_once('lib/classes/ToolTip.class.php');
	global $sess, $asessf, $s, $sitepath, $ss;
//    <form action='test_list.php' method=POST name=myform target='popup' enctype='multipart/form-data' onSubmit=\"javascript: wopen('about:blank', 'popup', 1000, 750); return true;\">
	echo "
   <form action='' method=POST name=myform enctype='multipart/form-data'>
   <table width=100% class=main cellspacing=0>
   <input type=hidden name=MAX_FILE_SIZE value=2300000>
   $asessf
   <input type=hidden name=c value=\"add_submit\">
   <input type=hidden name=cid value=\"$cid\">";

	if ($adding) {   
		/*echo "
		<input type=hidden name=goto value=1>
		<input type=hidden name=gototid value=$tid>
		<input type=hidden name=gotourl value=\"test_test.php?c=edit&tid=$tid&cid=$cid\">
		";*/
		echo "<input type=hidden name=adding2tid value=\"$tid\">";
	}
	else {
		echo "<input type=hidden name=adding2cid value=\"$cid\">";
	}

	echo "
   <input type=hidden name=jsclose value=\"1\">
   <input type=hidden name=jscloseurl value=\"".html(getenv("REQUEST_URI"))."\">

   <script>var show1=0</script>

   <!--tr><th colspan=2>"._("Добавить вопрос")."</a></th-->
   ";

	echo "<tr><td>"._("Текст вопроса")."</td><td><textarea name=\"f_vopros\" rows=3 cols=80></textarea></td></tr>";
	// echo "<tr><td>"._("Перевод текста вопроса")."</td><td><textarea name=\"f_vopros_translation\" rows=3 cols=80></textarea></td></tr>";

	// для опросов тему не указываем
	if(!isset($_GET['quiz_id'])){
		echo "<tr><td>" . _("Тема") . "</td><td><input type=text name=f_tema size=60></td></tr>";
		// echo "<tr><td>" . _("Перевод темы") . "</td><td><input type=text name=f_tema_translation size=60></td></tr>";
	}

	echo "
   <tr>
   <td>"._("Прикрепить файл")."</td><td>
   <input type=file name=f_attach size=30 style='width:100%' class=s8></td>
   </tr>";
	echo "<tr><td>" . _('Тип вопроса') ."</td><td><table border=0 cellspacing=0 cellpadding=0> ";


	foreach ($GLOBALS[qtypes] as $k=>$v) {
		include_once("template_test/$v-v.php");
		$strIcon = getVisualChar( $k );
		echo "<tr><td valign=bottom><span class=sym>".$strIcon."</span></td><td>";//$visualchar[$k].":
		echo "<label for=r$v>
      <input id=r$v type=radio name=type value=\"$v\"".
      ($s[$ss][type]==$v?" checked":"").">&nbsp;".$GLOBALS["v_edit_$v"]['title']."</label>&nbsp;";
      //helpalert($GLOBALS["v_edit_$v"]['info'],"(?)");
      $toolTip = new ToolTip();
      echo $toolTip->display("template_test_$v-v");
      echo "</td></tr>";
	}

	echo "</table></td></tr>";

	//   if ($s[usermode])
	//      echo "Код*: <input type=text class=lineinput name=kod value=\"autoindex\"><P>";
	//   else
	echo "<input type=hidden name=kod value=\"autoindex\">";

	echo "<tr><td colspan=2>";
	echo okbutton(_('Далее'));
	echo "</td></tr></table>";
	echo "</form>";
}

function echoAttachedFile( $cid,  $stid, $what, $kod){
	// открывает прикрепленный к ответу файл

    if($what == 5){
        $q = "SELECT * FROM logseance WHERE stid=$stid AND cid=$cid AND kod='$kod'";

            	$res=sql($q,"err10 in test");

            	$r = sqlget($res);

            	sqlfree( $res );
    }else{
    	$q = "SELECT * FROM seance WHERE stid=$stid AND cid=$cid AND kod='$kod'";
    	$res=sql($q,"err10 in test");

    	$r = sqlget($res);

    	sqlfree( $res );
    }



	if( $r ){
                if (dbdriver == "mssql" && substr($r['attach'], 0, 2) == '0x') {
                $r['attach'] = @pack("H*", substr($r['attach'],2));
                }
		switch ( $what ) {
			case 1:
				header("Content-type: application/unknown");
				header("Content-disposition: attachment; filename=\"seance_{$stid}_".time().".txt\";");
				echo $r[text];
				exit;
			case 2:
				echo "<h3>Сеанс $stid, вопрос $r[kod]</h3><pre>".html($r[text])."</pre>";
				exit;
			case 3:
				header("Content-type: application/unknown");
				header("Content-disposition: attachment; filename=\"seance_{$stid}_$r[filename]\";");
				echo $r['attach'];
				//echo $r[attach];
				exit;
			case 4:
				echo $r[attach];
				exit;
			case 5:

		        if (dbdriver == "mssql" && substr($r['review'], 0, 2) == '0x') {
                    $r['review'] = @pack("H*", substr($r['review'],2));
                }
			    header("Content-type: application/unknown");
				header("Content-disposition: attachment; filename=\"seance_{$stid}_$r[review_filename]\";");
				echo $r['review'];
				//echo $r[attach];
				exit;
		}
	}
}

function getRowspan($arrHaystack, $strCode) {
	$intCnt = 0;
	if (is_array($arrHaystack)) {
		foreach ($arrHaystack as $key => $value) {
			$key = trim($key);
			$strCode = trim($strCode);
			if (strpos($key, $strCode)>0 && (strpos($key, $strCode) == strlen($key)-strlen($strCode))) {
				$intCnt++;
			}
		}
	}
	return $intCnt;
}

/**
* @param array $kods
* @return int
*/
function get_maxbal_by_kods($kods) {
	if (is_array($kods) && count($kods)) {
		$sql = "SELECT SUM(balmax) as balmax FROM list WHERE kod IN ('".implode("','",$kods)."')";
		$res = sql($sql);
		while ($row = sqlget($res)) {
		    if (!isset($ret)) {
		        $ret = $row['balmax'];
		    } else {
		        if ($ret<0) {
		            $ret = ($row['balmax']>$ret) ? $row['balmax'] : $ret;
		        } else {
                    if ($row['balmax']>0) {
                        $ret += $row['balmax'];
                    }
		        }
		    }
		}
		return $ret;
	}
}

/**
* @param array $kods
* @return int
*/
function get_nomoder_maxbal_by_kods($kods) {
	if (is_array($kods) && count($kods)) {
		$sql = "SELECT SUM(balmax) as balmax FROM list WHERE kod IN ('".implode("','",$kods)."') AND qmoder=0";
		$res = sql($sql);
		while ($row = sqlget($res)) {
		    if (!isset($ret)) {
		        $ret = $row['balmax'];
		    } else {
		        if ($ret<0) {
		            $ret = ($row['balmax']>$ret) ? $row['balmax'] : $ret;
		        } else {
                    if ($row['balmax']>0) {
                        $ret += $row['balmax'];
                    }
		        }
		    }
		}
	}
	return $ret;
}

function get_minbal_by_kods($kods) {
	if (is_array($kods) && count($kods)) {
		$sql = "SELECT balmin FROM list WHERE kod IN ('".implode("','",$kods)."')";
		$res = sql($sql);
		while ($row = sqlget($res)) {
		    if (!isset($ret)) {
		        $ret = $row['balmin'];
		    } else {
		        if ($ret<0) {
		            if ($row['balmin']<0) $ret += $row['balmin'];
		        } else {
		            if ($row['balmin']<0) {
		                $ret = $row['balmin'];
		            } else {
		                $ret = ($row['balmin']<$ret) ? $row['balmin'] : $ret;
		            }
		        }
		    }
		}
		return $ret;
	}
}

function get_nomoder_minbal_by_kods($kods) {
	if (is_array($kods) && count($kods)) {
		$sql = "SELECT balmin FROM list WHERE kod IN ('".implode("','",$kods)."') AND qmoder=0";
		$res = sql($sql);
		while ($row = sqlget($res)) {
		    if (!isset($ret)) {
		        $ret = $row['balmin'];
		    } else {
		        if ($ret<0) {
		            if ($row['balmin']<0) $ret += $row['balmin'];
		        } else {
		            if ($row['balmin']<0) {
		                $ret = $row['balmin'];
		            } else {
		                $ret = ($row['balmin']<$ret) ? $row['balmin'] : $ret;
		            }
		        }
		    }
		}
		return $ret;
	}
}

function get_maxbal_by_stid($stid) {
	$query = "SELECT log FROM loguser WHERE stid = $stid";
	$result = sql($query,"errgrm345");
	if(sqlrows($result) == 0) {
		return "";
	}
	$row = sqlget($result);
    /*
     * Костыль...
     * Если используется БД MSSQL, то в поле log данные 
     * сохраняются в кодировке cp1251
     */ 
    if(dbdriver == 'mssql' || dbdriver == 'mssqlnative'){
        $config = Zend_Registry::get('config');
        if($config->charset != 'cp1251')
            $charset = $config->charset;
        $row['log'] = iconv('cp1251',$charset, $row['log']);
    }
	$log = unserialize(stripslashes($row['log']));
	if(!is_array($log)) {
		return "";
	}
	$kods = $log['akod'];
	$kods_set = implode("','", $kods);
	$query = "SELECT balmax FROM list WHERE kod IN ('".$kods_set."')";
	$result = sql($query,"errgmbs212");
	$ret = 'undefined';
	while($row = sqlget($result)) {
	    $ret = process_max($ret,$row['balmax']);
	}
	if ($ret == 'undefined') $ret = 0;
	return $ret;
}

function get_minbal_by_stid($stid) {
	$query = "SELECT log FROM loguser WHERE stid = $stid";
	$result = sql($query,"errgrm345");
	if(sqlrows($result) == 0) {
		return "";
	}
	$row = sqlget($result);
    /*
     * Костыль...
     * Если используется БД MSSQL, то в поле log данные 
     * сохраняются в кодировке cp1251
     */ 
    if(dbdriver == 'mssql' || dbdriver == 'mssqlnative'){
        $config = Zend_Registry::get('config');
        if($config->charset != 'cp1251')
            $charset = $config->charset;
        $row['log'] = iconv('cp1251',$charset, $row['log']);
    }
    
	$log = unserialize(stripslashes($row['log']));
	if(!is_array($log)) {
		return "";
	}
	$kods = $log['akod'];
	$kods_set = implode("','", $kods);
	$query = "SELECT balmin FROM list WHERE kod IN ('".$kods_set."')";
	$result = sql($query,"errgmbs212");
	$ret = 'undefined';
	while($row = sqlget($result)) {
	    $ret = process_min($ret,$row['balmin']);
	}
	if ($ret == 'undefined') $ret = 0;
	return $ret;
}

// ================================================================================================
/**
* Возвращает query result всех тем вопросов в задании
* темы не повторяются
*/
function test_getThemesArray($r) {

	switch ($r[datatype]) {

		case "1":
			$a1=array();
			$a2=array();
			foreach (explode($GLOBALS[brtag],$r[data]) as $k=>$v) {
				if (strpos($v,"%")!==false || strpos($v,"_")!==false)
				$a2[]=" kod LIKE '".ad($v)."'";
				else
				$a1[]="'".ad($v)."'";
			}
			if (count($a1)) $a3[]="kod IN (".implode(',',$a1).")";
			if (count($a2)) $a3[]="".implode(" OR ",$a2);
			if (!count($a3)) exit("Err2: error in 'data' tid=$r[tid].");
			//$rq="SELECT * FROM list WHERE ".implode(" OR ",$a3);
			$rq="
              SELECT DISTINCT `qtema`
              FROM list WHERE ".implode(" OR ",$a3)."
              ORDER BY `qtema`
      ";

			$res=sql($rq);
			break;

	}

	return $res;

}

/**
* Если выборка вопросов осуществляется через распределение по темам то возвращает
* массив распределений
* @result массив тема=>кол-во вопросов
*/
function test_getQuestionsByThemes($r) {

	$tmpl = "SELECT questions FROM testquestions WHERE tid={$r[tid]}";//" AND cid={$r[cid]}";
	$res = sql($tmpl);
	if (sqlrows($res) == 1) {

		$rs = sqlget($res);
		sqlfree($res);
		return unserialize($rs[questions]);

	}

	return false;

}

function prepare_files($dest, $qar){
    $filelist = array();
    $qar = remove_unexport_types($qar);
    if (is_array($qar) && count($qar)) {
        $sql = "SELECT kod, fname, fdata FROM file WHERE kod IN ('".join("','",$qar)."')";
        $res = sql($sql);
        $arrData = array();
        while($row = sqlget($res)) {
            $arrData[] = $row;
        }
        usort($arrData, "sortKod");
        foreach ($arrData as $row) {
            if (!empty($row['fname'])) {
                $filename = $dest.'/'.normal_filename($row['fname']);
                if ($fp = fopen($filename,'wb')) {
                    fwrite($fp,trim($row['fdata']));
                    if (!in_array($filename,$filelist))
                        $filelist[] = $filename;
                }
                fclose($fp);
            }
        }
    }
    return $filelist;
}

function prepare_xml($qar, $test_object = false, $imagepath = './files/'){

	$db_id=1;

	$doc=domxml_open_file($_SERVER['DOCUMENT_ROOT']."/template/export/template.xml");
	$organization = $doc->document_element();
	$doc->append_child($organization);
	$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Экспорт вопросов"));
	$organization->set_attribute("title",$title);

	$test = $doc->create_element("test");
	$organization->append_child($test);

	$testquestions = $testneed = array();
	if ($test_object) {

	    $sql = "SELECT kod FROM testneed WHERE tid = '".$test_object->test_id."'";
	    $res = sql($sql);

	    while($row = sqlget($res)) {
	        $testneed[$row['kod']] = $row['kod'];
	    }

		foreach ($arr = get_class_vars(get_class($test_object)) as $attribute => $value) {
		    if ($attribute == 'questions')
		    {
		        $testquestions = $test_object->$attribute;
		        continue;
		    }
		    if ($attribute == 'data') continue;
			if ($test_object->$attribute !== null) {
				$value=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$test_object->$attribute);
				$test->set_attribute($attribute, $value);
				$test->set_attribute("DB_ID", $test_object->test_id);
			}
		}
	} else {
		$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Вопросы для контроля"));
		$test->set_attribute("title",$title);
	}

	//sql("LOCK TABLES list WRITE, conf_cid WRITE, file WRITE","error");
    $sql="SELECT list.kod, list.timetoanswer AS timelimit, list.balmin, list.balmax, list.qtype, list.qdata, list.adata, list.qtema, `file`.fnum as fnum, `file`.fname as fname, `file`.ftype as ftype, list.weight, list.is_shuffled
          FROM list
          LEFT JOIN `file` ON list.kod=file.kod
          WHERE `list`.`kod` IN ('".implode("', '",$qar)."')";
    $res=sql($sql,"err");
    $arrData = array();
    while($row = sqlget($res)) {
        $files = array();
        if (isset($arrData[$row['kod']]['files'])) {
            $files = $arrData[$row['kod']]['files'];
        }
        if ($row['fname']) {
            $files[$row['fnum']] = array('fname' => $row['fname'], 'ftype' => $row['ftype'], 'fnum' => $row['fnum']);
        }
        $row['files'] = $files;
        $arrData[$row['kod']] = $row;
    }

    usort($arrData, "sortKod");
    foreach ($arrData as $row) {
		$kod = $row['kod'];

		$row['group_n'] = '';
		if (isset($testquestions[$row['qtema']])) {
		    $row['group_n'] = $testquestions[$row['qtema']];
		} else {
		    if (isset($testquestions['Без названия'])) {
		        $row['group_n'] = $testquestions['Без названия'];
		    }
		}
		$row['is_required'] = 0;
		if (isset($testneed[$row['kod']])) $row['is_required'] = 1;
		//foreach($row as $key=>$value) {
		//  echo $key." - ".$value."<br>";
		//}
		//kod - 4-18
		//qtype - 5
		//qdata - ДЛЯ ОТМЕНЫ ГРУППИРОВКИ ЛИСТОВ НЕОБХОДИМО ЩЕЛКНУТЬ ПО ЛИСТУ С НАЖАТОЙ КЛАВИШЕЙ~~~~ESC~~ЩЕЛКНУТЬ ПО ЛИСТУ С НАЖАТОЙ КЛАВИШЕЙ
		//qtema -
		//qmoder - 0
		//adata -
		//balmax - 1
		//balmin - 0
		//url -
		//last - 1102686108
		//timelimit -
		//fname -
		//ftype -

		//echo "sfsdf: ".$row['qtype'];
		//die();
		$weights = array();
        if (!empty($row['weight'])) {
            $weights = unserialize(stripslashes($row['weight']));
        }
		switch($row['qtype']) {
			case 1:

				//Split the text in arrey question and answers
				$question_text= $row['qdata'];
				$question_text=str_replace("&nbsp;","",$question_text);

				unset($question_array);
				$answs= explode($GLOBALS['brtag'],$question_text);
				$question_title = $answs[0];
				if (is_array($answs) && count($answs)) {
                    for ($i=1; $i<count($answs); $i+=2) {
                        $question_array[$answs[$i]]=trim($answs[$i+1]);
                    }
				}
				//$bounder_pattern="/".chr(126).chr(3).chr(126)."\d".chr(126).chr(3).chr(126)."/";
				//$question_array=preg_split($bounder_pattern,$question_text);

				//Add node in the document
				$question[$kod] = $doc->create_element("question");
				$test->append_child($question[$kod]);
				$question[$kod]->set_attribute("DB_ID",$kod);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Вопрос")." N");
				$question[$kod]->set_attribute("title",$title);
                $question[$kod]->set_attribute("timelimit",$row['timelimit']);
                $question[$kod]->set_attribute("is_required",(int) $row['is_required']);
				$question[$kod]->set_attribute("balmin",$row['balmin']);
				$question[$kod]->set_attribute("balmax",$row['balmax']);
                $question[$kod]->set_attribute("group_n",$row['group_n']);
                if ($row['is_shuffled']) {
				    $question[$kod]->set_attribute("shuffle",'true');
				}
				$question[$kod]->set_attribute("group",iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$row['qtema']));

				$text[$kod] = $doc->create_element("text");
				$question[$kod]->append_child($text[$kod]);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Текст"));
				$text[$kod]->set_attribute("title",$title);

				$question_title = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$question_title);

                // tansform database image to physical file
                $question_title .= prepare_xml_image_link($row['fname'], $imagepath);

				$cdata_section_q = $doc->create_cdata_section($question_title);
				$text[$kod]->append_child($cdata_section_q);


				// $text[$kod]->set_content($question_array[0]);

				$answers[$kod] = $doc->create_element("answers");
				$question[$kod]->append_child($answers[$kod]);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Ответы"));
				$answers[$kod]->set_attribute("title",$title);


				$answers[$kod]->set_attribute("type","single");

				foreach($question_array as $k=>$a) {
				//for($k = 1;$k < count($question_array); $k++) {
					$answer[$kod][$k] = $doc->create_element("answer");
					$answers[$kod]->append_child($answer[$kod][$k]);
					$answer[$kod][$k]->set_attribute("DB_ID",$k);
					if (isset($weights[$k])) {
	   				    $answer[$kod][$k]->set_attribute("weight",$weights[$k]);
					}
					$answer[$kod][$k]->set_attribute("DB_ID",$k);
					$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Ответ"));
					$answer[$kod][$k]->set_attribute("title",$title);


					$question_array[$k] = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$question_array[$k]);

					$cdata_section = $doc->create_cdata_section($question_array[$k]);
					$answer[$kod][$k]->append_child($cdata_section);

					if($row['adata'] == $k) {
						$answer[$kod][$k]->set_attribute("type","true");
					}
					else {
						$answer[$kod][$k]->set_attribute("type","false");
					}


				}


				break;
			case 2:

				$question_text=$row['qdata'];
				$question_text=str_replace("&nbsp;","",$question_text);
				$bounder_pattern="/".chr(126).chr(3).chr(126)."\d+".chr(126).chr(3).chr(126)."/";
				$question_array=preg_split($bounder_pattern,$question_text);

				//Add node in the document
				$question[$kod] = $doc->create_element("question");
				$test->append_child($question[$kod]);
				$question[$kod]->set_attribute("DB_ID",$kod);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Вопрос")." N");
				$question[$kod]->set_attribute("title",$title);
                $question[$kod]->set_attribute("timelimit",$row['timelimit']);
                $question[$kod]->set_attribute("is_required",(int) $row['is_required']);
				$question[$kod]->set_attribute("balmin",$row['balmin']);
				$question[$kod]->set_attribute("balmax",$row['balmax']);
                $question[$kod]->set_attribute("group_n",$row['group_n']);

				if ($row['is_shuffled']) {
				    $question[$kod]->set_attribute("shuffle",'true');
				}
				$question[$kod]->set_attribute("group",iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$row['qtema']));

				$text[$kod] = $doc->create_element("text");
				$question[$kod]->append_child($text[$kod]);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Текст"));
				$text[$kod]->set_attribute("title",$title);


				$question_array[0] = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$question_array[0]);

                // tansform database image to physical file
                $question_array[0] .= prepare_xml_image_link($row['fname'], $imagepath);

				$cdata_section_q = $doc->create_cdata_section($question_array[0]);
				$text[$kod]->append_child($cdata_section_q);

				//$text[$kod]->set_content($question_array[0]);

				$answers[$kod] = $doc->create_element("answers");
				$question[$kod]->append_child($answers[$kod]);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Ответы"));
				$answers[$kod]->set_attribute("title",$title);


				$answers[$kod]->set_attribute("type","multiple");

				$answer_right_from_base=explode(chr(126).chr(3).chr(126),$row['adata']);

				for($k = 1;$k < count($question_array); $k++) {
					$answer[$kod][$k] = $doc->create_element("answer");
					$answers[$kod]->append_child($answer[$kod][$k]);
					$answer[$kod][$k]->set_attribute("DB_ID",$k);
					if (isset($weights[$k])) {
	   				    $answer[$kod][$k]->set_attribute("weight",$weights[$k]);
					}
					$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Ответ"));
					$answer[$kod][$k]->set_attribute("title",$title);


					$question_array[$k] = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$question_array[$k]);

					$cdata_section = $doc->create_cdata_section($question_array[$k]);
					$answer[$kod][$k]->append_child($cdata_section);

					if($answer_right_from_base[$k-1] == 1) {
						$answer[$kod][$k]->set_attribute("type","true");
					}
					else {
						$answer[$kod][$k]->set_attribute("type","false");
					}
				}
				break;
			case 3:
				$question_text=$row['qdata'];
				$question_text=str_replace("&nbsp;","",$question_text);
//				$bounder_pattern="/".chr(126).chr(3).chr(126)."\d+".chr(126).chr(3).chr(126)."/";
//				$question_array=preg_split($bounder_pattern,$question_text);

				$question_array = explode($GLOBALS['brtag'], $question_text);
				array_filter_numbers(&$question_array);

				// Add node in the document
				$question[$kod] = $doc->create_element("question");
				$test->append_child($question[$kod]);
				$question[$kod]->set_attribute("DB_ID",$kod);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Вопрос")." N");
				$question[$kod]->set_attribute("title",$title);
                $question[$kod]->set_attribute("timelimit",$row['timelimit']);
                $question[$kod]->set_attribute("is_required",(int) $row['is_required']);
				$question[$kod]->set_attribute("balmin",$row['balmin']);
				$question[$kod]->set_attribute("balmax",$row['balmax']);
                $question[$kod]->set_attribute("group_n",$row['group_n']);

				if ($row['is_shuffled']) {
				    $question[$kod]->set_attribute("shuffle",'true');
				}
				$question[$kod]->set_attribute("group",iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$row['qtema']));

				$text[$kod] = $doc->create_element("text");
				$question[$kod]->append_child($text[$kod]);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Текст"));
				$text[$kod]->set_attribute("title",$title);


				$question_array[0] = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$question_array[0]);

                // tansform database image to physical file
                $question_array[0] .= prepare_xml_image_link($row['fname'], $imagepath);

				$cdata_section_q = $doc->create_cdata_section(array_shift($question_array));
				$text[$kod]->append_child($cdata_section_q);

				//$text[$kod]->set_content($question_array[0]);

				$answers[$kod] = $doc->create_element("answers");
				$question[$kod]->append_child($answers[$kod]);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Ответы"));
				$answers[$kod]->set_attribute("title",$title);


				$answers[$kod]->set_attribute("type","compare");

				$answer_right_from_base=explode(chr(126).chr(3).chr(126),$row['adata']);

				$k = 0;
				while(($qa[0] = array_shift($question_array)) && ($qa[1] = array_shift($question_array))) {

					$answer[$kod][++$k] = $doc->create_element("answer");
					$answers[$kod]->append_child($answer[$kod][$k]);
					$answer[$kod][$k]->set_attribute("DB_ID",$k);

					$qa[0] = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$qa[0]);
					$qa[1] = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$qa[1]);

					$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Ответ"));
					$answer[$kod][$k]->set_attribute("title",$title);
					$answer[$kod][$k]->set_attribute("right",$qa[1]);
					$answer[$kod][$k]->set_attribute("type","false");

					$cdata_section = $doc->create_cdata_section($qa[0]);
					$answer[$kod][$k]->append_child($cdata_section);
				}
				break;
			case 5:

				$question_text=$row['qdata'];
				$question_text=str_replace("&nbsp;","",$question_text);
				$bounder_pattern=chr(126).chr(3).chr(126);
				$question_array=explode($bounder_pattern,$question_text);

				$question[$kod] = $doc->create_element("question");
				$test->append_child($question[$kod]);
				$question[$kod]->set_attribute("DB_ID",$kod);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Вопрос")." N");
				$question[$kod]->set_attribute("title",$title);
                $question[$kod]->set_attribute("timelimit",$row['timelimit']);
                $question[$kod]->set_attribute("is_required",(int) $row['is_required']);
				$question[$kod]->set_attribute("balmin",$row['balmin']);
				$question[$kod]->set_attribute("balmax",$row['balmax']);
                $question[$kod]->set_attribute("group_n",$row['group_n']);

				if ($row['is_shuffled']) {
				    $question[$kod]->set_attribute("shuffle",'true');
				}
				$question[$kod]->set_attribute("group",iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$row['qtema']));

				$text[$kod] = $doc->create_element("text");
				$question[$kod]->append_child($text[$kod]);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Текст"));
				$text[$kod]->set_attribute("title",$title);


				$question_array[0] = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$question_array[0]);

                // tansform database image to physical file
                $question_array[0] .= prepare_xml_image_link($row['fname'], $imagepath);

				$cdata_section_q = $doc->create_cdata_section($question_array[0]);
				$text[$kod]->append_child($cdata_section_q);

				//$text[$kod]->set_content($question_array[0]);

				$answers[$kod] = $doc->create_element("answers");
				$question[$kod]->append_child($answers[$kod]);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Ответы"));
				$answers[$kod]->set_attribute("title",$title);


				$answers[$kod]->set_attribute("type","fill");

				for($k = 2; $k < count($question_array); $k++) {

					if($k%2==0) {
						$right = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$question_array[$k]);
					}
					else {
						$answer[$kod][$k] = $doc->create_element("answer");
						$answers[$kod]->append_child($answer[$kod][$k]);
						$answer[$kod][$k]->set_attribute("DB_ID",$k);
						$question_array[$k] = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$question_array[$k]);

						$cdata_section = $doc->create_cdata_section($question_array[$k]);
						$answer[$kod][$k]->append_child($cdata_section);

						//$answer[$kod][$k]->set_content("<![CDATA[".$question_array[$k]."]]>");


						$answer[$kod][$k]->set_attribute("right",$right);
						$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Ответ"));
						$answer[$kod][$k]->set_attribute("title",$title);

					}
				}
				break;
            case 6:
				$question_text = $row['qdata'];

				$question[$kod] = $doc->create_element("question");
				$test->append_child($question[$kod]);
				$question[$kod]->set_attribute("DB_ID",$kod);
				$question[$kod]->set_attribute("question_id",$kod);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Вопрос")." N");
				$question[$kod]->set_attribute("title",$title);
                $question[$kod]->set_attribute("timelimit",$row['timelimit']);
                $question[$kod]->set_attribute("is_required",(int) $row['is_required']);
				$question[$kod]->set_attribute("balmin",$row['balmin']);
				$question[$kod]->set_attribute("balmax",$row['balmax']);
                $question[$kod]->set_attribute("group_n",$row['group_n']);

				$question[$kod]->set_attribute("group",iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$row['qtema']));

				$text[$kod] = $doc->create_element("text");
				$question[$kod]->append_child($text[$kod]);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Текст"));
				$text[$kod]->set_attribute("title",$title);

				$question_text = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$question_text);

                // tansform database image to physical file
                $question_text .= prepare_xml_image_link($row['fname'], $imagepath);

				$cdata_section_q = $doc->create_cdata_section($question_text);
				$text[$kod]->append_child($cdata_section_q);

				$answers[$kod] = $doc->create_element("answers");
				$question[$kod]->append_child($answers[$kod]);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Ответы"));
				$answers[$kod]->set_attribute("title",$title);

				$answers[$kod]->set_attribute("type","free");

            break;
            case 8:

				//Split the text in arrey question and answers
				$question_text= $row['qdata'];
				$question_text=str_replace("&nbsp;","",$question_text);

				unset($question_array);
				$answs= explode($GLOBALS['brtag'],$question_text);
				$question_title = $answs[0];
				if (is_array($answs) && count($answs)) {
                    for ($i=1; $i<count($answs); $i+=2) {
                        $question_array[$answs[$i]]=trim($answs[$i+1]);
                    }
				}
				//$bounder_pattern="/".chr(126).chr(3).chr(126)."\d".chr(126).chr(3).chr(126)."/";
				//$question_array=preg_split($bounder_pattern,$question_text);

				//Add node in the document
				$question[$kod] = $doc->create_element("question");
				$test->append_child($question[$kod]);
				$question[$kod]->set_attribute("DB_ID",$kod);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Вопрос")." N");
				$question[$kod]->set_attribute("title",$title);
                $question[$kod]->set_attribute("timelimit",$row['timelimit']);
                $question[$kod]->set_attribute("is_required",(int) $row['is_required']);
				$question[$kod]->set_attribute("balmin",$row['balmin']);
				$question[$kod]->set_attribute("balmax",$row['balmax']);
                $question[$kod]->set_attribute("group_n",$row['group_n']);
                if ($row['is_shuffled']) {
				    $question[$kod]->set_attribute("shuffle",'true');
				}
				$question[$kod]->set_attribute("group",iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$row['qtema']));

				$text[$kod] = $doc->create_element("text");
				$question[$kod]->append_child($text[$kod]);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Текст"));
				$text[$kod]->set_attribute("title",$title);

				$question_title = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$question_title);

                // tansform database image to physical file
                //$question_title .= prepare_xml_image_link(iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$row['fname']), $imagepath);

				$cdata_section_q = $doc->create_cdata_section($question_title);
				$text[$kod]->append_child($cdata_section_q);


				// $text[$kod]->set_content($question_array[0]);

				$answers[$kod] = $doc->create_element("answers");
				$question[$kod]->append_child($answers[$kod]);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Ответы"));
				$answers[$kod]->set_attribute("title",$title);


				$answers[$kod]->set_attribute("type","single");

				foreach($question_array as $k=>$a) {
				//for($k = 1;$k < count($question_array); $k++) {
					$answer[$kod][$k] = $doc->create_element("answer");
					$answers[$kod]->append_child($answer[$kod][$k]);
					$answer[$kod][$k]->set_attribute("DB_ID",$k);
					if (isset($weights[$k])) {
	   				    $answer[$kod][$k]->set_attribute("weight",$weights[$k]);
					}
					$answer[$kod][$k]->set_attribute("DB_ID",$k);
					$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Ответ"));
					$answer[$kod][$k]->set_attribute("title",$title);


					$question_array[$k] = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$question_array[$k]);
                    if (isset($row['files'][$k])) {
                        $question_array[$k] = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",prepare_xml_image_link($row['files'][$k]['fname'], $imagepath)).$question_array[$k];
                    }

					$cdata_section = $doc->create_cdata_section($question_array[$k]);
					$answer[$kod][$k]->append_child($cdata_section);

					if($row['adata'] == $k) {
						$answer[$kod][$k]->set_attribute("type","true");
					}
					else {
						$answer[$kod][$k]->set_attribute("type","false");
					}


				}


				break;
            case 11:
				$question_text=$row['qdata'];
				$question_text=str_replace("&nbsp;","",$question_text);
				$bounder_pattern="/".chr(126).chr(3).chr(126)."/";
				$question_array=preg_split($bounder_pattern,$question_text);

				$_questions = $_answers = array();
				if (is_array($question_array) && count($question_array)) {
				    for($i=1;$i<count($question_array);$i+=2) {
				        $_questions[] = $question_array[$i];
				        $_answers[] = $question_array[$i+1];
				    }
				}

				//Add node in the document
				$question[$kod] = $doc->create_element("question");
				$test->append_child($question[$kod]);
				$question[$kod]->set_attribute("DB_ID",$kod);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Вопрос")." N");
				$question[$kod]->set_attribute("title",$title);
                $question[$kod]->set_attribute("timelimit",$row['timelimit']);
                $question[$kod]->set_attribute("is_required",(int) $row['is_required']);
				$question[$kod]->set_attribute("balmin",$row['balmin']);
				$question[$kod]->set_attribute("balmax",$row['balmax']);
                $question[$kod]->set_attribute("group_n",$row['group_n']);

				if ($row['is_shuffled']) {
				    $question[$kod]->set_attribute("shuffle",'true');
				}
				$question[$kod]->set_attribute("group",iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$row['qtema']));

				$text[$kod] = $doc->create_element("text");
				$question[$kod]->append_child($text[$kod]);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Текст"));
				$text[$kod]->set_attribute("title",$title);

				$question_array[0] = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$question_array[0]);

                // tansform database image to physical file
                $question_array[0] .= prepare_xml_image_link($row['fname'], $imagepath);

				$cdata_section_q = $doc->create_cdata_section($question_array[0]);
				$text[$kod]->append_child($cdata_section_q);

				$answers[$kod] = $doc->create_element("answers");
				$question[$kod]->append_child($answers[$kod]);
				$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Ответы"));
				$answers[$kod]->set_attribute("title",$title);

				$answers[$kod]->set_attribute("type","table");

				$count = max(count($_questions),count($_answers));

				for($k = 0;$k < $count; $k++) {
					$answer[$kod][$k] = $doc->create_element("answer");
					$answers[$kod]->append_child($answer[$kod][$k]);
					$answer[$kod][$k]->set_attribute("DB_ID",$k);
					if (isset($weights[$k+1])) {
	   				    $answer[$kod][$k]->set_attribute("weight",$weights[$k+1]);
					}
					$title=iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",_("Ответ"));
					$answer[$kod][$k]->set_attribute("title",$title);

					$_answers[$k] = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$_answers[$k]);
					$_questions[$k] = iconv($GLOBALS['controller']->lang_controller->lang_current->encoding,"UTF-8",$_questions[$k]);

					$cdata_section = $doc->create_cdata_section($_answers[$k]);
					$answer[$kod][$k]->append_child($cdata_section);

					if (!empty($_questions[$k])) {
					   $answer[$kod][$k]->set_attribute("right",$_questions[$k]);
					}
					$answer[$kod][$k]->set_attribute("type","false");
				}

            break;
			default:
				//          die("Неизвестный тип вопроса");
		}
	}
	return  $doc->dump_mem(true);
}

/**
* @param string $fname
* @return string
*/
function prepare_xml_image_link($fname, $path = './files/') {
    if (!empty($fname)) {
        return "<p align=center><img align=\"absmiddle\" src=\"{$path}".normal_filename($fname)."\"></p>";
    }
}

function array_filter_numbers($arr){
	foreach (array_keys($arr) as $key) {
		if ($key%3 == 1) unset($arr[$key]);
	}

}

function get_all_themes_array($cid=0) {
    if ($cid) {
        $cmsKods = getCMSkods($cid);
        $where = "WHERE kod LIKE '{$cid}-%' OR kod = '".(count($cmsKods)?implode("' OR kod = '", $cmsKods):'-1')."'";
    } else {
        return array();

        /*
        $where = "WHERE kod NOT LIKE '".(int) getField('Courses','CID','is_poll','1')."-%'";

        $resources = array();
        $sql = "SELECT CID FROM Courses WHERE `type` = 1";
        $res = sql($sql);

        while($row = sqlget($res)) {
            $resources[$row['CID']] = $row['CID'];
        }

        if (count($resources)) {
            foreach($resources as $courseId) {
                $where .= " AND kod NOT LIKE '".(int) $courseId."-%'";
            }
        }
        */
    }
    $sql = "SELECT DISTINCT qtema FROM list {$where} ORDER BY qtema";
    $res = sql($sql);
    while($row = sqlget($res)) {
        if ($row['qtema']) {
            $ret .= ", '{$row['qtema']}':'{$row['qtema']}'";
    }
    }
    $ret = strlen($ret)?("{'".STR_OPTIONS_ALL."':'-1', '"._('---без темы---')."':'-999'".$ret):("{'"._('---без темы---')."':'-999'".$ret);
    $ret .= '}';
    return $ret;
}

function remove_unexport_types($qar) {
    $arr_export_types = array(1, 2, 3, 5, 6, 8);
    $arr_qar = array();
    if (is_array($qar) && count($qar)) {
       $sql="SELECT kod, qtype FROM list WHERE kod IN ('".join("','",$qar)."')";
       $res = sql($sql);
       while($row = sqlget($res)) {
            if (in_array($row['qtype'], $arr_export_types)) {
                $arr_qar[] = $row['kod'];
            }
        }
    }
    return $arr_qar;
}

function sortKod($a, $b) {
    return (int)str_replace("-", "", $a['kod']) >= (int)str_replace("-", "", $b['kod']);
}

/**
 * Возвращает массив ID вопросов из ЦМС, если курс содержит тесты ЦМС
 *
 * @param  $cid
 * @return unknown
 */

function getCMSkods($cid) {
    $sql = "SELECT t.data
           FROM organizations o
                LEFT JOIN library l
                    ON (l.bid = o.module)
                LEFT JOIN test t
                    ON (t.tid = o.vol1)
                LEFT JOIN Courses c
                    ON (l.cid = c.CID OR t.cid = c.CID)
           WHERE o.cid = ".(int)$cid." AND
                 c.type = 1";

   $voprosIds = array();
   $res = sql($sql);
   while ($row = sqlget($res)) {
       $dummy = explode($GLOBALS['brtag'],$row['data']);
       if (is_array($dummy) AND count($dummy)) {
           foreach ($dummy as $val) {
               if ($val) {
                   $voprosIds[$val] = $val;
               }
           }
       }
   }
   return $voprosIds;
}

function showHistogram( $o ){
  $ss="<table width=100% >";
  if( count( $o ) > 1 ){
    foreach( $o as $oo ){
      $oo1=100-$oo;
      $ss.="<tr><td width=15%>$oo</td><td>
                              <table class=main cellspacing=0><tr><td class='tdr' width=".$oo."%></td><td width=".$oo1."%>$oo</td></tr></table>
                     </td></tr>";
    }
  }
  $ss.="</table>";
 return( $ss );
}

function search_user_options_sql($search, $cid=0, $tid =0, $mid=0) {
    //$html = "<select name='$name' id='$id' $extra>\n";
    intval('cid tid');
    $html = '<option value="-1"> '._('--- все ---').'</option>';
    if (!empty($search)) {
        $mids = array();
        $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
        $search = iconv('UTF-8',$GLOBALS['controller']->lang_controller->lang_current->encoding,unicode_urldecode($search));
        $search = trim($search);
        if ($search=='*') $search = '%';

        $filtered = array();

        if ($tid > 0) {
            $sql = "SELECT DISTINCT mid FROM loguser WHERE tid = '$tid'";
            if ($cid > 0) {
                $sql .= " AND cid = '$cid'";
            }
            $res = sql($sql);
            while($row = sqlget($res)) {
                $filtered[$row['mid']] = $row['mid'];
            }
        }

        $sql = "SELECT People.MID, People.LastName, People.FirstName, People.Patronymic, People.Login
                FROM People
                INNER JOIN Students ON (Students.MID = People.mid)
                WHERE (LastName LIKE '%".addslashes($search)."%'
                OR FirstName LIKE '%".addslashes($search)."%'
                OR Login LIKE '%".addslashes($search)."%')
                AND People.last>".(time()-60*60*24*365*2)."
                AND Students.CID IN (".implode(",",$_SESSION['s']['tkurs']).")";
        if ($cid > 0) {
            $sql .= " AND Students.CID = '".(int) $cid."' ";
        }
        $sql .= "
                ORDER BY LastName, FirstName, Login";



        $res = sql($sql);
        while($row = sqlget($res)) {
            if (!$peopleFilter->is_filtered($row['MID'])) continue;
            if (isset($mids[$row['MID']])) continue;
            if (($tid > 0) && !isset($filtered[$row['MID']])) {
                continue;
            }
            $mids[$row['MID']] = $row['MID'];
        	$html .= "<option value='{$row['MID']}'";
        	if ($row['MID']==$mid) $html .= " selected ";
        	$html .= ">".htmlspecialchars($row['LastName'].' '.$row['FirstName'].' '.$row['Patronymic'].' ('.$row['Login'].')',ENT_QUOTES)."</option>\n";
        }
    }
    //$html .= "</select>\n";

    return $html;
}

function get_test_select($cid, $tid = 0) {
    $html = '<select name="tid" id="tid" onChange="get_user_select(jQuery(\'#search\').get(0).value);"><option value="-1"> '._('--- все ---').'</option>';

    $tids = array();
    if ($cid > 0) {
        $sql = "SELECT DISTINCT vol1 FROM organizations WHERE vol1 > 0 AND cid = '".(int) $cid."'";
        $res = sql($sql);
        while($row = sqlget($res)) {
            $tids[$row['vol1']] = $row['vol1'];
        }
    }

    $where = '';
    if (count($tids)) {
        $tids = array_chunk($tids, 50);
        $where .= ' OR (';
        for($i=0; $i<count($tids); $i++) {
            if ($i>0) $where .= ' OR ';
            $where .= "tid IN ('".join("','",$tids[$i])."')";
        }
        $where .= ')';
    }
    $sql = "SELECT tid, title FROM test WHERE status > 0";
    if ($cid > 0) {
        $sql .= " AND (cid = '".(int) $cid."' $where)";
    }
    $res = sql($sql);
    while($row = sqlget($res)) {
        $html .= "<option value='{$row['tid']}'";
        if ($tid == $row['tid']) {
            $html .= " selected ";
        }
        $html .= "> ".htmlspecialchars($row['title'])."</option>";
    }
    $html .= "</select>";
    return $html;
}

function prepareGeshi($string)
{
    if (strlen($string)) {
        if (preg_match('/\[CODE(?:\s*LANG="(.+)")*\](.+)\[\/CODE\]/is', $string, &$matches)) {
            if (count($matches) == 3 || count($matches) == 2) {
                $lang = 'cpp';
                $code = $matches[1];
                if (count($matches) == 3) {
                    $lang = $matches[1];
                    $code = $matches[2];
                }

                if (!$lang) {
                    $lang = 'cpp';
                }

                require_once(APPLICATION_PATH.'/../library/geshi/geshi.php');
                $geshi = new GeSHi($code, $lang);
                $geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
                $string = preg_replace('/\[CODE(?:\s*LANG=".+")*\].+\[\/CODE\]/is', $geshi->parse_code(), $string);
            }
        }
    }
    return $string;
}

function prepareInnerLinks($string)
{
    $string = preg_replace('/href="(about:blank#)" +?InnerLink="EUL\:(.+?)"/i', 'href="javascript:void(0);" onClick="hm.test.openedWindow = window.open(\''.$GLOBALS['sitepath'].'resource/index/view/test_id/'.$_SESSION['s']['test_id'].'/db_id/${2}\', \'resourcePlay\', \'width=800, height=600, resizable=1, directories=0, location=0, toolbar=0, menubar=0, status=0\')"', $string);
    return $string;
}
?>