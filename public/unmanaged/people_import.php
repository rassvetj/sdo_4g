<?
include("1.php");
include("metadata.lib.php");
require_once('courses.lib.php');
require_once('move2.lib.php');
require_once($GLOBALS['wwf'].'/lib/classes/Roles.class.php');

define("MAIL_SEND", true);
define("MAIL_SEND_DEAN", true);

define("FIRST_TAG_CELL", 8);

if (!$s[login]) {
	exitmsg(_("Пожалуйста, авторизуйтесь"),"/?$sess");
}
if ($s[perm]<3) {
	exitmsg(_("Доступ к странице имеют представители учебной администрации, или администраторы"),"/?$sess");
}
$tmp = '';
//$controller->setHelpSection('import');
if (!isset($c)) {
    $c = trim($_REQUEST['c']);
}
if (!isset($che)) {
    $che = $_REQUEST['che'];
}
if (!isset($che_main)) {
    $che_main = $_REQUEST['che_main'];
}
/**
 * is this thing was set using global_variables early?
 * this way is better if somewhere else $people_type can be set, which i don't know yet.
 **/
if(!isset($people_type) && isset($_REQUEST['people_type'])){
    $people_type = $_REQUEST['people_type'];
}
switch ($c) {

case "":
    echo show_tb();
    $GLOBALS['controller']->captureFromOb(CONTENT);
    if (!$GLOBALS['controller']->enabled)
    echo ph(_("Импорт учётных записей из CSV-файла"));
    $GLOBALS['controller']->setHeader(_("Импорт учётных записей из CSV-файла"));
    echo "
    <form enctype='multipart/form-data' action=$PHP_SELF method=post>
    $asessf
    <input type=hidden name=c value=\"upload\">
    <input type=hidden name=MAX_FILE_SIZE value=500000>
    ";
    echo "<table width=100% class=main cellspacing=0>";
    echo "
   	<tr><th colspan=2>"._("Шаг 1. Выбор файла")."</th></tr>
   	<tr><td valign=top width=10% nowrap>"._("CSV-файл")."</td><td> <input name=userfile type=file class=s8></td></tr>
   	<!-- tr><td valign=top>"._("Разделитель:")." </td><td>
   	<input type=radio name=razd value=';' checked id=z2><label for=z2>"._("точка с запятой")." [;]</label><br>
   	<input type=radio name=razd value=',' id=z1><label for=z1>"._("запятая")." [,]</label></td>
   	</tr -->
    <tr><td valign=top>"._("Роль")." </td><td>
	<input type=radio name=people_type value='stud_cid' checked id=p1>&nbsp;<label for=p1>"._("Слушатели")."</label><br><br>
	<input type=radio name=people_type value='teac_cid' id=p2>&nbsp;<label for=p2>"._("Преподаватели")."</label></td></tr>";
    echo "</table>";
    if (!$s[usermode]) {
        echo "<input type=hidden name=check value=\"checked\">";
    }
    else {
        echo "<input checked type=checkbox name=check value=checked id=z3><label for=z3>"._("Галочки по умолчанию отмечены")."</label>";
    }
    echo "<P>".okbutton()."</form>";
    $GLOBALS['controller']->captureStop(CONTENT);
    echo show_tb();
    break;
case "upload":
    //$upload=$HTTP_POST_FILES[userfile];
    $upload=$_FILES['userfile'];
    $fn="$tmpdir/tmp_csv_".session_id()."_".mt_rand(0,9999999);
    if ($upload[size]==0 || !is_uploaded_file($upload[tmp_name])) {
        exitmsg(_("Вы не загрузили файл! Нажмите кнопку \"Обзор\" и выберите *.CSV файл для импорта"), $sitepath . '/admin/people_add.php');
    }
    move_uploaded_file($upload[tmp_name],$fn);
    echo show_tb();
    $GLOBALS['controller']->captureFromOb(CONTENT);
    $GLOBALS['controller']->setHelpSection('step2');

    if($people_type == "teac_cid") {
        if ($GLOBALS['controller']->enabled){
            $GLOBALS['controller']->setHeader(_("Импорт учётных записей из CSV-файла"));
        }
		else{
            echo ph(_("Импорт учётных записей из CSV-файла"));
        }
    }
    else {
        if ($GLOBALS['controller']->enabled) $GLOBALS['controller']->setHeader(_("Импорт учётных записей из CSV-файла"));
		else echo ph(_("Импорт учётных записей из CSV-файла"));
    }
    if($people_type == "stud_cid"){
   		$message = sprintf(_("Загружен файл размером %d байт. Выберите курс и отметьте %s, которых нужно импортировать. "), filesize($fn), "слушателей");
//   		$GLOBALS['controller']->setMessage($message);
   		echo "
   		<form action=$PHP_SELF method=post>
   		$asessf
   		<input type=hidden name=c value=\"import\">
   		<!--input type=hidden name=c value=\"group\"-->";

   		if (is_array($cids) && count($cids)) $res = sql('SELECT CID FROM Courses WHERE TypeDes > 0 OR (TypeDes = -1 AND chain > 0) AND CID IN (' . implode(',', $cids) . ')');
	      $cids_chains = array();
	      while ($row = sqlget($res)){
	      		$cids_chains[] = $row['CID'];
	      }

   		echo $GLOBALS['tooltip']->display_variable('people_gen_course', 'sel_course', $cids_chains);
    }
    elseif($people_type == "stud_trid"){
   		$message = sprintf(_("Загружен файл размером %d байт. Выберите курс и отметьте %s, которых нужно импортировать. "), filesize($fn), "слушателей");
//   		$GLOBALS['controller']->setMessage($message);
   		echo "
   		<form action=$PHP_SELF method=post>
   		$asessf
   		<input type=hidden name=c value=\"import\">
   		<!--input type=hidden name=c value=\"group\"-->
   		"._("На специальность")." ".selectOpenTracks( "TRID" )."<P>";
    }
    elseif($people_type == "teac_cid"){
   		$message = sprintf("Загружен файл размером %d байт. Выберите курс и отметьте преподавателей, которых нужно импортировать. ", filesize($fn));
//   		$GLOBALS['controller']->setMessage($message);
   		echo "
   		<form action=$PHP_SELF method=post>
   		$asessf
   		<input type=hidden name=c value=\"import\">
   		<!--input type=hidden name=c value=\"group\"-->
   		<select name=\"CID\">".selCourses(get_kurs_by_status(),$CID,false,true)."</select><P>";
    }
    else {
        echo "
   		<form action=$PHP_SELF method=post>
   		$asessf
   		<input type=hidden name=c value=\"import\">
   		<P>";
    }
    echo "<input type=hidden name=people_type value=\"$people_type\">";

    if (!file_exists($fn)) {
        err(_("Не удалось скопировать файл, нет прав записи в")." $fn",__FILE__,__LINE__);
    }

    $f=fopen($fn,"rb") or err(_("Не могу открыть файл")." $fn",__FILE__,__LINE__);
    $i=0;
	//;Фамилия;Имя;Отчество;ДатаРождения;ПаспортныеДанные;РабТел;МобТел;ДомТел;Дата приема на работу;Департамент,Должность;Категория;Точка
    echo "<TABLE width=100%>";
    while ( $ss=fgets($f,10000) ) {
        # установка необходимой кодировки текста из файла
        $to_encod = ($GLOBALS['controller']->lang_controller->lang_current->encoding)?
                        $GLOBALS['controller']->lang_controller->lang_current->encoding :
                        "UTF-8";
        $ss = iconv(detectEncoding($ss), $to_encod,$ss);

        if($i == 0) {
            $i++;
            $title_array =  explode(';', $ss);
            $title_array2 = explode(',',$ss);
            if (count($title_array) > count($title_array2)) {
                $razd = ';';
            }else {
                $title_array = $title_array2;
                $razd = ',';
            }

            continue;
        }
        $r=explode ( $razd, $ss );
        // Удаляем все пробельные символы 
        foreach($r as $key => $value){
            $r[$key] = trim($value);
        }
        
        //- пробелы или символы кириллицы в поле «логин» - заменять на подчеркивание и транслит (есть метод в unmanaged)
        //- символы «¬» - перенос текста в ячейке логин/емайл - заменять на подчеркивание
        $r[3] = to_translit($r[3]); // логин
        //- пробелы или символы кириллицы в поле «E-mail» - заменять на подчеркивание и транслит
        $r[4] = to_translit($r[4]); // E-mail
        //
        //- пробелы вначале текста - удалять
        //- символы «¬» - перенос текста в остальных ячейках - заменять на пробел


        session_register("title_array");

     $tagIndex = FIRST_TAG_CELL;
     $tags = array();
     while (!empty($r[$tagIndex])) {
         $tag = html(trim($r[$tagIndex++]));
         // Зачем все поля после 7 ячейки в теги забивать ?
         // if ($tagIndex != 8) {
     	 $tmp .= "<input type='hidden' name='buf[$i][tag][]' value=\"{$tag}\">";
         //}
     }

        $tagIndex = FIRST_TAG_CELL;
        $tags = array();
        while (!empty($r[$tagIndex])) {
            $tag = html(trim($r[$tagIndex++]));
            $tmp .= "<input type='hidden' name='buf[$i][tag][]' value=\"{$tag}\">";
        }

        if ($i) {
            echo "<tr><td width=5% style='background-color:#ECECEC;' align=center><input type=hidden name='che_main[{$i}]' value=1 /><input checked type=checkbox name='che[$i]'></td><td>";
        }
        echo $tmp;
        if ($i) {
            echo "</td></tr>";
        }
        $i++;
    }
    echo "</table><P>";
    if ($i==0) {
        echo _("К сожалению, не найдена информация о, учащихся в данном файле.")."
        "._("Либо вы загрузили пустой файл, либо файл не того типа.");
    }
    else {
        echo okbutton(_("Готово"))."</form>";
    }
    fclose($f);
    unlink($fn);
    $GLOBALS['controller']->captureStop(CONTENT);
    echo show_tb();
    break;
case "import":
    $_str = '';
    $metadata = $_POST['metadata'];
    $_SESSION['arrStorePasswords'] = array();
	echo show_tb();
    $GLOBALS['controller']->captureFromOb(CONTENT);
	if($people_type == "teac_cid") {
		if ($GLOBALS['controller']->enabled) $GLOBALS['controller']->setHeader(_("Импорт учётных записей из CSV-файла"));
        else
        echo ph(_("Импорт учётных записей из CSV-файла"));
    }
	else {
		if ($GLOBALS['controller']->enabled) $GLOBALS['controller']->setHeader(_("Импорт учётных записей из CSV-файла"));
        else
		echo ph(_("Импорт учётных записей из CSV-файла"));
    }
	if (!is_array($che) || count($che)==0)
		exitmsg(_("Ничего не было отмечено для импорта. Выберите хотя бы одного слушателя."),"admin/people_add.php");
	else
	{
			$res=sql("SELECT MID FROM People","err1C8911");
			$max=0;
			while( $r=sqlget($res)) {
				if($r[MID]>$max) $max=$r[MID];
			}
			sqlfree( $res );
			$max_k=count( $che_main );
			$mail_body = _("Добавлены учетные записи:")." <br /><br />
						 <table border='1'>
						 <th>"._("Фамилия")."</th><th>"._("Имя")."</th><th>"._("Логин")."</th><th>"._("Пароль")."</th>";
			$buf = $_POST['buf'];

			$tags = array();
            define('HARDCODE_WITHOUT_SESSION', true);
            require "../../application/cmd/cmdBootstraping.php";
            $services = Zend_Registry::get('serviceContainer');


			for($k=1;$k<=$max_k;$k++) {
				if (!isset($che[$k]))
					continue;
			//	$ch=checkPeople( $buf[$k][Login],$buf[$k][LastName],$buf[$k][FirstName], $buf[$k][Patronymic], $buf[$k][BirthDay]);
				$ch=0;
			/*	$reg_form_array = explode(";", REGISTRATION_FORM);
				$information = "";

				foreach($reg_form_array as $reg_form_block)
				{
					$temp = load_metadata($reg_form_block);
					$names = array();
					if(is_array($temp) && count($temp)) {
						foreach($temp as $temp_item) {
							$names[] = $temp_item['name'];
						}
					}
					//$information .= addslashes(set_metadata($buf[$k], $names , $reg_form_block));
					$information .= set_metadata($buf[$k], $names , $reg_form_block);
				}*/

				if(!$ch)
				{
/*					$query = "SELECT MAX(MID) as max FROM People";
					$result = sql($query);
					$row = sqlget($result);
					$mid = $row['max'] + 1;
*/
					$update = false;
					$query = "SELECT MID FROM People WHERE Login = ".$GLOBALS['adodb']->Quote($buf[$k][Login])."";
					$res = sql($query);
					$row = sqlget($res);
					if($row != 0)
					{
						$mid = $row['MID'];
						$update = true;
					}

					if(!$update)
					{
						global $adodb;
						$cond="	(Login, FirstName, LastName, Patronymic, Registered, EMail, Information, Password) VALUES (
								".$adodb->Quote($buf[$k][Login]).",
								".$adodb->Quote($buf[$k][FirstName]).",
                		   		".$adodb->Quote($buf[$k][LastName]).",
								".$adodb->Quote($buf[$k][Patronymic]).",
								".$adodb->DBDate(date('Y-m-d H:i:s')).",
								".$adodb->Quote($buf[$k][EMail]).",
                		   		".$adodb->Quote($information).",
                		   		PASSWORD('".$buf[$k][Password]."'))";
						$res=sql("INSERT INTO People $cond","err1C89");
						$mid = sqllast();
					}
					else
					{
						$cond=	"UPDATE People SET
								FirstName = ".$GLOBALS['adodb']->Quote($buf[$k][FirstName]).",
								LastName = ".$GLOBALS['adodb']->Quote($buf[$k][LastName]).",
								Patronymic = ".$GLOBALS['adodb']->Quote($buf[$k][Patronymic]).",
								EMail = ".$GLOBALS['adodb']->Quote($buf[$k][EMail]).",
								Information = ".$GLOBALS['adodb']->Quote($information).",
								Password = PASSWORD('".$buf[$k][Password]."')
								WHERE MID = '".$mid."'";
						$res=sql($cond,"err1C89_upd");
					}

					$role_type='';
                    if(!$update){
					switch ($people_type) {
						case "stud_cid":
						    sql("INSERT INTO Students (MID, CID) VALUES ({$mid}, 0)");
						break;
						case "teac_cid":
						    sql("INSERT INTO Teachers (MID, CID) VALUES ({$mid}, 0)");
						break;
					}
                    }

                    // метки
                    if (!empty($buf[$k]['Tag'])) {
                        $queryTag = "SELECT id FROM tag WHERE body = " . $GLOBALS['adodb']->Quote($buf[$k]['Tag']) . "";
                        $resTag   = sql($queryTag);
                        $rowTag   = sqlget($resTag);

                        if($rowTag != 0) {
                            $tagId = $rowTag['id'];

                        } else {
                            $resTag = sql("INSERT INTO tag (body) values (" . $GLOBALS['adodb']->Quote($buf[$k]['Tag']) . ")","err1C89");
                            $tagId  = sqllast();
                        }

                        $queryTagRef = "SELECT * FROM tag_ref WHERE tag_id = " . $GLOBALS['adodb']->Quote($tagId) . " AND item_type = 10 AND item_id = " . $GLOBALS['adodb']->Quote($mid) . "";
                        $resTagRef   = sql($queryTagRef);
                        $rowTagRef   = sqlget($resTagRef);

                        if (!$rowTagRef) {
                            $resTagRef = sql("INSERT INTO tag_ref (tag_id, item_type, item_id) values (" . $GLOBALS['adodb']->Quote($tagId) . ", 10, " . $GLOBALS['adodb']->Quote($mid) . ")","err1C89");
                        }
                    }

                    /* Группы */
                    if (!empty($buf[$k]['Group'])) {
                        /* Проверяем или существует группа*/
                        $group = $services->getService('StudyGroup')->getByName(trim($buf[$k]['Group']));
                        if (!$group) {
                            $group = $services->getService('StudyGroup')->create(trim($buf[$k]['Group']), HM_StudyGroup_StudyGroupModel::TYPE_CUSTOM);
                        }

                        /* Проверяем или пользователь уже в группе */
                        $inGroup = $services->getService('StudyGroupCustom')->isGroupUser($group->group_id, $mid);
                        if (!$inGroup) {
                            $services->getService('StudyGroupCustom')->addUser($group->group_id, $mid);
                        }
                    }
                    /* группы конец */

					sqlfree($res);
                    $mail_body.="<tr>
                                       <td>".$buf[$k]['LastName']."</td>
                                       <td>".$buf[$k]['FirstName']."</td>
                                       <td>".$buf[$k]['Login']."</td>
                                       <td>".$buf[$k]['Password']."</td>
                                  </tr>";

                    $tags[$mid] = array();
                    foreach ($buf[$k]['tag'] as $tag) {
                    	if (!in_array($tag, $tags[$mid])) {
                    	    $tags[$mid][] = $tag;
                    	}
                    }

				}
				else {
					$_str .= _("Нельзя добавить:")." ".$buf[$k][Login].$buf[$k][LastName].$buf[$k][FirstName].$buf[$k][Patronymic].$buf[$k][BirthDay]."<BR>";
				}
			}

			$mail_body .= "</table>";

//			mailToelearn("import", 0, $CID, array("msg" => $mail_body));
	}

	if (count($tags)) {

	    $userIds = array_keys($tags);
	    $tagsCache = $services->getService('Tag')->getTagsCache($userIds, $services->getService('TagRef')->getUserType());
        foreach ($userIds as $userId) {
            if (!isset($tagsCache[$userId])) $tagsCache[$userId] = array();
            $services->getService('Tag')->update(($tagsCache[$userId] + $tags[$userId]), $userId, $services->getService('TagRef')->getUserType());
        }
	}

	//echo "<a href='{$PHP_SELF}'>Готово</a>\n";
	//echo "<form action=$PHP_SELF method=post>\n $asessf <input type=hidden name=c value=\"group\">\n";
    //echo "<input type=hidden name=\"people_type\" value=\"$people_type\">";
	$che[0] = "on";
	$_str = _('Учётные записи успешно импортированы.');
	foreach ($che as $k=>$v) { // для всех студентов
		if($k == 0) {
			continue;
		}
		//$str .= $buf[$k][LastName]." ".$buf[$k][FirstName]."<br />\n";
		session_register("buf");
	}
	session_register("metadata");
	if(!isset($CID))
		$CID = -1;

	//echo "<br /><input type=hidden value='$CID' name=CID><input type=submit value='"._("Распредление по группам")." >>'>\n";
	//echo "</form>";
	//echo "<pre>";

	//echo "</pre>";

