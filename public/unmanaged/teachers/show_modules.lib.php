<?

define("COLUMN_WIDTH_EXPANDED", 200); //px
define("COLUMN_WIDTH_COLLAPSED", 45); //px

function show_mod_title( $CID, $pid, $ModId, $mod_name ){
  global $sitepath;
  $tmp="<form name='editModProp' action='".$sitepath."teachers/mod_properties.php4' method='GET' target='editMod'>
               <input type='hidden' name='make' value='editModp'>
               <input type='hidden' name='PID' value='".$pid."'>
               <input type='hidden' name='ModID' value='".$ModId."'>
               <input type='hidden' name='CID' value='".$CID."'>
              <span style='cursor:hand'
                onClick=\"window.open('', 'editMod', 'width=540,height=350,scrollbars=yes,titlebar=0,resizable=yes');
                editModProp.submit();\" title=\"\">
              <FONT SIZE=+1><span class=kurs><u>".$mod_name."</u></span></FONT></span>
         </form>";
  return( $tmp );

}

function show_mod_description( $res ){

   $tmp="<span class=testsmall><Br>";

   $tmp.="курс: ".$res['course_name']."<br>";
/*   if ( strlen($res['theme'])>1 && $res['theme']!="")
     $tmp.="тема: ".get_theme("1",$res['theme'])."<br>";
*/   if ( strlen($res['pub'])>1 )
     $tmp.="статус: ".get_status($res['pub'])."<br>";
   if (strlen($res['descript'])>0)
     $tmp.="описание: ".get_descr($res['descript'])."<br>";
   $tmp.="</span>";
   return($tmp);
}

function  show_mod_edit( $CID, $pid, $i, $ModID, $res ){
  global $sitepath;
  global $s;
  if(check_teachers_permissions(19, $s[mid])) {
          $tmp="<form name='editModp1".$i."' action='".$sitepath."teachers/podmod_properties.php4' method='POST'
         target='editModpWin".$i."'>
         <input type='hidden' name='ModID' value='".$ModID."'>
         <input type='hidden' name='McID' value='".$res['McID']."'>
         <input type='hidden' name='att_name' value='".basename($res['mod_l'])."'>
         <input type=\"hidden\" name=\"showfull\" value=\"".$showfull."\">
         <input type=\"hidden\" name=\"PID\" value=\"".$pid."\">
         <input type=\"hidden\" name=\"make\" value=\"editpodMod\">
         <input type=\"hidden\" name=\"CID\" value=\"".$CID."\">
         <span style='cursor:hand' class='cHilight'
              onClick=\"window.open('', 'editModpWin".$i."', 'width=540,height=350,scrollbars=0,titlebar=0, resizable=yes');
                        editModp1".$i.".submit();\" title=\""._("Править")."\">".getIcon("edit")."</span>
        </form>";
  }
  else {
          $tmp = "";
  }
  return( $tmp );
}

function show_mod_del($CID, $pid, $i, $ModID, $res, $showfull=1){
  global $sitepath;
  global $s;
  if(check_teachers_permissions(19, $s[mid])) {
          $tmp="<form name='delMod".$i."' action='".$sitepath."teachers/edit_mod.php4?make=delete' method='POST' target='_self'>
          <input type='hidden' name='ModID' value='".$ModID."'>
          <input type='hidden' name='McID' value='".$res['McID']."'>
          <input type=\"hidden\" name=\"popup\" value=\"{$GLOBALS['popup']}\">
          <input type=\"hidden\" name=\"PID\" value='".$pid."'>
          <input type='hidden' name='att_name' value='".basename($res['mod_l'])."'>
          <input type=\"hidden\" name=\"showfull\" value=\"".$showfull."\">
          <input type=\"hidden\" name=\"CID\" value=\"".$CID."\">
          <span class='cHilight' style='cursor:hand' onClick=\"if (confirm('"._("Вы уверены, что хотите удалить?")."'))
             delMod".$i.".submit()\" title=\""._("удалить")."\">".getIcon("delete")."</span>
        </form>";
  }
  else {
          $tmp="";
  }
  return( $tmp );
}

