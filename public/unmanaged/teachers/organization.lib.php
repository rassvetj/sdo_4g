<?php
require_once ('metadata.lib.php');
define('IMPORT_TYPE_EAU2', 1);
define('IMPORT_TYPE_EAU3', 4);
define('IMPORT_TYPE_EAU3_2', 'eauthor3_2');
define('IMPORT_TYPE_SCORM', 2);
define('IMPORT_TYPE_AICC', 3);
define('IMPORT_TYPE_ZIP', 5);
function edit_box( $id, $title="", $data, $CID="", $extra="" ){
   global $sitepath;
   
   if (empty($data)) {
       $dummy = sqlget(sql("SELECT metadata FROM organizations WHERE oid = '$id'"));
       $data = read_metadata(stripslashes($dummy['metadata']), "item" );
   }
   
   $itemType = "<select name='item_type'>";
   foreach (get_eventtools() as $key=>$val){
       $itemType .= "<option value='$key' ".(($data[0]['value']==$key)?"selected='selected'":'').">"._($val)."</option>";
   }
   $itemType .= "</select>";
   
   $tmp="
<span id='edit_$id' style=\"display: none;\">
  <form name='123' action='".$sitepath."teachers/edit_navigation.php'>
  <input  name='make' type=\"hidden\" value=edit_item>
  <input  name='CID' type=\"hidden\" value=$CID>
  <input  name='item_id' type=\"hidden\" value=$id>
    <table class=main cellspacing=0>
    <tr><td>
    "._("Название").": </td><td><input name='item_title' size=50 type=text value=\"".htmlspecialchars(str_replace("\n", " ", $title))."\">
    </td></tr>
    <tr><td>
    "._("Тип занятия").": </td><td>$itemType
    </td></tr>
    <tr><td>
    "._("Длительность (академ. час)").": </td><td><input name='item_duration' size=50 type=text value=\"".$data[1]['value']."\">
    </td></tr>
    <tr><td colspan=2><input  type=submit value='"._("применить")."'>
    <input  type=button value='"._("свернуть")."' onClick=\"removeElem('edit_$id')\">
    </td></tr>
    </table>
  </form>
</span>";
   return $tmp;
}

function get_eventtools(){
    $sql = "SELECT TypeID, TypeName FROM EventTools";
    $res = sql($sql);
    $ret = array();
    while ($row = sqlget($res)) {
        $ret[$row['TypeID']] = $row['TypeName'];
    }
    return $ret;
}

function get_organization( $CID, $sort=0 ){
 if($CID > 0) {
 	$sss="SELECT * FROM organizations WHERE cid=$CID";
 	$res=sql( $sss, "ERR_navi_get" );
 	$i=0;
 	while( $r=sqlget( $res ) ){
   		$items[ $r[oid] ][oid]=$r[oid];
   		$items[ $r[oid] ][title]=$r[title];
   		$items[ $r[oid] ][prev_ref]=$r[prev_ref];
   		$items[ $r[oid] ][mod_ref]=$r[mod_ref];
   		$items[ $r[oid] ][level]=$r[level];
   		$items[ $r[oid] ][metadata]=$r[metadata];
   		$items[ $r[oid] ][module]=$r[module];
   		$i++;
 	}
 }	else
 		echo "ERROR-zero CID";
 return( $items );
}

function sort_organization( &$items, $sort=1, $cid=0) {
 $id=getLastInOrg($items, $cid);
 if($sort == 1)
 	$i=count( $items )-1;
 else $i=0;
 while( ( $id > -1) && ($i<10000 ) ) {
  	$items[ $id ][num]=$i; //$items[ $id ];
   	$org[$i]=$items[ $id ];
	$prev_id = $id;
   	$id = $items[ $id ]['prev_ref'];
    if( $sort==1 )
     	$i--;
   	else
    	$i++;
 }
 return( $org );
}


function getLastItem( $CID ){
 // возвращает последний item тот на который никто не ссылается
 //$org=get_organization( $CID );
 $id=getLastInOrg( array(), $CID );
 return( $id );
}

function getLastInOrg($ids, $cid=0){
  // ids - массив items с параметрами
  //if(count( $ids) == 0 ) return(-1);

  $ret = -1;

  $sql = "SELECT t1.oid
          FROM organizations t1
          LEFT JOIN organizations t2 ON (t1.oid = t2.prev_ref)
          WHERE t1.cid = '".(int) $cid."' AND t2.prev_ref IS NULL
          LIMIT 1";
  $res = sql($sql);
  if ($row = sqlget($res)) {
      $ret = $row['oid'];
  }

  return $ret;

  // garbage begin

  foreach( $ids as $id ){ // формируем массив ссылок на предидущие
    $prevs[]=$id[prev_ref];
    //echo $id[prev_ref];
  }
  foreach( $ids as $id ){
   $last=$id[oid];
   foreach($prevs as $prev){
     if( $id[oid] == $prev ){
      $last=-1;
      break;
     }
   }
   if( $last==$id[oid] )
     return( $id[oid] );
 }
}