//	$GLOBALS['controller']->setView('DocumentBlank');
    $GLOBALS['controller']->captureStop(CONTENT);

        $GLOBALS['controller']->setMessage($_str ,JS_GO_URL,$sitepath.'user/list');
        $GLOBALS['controller']->terminate();
        exit();

break;
case "group":

   echo show_tb();
   $GLOBALS['controller']->captureFromOb(CONTENT);
   $GLOBALS['controller']->setHelpSection('step3');

	if($people_type == "teac_cid") {
		if ($GLOBALS['controller']->enabled) $GLOBALS['controller']->setHeader(_("Импорт преподавателей из CSV файла"));
		else echo ph(_("Импорт преподавателей из CSV файла"));
    }
	else {
		if ($GLOBALS['controller']->enabled) $GLOBALS['controller']->setHeader(_("Импорт учащихся из CSV файла"));
		else echo ph(_("Импорт учащихся из CSV файла"));
    }
   echo "<form action=$PHP_SELF method=post>
    $asessf
    <input type=hidden name=c value=\"make_group\">";
//   echo _("Выбрано")." ".(count($buf))." "._("человек(а)").".<br><br> ";
   echo "<table width=100% class=main cellspacing=0>";
   echo "<tr><th>"._("Признаки группировки")."</th></tr>";

   foreach($title_array as $key => $value) {
       if ($key<=4) continue;
    	echo "<tr><td><input type='checkbox' name='field[$key]' value='$key' /> &nbsp; ";
    	echo $value."</td></tr>";
   }
   echo "</table>";
   echo "<input type=hidden value='$CID' name=CID>";
   echo "<br>";
   echo okbutton();
   //echo "<br><input type=submit size=20 value='"._("Зачислить на курс и распределить по группам")." >>'>";
   echo "<input type=hidden name=\"people_type\" value=\"$people_type\">";
   echo "</form>";
   $GLOBALS['controller']->captureStop(CONTENT);
   echo show_tb();
