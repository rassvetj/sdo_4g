<?php

include($_SERVER['DOCUMENT_ROOT']."/adodb/adodb.inc.php");
include("adodb_field_names.inc.php");
//$db = "";

$_arrReserverdWords = array(
	'mssql' => array("begin","end","log","file","default","list","percent","user", "order", "current", "with", "left"),
    'mssqlnative' => array("begin","end","log","file","default","list","percent","user", "order", "current", "with", "left"),
	'oci8' => array("type","date","default","begin","end","start", "stop", "level", "work", "data", "random", "sort", "skip", "file", "sum", "number", "size", "char","user","uid", "sequence", "access", "mode", "order", "comment", "current", "object", "state"),
);


function myconnect() {
   global $adodb;
   $adodb = ADONewConnection(dbdriver);
   $adodb->debug = false;

   if(!$adodb->Connect(dbhost, dbuser, dbpass, dbbase)) {
       die("</table></table></table><table height=95% width=100%><tr><td align=center><Table border=0 bgcolor=#997766><tr><td><Table border=0 bgcolor=#ffffff><tr><td><Table border=0 bgcolor=#997766><tr><td><Table border=0 bgcolor=#ffffff><tr><td align=center><b><font style='font-size:10pt;'>Извините, сервер временно не работает. 200</font></b></td></tr></table></td></tr></table></td></tr></table></td></tr></table></td></tr></table>");
   }
}

function sql($a,$err="",$free=0) {
/*    if (!isset($GLOBALS['sqlcount'])) {
        $GLOBALS['sqlcount'] = 1;
    }
    ob_start();
    debug_print_backtrace();
    $bt = ob_get_contents();
    ob_end_clean();

    putlog("SQL ".$GLOBALS['sqlcount']++." \n$a\n\n$bt\n----------------\n");*/

   global $adodb;
   global $_arrLastInsert;
   static $connect;
   if ($connect<>1) {
      myconnect();
      $connect=1;
   }

   $sql = trim($a);

   parse_back_ticks($sql);
   parse_keywords($sql);
   parse_udf($sql);
   set_dateformat();

   if (is_array($arrLimit = parse_limit($sql))) {
		$res = $adodb->SelectLimit($sql, $arrLimit[1], $arrLimit[0]);
   } else {
	   $res = $adodb->Execute($sql);
   }

   if (!in_array(dbdriver, array('mysql', 'mssql', 'mssqlnative')) && ($arrTmp = parse_insert($sql))) {
   		$_arrLastInsert = $arrTmp;
   }


   if (!$res) {
      if (function_exists("debug_backtrace")) {
          $bt = debug_backtrace();
          $file = "in ".@$bt[0]['file']." on line ".@$bt[0]['line'];
      }
      sqlerror($sql,$err,$file);
//      sqlerror_user_friendly($sql,$err);
      exit();
   }
   if ($free) {
      sqlfree($res);
      return false;
   }
   return $res;
}

//Return number of string of last query
function sqlrows($a) {
   return $a->_numOfRows;
}

//Return content of cell from result row
function sqlres($a, $b, $c) {
   for($i = 0; $i <= $b; $i++) {
      $array_of_row = $a->FetchRow();
   }

   $num_fields = $a->FieldCount();
   for($i = 0; $i <= $num_fields; $i++) {
      $field = $a->FetchField($i);
      if(($c == $field->name) || (strtoupper($c) == $field->name) || (strtolower($c) == $field->name)) {
//         if(dbdriver == "mysql") return $array_of_row[$i];
//         if(dbdriver == "mssql") return iconv("cp866", $GLOBALS['controller']->lang_controller->lang_current->encoding,$array_of_row[$i]);
         return $array_of_row[$i];
      }
   }
}

//Return associative array from result gdfgd
function sqlget(& $a) {
   if($a->_numOfRows == 0) return 0;
   $return_value = $a->GetRowAssoc();
   if ((dbdriver == "oci8")) {
		replace_fields_oci8($return_value, parse_fields($a->sql));
   }
   if(!$a->EOF) $a->MoveNext();
   else return 0;
   return $return_value;
}

