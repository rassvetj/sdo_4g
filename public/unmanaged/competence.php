<?php
   require_once("1.php");
   require('courses.lib.php');
   require('competence.lib.php');

   if (!$dean) login_error();

   $c = (isset($_GET['c'])) ? $_GET['c'] : $_POST['c'];
      
switch ($c) {

case "":

   $ech=show_tb();
   $ech.=ph(_("Управление компетенциями"));
   $ech.="        <br>";
   $GLOBALS['controller']->captureFromVar(CONTENT,'ech',$ech);

   $ech.="
    <table width=100% class=main cellspacing=0>
     <tr><th>"._("Название")."</th><!--th width='100px' >"._("Макс.уровень")."</th--><th width='50%' >"._("Описание")."</th>";
   if ($GLOBALS['controller']->checkPermission(COMPETENCE_PERM_EDIT))
   $ech.="<th width='50px'></th>";
   $ech.="</tr>";

   $res=sql("SELECT * FROM competence ORDER BY name","errGR__73");
   while ($r=sqlget($res)) {

 //     $people=getPeopleByCompetence( $r[coid] );


      if($r[color]>""){ $color=$r[color]; $pic=""; }else{ $color="white"; $pic="-";}
      $ech.="<tr>
            <td><!--a href=$PHP_SELF?c=search&coid=$r[coid]$sess title=\""._("Подбор по компетенциям")."\"-->
                 $r[name]
            <!--/a--></td>
            <!--td> $r[level] </td-->
            <td>".nl2br($r[info])."<br>$people</td>";
      if ($GLOBALS['controller']->checkPermission(COMPETENCE_PERM_EDIT))
      $ech.="<td  align='center'>
            <a href=$PHP_SELF?c=edit&coid=$r[coid]$sess>".getIcon("edit")."</a>
            <a href=$PHP_SELF?c=delete&coid=$r[coid]$sess
            onclick=\"if (!confirm('"._("Удалить?")."')) return false;\" >".getIcon("delete")."</a>
            </td>";
      $ech.="</tr>";
   }

   if (sqlrows($res)==0) $ech.="<tr><td colspan=3>"._("Критерии не созданы")."</td></tr>";
   else sqlfree($res);

   $ech.="</table>";
   $ech.="<br />";   
   $GLOBALS['controller']->captureFromVar(TRASH,'ech',$ech);   
   $ech.="<a href=$PHP_SELF?c=search$sess>"._("подбор по компетенцям")." >></a><br><br>";
   $ech.="<a href=$PHP_SELF?c=assign$sess>"._("назначение курсов по компетенциям")." >></a><br>";
   $GLOBALS['controller']->captureStop(TRASH);
   if ($GLOBALS['controller']->checkPermission(COMPETENCE_PERM_EDIT)) {
   $ech.="
   <form action=$GLOBALS[PHP_SELF] method=post>
   <input type=hidden name=c value='new'>
   <table width=100% class=main cellspacing=0>
      <tr>
         <th>"._("Добавить")."</th>
      </tr>
      <tr>
         <td>
         <input type=text name=name size=40 style=\"width: 300px;\" value=\""._("введите название")."\">
         </td>
      </tr>
   </table>";

   $ech.="<br>
   <table cellspacing=\"0\"  cellpadding=\"0\" border=0 width=\"100%\">
   <tr>
      <td align=\"right\" valign=\"top\">";
      if ($GLOBALS['controller']->enabled) $ech.=okbutton();
      else
      $ech.=getIcon("send_");
      $ech.="</td>
   </tr>
   </table>
   </form>";
   }
   $GLOBALS['controller']->captureStop(CONTENT);

   echo $ech;
   $ech.=show_tb();
   break;

case "search":

   $ech=show_tb();
   $ech.=ph(_("Подбор по компетенциям"));

   $ech.="<a href=$PHP_SELF?$sess><< "._("к списку компетенций")."</a><br><br>";
   $GLOBALS['controller']->captureFromVar(CONTENT,'ech',$ech);
   $GLOBALS['controller']->setHeader(_("Подбор по компетенциям"));

   $ech.="<FORM action='{$_SERVER['PHP_SELF']}' method='POST'>
   <input type=hidden name=c value=\"search\">
   <input type=hidden name=coid value='$coid'>";

   $ech.="
    <table width=100% class=main cellspacing=0>
     <tr><th>"._("Название")."</th><th>"._("Процент")."</th><th width='50%' >"._("Описание")."</th><!--th width='50px'></th--></tr>
    ";

        if (is_array($_POST['ch_coid_arr'])) {
                $arrCoids = $_POST['ch_coid_arr'];
        } elseif (isset($_GET['coid'])) {
                $arrCoids[] = $_GET['coid'];
        } else {
                $arrCoids = array();
        }

   $res=sql("SELECT * FROM competence ORDER BY name","errGR__73");
   if(sqlrows($res) > 0)
   while ($r=sqlget($res)) {

           if($r[color]>""){ $color=$r[color]; $pic=""; }else{ $color="white"; $pic="-";}

      if (in_array($r[coid], $arrCoids)) {
              $strCheckedCoid = "checked";
              $strDisabledAnyMark = "";
              @$boolCond = ((!is_array($_POST['ch_coid_arr'])) || in_array($r[coid], array_keys($_POST['ch_any'])));
                $strCheckedAnyMark = ($boolCond) ? "checked" : "";
                $strDisabledMark = ($boolCond) ? "disabled" : "";
            $strValueMark = ($strDisabledMark) ? "" : intval($_POST['txt_mark_arr'][$r[coid]]);
      } else {
              $strCheckedCoid = "";
              $strDisabledAnyMark = "disabled";
              $strCheckedAnyMark = "checked";
              $strDisabledMark = "disabled";
              $strValueMark = "";
      }

      $ech.="<tr>
            <td valign=top>
                      <input type=checkbox name='ch_coid_arr[{$r[coid]}]' value=$r[coid] {$strCheckedCoid}  onClick=\"javascript:document.getElementById('ch_any[{$r[coid]}]').disabled=!this.checked;document.getElementById('ch_any[{$r[coid]}]').checked=this.checked;document.getElementById('txt_mark_arr[{$r[coid]}]').disabled=(document.getElementById('ch_any[{$r[coid]}]').checked)||(document.getElementById('ch_any[{$r[coid]}]').disabled);\"\">
                 $r[name]
            </td>
            <!--td> $r[level] </td-->
            <!--td>: $r[coid]</td-->
            <td>
                      <input type=text name=txt_mark_arr[{$r[coid]}] id=mark[{$r[coid]}] size=2 {$strDisabledMark} value='{$strValueMark}'> "._("и выше")."<br>
                        <input name='ch_any[{$r[coid]}]' type='checkbox' id='ch_any[{$r[coid]}]' value='1' onClick=\"javascript:document.getElementById('mark[{$r[coid]}]').disabled=this.checked;\" {$strDisabledAnyMark} {$strCheckedAnyMark}>"._("любой")."
                      </td>
            <td>
            $r[info]
            </td></tr>";
      //$for_unstrong_sel =
   }

   if(isset($_POST['strong_corresp'])) {
      if($_POST['strong_corresp'] == 0) {
         $strong_corresp_sel = "";
         $unstrong_corresp_sel = " selected";
      }
      else {
         $strong_corresp_sel = " selected";
         $unstrong_corresp_sel = "document.getElementById(mark[{$r[coid]}]).disabled = 'true'; ";
      }
   }

   $ech .= "<script language='javascript'>
             function disable_mark() {
                     var s = document.getElementById('strong_corresp');
                     if(s.selectedIndex == 1) {

                     }
             }
            </script>
            <tr>
             <td colspan='3' align='left'>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              <select name='strong_corresp' onchange='javascript: disable_mark();'>
               <option value='1'$strong_corresp_sel>"._("Строгое соответствие")."
               <option value='0'$unstrong_corresp_sel onselect=\"javascript: \">"._("Нестрогое соответствие")."
              </select>
             </td>
            </tr>";

   if (sqlrows($res)==0) $ech.="<tr><td colspan=2>"._("Критерии не созданы")."</td></tr>";
   else sqlfree($res);



   $ech.="</table><BR>";
   $ech .= okbutton();
   $ech .= "</form>";


   //   $ps = getPeopleByCompetences( $cip ); // формирует перечень людей по перечню компетенций
   //if($_POST['strong_corresp']) {
      if (is_array($arrCoids) && count($arrCoids) > 0) {
        foreach ($arrCoids as $key => $val) {
                $arrMarks[$key] = (isset($_POST['ch_any'][$key])) ? 0 : isset($_POST['txt_mark_arr'][$key]) ? $_POST['txt_mark_arr'][$key] : 0;
        }
        
        $arrPeople = getPeopleByCompetences( $arrCoids, $arrMarks );
        if( count($arrPeople) > 0 ){
            $ech.="<br><table width=100% class=main cellspacing=0><tr><th>"._("ФИО")."</th><th>"._("Результаты")."</th></tr>";
            $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
            foreach( $arrPeople as $key => $val ){
                     if (!$peopleFilter->is_filtered($val['people_mid'])) continue;
                     $ech .= "<tr><td>{$val['people_name']}</td><td>";
                     foreach($val['results'] as $v) $ech .= $v.'% ';
                     $ech .= "</td></tr>";
            }
            $ech.="</TABLE>";
        }
        else
            $ech.=_("Нет ни одного с заданными компетенциями");
      }
      
      $GLOBALS['controller']->captureStop(CONTENT);      
      
      echo $ech;
      $ech.=show_tb();
  // }
 //  else {
       /* if (is_array($arrCoids) && count($arrCoids) > 0) {
            foreach ($arrCoids as $key => $val) {
                $arrMarks[$key] = (isset($_POST['ch_any'][$key])) ? 0 : isset($_POST['txt_mark_arr'][$key]) ? $_POST['txt_mark_arr'][$key] : 0;
            }
            $arrPeople = getPeopleByCompetencesUnstrong( $arrCoids, $arrMarks );
            //if( count($arrPeople) > 0 ){
            //    $ech.="<br><table width=100% class=main cellspacing=0><tr><th>ФИО</th></tr>";
            //    foreach( $arrPeople as $key => $val ){
            //         $ech .= "<tr><td>{$val['people_name']}</td></tr>";
            //    }
            //    $ech.="</TABLE>";
            //}
            //else
           //     $ech.="Нет ни одного с заданными компетенциями";
        }
        $ech.=show_tb();
        echo $ech; */
 //  }
break;
case "assign":
      if(isset($_POST['people_search_go'])) {
         $people_search = $_POST['people_search'];
         $comp_id = $_POST['comp_id'];
      }
      else {
            $comp_id = "";
            $people_search = "";
      }
      $ech=show_tb();
      $ech.=ph(_("Назначение курсов по компетенциям"));
      $ech.="<a href=$PHP_SELF?$sess><< "._("к списку компетенций")."</a><br><br>";
      $GLOBALS['controller']->captureFromVar(CONTENT,'ech',$ech);
      $GLOBALS['controller']->setHeader(_("Назначение курсов по компетенциям"));
      $ech.="<script language='javascript'>
              function validate_search_form() {
                       checkboxes = document.getElementsByTagName('input');
                       for(var s in checkboxes) {
                           if((st = checkboxes[s]).type == 'checkbox') {
                              if(st.checked == true)
                                  return true;
                           }
                       }
                       alert('"._("Ничего не назначено")."');
                       return false;
              }

              function validate_form() {
                      //var s = document.getElementById('people_search');
                      //if(s.selectedIndex == 0) {
                      //   alert('"._("Не выбрано для кого")."');
                      //   return false;
                      //}
                      var s = document.getElementById('comp_id');
                      if(s.selectedIndex == 0) {
                         alert('"._("Не выбрана компетенция")."');
                         return false;
                      }
                      return true;
              }
             </script>";
      $ech.="<FORM action='{$_SERVER['PHP_SELF']}' method='POST' onsubmit='javascript: return validate_form()'>
              <input type=hidden name=c value=\"assign\">";
      $query = "SELECT * FROM competence";
      $result = sql($query);
      while($row = sqlget($result)) {
           $competences[$row['coid']] = $row['name'];
      }
      $ech.="
             <table width=98% class=main cellspacing=0>
              <tr>
               <td>
               "._("Для кого:")."
               <select name='people_search' id='people_search'>";
      $ech.= selGrved(0,$people_search);
      $ech.= "</select>&nbsp;&nbsp;&nbsp;&nbsp;
               "._("Требуемая компетенция:")."
               <select name='comp_id' id='comp_id'>";
      $ech.= "<option value='-1'>-- "._("выберите комптенцию")." --";
      if((is_array($competences))&&(count($competences) > 0))
      foreach($competences as $key => $value) {
              if($key == $comp_id) {
                      $sel = " selected";
              }
              else {
                    $sel = "";
              }
              $ech.= "<option value='$key'$sel>".$value."</option>";
      }
      $ech.=  "</select>";
      $ech .= "&nbsp;&nbsp;&nbsp;<input type='submit' value='"._("Искать")."' name='people_search_go'>";
      $ech.="  </td>
              </tr></table>";
      $ech .= "</form>";
      if(!empty($comp_id)) {
           $peoples = get_people_and_not_enough_courses_by_comp($comp_id, $people_search);
           $ech.= "<form id='search_form' action='' method='POST' onsubmit='javascript: return validate_search_form();'>";
           $ech.= " <input type='hidden' name='c' value='assign_to_base'>";
           $ech.= "<table width=98% class=main cellspacing=0>";
           $ech.= " <th>"._("Имя и крусы на кот. зарегистр.")."</th><th>"._("Необходимые курсы")."</th><th>"._("Назначить")."</th>";           
           $peopleFilter = new CPeopleFilter($GLOBALS['PEOPLE_FILTERS']);
           foreach($peoples as $key => $value) {
                   if (!$peopleFilter->is_filtered($value['MID'])) continue;
                   if($value['courses'] == "")
                      continue;
                   $query = "SELECT CID,cgid FROM Students WHERE MID=".$value['MID'];
                   $result = sql($query);
                   $i = 0;
                   $temp = array();
                   while($row = sqlget($result)) {
                           $temp[$i] = $row['CID'];
                           if($row['cgid'] != 0) {
                              $temp2[$i] = $row['cgid'];
                           }
                           $i++;
                   }
                   $st_courses = transform_array_cids_to_string($temp);
                   $st_groups = transform_array_cgids_to_string($temp2);

                   $ech.= " <tr>
                             <td valign='top'><a href='reg.php4?showMID=".$value['MID']."' target='_blank'>".$value['name']."</a> ".(!empty($st_groups) ? "(<small>$st_groups</small>)" : '')."<p>
                             ".$st_courses."</p>
                             </td>
                             <td>".$value['courses']."</td>
                             <td><input type='checkbox' name='st[".$value['MID']."]' value='".$value['for_checkbox']."'></td>
                            </tr>";

           }
           $ech.= "<script language='javascript'>
                    function check_all() {
                             if(document.getElementById('edf').checked) {
                                var temp = true;
                             }
                             else {
                                var temp = false;
                             }
                            var checkboxes = document.getElementsByTagName('input');
                             for(s in  checkboxes) {
                                 if(checkboxes[s].type == 'checkbox') {
                                    checkboxes[s].checked = temp;
                                 }
                             }
                    }
                   </script>";
           $ech.="    <tr>
                       <td colspan='3'><input type='checkbox' name='edf' id='edf' onclick='javascript: check_all();'>
                       "._("Выделить все")."
                       </td>
                      </tr>";
           $ech.= "</table><br>";
           $ech.= okbutton();
           $ech.="</form>";
      }
      $GLOBALS['controller']->captureStop(CONTENT);
      echo $ech;
      $ech .= show_tb();

break;
case "assign_to_base";
      if(isset($_POST['st'])) {
         $st = $_POST['st'];
      }
      foreach($st as $mid => $cids) {
              $cids_array = explode(",", $cids);
              foreach($cids_array as $key => $value) {
                      $query = "SELECT cgid, registered FROM Students WHERE MID=$mid";
                      $result = sql($query);
                      $row = sqlget($result);
                      $cgid = $row['cgid'];
                      $reg = $row['registered'];
                      $query = "INSERT INTO Students (CID, MID, cgid, Registered)
                                VALUES
                                ($value, $mid, $cgid, $reg)";
                      $result = sql($query);
              }
      }

      $ech=show_tb();
      $GLOBALS['controller']->captureFromVar(CONTENT,'ech',$ech);
      $ech.=ph(_("Назначение курсов по компетенциям"));
      $ech.="<a href=$PHP_SELF?$sess><< "._("к списку компетенций")."</a><br><br>";
      $ech.="<b>"._("Курсы назначены")."</b>";
      $GLOBALS['controller']->captureStop(CONTENT);
      echo $ech;
      $ech .= show_tb();
break;
case "new":
   if ($GLOBALS['controller']->checkPermission(COMPETENCE_PERM_EDIT)) {

   if (!empty($name))
   $res=sql("INSERT INTO competence (name, level) values (".$GLOBALS['adodb']->Quote($name).",100) ","errFM185");
   sqlfree($res);
   }
   refresh("$PHP_SELF?$sess");
   
   break;

case "delete":
   if ($GLOBALS['controller']->checkPermission(COMPETENCE_PERM_EDIT)) {

   intvals("coid");
   if (!empty($coid)) {
        $res=sql("DELETE FROM competence WHERE coid='$coid'","errFM185C");
        sqlfree($res);

        $res=sql("DELETE FROM comp2course WHERE coid='$coid'","errFM185C");
        sqlfree($res);

        $res=sql("DELETE FROM str_of_organ2competence WHERE coid='$coid'", "errFM185C");
        sqlfree($res);
   }
   }
   refresh("$PHP_SELF?$sess");
   
   break;

case "edit":

   intvals("coid");
   echo show_tb();

   $tmp="SELECT * FROM competence WHERE coid=$coid";
   $r=sql( $tmp );
   $res=sqlget( $r );
   sqlfree($r);

   echo ph(_("Редактирование свойств")." ".$res[ name ]);

   $color= $res[ color ];
   if($color=="" ) $color="white"; else $color=$res[ color ];
   $tmp="<a href=$PHP_SELF?$sess><< "._("к списку компетенций")."</a><br>";
   $GLOBALS['controller']->captureFromVar('m070203','tmp',$tmp);
   $tmp.="<form action=$PHP_SELF method=post>
   <input type=hidden name=c value=\"post_edit\">
   <input type=hidden name=coid value='$coid'>";

   $tmp.="<table width=100% class=main cellspacing=0>
   <tr>
   <td>"._("Название")."</td><td ><input type=text name=name size=80 value=\"".htmlspecialchars($res[ name ])."\"></td>
   </tr>
   <!--tr>
   <td>"._("Уровень")."</td><td >";
   $tmp.="<INPUT type=text name=level size=3 value='".$res[level]."' />";
   $tmp.="</td>
   </tr-->
   <tr>
   <td>"._("Описание")."</td>
   <td ><textarea name='info' rows=5 cols=77>".$res[ info ]."</textarea></td>
   </tr>";

   $tmp.="<tr>
      <td colspan=100 align=\"right\" valign=\"top\">";
      if ($GLOBALS['controller']->enabled) $tmp.=okbutton();
      else
      $tmp.=getIcon("send_");
      $tmp.="
      </td>
     </tr>";


   $tmp.="</table>
   </form>";
   $GLOBALS['controller']->captureStop('m070203');
   

   $tmp.=ph(_("Каждый из этих курсов и заданий обеспечивает данную компетенцию"));
   $GLOBALS['controller']->captureFromVar('m070204','tmp',$tmp);
   if ($GLOBALS['controller']->enabled) $tmp.='<p>'._("Каждый из этих курсов и заданий обеспечивает данную компетенцию").'</p>';

   $courses = getCoursesByCompetence( $coid, $t );
   if( count($courses) > 0 ){
     $tmp.="<table width=100% class=main cellspacing=0>";
     foreach( $courses as $course ){
      $tasks=getCourseTasks( $course[cid] );
      $t=getTasksList( $tasks, $course[tid], $coid, $course[ccoid] );
      if( $course[tid] > 0 )
        $test=" : <u>".$tasks[ $course[tid] ][title]."</u>";
      else
        $test=": <u>"._("тест не выбран")."</u>";
      $tmp.="<tr><td>$course[title] $test<BR>$t</td>
                 <td>".get_course_status($course[status])."</td>
                 <td><a href=$PHP_SELF?c=delete_cid&cid=$course[cid]&coid=$coid&ccoid=$course[ccoid]$sess
            onclick=\"if (!confirm('"._("Удалить?")."')) return false;\" >".getIcon("delete")."</a></td></tr>";
     }
     $tmp.="</table>";
   }

   $tmp.="<form action=$PHP_SELF method=post>
   <input type=hidden name=c value=\"post_add_edit\">
   <input type=hidden name=coid value='$coid'>";

   $tmp.="<table width=100% class=main cellspacing=0>
   <tr><td>
     "._("Добавить")." <SELECT name=cid>
      <OPTION> - "._("укажите курс")." - </OPTION>";
   $courses=get_all_courses();
   $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
   foreach( $courses as $course ){
       if (!$courseFilter->is_filtered($course['cid'])) continue;         
       $tmp.="<OPTION value=$course[cid]>".$course[title]."</OPTION>";
   }
   $tmp.="</SELECT>
   </td><tr>
      <td colspan=100 align=\"right\" valign=\"top\">";
      if ($GLOBALS['controller']->enabled) $tmp.=okbutton();
      else
      $tmp.=getIcon("send_");
      $tmp.="
      </td>
     </tr>";


   $tmp.="</table>
   </form>";
   echo $tmp;
   $GLOBALS['controller']->captureStop(m070204);
   echo show_tb();
   return;

case "post_edit":

   if ($GLOBALS['controller']->checkPermission(COMPETENCE_PERM_EDIT)) {
   intvals("coid");

   $rq="UPDATE competence
           SET name = ".$GLOBALS['adodb']->Quote($name).",
               level = '$level',
               info = ".$GLOBALS['adodb']->Quote($info).",
               type = '$type',
               status = '$status'
         WHERE coid=$coid";
   $res=sql($rq,"errGR138");
   sqlfree($res);
   refresh("$PHP_SELF?$sess");
   }
   return;
case "change_test":

   if ($GLOBALS['controller']->checkPermission(COMPETENCE_PERM_EDIT)) {
   intvals("ccoid");
   intvals("coid");
   intvals("tid"); // тест

   $rq="UPDATE          comp2course
           SET   tid  = '$tid'
           WHERE ccoid ='$ccoid'
        ";
   $res=sql($rq,"errGR138_$c");
   sqlfree($res);
   }
   refresh("$PHP_SELF?$sess&c=edit&coid=$coid");
   return;
case "post_add_edit":

   if ($GLOBALS['controller']->checkPermission(COMPETENCE_PERM_EDIT)) {
   intvals("coid");
   intvals("cid");
   intvals("tid"); // тест

   $rq="INSERT INTO comp2course (coid, cid, tid) values (
               '$coid',
               '$cid',
               '$tid')";
   $res=sql($rq,"errGR138__");
   sqlfree($res);
   }
   refresh("$PHP_SELF?$sess&c=edit&coid=$coid");
   return;
case "delete_cid":
   if ($GLOBALS['controller']->checkPermission(COMPETENCE_PERM_EDIT)) {
   intvals("coid");
   intvals("cid");
   $rq="DELETE FROM comp2course
           WHERE ccoid ='$ccoid'";
   $res=sql($rq,"errGR138__D");
   sqlfree($res);
   }
   refresh("$PHP_SELF?$sess&c=edit&coid=$coid");
   return;

}


?>