function makeLink2Mod( $i, $CID, $PID, $mod_id, $text, $help, $teach=0, $mode=1) {
   global $sitepath;
   global $s;

    switch ($s['perm'])
    {
		case 1:
			$allow = check_students_permissions(23, $s['mid']) ? true : false;
			break;
		default:
			$allow = true;
	}

   if( $mod_id > 0 ) {
   		$href="href='".$sitepath."teachers/edit_mod.php4?make=editMod&ModID=$mod_id&CID=$CID&PID={$PID}&new_win=1&teach=".(int) (($s['perm']>1) && ($mode==1))."&showfull=".(int) (($s['perm']>1) && ($mode==1))."&mode_frames=1&popup=1'";
//   		$href="href='".$sitepath."teachers/edit_mod.php4?make=editMod&ModID=$mod_id&CID=$CID&PID=$PID&new_win=1&teach=$teach&showfull=$teach&mode_frames=".MODE_SHOW_FRAMES."'";
	 	$strTarget = (($s['perm']>1) && ($mode==1))?"_blank":"_self";
//	 	$strTarget = ( ($s['perm'] > 1)&&(check_teachers_permissions(19, $s['mid'])) )? "_self" : "_top";
	 	if($allow)
     		$tmp.="<a $href title='".$help."' target='{$strTarget}'>";//[che] target='navi_win' $act>"; //"
     	$tmp.=stripslashes($text);
     	if($allow)
     		$tmp.="</a>";
   }
   else
   		$tmp.=stripslashes($text);
   return( $tmp );
}

function fputhtml( $f, $tmp ){
//  $tmp=startHTML().$tmp;
//  $tmp=$tmp.stopHTML();
  $ret=fputs( $f, $tmp );
  return( $ret );
}

function make_mod_page($CID,$mod_id, $dir){

     $res=get_all_mod_param( $PID, $CID,$mod_id );
     $tmp.=show_mod_content( $CID, $PID, $res, $mod_id, 1 );

     $f=fopen( $dir."/index.htm","w+");
     if( $f ){
       if ( ! fputhtml( $f, $tmp ) ) echo "WRITE $dir ERROR!! ";

       fclose( $f );
     }
}

function makeLink2Dir( $CID, $mod_id, $title, $course_dir  ){
  // создает  каталог даннх модуля копирует туда данные и формирует ссылку на наих
   global $sitepath;

   if($mod_id>0){
  // создаем каталог для содержимого модуля
      $dir=$course_dir."/mod$mod_id";
      if(  !is_dir($dir) )  mkdir ($dir, 0700);
  // формируем его содержание

     $href="href='".$dir."/index.htm";
     $tmp.="<a $href target=browse_frame>"; //"
     $tmp.=stripslashes($title)."</a>";
     // создать данные для этого курса в каталоге
     make_mod_page($CID,$mod_id, $dir);

   }else
     $tmp.=stripslashes($title);

   return( $tmp );
}