function sqlval($a) {
   return @sqlget(sql($a));
}

function sqlvalue($a,$err = false) {
   $res=sql($a,$err);
   if (sqlrows($res)==0) $val=false;
   else $val=sqlres($res, 0, 0);
   sqlfree($res);
   return $val;
}

function sqlgetrow(& $a) {
   if($a->_numOfRows == 0) return 0;
   $return_value = $a->GetRows();
   if(!$a->EOF) $a->MoveNext();
   else return 0;
   foreach ($return_value as $key => $value) {
      if(in_array(dbdriver, array("mssql", 'mssqlnative')))
         $return_value[$key] = iconv("cp866", $GLOBALS['controller']->lang_controller->lang_current->encoding, $return_value[$key]);
   }
   return $return_value;
}

//Free memory
function sqlfree(&$a) {
  if(!empty($a))
     return $a->Close();
  return $a;
}

//Last inserted value of auto_increment field
function sqllast() {
   global $adodb;
   global $_arrLastInsert;
   if (in_array(dbdriver, array('mysql', 'mssql', 'mssqlnative'))) { //also postrgre
	   return $adodb->Insert_ID();
   } else {
   		$q = "SELECT {$_arrLastInsert['field']} FROM {$_arrLastInsert['table']} ORDER BY {$_arrLastInsert['field']} DESC";
   		$r = sql($q);
   		/*if (sqlrows($r)) {
   		    while ($a = sqlget($r)) {
   		        if (intval($a[$_arrLastInsert['field']])) {
   		           return $a[$_arrLastInsert['field']];
   		           break;
   		        }
   		    }
   		}
   		return false;*/
   		return ($a = sqlget($r)) ? $a[$_arrLastInsert['field']] : false;
   }
}

//Errors
function sqlerror($zapros="", $info="", $file="") {
   global $adodb;
   putlog("SQLERROR $file\n $zapros \n $info \n " . $adodb->ErrorMsg() . "\n----------------\n");
//   exit("</table></table></table></table><hr><h3>************ $info ***********<br><br>
//   SQL ERROR..... N".$adodb->ErrorNo().": ".$adodb->ErrorMsg()."</h3><pre>$zapros</pre><hr>");

    if ($GLOBALS['controller']->enabled) {
	$GLOBALS['controller']->page_id = $_GET['page_id'];
	$GLOBALS['controller']->persistent_vars->destroy('page_id');
	$GLOBALS['controller']->setMessage(_("Произошла ошибка базы данных. Обратитесь в службу технической поддержки."), JS_GO_URL, "{$GLOBALS['sitepath']}");
	$GLOBALS['controller']->terminate();
    } else {
        die("$zapros <br> $info <br> " . $adodb->ErrorMsg());
    }
}

function sqlerror_user_friendly($zapros="", $info="") {
   exit("</table></table></table><table height=95% width=100%><tr><td align=center><Table border=0 bgcolor=#997766><tr><td><Table border=0 bgcolor=#ffffff><tr><td><Table border=0 bgcolor=#997766><tr><td><Table border=0 bgcolor=#ffffff><tr><td align=center><b><font style='font-size:10pt;'>Ошибка базы данных</font></b></td></tr></table></td></tr></table></td></tr></table></td></tr></table></td></tr></table>");
}

function sqldupl() {
   global $adodb;
   if (eregi("Duplicate entry",$adodb->ErrorMsg())) return true;
   return false;
}


// проверяет наличие таблицы в БД
function showTableFields( $table ){
   global $adodb;
   $fields = $adodb->MetaColumns($table);
   foreach($fields as $key => $value) {
       echo "<li>".$value->name." ( ".$value->type." ) [".$value->max_length." ]<br>";
  }
}

function getTableFields($table) {
  global $adodb;
  $fields = $adodb->MetaColumns($table);
  return $fields;
}

