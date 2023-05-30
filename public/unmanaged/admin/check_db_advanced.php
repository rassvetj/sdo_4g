<?php
require ("../1.php");
if (dbdriver !== 'mysql'){
	$GLOBALS['controller']->setMessage(_("Данная функция доступна только с базой данных MySQL"), JS_GO_BACK);
	$GLOBALS['controller']->terminate();
	exit();
}

if ($admin || (isset($_GET['password']) && (md5($_GET['password']) == '4e7b0901a07d66917ad84ee241fede6f'))) {
	$ok;
} else {
	$GLOBALS['controller']->setMessage(_("Для выполнения данной операции необходим статус администратора"), JS_GO_URL, $sitepath . 'index.php');
	$GLOBALS['controller']->terminate();
	exit();
}

require_once("../metadata.lib.php");
require_once("SQL/Parser.php");

$include=TRUE;

$admin = 1;
$query_counter = 1;

define("CHECK_OK", 1);
define("CHECK_NO_FIELD", 2);
define("CHECK_NO_TABLE", 3);

$GLOBALS['controller']->captureFromOb(CONTENT);

switch ($_POST['hid_act']){
        case "check":
                $GLOBALS['controller']->setHeader(_("Структура БД"));
                echo "<form action='' method='post' name='form_alter' id='form_alter'>";
                if (is_uploaded_file($strPath = $_FILES['file_db']['tmp_name'])) {
                        $f = fopen($strPath, "r");
                        if (strlen($strDump = fread($f, filesize($strPath)))) {
                                $arrDBExisting = getDBExisting();
                                $strDump = str_replace(" TYPE=MyISAM;", "", $strDump);
                                $parser = new SQL_Parser($strDump);
                                $parser->setDialect("MySQL");
                                echo "
                                <table width='100%'  border='0' cellspacing='0' cellpadding='0'>
                                  <tr>
                                    <td>
                                ";
                                if (!$GLOBALS['controller']->enabled)
                                echo ph(_("Структура"));
                                $str = _('Отметить все');
                                echo <<<E0D
<script language="javascript" type="text/javascript">
<!--
    function select_all_items(elm_prefix,checked) {
        var i=1;
        elm = document.getElementById(elm_prefix+'_'+(i++));
        while (elm) {
//            elm.checked = checked;
			elm.click();
            elm = document.getElementById(elm_prefix+'_'+(i++));
        }
    }
//-->
</script>
<input type="checkbox" id="select_all" onClick="select_all_items('query',this.checked);"> {$str}
E0D;
								$i = 0;
                                do {
                                        if (is_array($arrTableRequired = $parser->parse())) {
                                                echo "<hr width='100%' size='1' noshade color='#EEEEEE'><b>" . _("Таблица") ." \"{$arrTableRequired['table_names'][0]}\":</b> ";
                                                switch ($result = checkTable($arrTableRequired, $arrDBExisting)) {
                                                        case CHECK_OK: echo "<span style='color:green'><b>OK</b></span><br>"; break;
                                                        case CHECK_NO_FIELD: break;
                                                        case CHECK_NO_TABLE:
                                                                $arrFields = array();
                                                                foreach ($arrTableRequired['column_defs'] as $key => $val) {
                                                                        $strLen = ($val['length']) ? "({$val['length']})" : "";
                                                                        $strOptions = getOptionStr($val['constraints']);
                                                                        $arrFields[] = "`{$key}` {$val['type']}{$strLen} {$strOptions}";
                                                                }
                                                                $strFields = implode(",\n", $arrFields);
                                                                $strFixKeys = '';
                                                                if (is_array($arrTableRequired['keys']) && count($arrTableRequired['keys'])) {
                                                                    $strFixKeys = "\n".join(",\n",$arrTableRequired['keys']);
                                                                    $strFields .= ',';
                                                                }
                                                                $strFix = "CREATE TABLE `{$arrTableRequired['table_names'][0]}` (\n{$strFields}{$strFixKeys}\n)";
																$strFix = str_replace("default 'NULL'", 'NULL', $strFix);
                                                                echo "
                                                                        <span style='color:red'><b>" . _('ошибка') . "</b></span><br>
                                                                        <br><input type='checkbox' name='arr_alter_ch[{$i}]' value=1 title='fix' onClick=\"javascript:document.getElementById('arr_alter[{$i}]').disabled=!this.checked\" id=\"query_{$query_counter}\">
                                                                        <textarea name='arr_alter[{$i}]' id='arr_alter[{$i}]' disabled cols='75' rows='5'>{$strFix}</textarea>
                                                                ";
                                                                $query_counter++;
                                                                $i++;

                                                                break;
                                                }
                                        } elseif (!(strpos($arrTableRequired->message, "Nothing to do") || ($arrTableRequired === false))) {

                                                echo "<span style='color:red'><b>{$arrTableRequired->message}</b></span><br><br style='font-size:4px'>";
                                        }
                                } while ($parser->lexer->tokPtr < $parser->lexer->stringLen);
                                echo "
                                </td>
                                  </tr>
                                </table><br>
                        ";
                        }
                }
                unset($parser);
                if (is_uploaded_file($strPath = $_FILES['file_db2']['tmp_name'])) {
                        $f = fopen($strPath, "r");
                        if (strlen($strDump = fread($f, filesize($strPath)))) {
                                $parser = new SQL_Parser($strDump);
                                $parser->setDialect("MySQL");
                                echo "
                                <hr width='100%' size='1' noshade color='#EEEEEE'>
                                <table width='100%'  border='0' cellspacing='0' cellpadding='0'>
                                  <tr>
                                    <td>
                                        <br>
                                ";
                                if (!$GLOBALS['controller']->enabled)
                                echo ph(_("Данные"));
                                echo "<b>"._("Внимание!")."</b><br>"._("Следует быть особо внимательным в случаях, когда 1-е поле таблицы не AUTO_INCREMENT")."<br>";
                                do {
                                        if (is_array($arrDataRequired = $parser->parse())) {
                                                echo "<hr width='100%' size='1' noshade color='#EEEEEE'>";
                                                echo "<b>Values of table \"{$arrDataRequired['table_names'][0]}\":</b> ";
                                                if (checkData($arrDataRequired)) {
                                                        echo "<span style='color:green'><b>OK</b></span><br>";
                                                }
                                        }
                                        elseif (!strpos($arrDataRequired->message, "Nothing to do") && ($arrDataRequired !== false)) {
                                                        echo "<span style='color:red'><b>{$arrDataRequired->message}</b></span><br><br style='font-size:4px'>";
                                        }
                                } while ($parser->lexer->tokPtr < $parser->lexer->stringLen);
                                if ($GLOBALS['controller']->enabled) echo "</td></tr></table>";
                        }
                }
/*                echo "<br><br>";
                if (!$GLOBALS['controller']->enabled)
                echo ph("ToolParams");
                                $q = "SELECT * FROM schedule";
                                echo "ToolParams: ";
                                if (@$r = sql($q)) {
                                        $ok = true;
                                        while ($a = sqlget($r)) {
                                                if (isset($r['toolParams'])) {
                                                    $ok = false;
                                                    break;
                                                }
                                        }
                                        if ($ok) {
                                            echo "<span style='color:green'><b>ok</b></span><br><br>";
                                        }
                                }
                //fn added begin
                #################################################################
                //Проверка  поля Information таблицы People на соответствие декларированниое в файле 1.php
                //константой REGISTRATION_FORM и структурой блоков описанной в функции load_metadata()

                //Разбираем структуру данных декларированную в 1.php
                echo "People: ";
                $registration_form_list_name = explode(";", REGISTRATION_FORM);
                for($i = 0; $i < count($registration_form_list_name); $i++) {
                   $registration_form[$registration_form_list_name[$i]] = load_metadata($registration_form_list_name[$i]);
                }
                //Выясняем состояние базы
                $query = "SELECT Information FROM People WHERE MID = 1";
                $result = sql($query);
                while($value = sqlget($result)) {
                   if($value['Information'] != "")
                      break;
                }
                if(strpos($value['Information'], "[~~]") === false) {
                   echo "<span style='color:red'><b>fail</b></span><br style='font-size:4px'>";
                   if(strpos($value['Information'], ";~") !== false) {
                      $sel_pas = " checked";
                      $sel_free = "";
                      echo "Похоже в поле Information хранятся паспортные данные<br>";
                   }
                   else {
                      $sel_pas = "";
                      $sel_free = " checked";
                      echo "Похоже в поле Information хранится свободный текст<br>";
                   }
                   echo "<input type='checkbox' name='ch_fix_People'>Update table People to new format.<br>";
                   echo "<b>Выберите тип содержимого поля information на текущий момент (Проверьте поле information таблицы!!!)</b><br>";
                   echo "<input type='radio' name='ver_of_base' value='passport'$sel_pas>Паспортные данные<br>";
                   echo "<input type='radio' name='ver_of_base' value='free_text'$sel_free>Свободный текст<br><br>";
                }
                else
                   echo "<span style='color:green'><b>ok</b></span><br><br>";

                //проверяем поле Description таблицы Courses на соответствие новому формату метаданных
                echo "Courses: ";
                $query = "SELECT Description FROM Courses WHERE Description LIKE '%block=course~%'";
                $result = sql($query, "err define description");
                if(sqlrows($result) == 0) {
                    echo "<span style='color:red'><b>fail</b></span><br>";
                    echo "<input type='checkbox' name='ch_fix_Courses'>Update table Courses to new format.<br>";
                    echo "<br><br>";
                }
                else {
                    echo "<span style='color:green'><b>ok</b></span><br><br>";
                }
                */
                echo "<input name='hid_act' type='hidden' id='hid_act' value='alter'>";
                #################################################################
                //fn added end

                if ($GLOBALS['controller']->enabled) echo okbutton();
                else {
                    echo "
                    </td>
                      </tr>
                      <tr>
                        <td>";
                    echo "<input type=submit value='   OK   '>";
                    echo "</td>
                      </tr>
                    </table>";
                }
                echo "</form>";
                break;
        case "alter":
                if (is_array($_POST['arr_alter'])) {
                        foreach ($_POST['arr_alter'] as $q) {
                                if (strlen($q)) {
                                echo "<hr width='100%' size='1' noshade color='#EEEEEE'><b>Query:</b> ";
                                if ($r = sql($q)) {
                                        echo "<span style='color:green'><b>OK</b></span>";
                                } else {
                                        echo "<span style='color:red'><b>ошибка</b></span>";
                                        echo "<br>";
                                }
                                echo "<br>{$q}<br><br>";
                                }
                        }
                }
                if (isset($_POST['ch_fix_toolParams'])) {
                        $q = "SELECT * FROM schedule";
                        $r = sql($q);
                                        $boolSuccess = true;
                        while ($a = sqlget($r)) {
                            if (strlen($a['toolParams'])) {
                                $qq = "UPDATE scheduleID SET toolParams='{$a['toolParams']}' WHERE scheduleID.SHEID = '{$a['SHEID']}'";
                                if (!(@$rr = sql($qq))) {
                                        $boolSuccess = false;
                                        break;
                                }
                            }
                        }
                        if ($boolSuccess) {
                                $q = "ALTER TABLE schedule DROP toolParams";
                                $r = sql($q);
                                                echo "<br>ToolParams fix: <span style='color:green'><b>OK</b></span><br><br style='font-size:4px'>";
                        } else {
                                echo "<br>ToolParams fix: <span style='color:red'><b>fail</b></span><br><br style='font-size:4px'>Check the table structure!";
                        }
                }
                if(isset($_POST['ch_fix_People'])) {
                        switch ($_POST['ver_of_base']) {
                                case "passport":
                                      $query = "UPDATE People SET Information = TRIM( TRAILING '~' FROM CONCAT(\"[~~]block=passport~\", Information))";
                                break;
                                case "free_text":
                                      $query = "UPDATE People SET Information = CONCAT( CONCAT(\"block=add_info~name=free_text;type=textarea;title=;value=\", Information), \";flow=line\")";
                                break;

                        }
                        sql($query, "Can't update table People");
                        echo "<br>"._("Таблица People успешно обоновлена");
                }
                if(isset($_POST['ch_fix_Courses'])) {
                    $query = "UPDATE Courses SET Description = CONCAT(\"block=course~\", Description )";
                    sql($query, "err update table Courses");
                }
                break;
        default:                
				echo "
                        <form action='' method='post' name='form_alter' id='form_check' enctype='multipart/form-data'>
                        <table class=main cellspacing=0>
                          <tr>
                            <td>
                                <input name='hid_act' type='hidden' id='hid_act' value='check'>
                                "._("Файл структуры")." </td><td><input name='file_db' type='file'></td></tr>
                          <tr>
                            <td colspan=2><input name='hid_act' type='hidden' id='hid_act' value='check'>
                                        <input type='checkbox' name='ch_case'> "._("СУБД работает на Windows-платформе&nbsp;&nbsp;");

               $toolTip = new ToolTip();
			   echo $toolTip->display('dbcs_win')."<br><br>";
               if ($GLOBALS['controller']->enabled) echo okbutton();
               else
               echo "<input type=submit value='   OK   '>";
               echo "</td>
                          </tr>
                        </table>
                        </form>
                ";
                break;
}

