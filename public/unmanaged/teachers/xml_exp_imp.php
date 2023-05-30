<?php
$wwf = $GLOBALS['wwf'];

//include_once("../1.php");
include_once($wwf."/xml2/xml.class.php4");
include_once("ziplib.php");
include_once($wwf."/test.inc.php");
require_once('../make_cd.lib.php');
$GLOBALS['controller']->setHelpSection('import_vopros');
$cource_title;
//$con = mysql_connect(dbhost, dbuser, dbpass);
//mysql_select_db(dbbase);
?>

<html>
<head>
<title><?=_("Импорт вопросов из текстового файла")?></title>
</head>
<BODY><?php
$GLOBALS['controller']->setView('DocumentPopup');
$GLOBALS['controller']->setHeader(_("Импорт вопросов"));
$GLOBALS['controller']->captureFromOb(CONTENT);
$tid = isset($_REQUEST['tid']) ? (integer)$_REQUEST['tid'] : false;
$theme = $_POST['theme'];
$tmpdir = $GLOBALS['wwf'].'/temp';

if(isset($oper)) {

        switch($oper) {
                case 1:
                        $xmlName = $tmpdir."/course_".$cource.".xml";
                        $ctit = modExport($cource, $xmlName);
                        createModZip($cource, $tmpdir."/course_".$cource.".tar");
                        printf("<p>"._("Курс")."<b> %s </b>"._("сохранен в файлах:")."</p>", $ctit);
                        echo "<table width=100% class=main cellspacing=0>";
                        printf("<tr><td><li>"._("структура")."<td><b> %s</b> (%d) <td><a href='sendPage.php?fName=%s'>"._("загрузить")."</a>",
                                   "course_".$cource.".xml",filesize($tmpdir."/course_".$cource.".xml"),$tmpdir."/course_".$cource.".xml");
                        printf("<tr><td><li>"._("содержание")."<td><b> %s</b> (%d) <td><a href='sendPage.php?fName=%s'>"._("загрузить")."</a>",
                                   "course_".$cource.".tar",filesize($tmpdir."/course_".$cource.".tar"),$tmpdir."/course_".$cource.".tar");
                        echo "</table>";
                break;
                case 2:
/*                       if (!$GLOBALS['controller']->checkPermission(QUESTION_PERM_EDIT_OWN) 
                           && !$GLOBALS['controller']->checkPermission(QUESTION_PERM_EDIT_OTHERS)) {
                           exitmsg("У вас не хватает привилегий",'javascript:window.close();');
                       }      */        

                        if($send==1) {
                        	    $theme = $_POST['themeName'];
                        		$msg = "";
                                if( intval($onlytest)==0 ) {
                                        $SAVE_DB=TRUE;
                                }
                                else {
                                        $msg .= _("Только проверка формата входных данных!")."<br>";
                                        $SAVE_DB=FALSE;
                                }
                                //////////////////////////////////////// ЗАГРУЗКА ТЕСТА
                                //$testfile = $_FILES['testfile']['tmp_name'];
                                $testfile_name = $_FILES['testfile']['name'];
                                if (!move_uploaded_file($testfile, $tmpdir."/".$testfile_name) && !file_exists($tmpdir."/".$testfile_name))
                                        $msg .= _("Нет файла тестов")." $testfile_name<BR>";
                                //else
                                        //if ( ! move_uploaded_file($ansfile, $tmpdir."/".$ansfile_name)) {
                                               //$msg .= "Нет файла ответов $ansfile_name<BR>";
                                        //}
                                if ( ! testImport($cid, $tmpdir."/".$testfile_name,  $tmpdir."/".$ansfile_name, $theme, $tid)){
                                        $msg .= _("Ошибка записи вопросов")."<BR>";
                                } elseif (!$onlytest) {
                                        $msg .= _("Вопросы импортированы успешно")."<BR>";
                                }
                        	//echo $msg; die();

                                $js = (!$onlytest) ? JS_CLOSE_SELF_REFRESH_OPENER : false;       
                                $GLOBALS['controller']->setMessage($msg, $js);
                        }
                        else {
                                echo "<form enctype='multipart/form-data' method=post action='xml_exp_imp.php?oper=2&send=1&cid=$cid'>";
                                echo "<table width=100% class=main cellspacing=0>";
                                echo "<tr><th colspan='99'>"._("Введите файлы для загрузки на курс")."</th></tr>";
                                echo "<tr><td>"._("Файл тестов")." (.txt) </td>
                                          <td>
                                            <input name=testfile type=file />
                                            &nbsp;
                                            <input type=checkbox value=1 name=onlytest checked />&nbsp;"._('Только проверить')."                                                
                                          </td>                                          
                                      </tr>";
//                                echo "<tr><td><LI>файл ответов (.csv): </td><td><input name=ansfile type=file></td></tr>";
                                echo "<tr><td>"._("Тема")." <td><input name=themeName type=text>";
                                if ($tid) echo "<input name=tid type=hidden value='{$tid}'>";
                                echo "<tr><td colspan='99'>".okbutton()."</td></tr>";
                                echo "</table>";
                                echo "</form>";
                        }
                break;
                case 3:
                        if(isset($oper)) {
                                echo "saveQuestions()";
                        }
                break;
                case 4:
                        $GLOBALS['controller']->setHeader(_("Подготовить дистрибутив курсов специальности"));
                        $courses=getArrayOfTrackCourses( $trid, $level );
                        echo showCourses( $trid, $courses );
                break;
                case 5: // собственно генерация дистрибцтива
                        if(!isset($level))
                                $level=-1;
                        $courses=getArrayOfTrackCourses( $trid, $level );
                        echo makeCoursesDistr( $trid, $courses, "CD-ROM" );
                break;
                default:
                        echo "Undefine operation";
        }
}
$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();
?>
</BODY>
</html>

