<?


$BORDER="0";

require_once("show_modules.lib.php");
require_once("../courses.lib.php");
//require_once("..\metadata.lib.php");

function startHTML( $title, $style="cPageBG" ){
  global $sitepath;
  $GLOBALS['controller']->setView('DocumentFrame');
  $GLOBALS['controller']->view_root->return_path = "manage_course.php4?CID={$GLOBALS['CID']}";
  $tmp="
    <HTML><head>
    <META content=\"text/html; charset=windows-1251\" http-equiv=\"Content-Type\">
    <TITLE>$title</TITLE>
     <SCRIPT src=\"".$sitepath."js/FormCheck.js\" language='JScript' type='text/javascript'></script>
     <SCRIPT src=\"".$sitepath."js/img.js\" language='JScript' type='text/javascript'></script>
     <SCRIPT src=\"".$sitepath."js/hide.js\" language='JScript' type='text/javascript'></script>
     <title>$title</title>
     <link rel='stylesheet' href='".$sitepath."styles/style.css' type='text/css'>
     </head>
     <BODY  class=$style leftmargin=0 rightmargin=0 marginwidth=0 topmargin=0 marginheight=0>";
  return( $tmp );
}

function stopHTML( $sss ){
  $GLOBALS['controller']->terminate();
  return( "$sss</BODY></HTML>" );
}

function get_cid_list($who,$cid)
{
   global $teacherstable,$coursestable;
   $sql="SELECT ".$coursestable.".CID,Title FROM ".$teacherstable.",".$coursestable." WHERE ".$coursestable.".Status!=0 AND ".$teacherstable.".MID='".$who."' AND ".$teacherstable.".CID=".$coursestable.".CID ORDER by ".$coursestable.".CID";
   $sql_result=sql($sql, "err3234");
   if (sqlrows($sql_result)<0) return 1;
   while($res=sqlget($sql_result))
   {
      echo "<option value=\"".$res['CID']."\"";
      if ($res['CID']==$cid) echo "selected";
      echo ">".$res['Title']."</option>";
   }
   return 0;
}

function count_modules($string_id) {
	$string_ids = explode(";", $string_id);	
    if (is_array($string_ids) && count($string_ids)>1) {
    	foreach($string_ids as $key=>$string_id) {
    		if ($string_id <= 0) {
    			unset($string_ids[$key]);	
    		}
    	}
    	return count($string_ids);
    }
    else {
    	return intval($string_ids[0]) ? 1 : 0;
    }
}

/*function import_button(  $win, $value="Загрузить" ){
   $extra="<input type=button name=bbImp value='$value'
            onclick=\"
             parent.open( $win ,
                         null,'height=400,width=650,status=no,toolbar=no,menubar=no,location=no,scrollbars=yes');\">";
   return( $extra );
} */

function show_add_mod($MID,$CID)
{
  global $sitepath;
//   echo showBox( "teachers/manage_course.php4",  $CID, "Mod", "модуль");
   echo showBoxImport($CID);
}

function get_all_mod_param($PID,$CID,$mod="")
{
   global $mod_list_table,$coursestable;
//   $sql="SELECT ".$mod_list_table.".ModID as ModID,".$coursestable.".Title as course_name,".$mod_list_table.".Title as mod_name,".$mod_list_table.".Descript as descript,".$mod_list_table.".Num as theme,".$mod_list_table.".Pub as pub,".$mod_list_table.".forum_id as forum_id,".$mod_list_table.".test_id as test_id, ".$mod_list_table.".run_id as run_id FROM ".$mod_list_table.",".$coursestable." WHERE ".$coursestable.".Status!=0 AND ".$mod_list_table.".CID='".$CID."' AND ".$mod_list_table.".CID=".$coursestable.".CID";
   $sql="SELECT ".$mod_list_table.".ModID as ModID,".$coursestable.".Title as course_name,".$mod_list_table.".Title as mod_name,".$mod_list_table.".Descript as descript,".$mod_list_table.".Num as theme,".$mod_list_table.".Pub as pub,".$mod_list_table.".forum_id as forum_id,".$mod_list_table.".test_id as test_id, ".$mod_list_table.".run_id as run_id FROM ".$mod_list_table.",".$coursestable." WHERE ".$mod_list_table.".CID='".$CID."' AND ".$mod_list_table.".CID=".$coursestable.".CID";
   if(!empty($mod)) $sql.=" AND ".$mod_list_table.".ModID='".$mod."'";
   $sql.=" ORDER by ".$mod_list_table.".ModID ASC";
   if (!$sql_result=sql($sql,"errgfdf545")) return 0;
   if (sqlrows($sql_result)<0) return 0;
   return $sql_result;
}