function show_mod_organization( $PID, $CID, $mode, $dir="")  // показывает структуру курса (рабочую программу) для человека
{                                             // в ней выделен пройденный материал, и тот, что еще предстоит пройти
	global $sitepath;
	global $BORDER;
	global $s;

	if( $mode == 1  ){
	   $tmp .= "
	   <form action=\"\" method=\"POST\" name=\"settings\">
	   <table class=main cellspacing=0>
	   <tr>
	       <td style=\"background: #F0F7FF url({$GLOBALS['sitepath']}images/elementbox.gif) no-repeat top left; width: 175px; height: 32px;\" align=center>
	       <input value=\"1\" type=\"checkbox\" ".($_REQUEST['groupmove'] ? ' checked ' : '')." name=\"groupmove\" id=\"groupmove\">
	       <input size=1 value=\"".($_REQUEST['groupstep'] ? (int) $_REQUEST['groupstep'] : COURSE_ITEM_MOVE_STEP)."\" type=\"text\" name=\"groupstep\" id=\"groupstep\">
	       <img title=\""._('Перемещать сразу на несколько позиций')." (".COURSE_ITEM_MOVE_STEP.' '._('позиций').")"."\" border=0 align=absmiddle src=\"{$GLOBALS['sitepath']}images/groupmove.gif\" hspace=2>
	       <input value=\"1\" type=\"checkbox\" ".($_REQUEST['submove']   ? ' checked ' : '')." name=\"submove\" id=\"submove\"> <img title=\""._('Перемещать все подэлементы')."\" border=0 align=absmiddle src=\"{$GLOBALS['sitepath']}images/tree.gif\" hspace=2>
	       <img src='{$sitepath}images/spacer.gif' width='15' height='1'><a href='{$sitepath}teachers/edit_navigation.php?make=delete_all&CID={$CID}' onClick=\"javascript: return(confirm('Вы действительно желаете очистить программу курса?'))\"><img title=\""._('Очистить программу курса')."\" border=0 align=absmiddle src=\"{$GLOBALS['sitepath']}images/icons/delete.gif\" hspace=2 align='top' style='padding-bottom:5px;'>
	       </td>
	   </tr>
	   </table>
	   </form>
	   ";
	   $smarty = new Smarty_els();
	   $tmp .= $smarty->fetch('control_treeselect2_js.tpl');
	   unset($smarty);
	}

	// MODE - 0 ЕСЛИ ПРОСТО ПОКАЗАТЬ ОРГАНИЗАЦИЮ 1 ЕСЛИ В РЕЖИМЕ ПРАВКИ 2 ЕСЛИ ДЛЯ сд
	$i=0;
	$tmp.="<TABLE width=99% cellpadding=0 cellspacing=0>";

	//$GLOBALS['controller']->setLink('m130105', array($CID));
	//$GLOBALS['controller']->setLink('m130106', array($CID,$PID));
    if ($mode==1) $GLOBALS['controller']->setHeader(_("Редактирование содержания курса:")." ".cid2title($CID));
	$GLOBALS['controller']->captureFromVar('trash001', 'tmp', $tmp);

	$tmp.="<tr><th>"._("содержание");
	if( $mode==1 )
		$tmp.="";//navi_key( $r[oid], $CID, "delete_all",getIcon("X"), "удалить все" );
	if( $mode==0 ) {
		if(check_teachers_permissions(19, $s[mid])) {
			$tmp.="</th><th width=10%>";
			if( $s['perm'] == 2 ) {
				$tmp.="<a href='course_print.php?cid=$CID' target='_blank'>".getIcon("print")."</a>&nbsp;&nbsp;&nbsp;";
			}
			$tmp.="<a href='".$sitepath."teachers/edit_navigation.php?CID=$CID' target='show_win1' onclick=\"window.open
      			('', 'show_win1', 'status=no,toolbar=yes,menubar=no,scrollbars=yes,resizable=yes,width=600, height=600');\"
      			title='"._("править")."'>".getIcon("edit")."</a>";
		}
		else {
			$tmp.="";
		}
	}
	if( $mode==2 )
		$tmp.="<H1>".cid2title($CID)."</h1>";
	if( $mode==3 )	{
		$tmp.="";
		$mode=0;
	}
	$tmp.="</th></tr><tr><td height=2></td></tr>";

	$GLOBALS['controller']->captureStop('trash001');

	$tmp .="<tr ><td >";
	if ($mode != 0) $tmp.="<form method=GET name=\"save\" action=\"".$sitepath."teachers/edit_navigation.php\">
    	   <input type='hidden' name='make' value='save_links'>
       	   <input type=\"hidden\" name=\"CID\" value=\"".$CID."\">";
	if ($mode != 0) $tmp.="<TABLE cellpadding='0' cellspacing='0' width=100% class='tests' id='course_constructor'>";
	$i = 0;
	//$mods=getModulesList( $CID  );
	$items=get_organization( $CID );
	$org=sort_organization( $items, 1, $CID);
	//echo count($items)."<br />";
	//echo count($org)."<br />";


	$jc=count($org);
	$edit_boxes="";
	//for( $j=0; $j<$jc; $j++ ) {
	//$j = 0;
	//if(is_array($org))
	//foreach($org as $key => $value) {

	$j = 0;
//	foreach(array_keys($org) as $j) {
	for($j = 0; $j < $jc; $j++) {
	    if (!isset($org[$j])) continue;
		$r = $org[ $j ];
		//$r = $value;

        // ==================
        if ($mode==0) {

        if (!isset($itemIDs)) {
            $itemID = "{$r['level']}_1";
            $itemCount[$itemIDs[$r['level']-1]] = 1;
        }
        else {
            if (!isset($itemCount[$itemIDs[$r['level']-1]]))
            $itemCount[$itemIDs[$r['level']-1]] = 1;
            $itemID = $itemIDs[$r['level']-1]."_".$itemCount[$itemIDs[$r['level']-1]];

        }

        $itemIDs[$r['level']] = $itemID;
        $itemCount[$itemIDs[$r['level']-1]]++;

        }
        // ==================

		if ($mode==0) {
        $tmp.="<tr id='org_{$itemID}' ";
        if (($r['level']>0) && defined('СOURSE_ORGANIZATION_TREE_VIEW') && СOURSE_ORGANIZATION_TREE_VIEW) $tmp.="style='display: none;'";
        } else $tmp.="<tr";
		$tmp.="><TD width=70%>";
		$dots = str_pad('', $r[level]+1, '.');
		$td="<td width=".($r[level]*2+1)."%>{$dots}</td>"; //".($r[level]*5)."
		//$tmp.="<div id='org_{$itemID}' ";
        //if ($r['level']>0) $tmp.="style='display: none;'";

        //var_dump($_SESSION['itemID']);die();
        //$bgcolor[0] = $bgcolor[1] = "";
        $bgcolor = ''; $bgcolor2 = '#e0e7ff';
        if (isset($_SESSION['itemID'][$r[oid]])) {
        	$bgcolor = "#dee5ec";
        	$bgcolor2 = '#fff';
        }
        $tmp.="<a name='element_{$r[oid]}'></a><table width=100%><tr class=message>$td<td>";

		switch( $mode ) {
			case 0:
				$meta=view_metadata( read_metadata ( get_descr($r['metadata']), "item" ), "descr");
                $title = "";
				if (defined('СOURSE_ORGANIZATION_TREE_VIEW') && СOURSE_ORGANIZATION_TREE_VIEW) {
                $title = "
                <a style='display:none;' id='org_{$itemID}_minus' href='javascript:void(0);' onClick=\"removeTreeElementsByPrefix('org_{$itemID}');\"><img align=absmiddle border=0 src=\"{$sitepath}images/ico_minus.gif\"></a>
                <a id='org_{$itemID}_plus' href='javascript:void(0);' onClick=\"putTreeElementsByPrefix('org_{$itemID}','table-row');\"><img align=absmiddle border=0 src=\"{$sitepath}images/ico_plus.gif\"></a>";
                }

                $title .= makeLink2Mod( $j, $CID, $PID, $r[mod_ref], $shift.$r[title], getModuleTitle( $mods, $r[mod_ref] ), $mode, $mode)."";

				$tmp.=$title;
				//"Дополнительная информация"
			break;
		case 1:
			//$meta=edit_metadata( read_metadata ( get_descr($r['metadata']), "item" ) );
			$navy_keys="";//$title;
            $navy_keys.=" ".change_key($r[oid], $CID);
			$navy_keys.=" ".add_key($r[oid], $CID);
			$navy_keys.=" ".edit_key($r[oid]);
			$navy_keys.=navi_key( $r[oid], $CID, "prev_level",getIcon("<"), _("на уровнь выше") );
			$navy_keys.=navi_key( $r[oid], $CID, "next_level",getIcon(">"), _("на уровень ниже") );
			$navy_keys.=navi_key( $r[oid], $CID, "up_item", getIcon("^"), _("переместить вверх") );
			$navy_keys.=navi_key( $r[oid], $CID, "down_item",getIcon("v"), _("переместить вниз") );
			$navy_keys.=navi_key( $r[oid], $CID, "deleteItem",getIcon("x"), _("удалить пункт") );
			$r[title_short] = (strlen($r[title]) > ORGANIZATION_TITLE_TRUNCATE) ? substr($r[title], 0, ORGANIZATION_TITLE_TRUNCATE) . "..." : $r[title];
			$title=
				"<span style='cursor:hand'
                  onMouseOver=\"putElem('navi_".$r[oid]."');document.getElementById('row{$r[oid]}').style.backgroundColor='{$bgcolor2}';\"
                  onMouseOut=\"removeElem('navi_".$r[oid]."');document.getElementById('row{$r[oid]}').style.backgroundColor='{$bgcolor}';\">
                  <table cellpadding=0 cellspacing=0 border=0 class='row' width=100% id='row{$r[oid]}' style='background: {$bgcolor}'><tr><td valign=bottom height=20 width=100% class='oid_title' {$bgcolor}>
            	 <span id=bullet_".$r[oid]." title='".htmlspecialchars($r[title])."'>".$r[title_short]."</span>
             	</td><td nowrap><span style='display: none;' id=navi_".$r[oid].">
             	".$navy_keys."</span></td></tr></table>
            	 </span>";
			//echo $title;
			if( intval($r[mod_ref])>0 ){
				$tm=getModuleTitle( $mods, $r[mod_ref]);
				//$tit=makeLink2Mod( $j, $CID, $PID, $r[mod_ref], getIcon("edit",_("править")." $tm"), $tm, $mode );
			}
			else
				$tit="";

		    //$extra="<tr><td valign=top>"._("Учебные материалы").": </td><td>".SelectModules( $mods, $r[mod_ref], "shown", $r[oid])." ".$tit."</td></tr>";
//		    $extra="<tr><td valign=top>"._("Заменить").": </td><td>".getOrganizationItems($r['oid'], $r['module'])."</td></tr>";
			$extra = "";
			$tmp.=$title.edit_box( $r[oid], $r[title], $meta, $CID, $extra )."</td>";
		break;
		case 2:
			$meta="<span class=small>".view_metadata( read_metadata ( get_descr($r['metadata']), "item" ), "descr")."</span>";
			$title="<H".($r[level]+1).">".makeLink2Dir( $CID, $r[mod_ref], $r[title], $dir )."</H".($r[level]).">";             // генерируем дистрибудтив для курса
			if( intval($r[mod_ref])<=0 )
				$tmp.="<H". ($r[level]+1) .">".$title."</H". ($r[level]+1) .">".$meta;
		break;
	}
	$tmp.="</td></tr></table></td></tr>";
//	$tmp.="</td></tr> <tr><td></td><td>$minus</td></tr></table>";
	$i++;
//	$tmp.="</tr>";
	//$j++;
}
unset($_SESSION['itemID']);

if( ($mode==1) && ($i>0) ){
	$tmp.="<tr><td></td>
     <td></td></tr>"; //<INPUT class='hidden' type=\"submit\" id='save_' name=\"submit\" value='сохранить ссылки'>
}
if ($mode != 0) $tmp.="</table></form>";
if( $mode==1  ){
//	$GLOBALS['controller']->captureFromVar('trash002', 'tmp', $tmp);
/*    if ($GLOBALS['controller']->enabled) $tmp.="<br><br>";
	$tmp.=show_add_item( $PID, $CID );
*/
    $tmp .= "
    <script type=\"text/javascript\">
    <!--
    window.organizationItemId = 0;
    //-->
    </script>
    ";
    if ($_GET['refresh']) {
        $tmp .= "<script type=\"text/javascript\">if (parent.bottomFrame) parent.bottomFrame.location.reload();</script>";
    }
//	$GLOBALS['controller']->captureStop('trash002');
}

if ($mode != 0) $tmp.="</td></tr></table>";
if ($mode === 0) $tmp.="</table>";
return $tmp;
}