<?php
/*##############################################################################################
#######                                                                               #############
#######                            .....FUNCTIONS......                               #################################
#######                                                                               #############
##############################################################################################*/
//-------------------------------------------------------------------------------------

function changeFileExt( $s, $ext){
        return( eregi_replace (".txt", ".".$ext, $s ) );
}

function getQuestion( $source, &$s ){
        // возвращает текст вопроса во втором кармане и код вопроса в первом
        $f = preg_match("/^(\d+)\. (.+)$/i",$source, $p);
        //$f = eregi("^([[:digit:]]+)\. ([a-zа-яА-ЯA-Z0-9 '/|<>?\\!@$%^&():;_.,-]+)",$source, $p);
        //$f = eregi("^([[:digit:]]+)\. ([a-zа-яА-ЯA-Z0-9 '/|<>?\\!@$%^&():\x2b;_.,-\[]+)",$source, $p);

        if($f) {
                $source = trim($source);
                $tmp = explode(".", $source);
                $number = intval($tmp[0]);

                $question_text = trim(ltrim($source, $number."."));

                $p[0] = $source;
                $p[1] = $number;
                $p[2] = $question_text;
        }

        if ( $f ) {
                $s[0]  = $p[1];
                $s[1]  = $p[2];
        }
        return( $f );
}

function getVariant( $ss, &$s ){
	//global $adodb;
        // возвращает текст ответа во втором кармане и код вопроса в первом
        // считает вариантом ответа запись вида (L) text  где L буква или цифра
        // если L= ! вариант верен
        // если L=?  неверен
        // во всех остальных случаях требуется отдельно файл ответов
//        $ss = $adodb->qstr( $ss );
//        $ss = addslashes( $ss );
        //$f = eregi(" *\\(([a-zа-яА-ЯёЁ0-9 \?!_.-])\\) *([a-zа-яА-ЯёЁ0-9 [:space:]–\*'/|<>~?\\!@#$%^&=();:_.,-]+)", $ss, $p ); // type 1 (*) *
        $f = preg_match("/^\(([!?])\) *(.+)$/i",$ss, $p);
        if ( $f ){
                $s[0] = $p[1];   // код варианта
                $s[1] = $p[2];   // текст варианта
                //echo "<LI>$p[2]";
        }
        return( $f );
}


function getTrueAnswer( $s ){
        // возвращает если есть заголовок таблицы
        //  $s="1;22";
        $f = eregi("^;*(([a-zа-яА-ЯёЁ0-9]+);*)+", $s, $p ); // type 1 (*) *
        //echo "p0=".$p[0]." p1=".$p[1]." p2=".$p[2]." p3=".$p[3]."<BR>";
        if ( $f ){
                $f=$p[2];
                //   echo $f."-верный ответ<BR>";
        }
        return( $f );
}