function showBox( $action,  $CID, $name, $text, $extra ){
  // name - часть идентификаторов
    global $sitepath;
    global $BORDER;

    $BORDER = 0;

    if (!$GLOBALS['controller']->enabled)
    $tmp="
    <table  width=100% border=0 cellspacing=0 cellpadding=0><tr><td> ";
    $tmp.="    

<table width=100% border=$BORDER cellspacing=\"0\" cellpadding=\"0\" >
   <tr><td>
    
   <form method=GET name=\"add".$name."\" action=\"".$sitepath.$action."\">
   <input type='hidden' name='c' value='new_gr'>
   <table width=100% class=main cellspacing=0>
         <tr>
            <th colspan='2'>"._("Добавить")."
            </th>
         </tr>
         <tr>
            <td>"._("Название")."</td>
            <td><input type='text' size=60 name=\"".$name."Title\" class=lineinput value='"._("Название")."'></td>
         </tr>
         <tr>
         <td colspan='2'>
           <input type='hidden' name='make' value='add".$name."'>
           <input type=\"hidden\" name=\"CID\" class=\"lineinput\" value=\"".$CID."\">".$extra;
           
    if ($GLOBALS['controller']->enabled) {
        $tmp .= okbutton();
    }
    else {
        $tmp .="<input type=\"image\" onMouseOver=\"this.src='".$sitepath."images/send_.gif';\"           
           onMouseOut=\"this.src='".$sitepath."images/send.gif';\" src=\"".$sitepath."images/send.gif\" align=\"right\" alt=\"ok\"
           border=$BORDER>";
    }

    $tmp .= "
         </td>
         </tr>
   </table>
   </form>
   
</td></tr>
</table>";
     
    return($tmp);
}

function get_courses_templates_as_array() {
        $fp = fopen($_SERVER['DOCUMENT_ROOT']."/template/interface/correspondence.csv", "r");
        while($csv_string = fgets($fp, 1000)) {
                if(strpos($csv_string, ";") !== false) {
                        $tmp = explode(";", $csv_string);
                        $return_value[trim($tmp[0])] = trim($tmp[1]);
                }
        }
        return $return_value;
}