// выводит все таблицы БД
function showTables( $mode ){
   global $adodb;
   $tables = $adodb->MetaTables("TABLES");
   foreach($tables as $key =>$value) {
      echo "<b>Table: $value</b> lines=".rowsCount( $value )."<br>";
      if($mode) showTableFields($value);
  }
}

function getfieldname($table) {
}

function sqlcols($res) {
   return $res->_numOfFields;
}

function & sqlfetchfield(& $res) {
   static $i;
   if (!isset($i)) $i = 0;
   if($i > sqlcols($res)) return false;
   return $res->FetchField($i++);
}

function sqlfieldlen($res, $i) {
   $field = $res->FetchField($i);
   return $field->max_length;
}

function parse_back_ticks(&$strQuery) {
	$replace_back_ticks_function = "replace_back_ticks_" . dbdriver;
	if (function_exists($replace_back_ticks_function)) {
		$strQuery = $replace_back_ticks_function($strQuery);
	}
}

function replace_back_ticks_mssql($str) {
   return str_replace("`", "", $str);
}

function replace_back_ticks_mssqlnative($str) {
   return replace_back_ticks_mssql($str);
}

function replace_back_ticks_oci8($str) {
   return str_replace("`", "", $str);
}

function parse_keywords(&$strQuery) {
	global $_arrReserverdWords;
	if (isset($_arrReserverdWords[dbdriver])) {
		foreach ($_arrReserverdWords[dbdriver] as $value) {
			replace_keyword($strQuery, $value);
		}
	}
    $replace_keyword_function_quote = "replace_keyword_quote_" . dbdriver . "";
    if (function_exists($replace_keyword_function_quote)) {
        $strQuery = $replace_keyword_function_quote($strQuery);
    }
}

function replace_keyword_quote_mssql($s){
     $s = str_replace('\\\\','\\',$s);
     return str_replace("\\'","''",$s);
}

function replace_keyword_quote_mssqlnative($s){
     $s = str_replace('\\\\','\\',$s);
     return str_replace("\\'","''",$s);
}

function replace_keyword(&$strQuery, $strKeyword) {
	if (strpos($strQuery, $strKeyword) === false) {
		return;
	}
	// a little bit stupid..
	$replace_keyword_function = "replace_keyword_" . dbdriver;
	if (function_exists($replace_keyword_function)) {
		$strKeywordValid = $replace_keyword_function($strKeyword);
	} else {
		return;
	}
    $pattern = "([[:space:]]|[\.,\(\)`=]){1}";
    if(strpos($strQuery,'INSERT') === 0){
        $arrsql = explode(')',$strQuery);
        if (strpos($arrsql[0], $strKeyword) !== false) {
            $arrsql[0] = ereg_replace("({$pattern}){$strKeyword}({$pattern})", "\\1{$strKeywordValid}\\3", $arrsql[0] . " ");
        }
        $strQuery = join(')',$arrsql);
    }elseif(strpos($strQuery,'UPDATE') === 0){
        $rou = strpos($strQuery,'=');
        $sep = " ";
        for($i=1; $sep==" ";$i++){
            $sep = $strQuery[$rou+$i];
       }

        $pre ="/(?<!\\\)\\$sep/";


        $arrsql = preg_split($pre,$strQuery);
        foreach($arrsql as $k=>$v){
            if($k%2==0){
                $v = ereg_replace("({$pattern}){$strKeyword}({$pattern})", "\\1{$strKeywordValid}\\3", $v);
            }
            $arrsql[$k] = $v;
        }
        $strQuery = join("$sep",$arrsql);

    }else{
        $strQuery = ereg_replace("({$pattern}){$strKeyword}({$pattern})", "\\1{$strKeywordValid}\\3", $strQuery . " ");
    }

	//$strWrapper = "([[:space:]]|[\.,\(\)`=]){1}";
	//$strQuery = ereg_replace("({$strWrapper}){$strKeyword}({$strWrapper})", "\\1{$strKeywordValid}\\3", $strQuery . " "); // space for the case of last word in the query
}