function getHeader( $s ){
        // возвращает если есть заголовок таблицы
        //  $s="1;22";
        $f = eregi("^;*(([0-9]+);*)+", $s, $p ); // type 1 (*) *
        //echo "p0=".$p[0]." p1=".$p[1]." p2=".$p[2]." p3=".$p[3]."<BR>";
        // if ( $f ) echo "заголовок $s<BR>";
        return( $f );
}

function getQuestionType( $s ){
        // дописать для других типов вопроссов
        switch ( $s[0] ) {
                case "(": $t=1;
                break;
                case "[": $t=2;
                break;
                case "":  $t=3;
                break;
                default:   $t=1;
                return( $t );
        }
        return( 1 );
}

function saveQuestions($cID, $questions, $answers, $theme, $tid = false ) {
        $ok = TRUE;
        $arrKods = array();
        
    	if ($tid){
	        $query = "SELECT data FROM test WHERE tid='{$tid}'";
	    	$res = sql($query);
	    	if($row = sqlget($res)){
	    		$arrKods[] = $row['data'];
		   	}
    	}
    	
        if(count($questions) && is_array($questions))
        foreach( $questions as $i => $q ){
                if($kod = saveQuestion($cID, $questions[ $i ], $answers[ $i ], $theme )){
                	$arrKods[] = $kod;
                } else {
                	$ok = FALSE;
                }
        }
        if ($ok && $tid) {
        	$strKod = implode($GLOBALS['brtag'], $arrKods);
        	$query = "UPDATE test SET data='{$strKod}' WHERE tid='{$tid}'";
        	sql($query);
        }
        return ( $ok );
}

function getOkCount( $questions ){
        // возвращает кол-во вопосов для которых есть ответы
        $i=0;
        if(count($questions) && is_array($questions))
        foreach( $questions as $q ){
                if( isset( $q[ok] ) ) $i++;
        }
        return( $i );
}

function writeNoOk( $questions ){
        // возвращает кол-во вопосов для которых есть ответы
        $i=0;
        if(count($questions) && is_array($questions))
        foreach( $questions as $q ){
                if( ! isset( $q[ok] ) ){
                        echo $q[id].":".$q[text]."<BR>";
                }
        }
}

function correctTestFile($testFileName) {
        $file = file($testFileName);
        if(is_array($file) && count($file)) {
            foreach ($file as $key=>$value) {
                if(!(eregi("^([[:digit:]]+)\. ([a-zа-яА-ЯA-Z0-9 '/|<>?\\!@$%^&():;_.,-]+)",$value) || eregi(" *\\(([a-zа-яА-ЯёЁ0-9 \?!_.-])\\) *([a-zа-яА-ЯёЁ0-9 [:space:]\*'/|<>~?\\!@#$%^&=();:_.,-]+)", $value))) {
                    if($key) {
                        $file[$key-1] = substr($file[$key-1], 0, -2);
                    }
                }
            }
        }
        $test = fopen($testFileName, "w");
        $str = implode("", $file);
        fwrite($test, $str);
        fclose($test);
}

function testImport($cID, $testFileName,  $ansFileName, $theme, $tid = false ){
        correctTestFile($testFileName);
        $f=FALSE;
        $msg = "";
        if( questionsImport($cID, $testFileName, $questions, $answers, $themes ) ) {

                echo _("Найдено вопросов")." ".count( $questions )."<BR>";
                $q_ok=getOkCount( $questions );
                echo _("Ответов в вопросах")." $q_ok<BR>";
                $f=TRUE;
                /*
                if ( count( $questions ) != $q_ok ) { // надо тогда читать файл ответов
                        writeNoOk($questions);
                        if ( answersImport( $cID, $ansFileName, $true_answers ) ) {
                                echo _("Найдено ответов")." ".count( $true_answers )."<BR>";
                                if ( a2q( $questions, $true_answers ) ) {
                                        $f=TRUE;
                                }
                                else
                                        echo _("Ответы не соответсвуют вопросам")."<BR>";
                        }
                        else
                                echo _("Ошибка импорта ответов")."<BR>";
                }
                else {
                        echo _("Файл ответов проигнорирован. Ответов")." $q_ok<BR>";
                        $f=TRUE;
                }
                */
        }
        else
                echo _("Ошибка импорта тестов")."<BR>";
        if ( saveQuestions($cID, $questions, $answers, $theme, $tid ) && $f )
                $f=TRUE;
        else {
                $f=FALSE;
        }
        showQA( $questions, $answers, $theme );
        return( $f );
}