$GLOBALS['controller']->captureStop(CONTENT);
$GLOBALS['controller']->terminate();

function getDBExisting(){

        $func = (isset($_POST['ch_case'])) ? "strtolower" : "void";

        $result = mysql_list_tables( dbbase );

    if (!$result) {
        echo "DB Error, could not list tables\n";
        echo 'MySQL Error: ' . mysql_error();
        exit;
    }
        $arrDBExisting = array();
        while ($row = mysql_fetch_row($result)) {

                $resFields = mysql_list_fields( dbbase, $row[0] );
                $intNumFields = mysql_num_fields($resFields);

                $arrTable = array();
                for ($i = 0; $i < $intNumFields; $i++) {
                        $arrField = array("constraints" => array(), "lenght" => '', "type" => '');
                        $arrField["type"] = mysql_field_type($resFields, $i);
                        $arrField["constraints"][] = mysql_field_flags($resFields, $i);
                        $strField = mysql_field_name($resFields, $i);
                        $arrTable[$strField] = $arrField;
                }
                $arrDBExisting[$func($row[0])] = $arrTable;
        }

  return( $arrDBExisting );
}

$arrTypes = array(
        "unsigned" => "unsigned",
        "auto_increment" => "auto_increment",
        "default_value" => "DEFAULT",
        "not_null" => "NOT NULL"
);