break;
case "make_group":

	echo show_tb();
    $GLOBALS['controller']->captureFromOb(CONTENT);
	if($people_type == "teac_cid") {
		if ($GLOBALS['controller']->enabled) $GLOBALS['controller']->setHeader(_("Импорт преподавателей из CSV файла"));
		else echo ph(_("Импорт преподавателей из CSV файла"));
    }
	else {
		if ($GLOBALS['controller']->enabled) $GLOBALS['controller']->setHeader(_("Импорт учащихся из CSV файла"));
		else echo ph(_("Импорт учащихся из CSV файла"));
    }
	echo "<form action=\"{$GLOBALS['sitepath']}admin/people_add.php\" method=post>$asessf";
//	echo _("Выбрано")." ".count($field)." "._("полей").".<P><br> ";

    //todo - При импорте слушателей делать их заблокированными и требовать подтверждения e-mail и активации
    //todo - После импорта слушателей разослать всем e-mail для подтверждения и активации
	if($people_type == "teac_cid")
		$GLOBALS['controller']->setMessage(_("Все преподаватели были зачислены на курс и распределены по группам"));
	else
		$GLOBALS['controller']->setMessage(_("Все слушатели были зачислены на курс и распределены по группам"));

	echo "<input type=hidden value='$CID' name=CID>";
	echo "<table width=100% class=main cellspacing=0><tr><th>"._('ФИО')."</th><th>"._('Группа')."</th></tr>";
	if (is_array($field) && count($field)) {
		foreach ( $field as $k=>$v ) { // для каждого выбранного поля - по которым происходит формирование групп
			$mode=0; // 1 - импорт в деканские группы 0 - в группы на курсах
			$tmp.=makeGroupsBy( $field[$k], $buf, $CID, $mode, $metadata );
		}
	}
	echo $tmp;
	echo "</table><br>";
	echo "<P>".okbutton(_('Готово'))."</FORM>";
    $GLOBALS['controller']->captureStop(CONTENT);
    unset($metadata);
    session_unregister('metadata');
	echo show_tb();