function questionsImport($cID, $testFileName, &$questions, &$answers, &$themes ){
    $iq=$ia=0;
    if ($lines = @file($testFileName)) {
        $lines[] = '';
        if (is_array($lines) && count($lines)) {
            $buffer = '';
            for($i=0;$i<count($lines);$i++) {
                $line = $lines[$i];
                
                if (preg_match("/^\d+\./", $line)) {                    
                    $buffer = '';
                }
                                
                if ((preg_match("/^\([!?]\)/",$line) && !empty($buffer)) || ($i==(count($lines)-1))) {
                    if (getQuestion($buffer, $question)) {
                        $iq++;
                        $questions[$iq][text]= $question[1];
                        $questions[$iq][id]  = $question[0];
                        $questions[$iq][type]= 1;
                        
                        $ia = 0;
                        
                        $buffer = $line;
                        $i++;
                        while($i<count($lines)) {
                            $line = $lines[$i];
                            if (preg_match("/^(\([!?]\)|\d+\.)/", $line) || ($i==(count($lines)-1))) {
                                if (getVariant($buffer, $answer) && ($iq>=0)) {
                                    $ia++;
                                    $answers[$iq][$ia][text]= $answer[1];
                                    $answers[$iq][$ia][kod]= $answer[0];
                                    switch($answer[0]) {
                                        case "!":
                                            if (isset($questions[$iq][ok])){
                                                $questions[$iq][type] = 2;
                                            }
                                            else {
                                                $questions[$iq][ok] = $answer[0];
                                            }
                                        break;
                                        case "?":
                                            //
                                        break;
                                    }
                                } else {
                                    $line = $buffer;
                                    $i--;
                                    break;
                                }
                                $buffer = '';
                            }
                            $buffer .= $line;
                            $i++;
                        }
                        
                    }
                    if (!isset($answers[$iq])) unset($questions[$iq--]);
                    $buffer = '';
                }
                
                $buffer .= $line;
            }
        }
        echo sprintf (_("НАЙДЕНО %s ВОПРОСОВ В ФАЙЛЕ %s<br><br>"),$iq,$testFileName);
    }
    return $lines;
}

function questionsImport2($cID, $testFileName, &$questions, &$answers, &$themes ){
        // загружает вопросы из текстового файла в формате
        // N. Текст вопроса
        // (A) вариант ответа
        // (B) вариант ответа

        $fi=@fopen( $testFileName,"r" );
        if ( $fi ){
                $i=0;
                $iq=-1; $ia=0; $iq_ok=0;

                while( ! feof( $fi ) ){
                        $s=fgets( $fi,1024 );
                        $s .= ' ';
                        $s = substr ( $s, 0 , strlen($s)-1 );            //echo $ss."<BR>";
                        $s = str_replace ("\"", "'", $s);

                        $f = getQuestion( $s , $sq );

                        if( $f ) {        // новый вопрос появился
                                $iq++;   // формируем след вопрос
                                $questions[$iq][text]= $sq[1];
                                $questions[$iq][id] = $sq[0];
                                $questions[$iq][type]= 1;
                                $ia = 0; // обнуляем счетчик ответов для вопроса
                        }
                        else {
                                $f = getVariant( $s, $sa );
                                if( $f && $iq >=0 ){
                                        $answers[$iq][$ia][text]= $sa[1];
                                        $answers[$iq][$ia][kod]= $sa[0];
                                        switch($sa[0]){
                                                case "!": // верный вариант
                                                if ( isset($questions[ $iq ][ ok ]) ){
                                                        $questions[$iq][type] = 2;
                                                }
                                                else
                                                // здесь дописать запоминание набора верных вариантов
                                                $questions[ $iq ][ ok ] = $sa[0];
                                                $iq_ok++;
                                                break;
                                                case "?":  // неверный
                                                break;
                                        }
                                        $ia++;
                                }
                                else {
                                        $themes[$s]=$s;
                                }
                        }
                        $i++;
                }
                $iq++;
                echo _("НАЙДЕНО")." $iq "._("ВОПРОСОВ В ФАЙЛЕ")." {$testFileName}<br><br>";
                fclose( $fi );
        }else
        echo "Can't read temporary file $testFileName";

        return $fi;
}