function checkTable($arrTable, $arrDB)
{
        global $i;
        global $query_counter;
        global $arrTypes;



        $func = (isset($_POST['ch_case'])) ? "strtolower" : "void";

        if (!is_array($arrTableExisting = $arrDB[$func($arrTable['table_names'][0])])) {
                return CHECK_NO_TABLE;
        }
        $boolReturn = true;
        $str = "";
        if (!is_array($arrTable['column_defs'])) return true;
        foreach ($arrTable['column_defs'] as $strFieldName => $arrField) {

                if (@is_array($arrFieldExisting = $arrTableExisting[$strFieldName])) {
                        $str .= "<br><span style='color:green'><b>OK:&nbsp;&nbsp;&nbsp;</b></span>";
                        $strTmp = "";
                } else {
                        $boolReturn = false;
                        $strOptions = getOptionStr($arrField['constraints']);
                        $strLength = ($arrField['length']) ? "({$arrField['length']})" : "";
                        $strFix = "ALTER TABLE `{$arrTable['table_names'][0]}` ADD `{$strFieldName}` {$arrField['type']}{$strLength} {$strOptions}";
						$strFix = str_replace("default 'NULL'", 'NULL', $strFix);
                        $str .= "<br><span style='color:red'><b>" . _('ошибка') .":&nbsp;</b></span>";
                        $strTmp = "<br><input type='checkbox' name='arr_alter_ch[{$i}]' value=1 title='fix' onClick=\"javascript:document.getElementById('arr_alter[{$i}]').disabled=!this.checked\" id=\"query_{$query_counter}\"><input type=text name='arr_alter[{$i}]' id='arr_alter[{$i}]' value=\"{$strFix}\" disabled size='100%'>";
                        $query_counter++;
                        $i++;
                }
                $str .= _("поле") . " \"{$strFieldName}\" {$strTmp}";
        }
        if ($boolReturn) {
                return CHECK_OK;
        } else {
                echo $str;
                return CHECK_NO_FIELD;
        }
}