function getModuleTitle( $mods, $ID  ){
  if( count( $mods ) > 0 ){
   foreach($mods as $mod){
    if($mod[Id]==$ID)
      if($mod[Title]=="")
        return("unknow link");
      else
       return($mod[Title]);
   }
  }
  return("unknow module");
}

function getModulesList( $CID  ){
 // выводит список модулей по курсу
 global $mod_list_table,$mod_cont_table;
 $sql="SELECT Title,ModID FROM ".$mod_list_table." WHERE CID='".$CID."'"; // without owner procted
 $res=sql($sql);
 $i=0;
 while($r=sqlget($res)){
  $tmp[$i][Title]=$r[Title];
  $tmp[$i][Id]=$r[ModID];
  $i++;
 }
 return($tmp);
}

function  save_links( $CID,  $PID, $modules, $title="Новый блок" ){
  if (is_array($modules) && count($modules)) {
      foreach( $modules as $mod ){
       $ss=explode (",", $mod );
       $ref=$ss[0];
       $id=$ss[1];
       save_link( $CID, $PID, $id, $ref, $title );
      }
      return(count($modules));
  }
}

function update_organization($fromId, $toId) {
    if ($fromId && $toId) {
        if ($toId[0] == 'o') {
            $toId    = substr($toId,1);

	        $fromCid = getField('organizations','cid','oid',$fromId);
	        $toCid   = getField('organizations','cid','oid',$toId);

	        if (!$fromCid || !$toCid) return false;

	        $fromStructure = CCourseContent::getChildren($fromCid,$fromId);
	        $toStructure   = CCourseContent::getChildren($toCid,$toId);

	        $lastOid = $fromId; $fromBlockEndId = $fromId;

	        $items = array();
	        if (count($fromStructure)) {
	            foreach($fromStructure as $item) {
                    $items[$item->attributes['oid']] = $item->attributes['oid'];
                    $fromBlockEndId = $item->attributes['oid'];
	            }
	        }

	        if (count($items)) {
	            $sql = "DELETE FROM organizations WHERE oid IN ('".join("','",$items)."')";
	            sql($sql);
	        }

	        $lastPrev = getField('organizations','prev_ref','oid',$fromId);
	        $level = -1;
	        $level = getField('organizations','level','oid',$fromId) - 1;
	        //if ($lastPrev) {
	           //$level = getField('organizations','level','oid',$lastPrev);
	        //}

	        $sql = "SELECT * FROM organizations WHERE oid = '$toId'";
	        $res = sql($sql);

	        if ($row = sqlget($res)) {
	            $row['cid']      = $fromCid;
	            $row['prev_ref'] = $lastPrev;
	            $row['root_ref'] = $toCid;
	            $diff = $level - $row['level'] + 1;
	            $row['level'] = $row['level'] + $diff;
	            unset($row['oid']);

	            foreach($row as $key => $value) $row[$key] = $GLOBALS['adodb']->Quote($value);

	            $sql = "INSERT INTO organizations (".join(",",array_keys($row)).") VALUES (".join(",",$row).")";
	            sql($sql);

	            $lastPrev = $lastOid = sqllast();
	            $_SESSION['itemID'][$lastPrev] = true;
                if (count($toStructure)) {
    	            foreach($toStructure as $item) {
    	                if ($item->attributes['oid']) {
    	                    $item->attributes['cid'] = $fromCid;
    	                    $item->attributes['root_ref'] = $toCid;
    	                    $item->attributes['prev_ref'] = $lastPrev;
    	                    $item->attributes['level'] = $item->attributes['level'] + $diff;
    	                    unset($item->attributes['oid']);

                            foreach($item->attributes as $key => $value) $item->attributes[$key] = $GLOBALS['adodb']->Quote($value);

    	                    $sql = "INSERT INTO organizations (".join(",",array_keys($item->attributes)).") VALUES (".join(",",$item->attributes).")";
    	                    sql($sql);

    	                    $lastPrev = $lastOid = sqllast();

    	                }
    	            }
	            }

	            sql("DELETE FROM organizations WHERE oid = '$fromId'");

	        }

	        $sql = "UPDATE organizations SET prev_ref = '$lastOid' WHERE prev_ref = '$fromBlockEndId' AND cid = '$fromCid'";
	        sql($sql);

        }
    }
}