function answersImport( $cID, $FileName, &$true_answers ){
        // читает из файла верыне коды ответов. записывает их true_answers для каждого вопроса
        $fi=@fopen( $FileName,"r");
        if ( $fi ){
                $i=0;
                $iq=-1; $ia=0;
                $f = FALSE;
                while( ! feof( $fi ) ){
                        $s=fgets( $fi,1024 );
                        $s = substr ( $s, 0 , strlen($s)-1 );            //echo $ss."<BR>";
                        $s = str_replace ("\"", "'", $s);
                        if( ! $f ){
                                $f=getHeader( $s );
                                $sheader=$s;
                        }
                        else {
                                if ( $f && strcmp( $s,$sheader) ){
                                        $a=getTrueAnswer( $s );
                                        if ( !$a )
                                        $f=FALSE;
                                        else{
                                                $true_answers[ $i ] = $a;
                                                $i++;
                                        }
                                }
                        }
                }
        }
        return( $fi );
}

function showQA( $questions, $answers, $theme ){


        $s=_("Тема:")." $theme<BR>";
        echo $s;
        if(count($questions) && is_array($questions))
        foreach ( $questions as $i=>$v ){ // для всех вопросов
                echo "<HR><B>".$questions[$i][id].". "._("ВОПРОС:")." ".$questions[$i][text]."</B><BR>";
                if (is_array($answers[$i]) && count($answers[$i])) {
                foreach ( $answers[$i] as $ans ){
                        if ( $ans[kod] == $questions[$i][ ok ] )
                                echo "<I><U><LI>(".$ans[kod].") ".$ans[text]."</U></I><BR>";
                        else
                                echo "<I><LI>(".$ans[kod].") ".$ans[text]."</I><BR>";
                }
                }
        }
}


function saveQuestion( $cid, $question, $answers, $theme ){
        global  $SAVE_DB;
        global  $adodb;
        // сохраняетя вопрос  в БД
        $kod = newQuestion( $cid );
        if (strlen($theme)>255)
        alert(_("Вы ввели слишком длинную тему. Максимальная длина 255 символов. Тема укорочена до этой границы."));
        $type=$question[type];
        include_once($_SERVER['DOCUMENT_ROOT']."/template_test/$type-v.php");
        $func="v_php2sql_$type";
        $vopros=$GLOBALS["v_edit_$type"]['default'];
        $vopros['kod']=$kod;//.$theme;
        $vopros['balmin'] = 0;
        $vopros['balmax'] = 1;
        $vopros['vopros'] = $question[text];
        // формируем ответы
        /////////////////////////////////////////////
        $k=1;
        $j=0;
        if (is_array($answers) && count($answers)) {
        foreach ( $answers as $ans ){
                $vrnt[$k] = $ans[text];
                if ( $question[ ok ] == $ans[ kod ] ){ // если коды ответов совпадают то запомнить номер верного варианта
                        switch( $type ){
                                case "1":  $vopros['otvet'] = $k;
                                break;
                                case "2":
                                        $vopros['otvet'][ $k ] = 1;
                                break;
                        }
                }
                $k++;
        }
        }
        ////////////////////////////////////////////
        $vopros['variant'] = $vrnt;

        $arrsql = $func($vopros);
        $rq="INSERT INTO list SET ";
        $arrsql['last']=time();
        $arrsql['qtema']=$theme;
        $arrsql['created_by']=$GLOBALS['s']['mid'];
        foreach ($arrsql as $k=>$v) {
                $keys[] = "`$k`";
                $values[] = $adodb->qstr($v);
                //$rq.=" `$k`=".$adodb->qstr($v).",";
//                $rq.=" `$k`='".addslashes($v)."',";
        }
        $rq = "INSERT INTO list (".join(",",$keys).") VALUES (".join(",",$values).")";
        //$rq=substr($rq,0,-1);
        // НАДО СФОРМИРОВАТЬ ОТВЕТЫ
        $f=TRUE;

        if( $SAVE_DB ){
                $res=sql($rq,"errIMPORT_QuEST123");
        }
        else {
                //echo "ЗАПИСИ НЕТ<br>";
        }
        sqlfree($res);
//        return( $f );
        return( $arrsql['kod'] );
}