function replace_keyword_mssql($strKeyword){
	return "[{$strKeyword}]";
}

function replace_keyword_mssqlnative($strKeyword){
	return replace_keyword_mssql($strKeyword);
}

function replace_keyword_oci8($strKeyword){
	return "{$strKeyword}_";
}

function parse_udf(&$strQuery) {
	$replace_udf_function = "replace_udf_" . dbdriver;
	if (function_exists($replace_udf_function)) {
		$strQuery = $replace_udf_function($strQuery);
	}
}

function set_dateformat() {
	$set_dateformat_function = "set_dateformat_" . dbdriver;
	if (function_exists($set_dateformat_function)) {
		$set_dateformat_function();
	}
}

function set_dateformat_mssql() {
	mssql_query("SET DATEFORMAT ymd");
}

function replace_udf_mssql($str) {
   $str = str_replace("UNIX_TIMESTAMP", "dbo.UNIX_TIMESTAMP", $str);
   $str = str_replace("FROM_UNIXTIME", "dbo.FROM_UNIXTIME", $str);
   $str = str_replace("PASSWORD", "dbo.PASSWORD", $str);
   $str = str_replace("CONCAT", "dbo.CONCAT", $str);
   $str = str_replace("LENGTH", "DATALENGTH", $str);
   $str = str_replace("NOW()", "GETDATE()", $str);
   $str = str_replace("GREATEST", "dbo.GREATEST", $str);
   $str = str_replace("LEAST", "dbo.LEAST", $str);
//   $str = str_replace("IF", "dbo.IF_CUSTOM", $str);
   $str = str_replace("weekday(", "DATEPART(weekday,", $str);
   return $str;
}

function replace_udf_mssqlnative($str) {
    return replace_udf_mssql($str);
}

function replace_udf_oci8($str) {
    global $adodb;
    //$str = str_replace("NOW()", "GETDATE()", $str); // ну нет в oci8 функции GETDATE(). t
    $str = str_replace("NOW()", $adodb->DBDate(date("Y-m-d")), $str);
	$arrPattern = array("weekday\(([^\)]+)\)", "substring\(([^\)]+)\)");
	$arrReplacement = array("to_char(\\1, 'D')", "substr(\\1)");
	while(($strPattern = array_shift($arrPattern)) && ($strReplacement = array_shift($arrReplacement))) {
		$str = eregi_replace($strPattern, $strReplacement, $str);
	}
	return $str;
}

function parse_limit(&$strQuery){
	if (strpos($strQuery, "LIMIT")){
	;
	}
	$strPatt = "(^SELECT.+)[[:space:]]LIMIT[[:space:]](.+)*";
	if (eregi($strPatt, $strQuery, $arrMat)){
		$strQuery = $arrMat[1];
		switch (count($arrLimit = explode(",", $arrMat[2]))) {
			case 2:
				return array((integer)$arrLimit[0], (integer)$arrLimit[1]);
				break;
			case 1:
				return array(0, (integer)$arrLimit[0]);
				break;
			default:
				return false;
		}
	}
}


function parse_insert($strQuery) {
	global $adodb;
	$arrReturn = array();
	$strPatt = "^INSERT[[:space:]]+INTO[[:space:]]+([_a-zA-Z0-9]+).+";
	if (eregi($strPatt, $strQuery, $arrMat)) {
		$arrReturn['table'] = $arrMat[1];
		$arrTmp = $adodb->MetaColumnNames($arrMat[1], true);
		$arrReturn['field'] = array_shift($arrTmp);
		return $arrReturn;
	}
	return false;
}

function get_case_sensetive_field($strTable, $strCaseInsensitive) {
	if (is_array($GLOBALS['_arrFieldNames'][$strTable])) {
		foreach ($GLOBALS['_arrFieldNames'][$strTable] as $value) {
			if ($strCaseInsensitive == strtoupper($value)) return $value;
		}
	}
	return $strCaseInsensitive;
}