function save_link( $CID, $PID, $id, $ref, $title="Новый блок" ){
   if( intval( $ref ) == -2 ){  // создаем новый модуль с именем title
     $ref=add_new_mod( $title, $CID, $PID );
   }
   if( intval( $ref ) > 0 ){
      $ss="UPDATE organizations SET mod_ref='$ref' WHERE oid=$id";
      $res=sql( $ss, "ERR_org_save_refs" );
   }else{ // удаляем ссылку на модуль
        $ss="UPDATE organizations SET mod_ref='' WHERE oid=$id";
        $res=sql( $ss, "ERR_org_save_refs" );
   }
  return( $res );
}


function SelectModules( $mods, $cur_mod, $show, $id ){
 // выводит список модулей по курсу
 global $mod_list_table,$mod_cont_table;
                                                          //onChange=\"putElem('save_');\"
 $tmp="<SELECT style=\"width: 100%\" size=5 id='select$id' name='modules[]' >
          <option value='-1,$id'>- "._("нет")." -</option>
          <option value='-2,$id'>- "._("создать блок материалов")." -</option>";
 if( count($mods)> 0 ){
    reset($mods);
    while(list(,$mod) = each($mods)) {
//   foreach( $mods as $mod ){
    $tmp.="<OPTION value='".$mod[Id].",".$id."'";
    if( $cur_mod == $mod[Id] ) $tmp.=" selected";
    $tmp.=">";
    $tmp.=stripslashes($mod[Title]);
    $tmp.="</OPTION>";
//   }

   }
 }
 $tmp.="</SELECT>";
 return($tmp);
}