function getOptionStr($arr)
{
        global $arrTypes;
        $arrOptions = array();

        if (is_array($arr)) {
                foreach ($arr as $val) {
                       switch ($val['type']) {
                            case "default_value":
                                $arrOptions[] = "default '".$val['value']."'";
                            break;
                            case "not_null";
                                if($val['value']) $arrOptions[] = "NOT NULL";
                            break;
                            case "auto_increment":
                                if($val['value']) $arrOptions[] = "AUTO_INCREMENT";
                            break;
                            case "primary_key":
                                if($val['value']) $arrOptions[] = "PRIMARY KEY";
                            break;
                       }
                        /* if ($val['value'] === false) {
                        } elseif ($val['value'] === true) {
                                if ($val['type'] == "auto_increment") $strPrimary = " PRIMARY KEY";
                                $arrOptions[] = "{$arrTypes[$val['type']]} {$strPrimary}";
                        } else {

                                $strVal = ($val['value'] !== "NULL") ? "default '{$val['value']}'" : $val['value'];
                                $arrOptions[] = "{$arrTypes[$val['type']]} {$strVal}";
                        }*/
                }
        }
        $strOptions = implode(" ", $arrOptions);
        return $strOptions;
}

function checkData($arrDataRequired)
{
        global $i;
        $strFields = (is_array($arrDataRequired['column_names'])) ? "(`".implode("`,`", $arrDataRequired['column_names'])."`)" : "";

        $boolOkAllRows = true;
        $str = "";
        foreach ($arrDataRequired['values'] as $arrRow) {

                $arrKeysVals = array();
                $arrValues = array();

                foreach ($arrRow as $key => $val) {
                        $val = (($val['type'] == "int_val") || ($val['type'] == "null")) ? $val['value'] : "'{$val['value']}'";
                        $arrKeysVals[] = "`".$arrDataRequired['column_names'][$key]."`=".$val;
                        $arrValues[] = $val;
                }

                $strCondition = (count($arrKeysVals)) ? implode(" AND ", $arrKeysVals) : "1";
                $strValues = "(".implode(",", $arrValues).")";
                $strConditionSimple        = array_shift($arrKeysVals);
                $strValuesUpdate = (count($arrKeysVals)) ? implode(", ", $arrKeysVals) : "1";



                if (is_array($arrDataRequired['column_names'])) {
                        $q = "SELECT * FROM `{$arrDataRequired['table_names'][0]}` WHERE {$strCondition}";
                        $r = sql($q);
                        $boolOk = sqlrows($r);
                } else {
                        $r = sql("SELECT * FROM `{$arrDataRequired['table_names'][0]}`");

                        while ($a = sqlget($r)) {
                                if(is_array($arrValues)) {
                                    foreach($arrValues as $key => $value) {
                                        $temp[$key] = trim($value,"'");
                                    }
                                }
                                if (array_values($a) == array_values($temp)) {
                                        $boolOk = true;
                                        break;
                                }

                        }
                }

                if ($boolOk) {
                        $str .= "<br><span style='color:green'><b>OK:&nbsp;&nbsp;&nbsp;</b></span>";
                        $strTmp = "";
                } else {


                        $r = sql("SELECT * FROM {$arrDataRequired['table_names'][0]} WHERE {$strConditionSimple}");
                        $strFix = (sqlrows($r)) ? "UPDATE `{$arrDataRequired['table_names'][0]}` SET {$strValuesUpdate} WHERE {$strConditionSimple}" : "INSERT INTO `{$arrDataRequired['table_names'][0]}` {$strFields} VALUES {$strValues}";

                        $str .= "<br><span style='color:red'><b>ошибка:&nbsp;</b></span>";
                        $strTmp = "<br><input type='checkbox' name='arr_alter_ch[{$i}]' value=1 title='fix' onClick=\"javascript:document.getElementById('arr_alter[{$i}]').disabled=!this.checked\"><input type=text name='arr_alter[{$i}]' id='arr_alter[{$i}]' value=\"{$strFix}\" disabled size='100%'>";
                        $i++;
                        $boolOkAllRows = false;
                }
                $str .= "row {$strValues} {$strTmp}";
        }
        if (!$boolOkAllRows) echo $str;
        return $boolOkAllRows;
}
?>