function a2q( &$questions, $true_answers   ) {
        // добавляет верные ответы в массив вопросов
        $f=FALSE;
        if ( count($questions)==count($true_answers) ){
                $f = TRUE;
                foreach ( $questions as $i=>$question ){
                        if( isset( $true_answers [ $i ] ) ){
                                if( ! isset( $questions[ $i ][ ok ] ) ){ // если вдруг заранее не сформировали прав ответ   !!!!!1
                                $questions[ $i ][ ok ] = $true_answers [ $i ];
                                }
                        }
                        else
                                echo _("Ошибка в вопросе или ответе под номером")." $i<BR>";
                }
        }
        return( $f );
}

function modImport($cID, $xmlFileName) {
        global $parser, $cource_title;
        global $con;

        $parser = new xml();
        $parser->setErrorShow();
        $parser->setSkipWhite();
        $parser->setCaseFolding();
        $parser->setInputFile($xmlFileName);
        $parser->parseStruct();
        if($parser->error) {
                return false;
        }
        foreach($parser->tags as $key=>$value) {
                if($key == "COURSE") {
                        $cource_attr = $parser->values[$value[0]];
                        $cource_title = $cource_attr["attributes"]["TITLE"];
                }
                if($key == "ABSTRACT") {
                        $abstr_attr = $parser->values[$value[0]];
                        $cource_abstract = $abstr_attr["value"];
                }
                if($key == "UNIT") {
                        $units_ind = $value;
                        for($i=0; $i<count($units_ind);$i+=2) {
                                loadUnit($units_ind[$i], $units_ind[$i+1], $cID);
                        }
                }
        }
        $parser->_xml();
        $sql = "update courses set Description=".$adodb->Concat("Description"," ", "$cource_abstract")." where CID=$cID";
        //mysql_query($sql, $con);
        sql($sql);
        return true;
}

//-------------------------------------------------------------------------------------
function loadUnit($begInd, $endInd, $cID) {
        global $parser;
        global $con, $wwf;
        $Unit_Arr=$parser->values[$begInd];
        foreach($Unit_Arr as $key=>$value) {
                if($key == "attributes") {
                        $unit_title = $value["TITLE"];
                        $unit_theme = $value["THEME"];
                        $unit_hidden = $value["HIDDEN"];
                        $unit_forum = $value["FORUM_ID"];
                        $unit_test = $value["TEST_ID"];
                }
        }
        foreach($parser->tags as $key=>$value) {
                if($key == "LEARNINGOBJECT") {
                        foreach($value as $ind) {
                                if($ind>$begInd && $ind<$endInd) {
                                        $unit_learn = $parser->values[$ind]["value"];
                                }
                        }
                }
        }
        if (!isset($unit_hidden))
                $unit_hidden=1;
        if ($unit_test == "")
                $unit_test = " ";
        if ($unit_forum == "")
                $sql = "insert into mod_list( Title, Num, Descript, Pub, CID, test_id )
                         values('$unit_title','$unit_theme','$unit_learn',$unit_hidden,$cID,'$unit_test')";
        else
                $sql = "insert into mod_list(Title,Num,Descript,Pub,CID,forum_id,test_id)
                         values('$unit_title','$unit_theme','$unit_learn',$unit_hidden,$cID,$unit_forum,'$unit_test')";
        echo $sql."<BR>";
        //mysql_query($sql, $con) or die("Error1:".mysql_error().$sql);
        sql($sql);
        //$ModID = mysql_insert_id($con);
        $ModID = sqllast();
        foreach($parser->tags as $key=>$value) {
                if($key == "TOPIC") {
                        $topics_ind = $value;
                        for($i=0; $i<count($topics_ind);$i++) {
                                if($topics_ind[$i]>=$begInd and $topics_ind[$i]<=$endInd) {
                                        $topic_atr = loadTopic($topics_ind[$i]);
                                        $src = $wwf."/COURSES/course".$cID."/".$topic_atr["SRC"];
                                        $sql = "insert into mod_content (Title,ModID,mod_l,type,conttype)
                                               values('".$topic_atr["TITLE"]."',$ModID,'".$src."',
                                               '".$topic_atr["TYPE"]."','".$topic_atr["CONTTYPE"]."')";
                                        //mysql_query($sql, $con);
                                        sql($sql);
                                }
                        }
                }
        }
}

//-------------------------------------------------------------------------------------
function loadTopic($begInd) {
        global $parser;
        $Top_Arr=$parser->values[$begInd];
        foreach($Top_Arr as $key=>$value) {
                if($key == "attributes") {
                        return $value;
                }
        }
}