function add_new_item_structure_safe($oid){
	list($level, $after, $CID) = CCourseContent::getPositionAdd($oid);
    $sql = "INSERT INTO organizations (title, level, cid, prev_ref)
    VALUES ('"._("пустой элемент")."', '{$level}', '{$CID}', '{$after}')";
    sql($sql);
    if ($id = sqllast()) {
        sql("UPDATE organizations SET prev_ref = '$id' WHERE prev_ref = '{$after}' AND oid <> '{$id}' AND cid = '$CID'");
    }
    return $id;
}

function add_new_item( $title, $CID, $PID, $vol1="", $vol2="",$after=0, $level=0){
// echo $title;    добавляет item делая его псле последнего на нулевом уровне

    if ($after) {
        $sql = "SELECT level FROM organizations WHERE oid = ".(int) $after;
        $res = sql($sql);

        if ($item = sqlget($res)) {
            $after = CCourseContent::getLastChild($CID, $after);
            $sql = "INSERT INTO organizations (title, level, cid, prev_ref, vol1, vol2 )
            VALUES (".$GLOBALS['adodb']->Quote($title).", '".intval($item['level'])."', '$CID', '".intval($after)."', ".intval($vol1).", ".intval($vol2)." )";
            sql($sql);

            if ($id = sqllast()) {
                sql("UPDATE organizations SET prev_ref = '$id' WHERE prev_ref = '".intval($after)."' AND oid <> '$id' AND cid = '$CID'");
            }
        }
    } else {
        $id=getLastItem(  $CID );
        $sss="INSERT INTO organizations (title, level, cid, prev_ref, vol1, vol2 ) VALUES (".$GLOBALS['adodb']->Quote($title).", {$level}, '$CID', $id, ".intval($vol1).", ".intval($vol2)." )";
        //setPrev( ,$id);
        $res=sql( $sss, "ERR_navi_add" );
        $id = sqllast();
    }
    return $id;
}

function delete_item($id, $sub = false, $cid = 0){
// echo $title;


$next=getNextItem($id);
if ($sub) {
     $items = array(); $last = 0;
     foreach(CCourseContent::getChildren($cid,$id) as $item) {
         $items[$item->attributes['oid']] = $item->attributes['oid'];
         $last = $item->attributes['oid'];
     }

     if ($last) $next = getNextItem($last);
 }

 if (count($items)) {
     sql("DELETE FROM organizations WHERE oid IN ('".join("','",$items)."')");
 }

 $prev=getPrevItem( $id );

 $sss="DELETE FROM organizations WHERE oid=$id";
 $res=sql( $sss, "ERR_navi_del" );
 if( $res ){
   setPrev( $next, $prev );
 }
 //если элементов не осталось вставим пустой
 if (!countElements($cid)) {
     sql("INSERT INTO organizations (title, cid, prev_ref, level) VALUES ('"._("&lt;пустой элемент&gt;")."','{$cid}','-1', '0')");
 }
}

function delete_all_items( $cid ){
// echo $title;
 $sss="DELETE FROM organizations WHERE cid=$cid";
 $res=sql( $sss, "ERR_navi_del" );
}

function setPrev( $id, $prev ){
  if( intval($id)>=0 )
    $res=sql( "UPDATE organizations SET prev_ref=$prev WHERE oid=$id", "ERR_org_change" );
}

function getNextItem( $id ){
 $next_id=-2;
 $res=sql( "SELECT * FROM organizations WHERE prev_ref='$id'", "ERR_org_sel" );
 if( $r=sqlget($res) )
   $next_id=$r['oid'];
 return( $next_id );
}

function getLinks2Mod( $mod_id ){
 $next_id=-2;
 $res=sql( "SELECT * FROM organizations WHERE mod_ref=$mod_id", "ERR_org_sel" );
 return( $res );
}

function getPrevItem( $id ){
 $res=sql( "SELECT * FROM organizations WHERE oid=$id", "ERR_org_sel" );
 if( $r=sqlget($res) )
   $id=$r['prev_ref'];
 return( $id );
}