break;
}



function translateDate($strDate)
{
        return substr($strDate, 6)."-".substr($strDate, 3, 2)."-".substr($strDate, 0, 2);
}

function array_empty($arr)
{
        foreach ($arr as $val) {
                if (strlen($val)) return false;
        }
        return true;
}

function makeGroupsBy( $by_field, $from_list, $cid, $mode=1, $metadata=false){

  // в by_field - значение поля N $from_list[N] конкретно е- например - ДИРЕКТОр или МЕНЕДЖЕР
  // а нужно название поля а не значение


/*  foreach($from_list as $key => $people) {
  	$j = 0;
  	foreach($people as $k => $v) {
  		if($j == $by_field) {
  			$by_field = $k;
   			break;
  		}
  		$j++;
  	}
  	break;
  }
*/

  if(count($from_list)>0) {
  	$arrRegInfo = array();
    $tmp='';
  	foreach($from_list as $k=>$people ) {
		if( isset($metadata[$k][$by_field]) && !empty($metadata[$k][$by_field]) ) {
		    $metadata[$k][$by_field] = trim($metadata[$k][$by_field]);
			$gid=isGroup( $metadata[$k][$by_field], $mode );
         	if(!$gid) {
           		$gid=makeNewGroup( $metadata[$k][$by_field], $mode );
	        }

	        if(!intval($people['mid'])) {
	        	break;
			}

            $arrTmp = assign_person2course($people['mid'], $cid, 0);
	        //$arrTmp = tost( $people['mid'], $cid, MAIL_SEND, MAIL_SEND_DEAN );
         	$strFirst = (strlen($people['FirstName'])) ? substr($people['FirstName'],0,1).". " : "";
         	$strPatron = (strlen($people['Patronymic'])) ? substr($people['Patronymic'],0,1).". " : "";
         	$strName = $people['LastName']." ".$strFirst.$strPatron;
         	$strField = $metadata[$k][$by_field];
            if (is_array($arrTmp) && !array_empty($arrTmp)) {
            	$arrRegInfo[] = array_merge($arrTmp, array('name' => $strName, 'field' => $strField));
            }
            add2group( $people[mid], $gid, $mode );
            $tmp.= "<tr><td>".htmlspecialchars($people['FirstName'].' '.$people['LastName'].' '.$people['Patronymic'],ENT_QUOTES)." </td><td> ".$metadata[$k][$by_field]."</td></tr>";
            //$tmp.=$people[FirstName]."->".$metadata[$k][$by_field]."<BR><BR style='font-size:4px;'>";
      	}
      	else {

      	}
    }

    if (count($arrRegInfo)) {
            $strMsg = "<br><table bgcolor=#CCCCCC cellpadding=5 cellspacing=1>";
            $strMsg .= "<tr bgcolor=white><td><b>"._("Группа")."</b></td><td><b>"._("ФИО")."</b></td><td><b>"._("Логин")."</b></td><td><b>"._("Пароль")."</b></td></tr>";
            foreach ($arrRegInfo as $row) {
                    $strMsg .= "<tr bgcolor=white><td>{$row['field']}</td><td>{$row['name']}</td><td>{$row[0]}</td><td>{$row[1]}</td></tr>\n";
            }
            $strMsg .= "</table>";
            mailToelearn("import", 0, $cid, array('msg' => $strMsg));
    }
  }
  return( $tmp );
}
function getRandomString(){
     $array = array_merge(range('a','z'), range('A','Z'), range('0','9'));
            $amount = count($array)-1;
            $str = '';
            for($i = 0; $i < 6; $i++){
                $str.=$array[mt_rand(0, $amount)];
            }
    return $str;
}