function replace_fields_oci8(&$arrRecordset, $arrFieldsCase){
	replace_keyword_oci8_backward($arrRecordset);
	$arrTmp = $arrRecordset;
	if (is_array($arrRecordset)) {
		foreach ($arrRecordset as $key => $value) {
			if (isset($arrFieldsCase[$key])) {
				unset($arrTmp[$key]);
				$arrTmp[$arrFieldsCase[$key]] = $value;
			}
		}
	}
	$arrRecordset = $arrTmp;
}

function parse_fields($strSql) {
	$arrReturn = array();
	$arrExclude = array('DISTINCT', 'distinct');
	//var_dump($strSql);echo"<pre>";
	/*if(strrpos($strSql, ".*")) {
	    die($strSql);
	}*/

//    if (preg_match("/select(.+?)from[\s]+([_0-9a-zA-Z]+)/ims",$strSql, $arrMat)) { // experimental, not tested
	if (eregi("^select(.+)from[[:space:]]+([_0-9a-zA-Z]+)",$strSql, $arrMat)) {
	   if ((trim($arrMat[1]) == "*") && strlen($arrMat[2])) {
	   		if (is_array($arrTableFieldNames = $GLOBALS['_arrFieldNames'][$arrMat[2]])) {
		   		foreach ($arrTableFieldNames as $strField) {
		   			$arrReturn[strtoupper($strField)] = $strField;
		   		}
	   		}
	   } else {
	   	   $arrFields = explode(",", $arrMat[1]);
		   foreach ($arrFields as $value) {
		   		if (count($arrAlias = explode(" as ", $value)) > 1) {
		   			$strToken = trim(array_pop($arrAlias));
		   		} elseif (count($arrAlias = explode(" AS ", $value)) > 1) {
		   			$strToken = trim(array_pop($arrAlias));
		   		} else {
		   			$strToken = trim(str_replace($arrExclude, '', $value));
		   		}
		   		$arrFieldName = explode(".", $strToken);
		   		$strField = array_pop($arrFieldName);
		   		if(trim($strField) == "*") {
	   		      if (is_array($arrTableFieldNames = $GLOBALS['_arrFieldNames'][$arrMat[2]])) {
		   		     foreach ($arrTableFieldNames as $strField) {
		   			    $arrReturn[strtoupper($strField)] = $strField;
		   		     }
                  }
		   		}
		   		$arrReturn[strtoupper($strField)] = $strField;
		   }
	   }
	}

    if (preg_match_all("/join[\s]+([_0-9a-zA-Z]+?)[\s]+on/i",$strSql, $arrMat)) { // experimental! not tested
        if ($arrMat[1]) {
            if (is_string($arrMat[1])) {
                $arrMat[1] = array($arrMat[1]);
            }
            foreach($arrMat[1] as $match) {
                if (false != stristr($match, 'as')) {
                    $parts = explode('as', $match);
                    if (!count($parts)) {
                        $parts = explode('AS', $match);
                    }
                }
                if (count($parts)) {
                    $match = $parts[0];
                }
                if (is_array($arrTableFieldNames = $GLOBALS['_arrFieldNames'][$match])) {
                    foreach ($arrTableFieldNames as $strField) {
                        if (!isset($arrReturn[strtoupper($strField)])) {
                            $arrReturn[strtoupper($strField)] = $strField;
                        }
                    }
                }
            }
        }
    }

	return $arrReturn;
}

function replace_keyword_oci8_backward(&$arrRecordset) {
	global $_arrReserverdWords;
	if (isset($_arrReserverdWords[dbdriver]) && is_array($arrRecordset)) {
		foreach ($_arrReserverdWords[dbdriver] as $strKeyword) {
			if (array_key_exists($key = strtoupper($strKeyword) . "_", $arrRecordset)) {
				$tmp = $arrRecordset[$key];
				unset($arrRecordset[$key]);
				$arrRecordset[$strKeyword] = $tmp;
			}
		}
	}
}

?>