function change_item( $id, $title, $level ){
 echo $title;
 $sss="UPDATE INTO organizations SET (title, level) VALUES ('$title','$level') WHERE oid=$id";
//            $sql="UPDATE ".$mod_list_table." SET forum_id='' WHERE ModID='".$ModID."'";
 $res=sql( $sss, "ERR_org_change" );
}

function next_level( $id ){
 $sss="SELECT * FROM organizations WHERE oid=$id";
 $res=sql( $sss, "ERR_org_sel" );
 if( $r=sqlget($res) ){
   $l=$r['level'];
   $l++;
   $sss="UPDATE organizations SET level=$l WHERE oid=$id";
   $res=sql( $sss, "ERR_org_change" );
 }
 return( $l );
}

function prev_level( $id ){
 $sss="SELECT * FROM organizations WHERE oid=$id";
 $res=sql( $sss, "ERR_org_sel" );
 if( $r=sqlget($res) ){
   $l=$r['level'];
   if( $l > 0){
     $l--;
     $sss="UPDATE organizations SET level=$l WHERE oid=$id";
     $res=sql( $sss, "ERR_org_change" );
   }
 }
 return( $l );
}

function up_item( $id ){ // prev_ref

  $prev_id=getPrevItem( $id );
  $next_id=getNextItem( $id );
  $prev_id_1=getPrevItem( $prev_id );
  $ok="<H1>id=$id prev=$prev_id next=$next_id prevprev=$prev_id_1</H1>";
  //echo "prev_id: ".$prev_id."<br />";
  //echo "next_id: ".$next_id."<br />";
  //echo "prev_id_l: ".$prev_id_l."<br /><br />";
  if($prev_id == -1) return $ok;
  if($prev_id_l != -2) {

  	setPrev( $id, $prev_id_1 );
  	//echo "setPrev($id,$prev_id_l);<br />";
  }
  else {
  	//echo "setPrev($id,-1);";
  	setPrev($id, -1);

  }
  //echo "setPrev($prev_id, $id);<br />";
  //echo "setPrev($next_id, $prev_id);<br />";
  setPrev( $prev_id, $id );
  setPrev( $next_id, $prev_id );


  return( $ok );
}

function down_item( $id ){ // prev_ref
  $prev_id=getPrevItem( $id );
  $next_id=getNextItem( $id );
  $next_id_1=getNextItem( $next_id );
  $ok="<H1>id=$id prev=$prev_id next=$next_id nextnext=$next_id_1</H1>";
  if($next_id == -2) {
  	return $ok;
  }
  setPrev( $id, $next_id );
  setPrev( $next_id, $prev_id );
  if($next_id_1 != -2) {
  	setPrev( $next_id_1, $id );
  }
  return( $ok );
}


function show_add_item( $MID, $CID )
{
  //$extra1="<INPUT type=text name='item_vol1' size=2 value=2> ак.час.";
  //$extra2="<INPUT type=text name='item_vol2' size=2 value=2> ак.час.";
  return( showBox( "teachers/edit_navigation.php" /*manage_course.php4",*/,  $CID, "item", "", $extra1."<BR>".$extra2 ));
  //"<input align=right onClick=\"window.close(); opener.location.reload();\" type=\"image\" src=\"{$GLOBALS['controller']->view_root->skin_url}/images/b_done.gif\" alt=\"Готово\" border=0 style=\"border: 0\">") ;

}

function edit_item( $id, $CID )
{
  //$r=get_item_par( $id );
  $extra="<INPUT type=text name='item_vol' size=2 value=".$r[vol]."> "._("ак.час.");

  return( showBox( "teachers/edit_navigation.php" ,  $CID, "item_ed", "", $extra )) ;
}

function set_item_title( $id, $title )
{
  $res=sql("UPDATE organizations SET title=".$GLOBALS['adodb']->Quote($title)." WHERE oid='$id'", "ERR - update Item");
  return($res);
}

function save_item_metadata( $id, $metadata )
{
  $res=sql("UPDATE organizations SET metadata=".$GLOBALS['adodb']->Quote($metadata)." WHERE oid='$id'", "ERR - update Item metadata");
//  echo $res."!!!".$id."<BR>".$metadata."<BR>";

  return($res);
}

function getLevel( $oid ){
   // возврашает уровень в слпуктуре
   $res=sql( "SELECT * FROM organizations WHERE oid=$oid", "ERR_navi" );
   $r=sqlget($res);
   sqlfree($res);
   return($r[level]);
}

function setLevel( $oid, $level ){
   // устанавливает уровень в слпуктуре
   $res=sql( "UPDATE INTO organizations SET level=$level WHERE oid=$oid", "ERR_navi1" );
   sqlfree($res);
}


function getLink( $oid ){
   // возвращает ссылку на модуль
   $res=sql( "SELECT * FROM organizations WHERE oid=$oid", "ERR_navi3" );
   $r=sqlget($res);
   sqlfree($res);
   return($r[mod_ref]);
}

function setLink( $oid, $ref ){
   // устанавливает ссылку на модуль
   $res=sql( "UPDATE INTO organizations SET mod_ref=$ref WHERE oid=$oid", "ERR_navi4" );
   sqlfree($res);
}