//-------------------------------------------------------------------------------------
function modExport($cID, $xmlFileName) {
        global $con;
        $fp = fopen($xmlFileName,"w+");
        xmlHead($fp);
        //$res = mysql_query("select * from courses where CID = ".$cID);
        $res = sql("select * from courses where CID = ".$cID);
        //$line = mysql_fetch_array($res, MYSQL_ASSOC);
        $line = sqlget($res);
        $atr = array("title"=>$line["Title"]);
        $courseTitle = $line["Title"];
        xmlBeginElement($fp, "course", "", $atr);                           //начало элемента
        $atr = array();
        xmlBeginElement($fp, "abstract", $line["Description"], $atr, 1);    //начало элемента
        xmlEndElement($fp, "abstract", 1);
        $res = sql("select * from mod_list where CID = ".$cID);
        while ($line = sqlget($res)) {
                $atr = array("title"=>$line["Title"], "theme"=>$line["Num"], "hidden"=>$line["Pub"],"forum_id"=>$line["forum_id"],"test_id"=>$line["test_id"]);
                xmlBeginElement($fp, "unit", "", $atr, 1);
                $atr = array();
                xmlBeginElement($fp, "learningobject", $line["Descript"], $atr, 2);
                xmlEndElement($fp, "learningobject", 2);
                $res1 = sql("select * from mod_content where ModID =".$line["ModID"]);
                while ($line1 = sqlget($res1)) {
                        $modl = strstr($line1["mod_l"],"mods/");
                        $atr = array("title"=>$line1["Title"], "type"=>$line1["type"], "conttype"=>$line1["conttype"], "src"=>$modl);
                        xmlBeginElement($fp, "topic", "", $atr, 2);                     //начало элемента
                        xmlEndElement($fp, "topic", 2);
                }
                xmlEndElement($fp, "unit", 1);
        }
        xmlEndElement($fp, "course");
        fclose($fp);
        sqlfree($res);
        sqlfree($res1);
        return $courseTitle;
}

//-------------------------------------------------------------------------------------
function makeLevelStr($l) {
        for($i=0; $i<$l; $i++)
                $outStr.="\t";
        return $outStr;
}

//-------------------------------------------------------------------------------------
function xmlHead( $fp ) {
        fputs($fp,"<?xml version=\"1.0\" encoding=\"windows-1251\" ?>\n");
        fputs($fp,"<!DOCTYPE course SYSTEM \"course.dtd\">\n");
        fputs($fp,"<!--  eLearning Server 3000 course -->\n");
}

//-------------------------------------------------------------------------------------
function xmlBeginElement( $fp, $nameElement, $text, $atributes, $level=0) {
        fputs($fp, makeLevelStr($level)."<".$nameElement);
        //вывод массива атрибутов
        foreach($atributes as $name=>$value) {
                fputs($fp, " ".$name."=\"".$value."\"");
        }
        fputs($fp, ">\n");
        if( strlen($text)>0 )
                fputs($fp, $text."\n");
}

//-------------------------------------------------------------------------------------
function xmlEndElement( $fp, $nameElement, $level=0) {
        fputs($fp, makeLevelStr($level)."</".$nameElement.">\n");
}


//-------------------------------------------------------------------------------------
function createModZip($cID, $zipFileName) {
        global $wwf;
        $dirs[]="mods";  //"../COURSES/course".$cID."/mods";
        $flags["overwrite"]=1;
        $zz = new tarfile("../COURSES/course".$cID."/",$flags);
        $zz->adddirectories($dirs);
        $err =$zz->filewrite($zipFileName);
        if($err)
        echo "Error: ". $err;
}

//-------------------------------------------------------------------------------------
function extractModZip($cID, $zipFileName) {
        global $tmpdir, $wwf;
        $workDir = getcwd();
        chdir($wwf."/COURSES/course".$cID);
        $flags["overwrite"]=1;
        $zz = new tarfile(".", $flags);
        $tararr = $zz->extractfile($zipFileName);
        foreach($tararr as $tarFile) {
                foreach($tarFile as $key=>$value) {
                        if($key=="filename")
                                $fName=$value;
                        if($key=="data")
                                $fData=$value;

                }
                $s= dirname($fName);
                if(!file_exists($s))
                        mkdir($s,711);
                $fp = fopen($fName, "wb");
                fwrite($fp,$fData);
                fclose($fp);
        }
        chdir($workDir);
}?>