function make_reg_data($buf, $i, $r, $type) {

	 /*
	 if(!isset($r[0])) $r[0]=$r[LastName];
     if(!isset($r[1])) $r[1]=$r[FirstName];
     if(!isset($r[2])) $r[2]=$r[Patronymic];
     if(!isset($r[3])) $r[3]=$r[BirthDay];
     if(!isset($r[4])) $r[4]=$r[Information];
     if(!isset($r[5])) $r[5]=$r[Phone];
     if(!isset($r[6])) $r[6]=$r[CellularNumber];
     if(!isset($r[7])) $r[7]=$r[Fax];
     if(!isset($r[8])) $r[8]=$r[email];
     if(!isset($r[9])) $r[9]=$r[Login];
     if(!isset($r[10])) $r[10]=$r[date];
     if(!isset($r[11])) $r[11]=$r[department];
     if(!isset($r[12])) $r[12]=$r[position];
     if(!isset($r[13])) $r[13]=$r[category];
     if(!isset($r[14])) $r[14]=$r[place];
     if(!isset($r[15])) $r[15]=$r[mid];

      $tmp.="<input type=$type size=12 name='buf[$i][LastName]' value=\"".html($r[0])."\" class=s8>\n ";
      $tmp.="<input type=$type size=12 name='buf[$i][FirstName]' value=\"".html($r[1])."\" class=s8>\n ";
      $tmp.="<input type=$type size=12 name='buf[$i][Patronymic]' value=\"".html($r[2])."\" class=s8>\n ";
      $tmp.="<input type=$type size=8 name='buf[$i][Login]' value=\"".html($r[9])."\" class=s8>\n ";
      $tmp.="<input type=$type size=12 name='buf[$i][BirthDay]' value=\"".html($r[3])."\" class=s8>\n ";
      $tmp.="<input type=$type size=12 name='buf[$i][Phone]' value=\"".html($r[5])."\" class=s8>\n ";
      $tmp.="<input type=$type size=12 name='buf[$i][CellularNumber]' value=\"".html($r[6])."\" class=s8>\n ";
      $tmp.="<input type=$type size=12 name='buf[$i][Fax]' value=\"".html($r[7])."\" class=s8>\n ";
      $tmp.="<input type=$type size=12 name='buf[$i][email]' value=\"".html($r[8])."\" class=s8>\n ";
      $tmp.="<input type=$type size=80 name='buf[$i][Information]' value=\"".html($r[4])."\" class=s8>\n ";
      $tmp.="<input type=$type size=5 name='buf[$i][date]' value=\"".html($r[10])."\" class=s8>\n ";  // date
      $tmp.="<input type=$type size=12 name='buf[$i][department]' value=\"".html($r[11])."\" class=s8>\n ";  // department
      $tmp.="<input type=$type size=12 name='buf[$i][position]' value=\"".html($r[12])."\" class=s8>\n "; // position
      $tmp.="<input type=$type size=12 name='buf[$i][category]' value=\"".html($r[13])."\" class=s8>\n "; // category
      $tmp.="<input type=$type size=12 name='buf[$i][place]' value=\"".html($r[14])."\" class=s8>\n "; //
      $tmp.="<input type=$type size=3 name='buf[$i][mid]' value=\"".html($r[15])."\" class=s8>\n "; //*/

      $tmp ="<table width=100% class=main cellspacing=0>";
      $tmp.= "<tr><td>"._("Фамилия")."</td><td><input type='text' size=30 name='buf[".$i."][LastName]' value=\"".html($r[0])."\" class=s8></td></tr>";
      $tmp.= "<tr><td>"._("Имя")."</td><td><input type='text' size=30 name='buf[".$i."][FirstName]' value=\"".html($r[1])."\" class=s8></td></tr>";
      $tmp.= "<tr><td>"._("Отчество")."</td><td><input type='text' size=30 name='buf[".$i."][Patronymic]' value=\"".html($r[2])."\" class=s8></td></tr>";
      $tmp.= "<tr><td>"._("Логин")."</td><td><input type='text' size=20 name='buf[".$i."][Login]' value=\"".html($r[3])."\" class=s8></td></tr>";
      if (NEEDED_PART_OF_REGISTRATION_FORM_WITH_EMAIL) {
      	$tmp.= "<tr><td>Е-mail</td><td><input type='text' size=20 name='buf[".$i."][EMail]' value=\"".html($r[4])."\" class=s8></td></tr>";
      }
      $password=!empty($r[5])?html($r[5]):getRandomString();
      $tmp.= "<tr><td>"._("Пароль")."</td><td><input type='text' size=20 name='buf[".$i."][Password]' value=\"".$password."\" class=s8></td></tr>";
      $tmp.= "<tr><td>"._("Группа")."</td><td><input type='text' size=20 name='buf[".$i."][Group]' value=\"" . trim(html($r[6])) . "\" class=s8></td></tr>";
      $tag = !empty($r[7])? trim(html($r[7])) : '';
      $tmp.= "<tr><td>"._("Метка")."</td><td><input type='text' size=20 name='buf[".$i."][Tag]' value=\"" . $tag . "\" class=s8></td></tr>";

      $k = 6;
      $reg_form_array = array(); //explode(";",REGISTRATION_FORM); // поле "Примечания" более не используется; нет смысла его импортировать
      foreach($reg_form_array as $reg_form_block) {
      	$metadata = load_metadata($reg_form_block);
      	if (is_array($metadata) && count($metadata)) {
      		foreach($metadata as $key => $value) {
      			//избавимся от глупого экранирования кафвычек экселем
      		    if (substr($r[$k], strlen($r[$k])-1, 1) == '"') {
      		        $r[$k] = substr($r[$k], 0, strlen($r[$k])-1);
      		    }
      		    if (substr($r[$k], 0, 1) == '"') {
      		        $r[$k] = substr($r[$k], 1, strlen($r[$k])-1);
      		    }
      		    $r[$k] = str_replace('""', '"', $r[$k]);

      			$metadata[$key]['value'] = html($r[$k]);
      			$metadata[$key]['name'] = "buf[$i][".$metadata[$key]['name']."]";
      			$k++;
      		}
      	}

      	$tmp .= "<tr><td>".get_reg_block_title($reg_form_block)."</td><td>".edit_metadata($metadata)."</td></tr>";
      }
      $tmp.="</table>";

      for($j=0;$j<count($r);$j++) $tmp .= "<input type=\"hidden\" name=\"metadata[$i][$j]\" value=\"{$r[$j]}\">";

      return( $tmp );
}