//function get_mod_title($mod)
//INTO organizations SET (title, level) VALUES ('$title','$level') WHERE oid=$id";

function navi_key( $id, $CID, $act, $char, $title="" ){
   $tmp=" <a href='".$GLOBALS['sitepath']."teachers/edit_navigation.php?CID=$CID&itemID=".$id."&make=$act' title='$title'>$char</a> ";
   return $tmp;
}

function change_key($id, $CID) {
   $tmp = "<a href=\"javascript:void(0);\" onClick = \"window.location.href = '".$GLOBALS['sitepath']."teachers/edit_navigation.php?CID=$CID&make=edit_item&item_id=$id&new_id=o"."'+parent.leftFrame.organizationItemId; return false;\" title='"._("добавить")."'>".getIcon("change_structure_item",_("заменить"))."</a>";
   return $tmp;
}

function add_key($id, $CID) {
   $tmp = "<a href=\"".$GLOBALS['sitepath']."teachers/edit_navigation.php?CID=$CID&make=additem&after=$id\" title='"._("добавить")."'>".getIcon("add_structure_item",_("добавить"))."</a>";
   return $tmp;
}

function edit_key( $id ){
//         $navy_keys.=navi_key( $r[oid], $CID, "edit_item",getIcon("edit"), "править" );
   $tmp="<a href=\"javascript:void(0);\" title='"._("править")."' onClick=\"putElem('edit_$id');\">".getIcon("edit",_("править"))."</a>";
//   $tmp="<span cursor='hand' title='"._("править")."' onClick=\"putElem('edit_$id'); treeSelectGet{$id}(0);\">".getIcon("edit",_("править"))."</span>";
   return $tmp;
}

function getOrganizationItems($id, $selected) {
    $smarty = new Smarty_els();
    $smarty->assign('id',$id);
    $smarty->assign('list_name','module_'.$id);
    $smarty->assign('container_name','container_module_'.$id);
    $smarty->assign('list_extra'," style=\"width: 300px;\" ");
    $smarty->assign('list_default_value', 0);
    $smarty->assign('list_selected', (int) $selected);
    $smarty->assign('url',$GLOBALS['sitepath'].'course_structure_items.php');
    return $smarty->fetch('control_treeselect2.tpl');
}

function getFullOrganization($CID = 0) {
	$organization = array(); $maxLevel = 0; $CID = intval($CID);
	$sql = !$CID ? "SELECT CID, Title FROM Courses WHERE type = '1' AND Status > 0 ORDER BY ordinal, Title" : "SELECT CID, Title FROM Courses WHERE Status > 0 AND CID = {$CID}";
	$res = sql($sql);
	while($row = sqlget($res)) {
		$item['title'] = htmlspecialchars($row['Title'],ENT_QUOTES);
		$item['module'] = 0;
		$item['level'] = 0;
		$item['cid'] = $row['CID'];
        //$organization[] = $item;
        $subItems = get_organization($row['CID']);
        $subItems = sort_organization($subItems, 1, $row['CID']);
        for($i=0;$i<count($subItems);$i++) {
        	//$subItems[$i]['level']++;
        	$subItems[$i]['cid'] = $row['CID'];
        	$organization[] = $subItems[$i];
        	if ($subItems[$i]['level'] > $maxLevel) $maxLevel = $subItems[$i]['level'];
        }
	}
	return array($organization,$maxLevel);
}

function countElements($CID, $level = 'unknown') {
    $where = '';
    if ($level !== 'unknown') {
        $where = " AND level = '".(int) $level."'";
    }
    $sql = "SELECT COUNT(oid) as count FROM organizations WHERE cid = '".(int) $CID."' $where";
    $res = sql($sql);

    while($row = sqlget($res)) {
        return $row['count'];
    }
}

function fixOganizationTree($cid){
    if ($cid) {
        //На случай если корневой элемент структуры курса повреждён
        $res = sql("SELECT oid, prev_ref FROM organizations WHERE cid = '$cid'");
        $orgs = $broken_nodes = array();
        while ($row = sqlget($res)) {
            $orgs[$row['prev_ref']] = $row['oid'];
        }
        if (!in_array('-1',array_keys($orgs))) {
            foreach ($orgs as $prev_ref=>$oid) {
                if (!in_array($prev_ref,$orgs)) {
                    $broken_nodes[] = $oid;
                }
            }
            if (count($broken_nodes) > 0) {
                if (count($broken_nodes) > 1) {
                    $GLOBALS['controller']->setMessage(_('Несколько элементов структуры курса были повреждены и не пожлежат восстановлению'));
                }
                $res = sql("UPDATE organizations SET prev_ref = -1, level = 0 WHERE oid = '{$broken_nodes[0]}'");
                //$duumy = sqlrows($res);
            }else {
                $GLOBALS['controller']->setMessage(_('Отсутствует корневой элемент структуры курсов! Восстановление невозможно, очистить структуру курса?'),false,$GLOBALS['sitepath']."teachers/edit_navigation.php?make=delete_all&CID=$cid");
            }
        }
    }
}
?>