function showBoxImport($CID){
          // name - часть идентификаторов
    global $sitepath;
    global $BORDER;

    $courses_templates = get_courses_templates_as_array();
    $template_options = "";
    foreach($courses_templates as $key => $value) {
            $template_options .= "<option value='$key'>$value</option>\n";
    }

    if(is_file($_SERVER['DOCUMENT_ROOT']."/COURSES/course$CID/course.xml")) {
            $refresh_checked = "";
            $write_checked = "checked";
    }
    else {
            $refresh_checked = "checked";
            $write_checked = "";
    }


    $tmp="
            <script language=\"JavaScript\">
            <!--
            function format_showDiv(showId, hideId) {
                document.getElementById(showId).style.display = 'block';
                document.getElementById(hideId).style.display = 'none';
                if (showId=='formatIMS') {
                
                    document.getElementById('ims_ch_info').disabled = false;
                    document.getElementById('ims_ch_org').disabled = false;
                    document.getElementById('ims_ch_del').disabled = false;
                    document.getElementById('ims_ch_del1').disabled = false;
                    document.getElementById('ims_ch_test').disabled = false;
                
                } else {
                
                    document.getElementById('ims_ch_info').disabled = true;
                    document.getElementById('ims_ch_org').disabled = true;
                    document.getElementById('ims_ch_del').disabled = true;
                    document.getElementById('ims_ch_del1').disabled = true;
                    document.getElementById('ims_ch_test').disabled = true;
                
                }
   
            }

            // -->
            </script>
            <table  width=99% border=0  cellspacing=0 cellpadding=0>";
if (!$GLOBALS['controller']->enabled) {
            $tmp .= "    
                    <tr>
                            <td>
                                        <table width=100% border=$BORDER cellspacing=\"0\" cellpadding=\"0\" >
                                            <tr>
                                                    <td>
                                                            <table width=100% class=brdr  cellspacing=\"0\" cellpadding=\"0\" >
                                                                    <th width=27 valign=top class=shown id=plus".$name."_1>
                                                                              <span style='cursor:hand' title=\""._("показать")."\" onClick=\"putElem('new".$name."');  removeElem('plus".$name."_1'); putElem('minus".$name."_1');\" >
                                                                                      <span class=cDisabled>".getIcon("+")."</span>
                                                                              </span>
                                                                    </th>
                                                                        <th width=27 valign=top class=hidden2 id=minus".$name."_1>
                                                                              <span style='cursor:hand' title=\""._("убрать")."\" onClick=\"removeElem('new".$name."'); removeElem('minus".$name."_1'); putElem('plus".$name."_1');\" >
                                                                                      <span class=cDisabled>".getIcon("-")."</span>
                                                                              </span>
                                                                    </th>
                                                                    <th width=100%>
                                                                             <span id=createitem>"._("загрузить курс")."</span>
                                                                    </th>
                                                             </table>
                                                    </td>
                                            </tr>
                                    </table>
                           </td>
                        </tr>";
} // if controller
            $tmp .=
                    "<tr>
                            <td height=1><img src=\"".$sitepath."images/spacer.gif\" width=1 height=1></td>
                    </tr>
                    <form enctype='multipart/form-data' method=post action='organization_exp.php?oper=2&send=1&cid=$CID'>
                    <input type='hidden' name='MAX_FILE_SIZE' value='30000000'>
                    <tr class=cHilight>
                            <td colspan=2 class=hidden id=new".$name." >
                                    <table  width=99% border=$BORDER  cellspacing=0 cellpadding=0 class=brdr  >
                                              <tr class=questt><td rowspan=9 width=10></td>
                                       <td width=250 class=\"shedaddform\">".$text."</td>
                                              </tr>
                                              <tr class=questt>
                                                   <td class=\"shedaddform\" >
                                                                <table width=99%>
                                                                        <tr>
                                                                            <td nowrap>"._("формат курса:")."</td>
                                                                            <td width=100%>
                                                                            <input type=\"radio\" checked name=\"xmlformat\" value=\"0\" onClick=\"format_showDiv('formatCourse','formatIMS');\"> "._("Курс")." eAuthor
                                                                            <input type=\"radio\" name=\"xmlformat\" value=\"1\" onClick=\"format_showDiv('formatIMS','formatCourse');\"> IMS Package
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td colspan=2><hr width=100%></td>
                                                                        </tr>
                                                                </table>
                                                                <div id='formatCourse'>
                                                                <table>
                                                                        <tr>
                                                                                <td nowrap>"._("файл структуры курса")." (.xml): </td>
                                                                                <td width='100%'><input id='xmlfile' name=xmlfile type=file></td>
                                                                        </tr>
                                                                        <tr>
                                                                                <td nowrap>"._("файл содержания курса")." (.tar, .zip): </td>
                                                                                <td><input name=zipfile1 type=file></td>
                                                                        </tr>
                                                                        <tr>
                                                                                <td nowrap>"._("шаблон курса:")."</td>
                                                                                <td width='100%'>
                                                                                        <select name='template' id='template_select' ".($refresh_checked == "checked"?"":"disabled").">
                                                                                                $template_options
                                                                                        </select>
                                                                                </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class=schedule colspan=2>
                                                                                          <input name='ch_info' type='checkbox' id='ch_info' value='1'> "._("переписать заголовок курса")."<br>
                                                                                          <input name='ch_org' type='checkbox' id='ch_org' value='1' checked onClick=\"javascript:document.getElementById('ch_del0').disabled=!this.checked;document.getElementById('ch_del1').disabled=!this.checked\"> "._("импортировать учебные модули и создать структуру курса")."<br>
                                                                                          <input name='ch_del' type='radio' id='ch_del1' value='1' $refresh_checked onClick=\"javascript: document.getElementById('template_select').disabled = false;\"> "._("обновить")."
                                                                                          <input name='ch_del' type='radio' id='ch_del0' value='0' $write_checked onClick=\"javascript: document.getElementById('template_select').disabled = true;\"> "._("дописать")."<br>
                                                                                          <input name='ch_test' type='checkbox' id='ch_test' value='1' checked> "._("импортировать задания")."<br>
                                                                            </td>
                                                                        </tr>
                                                                </table>
                                                                </div>
                                                                <div id='formatIMS' style='display: none;'>
                                                                <table>
                                                                        <tr>
                                                                                <td nowrap>"._("файл содержания курса")." (.tar, .zip): </td>
                                                                                <td><input name=zipfile2 type=file></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td class=schedule colspan=2>
                                                                                          <input disabled name='ch_info' type='checkbox' id='ims_ch_info' value='1'> "._("переписать заголовок курса")."<br>
                                                                                          <input disabled type='hidden' name='ch_org' id='ims_ch_org' value='1'>
                                                                                          <input disabled type='hidden' name='ch_del' id='ims_ch_del' value='1'>
                                                                                          <input disabled type='hidden' name='ch_del1' id='ims_ch_del1' value='1'>
                                                                                          <input disabled type='hidden' name='ch_test' id='ims_ch_test' value='0'>
                                                                            </td>
                                                                        </tr>
                                                                </table>
                                                                </div>";
            $tmp .= okbutton() . "<br>";        
            $tmp .= "
                                           </td>
                                         </tr>
                                    </table>
                             </td>
                     </tr>
             </form>
             </table>";
                    
  return($tmp);
}