function show_forum_del($CID, $pid, $i, $ModID, $res, $showfull=1){
  global $sitepath;
  global $s;
  if($GLOBALS['s']['perm']>=2/*check_teachers_permissions(19, $s[mid])*/) {
          $tmp="<form name='delMod".$i."' action='".$sitepath."teachers/edit_mod.php4' method='POST' target='_self'>
               <input type='hidden' name='ModID' value='".$ModID."'>
               <input type='hidden' name='make' value='del_forum'>
               <input type='hidden' name='McID' value='".$res['McID']."'>
               <input type=\"hidden\" name=\"PID\" value=\"".$pid."\">
               <input type=\"hidden\" name=\"popup\" value=\"{$GLOBALS['popup']}\">
               <input type=\"hidden\" name=\"showfull\" value=\"".$showfull."\">
               <input type=\"hidden\" name=\"CID\" value=\"".$CID."\">
               <span class='cHilight' style='cursor:hand' onClick=\"if (confirm('"._("Вы уверены, что хотите удалить?")."'))
                delMod".$i.".submit()\" title=\""._("удалить")."\">".getIcon("delete")."</span>
           </form>";
  }
  else {
          $tmp="";
  }
  return( $tmp );
}                                                                    //&#251;

function show_mod_content( $CID, $pid, $res, $ModId, $mode ) {
    global $mod_cont_table;
    global $s, $sitepath;
    $showfull=$mode;

    if($_GET['in_frame']) {
                if(!defined("IS_TWO_DISPLAYS")) {
                      define("IS_TWO_DISPLAYS", false);
                  }
                $tmp .= '
                <script>
                        function swapWidth() {
                        obj_frameset=top.document.getElementById(\'mainFrameset\');
                        str_cols=obj_frameset.cols;
                        state1=\''.COLUMN_WIDTH_EXPANDED.',*\';
                        state2=\''.COLUMN_WIDTH_COLLAPSED.',*\';
                        obj_frameset.cols = (str_cols==state2) ? state1 : state2;
                        return true;
                        }
                        function createAdditionalFrame() {
                        var obj_frameset = top.document.getElementById(\'mainFrameset\');
                         f = top.document.createElement(\'frame\');
                         obj_frameset.appendChild(f);
                         obj_frameset.cols = \''.COLUMN_WIDTH_COLLAPSED.','.(IS_TWO_DISPLAYS?'50%,':'').'*\';

                        }
                        swapWidth();
                </script>
                <a href="'.$sitepath.'" target="_top" class="coursename1"><img src="'.$sitepath.'images/icons/book.gif" border=0 alt="'.cid2title($CID).'" vspace=5 hspace=5></a><br>';
    }
    if ($_GET['in_frame']) {
        $tmp .= '<span id="menu_links" class=hidden>
                 &nbsp;&nbsp;&nbsp;<a style="cursor: hand" onClick="swapWidth();removeElem(\'menu_links\');putElem(\'menu_void\');" title="'._("свернуть").'"><span style="font-family:Webdings; color:black;">6</span></a>';
       $tmp_hidden = '
                <span id="menu_void">
                &nbsp;&nbsp;&nbsp;<a style="cursor: hand" onClick="swapWidth();removeElem(\'menu_void\');putElem(\'menu_links\');" title="'._("развернуть").'"><span style="font-family:Webdings; color:black;">4</span></a>
               ';
   }
   
   $GLOBALS['controller']->captureFromVar(CONTENT_COLLAPSED, 'tmp_hidden', $tmp_hidden);
   $GLOBALS['controller']->captureFromVar(CONTENT_EXPANDED, 'tmp', $tmp);
   if (!$pid && !$mode && $_SESSION['s']['location']) {
       if ($_SESSION['s']['perm']==2) {
           $js = "
           <script type=\"text/javascript\" src=\"../lib/scorm/request.js\"></script>
           <script type=\"text/javascript\">
           <!--
           window.sendLocation = function (location) {
               if (typeof(location)!='undefined') {
                   var request = NewHttpReq();
                   DoRequest(request, '{$GLOBALS['sitepath']}teachers/location.php', 'sheid='+'".(int) $_SESSION['s']['location']."&location='+location);
               }
           }
           //-->
           </script>
           ";
           $tmp .= $js;
       }
       if ($_SESSION['s']['perm']==1) {
           $js = "
           <script type=\"text/javascript\" src=\"../lib/scorm/request.js\"></script>
           <script type=\"text/javascript\">
           <!--
           globalLocation = 0;
           globalWatch = 0;
           function checkLocation() {
               globalWatch = 0;
               var elm = document.getElementById('watch');
               if (elm && elm.checked) {
                   globalWatch = 1;
                   var request = NewHttpReq();
                   var location = DoRequest(request, '{$GLOBALS['sitepath']}teachers/location.php', 'sheid='+'".(int) $_SESSION['s']['location']."');
                   
                   if ((location > 0) && (location!=globalLocation)) {
                       globalLocation = location; 
                       e = document.getElementById('module_content_'+location);
                       if (e) {
                           if (e.fireEvent) { // ie
                               e.fireEvent('onClick');
                           } else {
                               var evt = document.createEvent('MouseEvents');
                               evt.initEvent('click', true, false);
                               e.dispatchEvent(evt);
                           }
                           if (target = e.target) {
                               if (t = parent.document.getElementById(target)) {
                                   t.src = e.href;
                               }
                           }
                       }
                   }
               }
               timer = setTimeout('checkLocation()',5000);
           }                      
           //-->
           </script>
           ";
           $tmp .= $js;
       }
   }   
   $i=0;
   $sql="SELECT Title,type,McID,mod_l FROM ".$mod_cont_table." WHERE ModID='".$ModId."' ORDER BY McID";
   $sql_result=sql($sql);
   if(sqlrows($sql_result)>0) {
     $tmp.="<H3><U>"._("Изучить")."</U></H3>";
//     $tmp_hidden.="<H3></H3>";
     $tmp.="<table border=0 cellpadding=0 cellspacing=0 align=center width=\"100%\" class=cHilight>";
     $tmp_hidden.="<table border=0 cellpadding=0 cellspacing=0 align=center width=\"100%\" class=cHilight>";
     while($r = sqlget($sql_result)) {
         $i++;
         $tmp.="<tr><td>";
         $tmp_hidden.="<tr><td>";
         $tmp.=($showfull) ?
//         $tmp.=( ($s[perm] == 2)&&(check_teachers_permissions(19,$s['mid'])) ) ?
                         "<LI><a id=\"module_content_$i\" href='show_mod.php4?ModID=".$ModId."&McID=".$r['McID']."' target='show_win".$i."'
                  onclick=\"if (window.sendLocation) window.sendLocation($i); window.open ('', 'show_win".$i."','status=no,toolbar=yes,menubar=no,scrollbars=yes,resizable=yes,width=600, height=600');\">"
               .$r['Title'].//$r['mod_l'].
              "<br /><br /></a>"
              :
                         "<LI><a id=\"module_content_$i\" href='show_mod.php4?ModID=".$ModId."&McID=".$r['McID']."' onclick='if (window.sendLocation) window.sendLocation($i); expanded(false)' target='mainFrame'>".$r['Title']."</a>"
              ;
//              todo security 
		 $condition = ($GLOBALS['controller']->enabled) ? false : check_teachers_permissions(19,$s['mid']);
         $tmp_hidden.=($condition) ?
                         ""
              :
                         "<a id=\"module_content_hidden_$i\" onClick=\"if (window.sendLocation) window.sendLocation($i);\" href='show_mod.php4?ModID=".$ModId."&McID=".$r['McID']."' target='mainFrame' title='{$r['Title']}'><img src='{$sitepath}images/menu/bullet.gif' border='0' id='dm{$i}' name='dm{$i}' vspace='8' hspace='15'></a>";
         $tmp.="</td>";
         $tmp_hidden.="</td>";

//         if( ($s[perm] >= 2)&&(check_teachers_permissions(19, $s['mid'])) ) $mode = 1;
         if ( $mode ){
            $tmp.="<td width=5%> ";
            $tmp.=show_mod_edit( $CID, $pid, $i, $ModId, $r );
            $tmp.=" </td><td width=5%> ";
            $tmp.=show_mod_del($CID, $pid, $i, $ModId, $r, $showfull);
            $tmp.="</td>";
         }
         $tmp.="</tr>";
         $tmp_hidden.="</tr>";
     }
     $tmp.="</table>";
     $tmp_hidden.="</table>";
   }
   
   $res['test_id'] = trim($res['test_id']);
   
   if (strlen($res['test_id'] )){
     $i++; 
     $tmp.="<H3><U>"._("Выполнить")."</U></H3>";
     $tmp_hidden.="<H3></H3>";

//     if( ($s[perm] == 2)&&(check_teachers_permissions(19,$s['mid'])) )  {
     if($showfull)  {

       $tmp.=show_tests_link(explode(";",$res['test_id']),$ModId,$CID,$pid,$showfull,$i,1,0,$i);
     }
     else    {

       $tmp.=show_tests_link(explode(";",$res['test_id']),$ModId,$CID,0,$showfull,$i,0,0,$i);
       $tmp_hidden.=show_tests_link(explode(";",$res['test_id']),$ModId,$CID,0,$showfull,$i,0,1,$i);
     }
   }
    
   $res['run_id'] = trim($res['run_id']);
   if(strlen($res['run_id']) && $res['run_id']) {
       $i++;
       $run_ids = explode(";", $res['run_id']);
       if(is_array($run_ids)) {
          $tmp.="<h3><u>"._("Запустить программу")."</u></h3>";
          $tmp_hidden.="<h3></h3>";
          if($showfull) {
//          if( ($s[perm] == 2)&&(check_teachers_permissions(19, $s['mid'])) ) {
                  $tmp .= show_run_links($ModId, $CID, $showfull, 1, 0, $i);
          }
          else {
                  $tmp .= show_run_links($ModId, $CID, $showfull, 0, 0, $i);
                  $tmp_hidden .= show_run_links($ModId, $CID, $showfull, 0, 1, $i);
          }
       }
   }
   $res['forum_id'] = trim($res['forum_id']);
   if ( strlen( $res['forum_id'] ) && $res['forum_id'] ){
      $i++; 
      $tmp.="<H3><U>"._("Задать вопросы")."</U></H3>";
      $tmp_hidden.="<H3></H3>";
      $tmp.="<table width=100%>";
      $tmp_hidden.="<table width=100%>";
      $tmp.="<tr><td width=95%>";
      $tmp_hidden.="<tr><td width=95%>";
      $i++;
      $tmp.=show_forum_link( $res['forum_id'], $ModId, $i, 0, $i);
      $tmp_hidden.=show_forum_link( $res['forum_id'], $ModId, $i, 1, $i);
     $tmp.="</td>";
     $tmp_hidden.="</td>";
      if( $mode ){
        if ( $pid ){
          $tmp.="<td width=5% valign=bottom>";
          $tmp.=show_forum_del($CID, $pid, $i, $ModId, $res );
          $tmp.="</td>";
        }
      }
      $tmp.="</tr></table>";
      $tmp_hidden.="</tr></table>";
   }

   if (!$pid && !$mode && ($_SESSION['s']['perm']==1) && $_SESSION['s']['location']) {
        $js = "
        <table class=main cellspacing=0>
        <tr><td>
        <br><br>
        <input type=\"checkbox\" checked id=\"watch\" name=\"watch\" value=\"1\" onClick=\"document.getElementById('watch').checked = this.checked; checkLocation();\"> <small>"._('синхронизировать с преподавателем')."</small>
        <script type=\"text/javascript\">
        <!--
        checkLocation();
        //-->
        </script>
        </td></tr>
        </table>
        ";
        $tmp .= $js;
   }
   
   // Glossary
   if (CGlossaryWord::isWordsExist($CID) && !$mode) {
       $tmp .= "<p><a title=\""._('Глоссарий')."\" href=\"javascript:void(0);\" onClick=\"window.open('{$GLOBALS['sitepath']}glossary.php?mini&cid={$CID}','glossary','toolbar=0, status=0, menubar=0, scrollbars=1, resizable=1, width=400, height=300');\"><img src=\"{$GLOBALS['sitepath']}images/icons/book.gif\" border=0 alt=\""._("Глоссарий")."\" align=absmiddle> &nbsp; "._("Глоссарий")."</a>";
       $tmp_hidden .= "<p><a title=\""._('Глоссарий')."\" href=\"javascript:void(0);\" onClick=\"window.open('{$GLOBALS['sitepath']}glossary.php?mini&cid={$CID}','glossary','toolbar=0, status=0, menubar=0, scrollbars=1, resizable=1, width=400, height=300');\"><img src=\"{$GLOBALS['sitepath']}images/icons/book.gif\" border=0 alt=\""._("Глоссарий")."\"></a>";
   }
   
   $GLOBALS['controller']->captureStop(CONTENT_EXPANDED);
   $GLOBALS['controller']->captureStop(CONTENT_COLLAPSED);

   
$tmp .= "</span>"; //menu_links
$tmp_hidden .= "</span>"; //menu_links

if ($_GET['in_frame']) {
	$tmp .= $tmp_hidden;
}
   return( $tmp );
}

function start_block_( $B ){
 $tmp="<table border=".$B." cellspacing='0' cellpadding='0' width='100%'>
           <tr><td height=2></td></tr>
           <tr class=br>
             <td>
             <table align='center' width='100%' cellpadding='5' cellspacing='1' class='tests'  border=".$BORDER.">";

 return( $tmp );
}

function show_block_title( $text ){
   $tmp="<tr><td class=shedadd>
                   $text</td>
           </tr>
           <tr><td height=2></td>
           </tr>";
   return( $tmp );

}
//function show_tests_del($test,$ModID,$CID,$pid,$showfull,$num_pos){

function show_tests_link($test,$ModID,$CID,$pid,$showfull,$num_pos, $with_del = 0, $only_bullet = 0, $number=0){
   global $servletpath;
   global $test_title_table;
   global $sitepath;
   global $BORDER;
   global $s;

   $all=count($test);

   $tmp="<TABLE width=100%>";
   $j=1;
   for($i=0;$i<$all;$i++){
      if (!(integer)$test[$i]) continue;
      $tmp.="<TR><TD width=95%>";
      $tmp.="
      <script language='javascript'>
       function mergeFrames() {
               if(".(IS_TWO_DISPLAYS?1:0).") {
                     obj_frameset = top.document.getElementById('mainFrameset');
                     obj_frameset.cols = '50%,*';
                     obj_mainFrame = top.document.getElementById('mainFrame');
                     obj_mainFrame.src = 'about:blank';
                }


       }
      </script>
      ";
      $num_pos++;
      $title=getField("test","title","tid",$test[$i]);
      $title=(strlen($title)<60) ? $title : substr($title,0,57)." ...";
      $tmp.="";//.$num_pos;
//      if ($s[perm] == 2) {
//         $tmp.= "<LI><a href='".$sitepath."test_start.php?tid=".$test[$i]."' target='test_window_".$test[$i]."'
//                 onclick=\"window.open('','test_window_".$test[$i]."','status=yes,width=600,height=600,scrollbars=yes,titlebar=0,resizable=yes');
//         \">
//         Задание N ".($i+1).": ".$title."
//         </a>";
//      } else {
      //$tmp.= ($only_bullet) ? "<a href='{$sitepath}test_start.php?tid=".$test[$i]."' ".(IS_TWO_DISPLAYS?"":"target='_blank'")." onClick='mergeFrames();' title='Задание N ".($i+1).": ".$title."'><img src='{$sitepath}images/menu/bullet.gif' border='0' id='dm{$i}' name='dm{$i}' vspace='8' hspace='15'></a>" : "<a href='{$sitepath}test_start.php?tid=".$test[$i]."' ".(IS_TWO_DISPLAYS?"":"target='_blank'")." onClick='mergeFrames();'><img src='{$sitepath}images/menu/bullet.gif' border='0' id='dm{$i}' name='dm{$i}' vspace='8' hspace='15'>Задание N ".($i+1).": ".$title."</a>";      
      $tmp.= ($only_bullet) ? "<a id=\"module_content_hidden_$number\" href='{$sitepath}test_start.php?tid=".$test[$i].($pid ? '' : "&ModID=".(int) $ModID)."' ".(IS_TWO_DISPLAYS?"":"")." onclick='if (window.sendLocation) window.sendLocation($number); expanded(false)' title='"._("Задание")." N ".($i+1).": ".$title."' target='mainFrame'><img src='{$sitepath}images/menu/bullet.gif' border='0' id='dm{$i}' name='dm{$i}' vspace='8' hspace='15'></a>" : "<a id=\"module_content_$number\" href='{$sitepath}test_start.php?tid=".$test[$i].($pid ? '': "&ModID=".(int) $ModID)."' ".(IS_TWO_DISPLAYS?"":"")." onclick='if (window.sendLocation) window.sendLocation($number); expanded(false)' target='mainFrame'><img src='{$sitepath}images/menu/bullet.gif' border='0' id='dm{$i}' name='dm{$i}' vspace='8' style=\"margin-right: 10px\" align=absmiddle>"._("Задание")." N ".($j).": ".$title."</a>";
//      }
      $tmp.="</TD>";

      if ($pid){
        $tmp.="<TD width=5%>";
        if($GLOBALS['s']['perm']>=2/*check_teachers_permissions(19, $s[mid])*/) {
                       $tmp.="<form method='POST' action=\"".$sitepath."teachers/edit_mod.php4\" name=\"delete_window_".$test[$i]."\">
                          <input type=\"hidden\" name=\"test_del_id\" value=".$test[$i].">
                          <input type=\"hidden\" name=\"ModID\" value=".$ModID.">
                          <input type=\"hidden\" name=\"CID\" value=".$CID.">
                          <input type=\"hidden\" name=\"PID\" value=".$pid.">
                          <input type=\"hidden\" name=\"popup\" value=\"{$GLOBALS['popup']}\">
                          <input type=\"hidden\" name=\"showfull\" value=".$showfull.">

                        <input type=\"hidden\" name=\"make\" value=\"del_test\">
                        <span class='cHilight' style='cursor:hand'
                              onClick=\"if (confirm('"._("Вы уверены, что хотите удалить?")."')) delete_window_".$test[$i].".submit()\"
                               title=\""._("удалить задание из модуля")."\">".getIcon("delete")."</span>
                        </form>";
        }
        else {
                $tmp.="";
        }
        $tmp.="</TD>";
      }
      $tmp.="</TR>";
      $j++;
   }

   $tmp.="</TABLE>";
   return( $tmp );
}

function show_run_links($ModId, $CID, $showfull, $with_del = 0, $only_bullet = 0, $number=0) {

        $query = "SELECT * FROM mod_list WHERE ModID = $ModId";
        $result = sql($query, "errfn7738");
        $row = sqlget($result, "errfn63782");

        $run_ids_array = explode(";", $row['run_id']);
        if(is_array($run_ids_array)) {
           $return_value = "<table width='97%' border='0'>";
           foreach($run_ids_array as $key => $run_id) {
                   if(trim($run_id) == "") continue;
                   $training_run = get_training_run_parameter($run_id);
                   $return_value .= "<tr>";
                   $return_value .= "<OBJECT ID='Runner' width='0' height='0' CLASSID='CLSID:E3E38406-88AB-11D3-B551-080030810427'></OBJECT>";
                                   $str = ($only_bullet) ? "<img src='{$GLOBALS['sitepath']}images/menu/bullet.gif' border='0' id='dm{$i}' name='dm{$i}' vspace='8' hspace='15'>" : $training_run['name'];
                   $return_value .= "<td>".($only_bullet ? '' : '<li>')."<a id=\"module_content_".($only_bullet ? "hidden_" : "")."$number\" href='#' onClick=\"javascript:if (window.sendLocation) window.sendLocation($number); document.getElementById('Runner').Run('".$training_run['exe']."', 'open', '', '".$training_run['path_to']."', 5);\" title='{$training_run['name']}'>{$str}<br /><br /></td>";
                   if(($with_del)&&($GLOBALS['s']['perm']>=2/*check_teachers_permissions(19, $s[mid])*/)) {
                      $return_value .= "<form action='edit_mod.php4' method='POST' id='del_run_form'\">";
                      $return_value .= "<input type=\"hidden\" name=\"run_id\" value=".$run_id.">
                                        <input type=\"hidden\" name=\"ModID\" value=".$ModId.">
                                        <input type=\"hidden\" name=\"CID\" value=".$CID.">
                                        <input type=\"hidden\" name=\"showfull\" value=".$showfull.">
                                        <input type=\"hidden\" name=\"popup\" value=\"{$GLOBALS['popup']}\">
                                        <input type=\"hidden\" name=\"make\" value=\"del_run\">";
                      $return_value .= "<td align='right'><a href='#' onClick=\"if(confirm('Are you sure that you want delete this item?')) getElementById('del_run_form').submit();\">".getIcon("delete")."</a></td>";
                      $return_value .= "</form>";
                   }
                   $return_value .= "</tr>";
           }
           $return_value .= "</table>";
        }
        return $return_value;
}

function get_training_run_parameter($run_id) {
         $query = "SELECT * FROM training_run WHERE run_id = $run_id";
         $result = sql($query);
         $row = sqlget($result);
         $return_value['name'] = $row['name'];
         $items_of_path = explode("\\", $row['path']);
         $prev_value = "";
         $return_value['path_to'] = "";
         if(is_array($items_of_path)) {

            foreach($items_of_path as $key => $item) {
                    if($prev_value) {
                             $return_value['path_to'] .= $prev_value."\\";
                    }
                    $prev_value = $item;
            }
         }
         $return_value['path_to'] = trim(addslashes($return_value['path_to']), "\\");
         $return_value['exe'] = $prev_value;

         return $return_value;
}

function show_forum_link( $f_link, $ModID, $i, $only_bullet=0, $number=0)
{
   global $forummessages;
   global $forumthreads;
   global $sitepath;
   global $mod_list_table;
   global $BORDER;

   $sql = "SELECT id, message,forummessages.name, email, ".$forumthreads.".lastpost,".$forumthreads.".answers
         FROM ".$forummessages.",".$forumthreads."
         WHERE ".$forummessages.".thread= '".(int) $f_link."'
           AND ".$forummessages.".thread=".$forumthreads.".thread AND
           ".$forummessages.".is_topic=1";
   $result=sql($sql);
   if (sqlrows($result)>0)
     while ($res=sqlget($result)){
        $text=(strlen($res['message'])<60) ? $res['message'] : substr($res['message'],0,57)." ...";
        $text = $res['name'];
      	$str = ($only_bullet) ? "<img src='{$sitepath}images/menu/bullet.gif' border='0' id='dm' name='dm' vspace='8' hspace='15'>" : $text;
			/*$tmp.= "<a href='".$sitepath."forum.php4?thread=".$f_link."&showfull=1' target='show_mod".$i.
           "'onclick=\"window.open ('', 'show_mod".$i."','width=600,height=500,scrollbars=1,titlebar=0,resizable=yes');\"".
                " title='{$text}'>".$str.
      "</a>";*/
			$tmp.= ($only_bullet ? "" : "<li>")."<a id=\"module_content_".($only_bullet ? "hidden_" : "")."$number\" href='{$sitepath}forum.php?thread={$f_link}&view=blank' onclick='if (window.sendLocation) window.sendLocation($number); expanded(false)' title='{$text}' target='mainFrame'>{$str}</a>";      

     }
   else{
     $tmp.=_("Данный форум был удалён либо дана ошибочная сслыка, измените ссылку на форум");

     $sql="UPDATE ".$mod_list_table." SET forum_id='' WHERE ModID='".$ModID."'";
     $sql_result=sql($sql);
   }
   return $tmp;
}

/*function show_courseprofile($CID){
{
   global $sitepath;
   global $sess;
   global $teach;

   if (!$teach) return ("");

   echo "</table></td></tr>";
}*/
?>