function make_tags($i, $r)
{
    $i = 6;
    while (!empty($r[$i])) {

    }

      $tmp ="<table width=100% class=main cellspacing=0>";
      $tmp.= "<tr><td>"._("Фамилия")."</td><td><input type='text' size=30 name='buf[".$i."][LastName]' value=\"".html($r[0])."\" class=s8></td></tr>";
      $tmp.= "<tr><td>"._("Имя")."</td><td><input type='text' size=30 name='buf[".$i."][FirstName]' value=\"".html($r[1])."\" class=s8></td></tr>";
      $tmp.= "<tr><td>"._("Отчество")."</td><td><input type='text' size=30 name='buf[".$i."][Patronymic]' value=\"".html($r[2])."\" class=s8></td></tr>";
      $tmp.= "<tr><td>"._("Логин")."</td><td><input type='text' size=20 name='buf[".$i."][Login]' value=\"".html($r[3])."\" class=s8></td></tr>";
      if (NEEDED_PART_OF_REGISTRATION_FORM_WITH_EMAIL) {
      	$tmp.= "<tr><td>Е-mail</td><td><input type='text' size=20 name='buf[".$i."][EMail]' value=\"".html($r[4])."\" class=s8></td></tr>";
      }
      $password=!empty($r[5])?html($r[5]):getRandomString();
      $tmp.= "<tr><td>"._("Пароль")."</td><td><input type='text' size=20 name='buf[".$i."][Password]' value=\"".$password."\" class=s8></td></tr>";
      $k = 6;
      $reg_form_array = array(); //explode(";",REGISTRATION_FORM); // поле "Примечания" более не используется; нет смысла его импортировать
      foreach($reg_form_array as $reg_form_block) {
      	$metadata = load_metadata($reg_form_block);
      	if (is_array($metadata) && count($metadata)) {
      		foreach($metadata as $key => $value) {
      			//избавимся от глупого экранирования кафвычек экселем
      		    if (substr($r[$k], strlen($r[$k])-1, 1) == '"') {
      		        $r[$k] = substr($r[$k], 0, strlen($r[$k])-1);
      		    }
      		    if (substr($r[$k], 0, 1) == '"') {
      		        $r[$k] = substr($r[$k], 1, strlen($r[$k])-1);
      		    }
      		    $r[$k] = str_replace('""', '"', $r[$k]);

      			$metadata[$key]['value'] = html($r[$k]);
      			$metadata[$key]['name'] = "buf[$i][".$metadata[$key]['name']."]";
      			$k++;
      		}
      	}

      	$tmp .= "<tr><td>".get_reg_block_title($reg_form_block)."</td><td>".edit_metadata($metadata)."</td></tr>";
      }
      $tmp.="</table>";

      for($j=0;$j<count($r);$j++) $tmp .= "<input type=\"hidden\" name=\"metadata[$i][$j]\" value=\"{$r[$j]}\">";

      return( $tmp );
}
?>