function show_list_mod( $PID, $CID, $teacher=1 ) {
// показывает модули курса для человека
   global $sitepath;
   global $BORDER;
   global $s;

   if( $teacher )
      $cHilight="cHilight";
   else
      $cHilight="hidden";
   $i=0;
   $sql_result=get_all_mod_param($PID,$CID);
   if($sql_result){
   echo "<table id='lib1' width=100% class='tests' border=$BORDER cellpadding=0 cellspacing=0>\n";
   echo "<tr><th><a style='cursor: hand' onClick=\"removeElem('lib1');putElem('lib2');\">".getIcon("+")."</a>"._("библиотека учебных материалов")."</th></tr>\n";
   echo "</table>";

   echo "<table id='lib2' class=hidden2 width=100% class='tests' border=$BORDER cellpadding=0 cellspacing=0>\n";
   echo "<tr><th><a style='cursor: hand' onClick=\"removeElem('lib2');putElem('lib1');\">".getIcon("-")."</a>"._("библиотека учебных материалов")."</th></tr>\n";
   echo "<tr><td></td></tr>\n";
   echo "<tr><td><table cellpadding=0 cellspacing=0 width=100% ><tr><td  class='brdr2'>\n";

   $GLOBALS['controller']->captureFromOb('m130103');
   
?>
<table width=100% class=main cellspacing=0>
<tr>
<th><?=_("Название")?></th>
<th><?=_("Кол-во")?><br>
<?=_("модулей")?></th>
<th><?=_("Опубликован")?></th>
<th><?=_("Тема")?></th>
<th><?=_("Описание")?></th>
<th>&nbsp;</th>
</tr>
<?   
   while($res=sqlget($sql_result))
   {  $i++;
      ?>
        <tr  bgcolor="#FFFFFF">
        <td>
               <!--
               <form name='editMod<?=$i?>' action='<?=$sitepath?>teachers/edit_mod.php4' method='POST' target='_self'>
               <input type='hidden' name='make' value='editMod'>
               <input type='hidden' name='PID' value='<?=$PID?>'>
               <input type='hidden' name='ModID' value='<?=$res['ModID']?>'>
               <input type='hidden' name='CID' value='<?=$CID?>'>
               <input type='hidden' name='showfull' value='1'>
               <input type='hidden' name='popup' value='0'>
               
               <a href="javascript:document.editMod<?=$i?>.submit()"><?=stripslashes($res['mod_name'])?></a>
               </form>
               -->
               <a href="<?=$sitepath?>teachers/edit_mod.php4?make=editMod&PID=<?=$PID?>&ModID=<?=$res['ModID']?>&CID=<?=$CID?>&showfull=1&popup=0" title="<?=_("Редактировать содержание")?>"><?=stripslashes($res['mod_name'])?></a>
        </td>
        <td>
        <?
        	$count = get_num_obj($res['ModID']);
		$sql_result_2 = get_all_mod_param($PID, $CID, $res['ModID']);
		if (sqlrows($sql_result_2)>0) {
        		$res_2 = sqlget($sql_result_2);
	        	$count += count_modules($res_2['test_id']);
			$count += count_modules($res_2['run_id']);
			$count += count_modules($res_2['forum_id']);
		}
		echo $count;
        ?>
        </td>
        <td>
            <!-- span class=cGray>курс: <?=stripslashes($res[1])?></span -->
            <span class=cGray><?=get_status($res['pub'])?><br>
         </td>
         <td class=testsmall>
<?
if ($res['theme']) echo stripslashes($res['theme']);
//if (strlen($theme = get_theme($res['theme'],$res['ModID']))){
//  echo $theme;
//}
?>            
         </td>
         <td class=testsmall>
      <span id=timelimit1>
      <?
//      echo view_metadata( read_metadata ( get_descr($res['descript'])) );
if ($res['descript']) echo stripslashes($res['descript']);
      ?></span></span>
            </td>
      <td>
         <table border=<?=$BORDER?> cellpadding=0 cellspacing=0 width=100%>
            <tr>
            <td align=right class=wing>
               <form id='editModp1<?=$i?>' name='editModp1<?=$i?>' action='<?=$sitepath?>teachers/mod_properties.php4' method='GET' target='editModpWin<?=$i?>'>
               <input type='hidden' name='make' value='editModp'>
               <input type='hidden' name='PID' value='<?=$PID?>'>
               <input type='hidden' name='ModID' value='<?=$res['ModID']?>'>
               <input type='hidden' name='CID' value='<?=$CID?>'>
               <? if(check_teachers_permissions(19, $s[mid])) {?>
               <a href="javascript:void(0);" onClick="javascript:window.open('', 'editModpWin<?=$i?>', 'width=540,height=480,scrollbars=yes,titlebar=0,resizable=yes');document.getElementById('editModp1<?=$i?>').submit();">  
               <?=getIcon("edit");?>
               </a>
            <? } ?>
               </form>
            </td>
            <td class=wing width='8'>
               <form id='delMod<?=$i?>' name='delMod<?=$i?>' action='<?=$sitepath?>teachers/manage_course.php4?CID=<?=$CID?>&make=delete' method='GET'>
               <input type='hidden' name='ModID' value='<?=$res['ModID']?>'>
            <? if(check_teachers_permissions(19, $s[mid])) {?>
               <a onClick="if (confirm('<?=_("Вы уверены, что хотите удалить?")?>')) return true; else return false;" href="<?=$sitepath?>teachers/manage_course.php4?CID=<?=$CID?>&make=delete&ModID=<?=$res['ModID']?>">
                <?=getIcon("delete");?>
                </a>
            <? } ?>
               </form>
            </td>
            </tr>
         </table></td></tr>
      <?
   }
?>
</table><br>
<?  
   if( $teacher ) {
//                show_add_mod( $MID, $CID );
                $BORDER = 0;
                if(check_teachers_permissions(19, $s[mid])) {
                        echo "
                            <form method=GET name=\"addMod\" action=\"".$sitepath."teachers/manage_course.php4\">
                            <table class=main cellspacing=0>
                                      <tr class=questt colspan=2><th>&nbsp;&nbsp;"._("добавить учебный материал")."</th>
                                      </tr>
                                      <tr class=questt>
                                        <td>"._("Название")."</td>
                                        <td>
                                           <input type='hidden' name='CID' value='{$CID}'>
                                           <input type='hidden' name='make' value='addMod'>
                                           &nbsp;&nbsp;<input type='text' size=60 name=\"ModTitle\" value='"._("Учебный материал")."'>                         </td>
                                      </tr>
                                      <tr>
                                      <td colspan='2'>";
                                      echo okbutton();
                                      echo "</td>
                                      </tr>
                             </table>";
                             echo "</form>";
                }
   }
if (!$GLOBALS['controller']->enabled)      echo "</td></tr></table>";
   }

   if (!$GLOBALS['controller']->enabled) echo "</td></tr></td></tr></table>";

   
   $GLOBALS['controller']->captureStop('m130103');   
  
   return 1;
}

