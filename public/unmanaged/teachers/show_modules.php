<?
function show_mod_title( $CID, $pid, $ModId, $mod_name ){
  global $sitepath;
  $tmp="<form name='editModProp' action='".$sitepath."teachers/mod_properties.php4' method='POST' target='editMod'>
               <input type='hidden' name='make' value='editModp'>
               <input type='hidden' name='PID' value='".$pid."'>
               <input type='hidden' name='ModID' value='".$ModID."'>
               <input type='hidden' name='CID' value='".$CID."'>

              <span style='cursor:hand'
                onClick=\"window.open('', 'editMod', 'width=540,height=350,scrollbars=yes,titlebar=0,resizable=yes');
                editModProp.submit();\" title=\"\">
              <FONT SIZE=+1><u>".$mod_name."</u></FONT></span>
         </form>";
  return( $tmp );

}

function show_mod_description( $res ){

   $tmp.="<form action='".$sitepath."teachers/mod_properties.php4' target=_self id=self method=POST>";
   $tmp.="</form>";
   $tmp="<TABLE align=\"left\" width=\"50%\">
                  <tr>
                     <td class=testsmall>
                     <span class=cGray>"._("тема:")." ".get_theme($res['theme'])."</span><br>
                     <span class=cGray>"._("статус:")." ".get_status($res['pub'])."</span><br>
                     <span class=cGray>"._("курс:")." ".$res['course_name']."</span><br>
                     <span class=cGray>"._("описание:")." ".get_descr($res['descript'])."</span><br>
                     </td>
                  </tr>
           </table>";
   return($tmp);
}

function  show_mod_edit( $CID, $pid, $i, $ModID, $res ){
  global $sitepath;
  $tmp="<form name='editModp1".$i."' action='".$sitepath."teachers/podmod_properties.php4' method='POST'
         target='editModpWin".$i."'>
         <input type='hidden' name='ModID' value='".$ModID.">
         <input type='hidden' name='McID' value=".$res['McID'].">
         <input type='hidden' name='att_name' value='".basename($res['mod_l'])."'>
         <input type=\"hidden\" name=\"showfull\" value=\"".$showfull."\">
         <input type=\"hidden\" name=\"PID\" value=\"".$pid."\">
         <input type=\"hidden\" name=\"make\" value=\"editpodMod\">
         <input type=\"hidden\" name=\"CID\" value=\"".$CID."\">
         <span style='cursor:hand' class='cHilight'
              onClick=\"window.open('', 'editModpWin".$i."', 'width=540,height=350,scrollbars=0,titlebar=0, resizable=yes');
                        editModp1".$i.".submit();\" title=\""._("Править")."\">&#252;</span>
        </form>";
  return( $tmp );
}

function show_mod_del($CID, $pid, $i, $ModID, $res, $showfull=1){
  global $sitepath;
  $tmp="<form name='delMod".$i."' action='".$sitepath."teachers/edit_mod.php4?make=delete' method='POST' target='_self'>
          <input type='hidden' name='ModID' value='".$ModID."'>
          <input type='hidden' name='McID' value='".$res['McID']."'>
          <input type=\"hidden\" name=\"PID\" value='".$pid."'>
          <input type='hidden' name='att_name' value='".basename($res['mod_l'])."'>
          <input type=\"hidden\" name=\"showfull\" value=\"".$showfull."\">
          <input type=\"hidden\" name=\"CID\" value=\"".$CID."\">
          <span class='cHilight' style='cursor:hand' onClick=\"if (confirm('"._("Вы уверены, что хотите удалить?")."'))
             delMod".$i.".submit()\" title=\""._("удалить")."\">&#251;</span>
        </form>";
  return( $tmp );
}

function show_forum_del($CID, $pid, $i, $ModID, $res, $showfull=1){
  global $sitepath;
  $tmp="<form name='delMod".$i."' action='".$sitepath."teachers/edit_mod.php4' method='POST' target='_self'>
               <input type='hidden' name='ModID' value='".$ModID."'>
               <input type='hidden' name='make' value='del_forum'>
               <input type='hidden' name='McID' value='".$res['McID']."'>
               <input type=\"hidden\" name=\"PID\" value=\"".$pid."\">
               <input type=\"hidden\" name=\"showfull\" value=\"".$showfull."\">
               <input type=\"hidden\" name=\"CID\" value=\"".$CID."\">
               <span class='cHilight' style='cursor:hand' onClick=\"if (confirm('"._("Вы уверены, что хотите удалить?")."'))
                delMod".$i.".submit()\" title=\""._("удалить")."\">&#251;</span>
           </form>";
  return( $tmp );
}

/*function show_mod_content( $CID, $pid, $res, $ModId, $mode ){
  global $mod_cont_table;
 // выдает содержание модуля
 // mode - 1 - если с правами редактирования
 // res - сруктура описания модуля
  $showfull=$mode;

  $tmp="<table border=0 cellpadding=0 cellspacing=0 align=center width=\"100%\" class=cHilight>
                <tr>";
   if( $mode ){
      $tmp.= show_mod_title( $CID, $pid, $ModId, $res['mod_name'] );
   }else{
      $tmp.="<FONT SIZE=+1>".$res['mod_name']."</FONT>";
   }
   $tmp.=show_mod_description( $res );

   $i=0;
   $sql="SELECT Title,type,McID,mod_l from ".$mod_cont_table." WHERE ModID='".$ModID."'";
   $sql_result=sql($sql);
   if ( sqlrows($sql_result)>0 )
      while ( $res = sqlget($sql_result) ){
         $i++;
         $tmp.="<a href=\"show_mod.php4?ModID=<?=$_POST['ModID']?>&McID=<?=$res['McID']?>\"
                 target=\"show_win".$i."\" onclick=\"window.open ('', 'show_win'".$i."',
                 'status=no,toolbar=yes,menubar=no,scrollbars=yes,resizable=yes,width=600, height=600');\">"
               .$res['Title'].
              "</a>";

         if ( $mode ){
            $tmp.=show_mod_edit( $CID, $pid, $i, $ModID, $res );
            $tmp.=show_mod_del($CID, $pid, $i, $ModID, $res, $showfull);
          }
      }
   if ( !empty($forum_id) ){
      $i++;
      $tmp.=show_forum_link($forum_id,$ModID,$i);
      if( $mode ){
        if ( $pid )
          $tmp.=show_forum_del($CID, $pid, $i, $ModID, $res );
      }
   }

   if (!empty($test_id))
     if( $mode )
       $tmp.=show_tests_link(explode(";",$test_id),$ModID,$CID,$pid,$showfull,$i);
     else
       $tmp.=show_tests_link(explode(";",$test_id),$ModID,$CID,0,$showfull,$i);
   $tmp.="</table>";
   return( $tmp );
} */
?>