function get_descr($theme)
{
   if (empty($theme)) return "";
   return stripslashes($theme);
}

function get_status($type)
{
   if ($type>0) return _("да");
   return _("Не опубликован");
}

function get_theme( $theme, $mod_id=0 )
{
   if( $mod_id!=0 ){
     $r=getLinks2Mod( $mod_id );
     $tmp="";
     while( $res=sqlget( $r ) ){
       $tmp.=$res['title'].",<BR>";
     }
   }else{
     if (empty($theme)) $tmp="";
     else $tmp=stripslashes($theme);
   }
   return $tmp;
}

function show_title( $CID ){
   global $sitepath;
   global $BORDER;
   global $s;

   if(check_teachers_permissions(19, $s[mid])) {
           $tmp="<table border=$BORDER cellpadding=1 cellspacing=1 align=center width=\"540px\" class=skip>
                  <tr>
                          <td valign=top class=shedtitle nowrap>";
                  if($s['perm'] == 1) {
                            $tmp .= ph("<FONT SIZE=+1>
                                             ".get_course_title($CID)."
                                    </FONT>");
                  }
                  else {
                            $tmp .= ph("<FONT SIZE=+1>
                                             <a href=".$sitepath."cprof.php4?".$sess."CID=".$CID."\" alt='"._("править свойства курса")."'>".get_course_title($CID)."</a>
                                    </FONT>");
                  }
    $tmp .= "                            
                    </td>
                  </tr>
          </table>";
   }
   else {
                      $tmp="<table border=$BORDER cellpadding=1 cellspacing=1 align=center width=\"540px\" class=skip>
                  <tr>
                          <td valign=top class=shedtitle nowrap>".
                            ph("<FONT SIZE=+1>
                                             ".get_course_title($CID)."
                                    </FONT>")."
                    </td>
                  </tr>
          </table>";

   }
   return( $tmp );
}

function save_course_properies( $ALL_DATA, $CID ){
   global $coursestable;
   $meta=set_metadata( $ALL_DATA, get_posted_names( $ALL_DATA ), COURSES_DESCRIPTION );
   $ss="UPDATE ".$coursestable." SET Description =".$GLOBALS['adodb']->Quote($meta)." WHERE CID=$CID";
//   echo $ss;
   $res=sql($ss,"ERR save _decr");
   return( $meta );
}

function show_all_mod($CID,$MID,$PID, $mode=0) {
   global $sitepath;
   global $s;
   echo show_title($CID);
   if (isset($_GET['msg'])) {
       if ($GLOBALS['controller']->enabled) $GLOBALS['controller']->setMessage(strip_tags(base64_decode($_GET['msg'])));
       else echo base64_decode($_GET['msg']);
   }
   if($mode == 1) {
           if(check_teachers_permissions(19, $s[mid])) {
                   $sub_mode = 1;
           }
           else {
                   $sub_mode = 0;
           }
   }
   else {
           $sub_mode = $mode;
   }
   
   $GLOBALS['controller']->setLink('m130109',array($CID));
   $GLOBALS['controller']->setLink('m130114',array($CID));
   $GLOBALS['controller']->setLink('m130113', array($CID));
   $GLOBALS['controller']->setLink('m130116',array($CID));
   
   $GLOBALS['controller']->setLink('m130112', array($CID));

   $str = show_mod_organization($PID, $CID,1);
   echo $str;
   $GLOBALS['controller']->captureFromReturn('m130101', $str);
   echo "<BR>";
   show_list_mod( $PID, $CID, 1 );
   echo "<br>";
   echo "<br>";
   echo show_description( $CID, $sub_mode);
   if(check_teachers_permissions(19, $MID)) {
           $str = showBoxImport($CID);
           echo $str;
           $GLOBALS['controller']->captureFromReturn('m130104', $str);
   }
}

function add_new_mod($title,$CID,$PID)
{  
   global $mod_list_table;
   $title=return_valid_value($title);
   $sql="INSERT INTO ".$mod_list_table." (Title,CID,PID,Pub) VALUES (".$GLOBALS['adodb']->Quote($title).",'".$CID."','".$PID."','1')";
   $sql_result=sql($sql);
   $lID=sqllast();
   if (!@is_dir("../COURSES/course".$CID)) @mkdir("../COURSES/course".$CID, 0777);   
   if (!@is_dir("../COURSES/course".$CID."/mods")) @mkdir("../COURSES/course".$CID."/mods", 0777);   
   if (!@is_dir("../COURSES/course".$CID."/mods/".$lID)) @mkdir("../COURSES/course".$CID."/mods/".$lID, 0777);
      @chmod("../COURSES/course".$CID."/mods/".$lID,0777);
   return $lID;
}

function remove_mod($mod,$CID,$PID)
{
   global $mod_list_table,$mod_cont_table;
   $mod=return_valid_value($mod);
// $sql="SELECT Title FROM ".$mod_list_table." WHERE PID='".$PID."' AND ModID='".$mod."' AND CID='".$CID."'"; // with owner protection
   $sql="SELECT Title FROM ".$mod_list_table." WHERE ModID='".$mod."' AND CID='".$CID."'"; // without owner procted
   $sql_result=sql($sql, "err2343");
   if (sqlrows($sql_result)>0)
   {
// $sql="DELETE FROM  ".$mod_list_table." WHERE PID='".$PID."' AND ModID='".$mod."' AND CID='".$CID."'"; // with owner protection
   $sql="DELETE FROM  ".$mod_list_table." WHERE ModID='".$mod."' AND CID='".$CID."'";  // without owner procted
   $sql_result=sql($sql,"err2343");
   $sql="DELETE FROM  ".$mod_cont_table." WHERE ModID='".$mod."'";
   $sql_result=sql($sql);
   if (is_dir("../COURSES/course".$CID."/mods/".$mod))
      {
         empty_dir("../COURSES/course".$CID."/mods/".$mod);
         rmdir("../COURSES/course".$CID."/mods/".$mod);
      }
   return 1;
   }
   return 0;
}


function get_num_obj($mod)
{
   global $mod_cont_table;
   $mod=return_valid_value($mod);
   $sql="SELECT * FROM ".$mod_cont_table." WHERE ModID='".$mod."'";
   $sql_result=sql($sql);
   return sqlrows($sql_result);
}


function get_mod_title($mod)
{
   global $mod_cont_table;
   $mod=return_valid_value($mod);
   $sql="SELECT * FROM ".$mod_cont_table." WHERE ModID='".$mod."'";
   $sql_result=sql($sql);
   return ($sql_result[ 2 ]);
}

function show_stud_mod( $CID, $MID ) {
   global $sitepath;
   global $BORDER;
   echo show_title($CID);
   
   $GLOBALS['controller']->setLink('m130115',array($CID));
   $GLOBALS['controller']->setLink('m130108');

   $str = show_mod_organization( $PID, $CID, 3 );
   echo $str;
   $GLOBALS['controller']->captureFromReturn('m130101', $str);
   echo show_description( $CID, $mode );

   if ($GLOBALS['stud']) {
       echo "<br /><table width=100% class=main cellspacing=0><tr><th>"._("дополнительно")."</th></tr>";
       echo "<tr><td><a href=\"{$sitepath}teachers/edit_offline_courses.php\">"._("Редактировать путь к локальной версии курса")." >></a></td></tr>";
       echo "</table><br />";
   }
   echo "<BR>";
}

function show_stud_list_mod($CID)
{
   global $sitepath;
   global $BORDER;

   $i=0;
   $sql_result=get_pub_mod_param($CID);
   if($sql_result)
   while($res=sqlget($sql_result))
   {
      $i++;
      ?>
      <tr  bgcolor="#FFFFFF"><td width="50%">
      <table border=<?=$BORDER?> cellpadding=0 cellspacing=0 width=100% class="tests">
            <tr>
               <form name='editMod<?=$i?>' action='<?=$sitepath?>teachers/edit_mod.php4' method='POST' target='_self'>
               <input type='hidden' name='make' value='editMod'>
               <input type='hidden' name='ModID' value='<?=$res['ModID']?>'>
               <input type='hidden' name='CID' value='<?=$CID?>'>
               <input type='hidden' name='showfull' value='1'>
            <td>
               <span style='cursor:hand' onclick='submit();'><span class='cHilight'><u><?=stripslashes($res['mod_name'])?></u></span></span> (<?=get_num_obj($res['ModID'])?>)
               <br><span class=cGray><span class="testsmall">курс: <?=stripslashes($res[1])?></span></span>
            </td>
               </form>
            </tr>

      </table>
      </td>
      <td width="50%">
         <table border=<?=$BORDER?> cellpadding=0 cellspacing=0 width=100% class="tests">
            <tr>
            <td class=testsmall>
            <span class=cGray><?=_("тема:")?> <?=get_theme($res['theme'],$res['ModID'])?></span>
            <br>
            <span class=cGray><?=_("описание:")?> <span id=timelimit1><?=get_descr($res['descript'])?></span></span>
            </td>
            </tr>
         </table>
      </td>
      </tr>
      <?
   }
   return 1;
}

function get_pub_mod_param($CID)
{
   global $mod_list_table,$coursestable;
// $sql="SELECT ".$mod_list_table.".ModID as ModID,".$coursestable.".Title as course_name,".$mod_list_table.".Title as mod_name,".$mod_list_table.".Descript as descript,".$mod_list_table.".Num as theme,".$mod_list_table.".Pub as pub FROM ".$mod_list_table.",".$coursestable." WHERE ".$coursestable.".Status=2 AND ".$mod_list_table.".PID='".$PID."' AND ".$mod_list_table.".CID='".$CID."' AND ".$mod_list_table.".CID=".$coursestable.".CID";
   $sql="SELECT ".$mod_list_table.".ModID as ModID,".$coursestable.".Title as course_name,".$mod_list_table.".Title as mod_name,".$mod_list_table.".Descript as descript,".$mod_list_table.".Num as theme,".$mod_list_table.".Pub as pub FROM ".$mod_list_table.",".$coursestable." WHERE ".$coursestable.".Status!=0 AND ".$mod_list_table.".CID='".$CID."' AND ".$mod_list_table.".CID=".$coursestable.".CID AND ".$mod_list_table.".PUB='1'";
   if(!empty($mod)) $sql.=" AND ".$mod_list_table.".ModID='".$mod."'";
   $sql.=" ORDER by ".$mod_list_table.".ModID";
   $sql_result=sql($sql);
   if (sqlrows($sql_result)<0) return 0;
   return $sql_result;
}

/**
 * @return Mod Type
 * @param String.
 * @desc Show Mod Type, Stydy or Load.
 */
function get_type_mod($type)
{
   if ($type=="html") return _("Изучить");
   return _("Загрузить");
}

/**
 * @return Button
 * @param String.
 * @desc Create navigation bar (next,previos) in modules.
 */
function create_navigate_button( $CID, $mod, $PID=0)
{
   global $sitepath,$showfull;
   $i=-1;

   $mods=array();
   $titles=array();

   if ($PID>0)
      $sql_result=get_all_mod_param($PID,$CID);
   else
     $sql_result=get_pub_mod_param($CID);

   if($sql_result)
     while($res=sqlgetrow($sql_result))
     {
      if ($res[0]==$mod)
        $i=count($mods);
      $mods[]=$res[0];
      $titles[]=$res[2];
     }

   if ($i==-1){
//     echo "I=$i";
     return 0;
   }
   echo "<table border=0><tr><td width=100%><td>";
   if ($i>0)
   {
   ?>
               <td>
               <form name='editMod<?=$i?>' action='<?=$sitepath?>teachers/edit_mod.php4' method='POST' target='_self'>
               <input type='hidden' name='make' value='editMod'>
               <input type='hidden' name='PID' value='<?=$PID?>'>
               <input type='hidden' name='ModID' value='<?=$mods[$i-1]?>'>
               <input type='hidden' name='CID' value='<?=$CID?>'>
               <input type='hidden' name='showfull' value='<?=$showfull?>'>
            <!--td valign=top class=shedtitle width="20%"-->
               <span style='cursor:hand' onclick='submit();'><span class='cHilight' title='<?=stripslashes($titles[$i-1])?>'><u>&lt;&lt;&nbsp;</u>
               </span></span> <!-- (<?=get_num_obj($mods[$i-1])?>) -->
            <!--/td-->
               </form>
               </td>
   <?
   }//else
    //  echo "<td valign=top class=shedtitle width=\"50%\">&nbsp;</td>";

   if ($i<(count($mods)-1))
   {
   ?>
               <td>
               <form name='editMod<?=$i?>' action='<?=$sitepath?>teachers/edit_mod.php4' method='POST' target='_self'>
               <input type='hidden' name='make' value='editMod'>
               <input type='hidden' name='PID' value='<?=$PID?>'>
               <input type='hidden' name='ModID' value='<?=$mods[$i+1]?>'>
               <input type='hidden' name='CID' value='<?=$CID?>'>
               <input type='hidden' name='showfull' value='<?=$showfull?>'>
            <!--td valign=top class=shedtitle width="20%"-->
               <span style='cursor:hand' onclick='submit();'><span class='cHilight' title='<?=stripslashes($titles[$i+1])?>
               '><u>&nbsp;&gt;&gt;</u></span></span> <!-- (<?=get_num_obj($mods[$i+1])?>) -->
            <!--/td-->
               </form>
               <td>
   <?
   }//else
    // echo "<td valign=top class=shedtitle width=\"50%\">&nbsp;</td>";
   echo "</tr></table>";
   return 1;
}

function show_forum_list($CID)
{
   global $forummessages;
   global $forumthreads;
   global $coursestable;
//   $sql = "SELECT ".$forumthreads.".thread,MIN(id),lastpost,message FROM ".$forummessages.", ".$forumthreads.",".$coursestable." WHERE ".$forumthreads.".course=".$coursestable.".Title AND ".$coursestable.".CID=".$CID." AND ".$forummessages.".thread=".$forumthreads.".thread  GROUP BY thread";    

/*   $sql = "
    SELECT 
      forumthreads.thread,
      forummessages.message
    FROM
      forumthreads
      INNER JOIN forummessages ON (forumthreads.thread = forummessages.thread)
      INNER JOIN Courses ON (forumthreads.course = Courses.Title)
    WHERE
      forummessages.is_topic='1' AND
      Courses.CID = '{$CID}'
   ";*/
   
   $sql = "
   SELECT DISTINCT
       forumcategories.name as category_name, forummessages.thread, forummessages.name as message
   FROM forumcategories
   INNER JOIN forumthreads ON (forumthreads.category=forumcategories.id)
   INNER JOIN forummessages ON (forummessages.thread = forumthreads.thread)
   WHERE 
       (forumcategories.cid='".(int) $CID."'
       OR forumcategories.cid='0')
       AND forummessages.is_topic='1'
   ORDER BY forumcategories.name, forummessages.name";
   
   $result=sql($sql);
   if (sqlrows($result)>0)
   while ($res=sqlget($result))
   {
      $mes=(strlen($res['message'])<60) ? $res['category_name'].' :: '.$res['message'] : $res['category_name'].' :: '.substr($res['message'],0,57)." ...";
?>
                              <option value="<?=$res['thread']?>"><?=$mes?></option>
<?
   }
}

function show_tests_list($CID,$test_arr)
{
   global $test_title_table;
   $sql = "SELECT tid, title, status FROM test WHERE cid='".$CID."'";
   $result=sql($sql);
   $flag=0;
   if (sqlrows($result)>0)
   while ($res=sqlget($result))
   {
      if (!in_array($res['tid'],$test_arr))
      {
      $title=(strlen($res['title'])<60) ? $res['title'] : substr($res['title'],0,57)." ...";
      $status=($res['status']) ? "(open)" : "(closed)";
      $flag=1;
?>
                              <option value="<?=$res['tid']?>"><?=$title?> <?=$status?></option>
<?
      }
   }
   if (!$flag)
   {
?>
                              <option value="error"><?=_("нет зарегистрированных тестов")?></option>
<?
   }
}






function create_new_forum($name,$cid,$emailanswers,$user,$Email)
{
   global $forumthreads;
   global $forummessages;
   global $coursestable;
   global $BORDER;

   $posted=time();
   $selectedcourse=getField($coursestable,"Title","CID",$cid);
   @sql("INSERT INTO $forumthreads (course,lastpost) VALUES('$selectedcourse','$posted')");
   $thread=sqllast ();
   $SQL = "INSERT INTO $forummessages (thread,posted,icon,message,name,email,sendmail,is_topic) VALUES ('$thread','$posted','1','$name','$user','$Email','$emailanswers','1')";
   sql($SQL);
   return $thread;
}

function show_books($CID)
{
   global $bookstable;
   global $BORDER;


   $sql="SELECT * FROM ".$bookstable." WHERE CID='".$CID."'";
   if (!$result=sql($sql)) return "</table>";
   if (sqlrows($result)<1) return "</table>";

   echo "<P><table border=$BORDER cellspacing='0' cellpadding='0' width='100%'>
                      <tr><th >"._("литература")."</th><th width=5%> <a href='".$sitepath."../library.php4'>".getIcon("edit")."</th></tr>
                      <tr><td height=2><img src='".$sitepath."images/spacer.gif' width=1 height=1></td></tr>
                      <tr><td>
                      <table align='center' width='100%' cellpadding='5' cellspacing='1' class='tests' border=$BORDER>
         ";
   while ($res=sqlget($result))
      {
?>
                       <tr bgcolor="#FFFFFF">
                         <td>
<?
      echo "<b>".$res['Name']."<br /></b>";
      echo $res['Author']."<br />";
      echo $res['Izdatel'].", ".$res['Year']."<br />";
      echo "<i>".$res['Description']."<br /></i>";
      echo "<a href='".$res['Url']."' target='_blank'>".$res['Url']."</a>";
?>
                       </td>
                     </tr>
<?

       }

      ?>
                   </table>
             </td></tr>
        </table>
   <?

}



?>