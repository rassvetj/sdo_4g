<?
//require_once("metadata.lib.php");

///////////////////////////// СПЕЦИАЛЬНОСТЬ /////////////////////
function getTrackLevelsCount( $trid ){
 // ищет максимальную ступень обучения
//     $rq="INSERT INTO tracks2course SET trid=$trid, cid=$cid, level=$level[$k]";
  if( $trid < 0) $str=""; else "WHERE trid=$trid";
  $r=sql("SELECT * FROM tracks2course $str","ERRGETTR");
  $lmax=0;
  while ( $rr=sqlget($r) )
         if( $rr[level] > $lmax ) $lmax=$rr[level];
  sqlfree( $r );
 return($lmax);
}
/*
function getLevel( $trid, $mid ){
 // надо взять курсы специальности, затем текущие курсы человека
 // посмотреть какому уровню среди курсов специальности соотв курсы человека
 // непонятно что делать если они с разных уровней (нужно переводить на след если только ВСЕ курсы сданы текущего)

  $res=sql("SELECT * FROM Courses, tracks2course
              WHERE tracks2course.trid=$trid
                AND Courses.cid=tracks2course.cid",
             "errGR179");


  while ( $r = sqlget($res) ){
    $cid[]=$r[cid];
  }
  sqlfree($res);
 //
 // НЕДОПИСАНО!!!!!!!!!!!!!!!!!!
 //
  return(1);
} */
// перевод на след ступень возможен когда
// ВСЕ курсы этой ступни пройдены
// и по ним студ находится в статусе - закончившего обучение
// И когда у него есть деньги на след. - НО ЭТО ПОТОМ
// итак - что бы перевести на след ступень - надо найти все курсы студента, посмотреть с какой он спец и какие еще етам есть курсы
// и все ли эти курсы или чтото осталось - и если осталось 0 то тогда перевести
// кто закончил обучение по ступени и
//


function getGraduatedCourses( $mid ){
// формирует список курсов и набора курсов $cids которые зачтены для студны mid
  $sql="SELECT graduated.CID
         FROM graduated
         WHERE graduated.MID=$mid
           AND graduated.CID IN (". implode( ",",$cids).")";

      $res=sql($sql,"abiturEr07"); // формирует список курсов и набора курсов $cids которые зачтены для студны mid
      while ( $r = sqlget($res) ){
        $gcids[]=$r[CID];
      }
      return( $gcids );
}

function in_( $cids, $gcids){
 // проверяет - все ли $cids находятся в $gcids
 // я сплю уже на ходу глаза закрываются
  $f=FALSE;
  foreach( $cids as $cid ){
    $f=FALSE;
    foreach( $gcids as $gc){
       if( $cid==$gc){
         $f=TRUE;
         break;
       }
    }
    if( !$f ) break;
 }
 return( $f );
}
function nextLevel( $trid, $mid ){
  // ищет для специальности $trid какие след. курсы можно назначить

  $gcids = getGraduatedCourses( $mid );
  $CoursesOnLevels = getTrackCoursesByLevels( $trid );

  // теперь проверить сколько курсов с каждого уровня зачтено

  foreach( $CoursesOnLevels as $level=>$cids ){ // для каждого уровня
    if( in_( $cids, $gcids) )
      $levels[$level]=TRUE;
    else
      $levels[$level]=FALSE;
/*    foreach( $cids as $cid ){
      if( ! isGraduatedByCourse( $cid, $mid ) ){
        $levels[$level]=FALSE;
        break;
      }
    } */
  }
 // в levels находится номера ступеней с указанием какая из них выполнена
 // далее надо определить минимально возможную
 foreach( $levels as $l=>$level ){
   if( !$level )
     $next_level=$l;
 }
 // в $next_level следующая возможная ступень
 return( $next_level );
}

function isGraduatedByCourse( $cid, $mid ){
 // определяет, зачтен ли курс человеку

}


function getCoursesNum( $trid ){
   // выдает кол-во курсов на специальности
   //echo "track=".$trid."<BR>";
   $res=sql("SELECT Courses.CID FROM Courses, tracks2course
              WHERE tracks2course.trid=$trid
                AND Courses.cid=tracks2course.cid",
             "errGR159");
   $i=0;
   while ($r=sqlget($res)){
     $i++;
   }
   sqlfree( $res );
   return( $i );
}

function getTrackCoursesByLevels( $trid ){
   // выдает кол-во курсов на специальности
   //echo "track=".$trid."<BR>";
   $res=sql("SELECT Courses.CID FROM Courses, tracks2course
              WHERE tracks2course.trid=$trid
                AND Courses.cid=tracks2course.cid",
             "errGR159");
   $i=0;
   while ($r=sqlget($res)){
     $courses[$r[level]][]=$r[cid];
     $i++;
   }
   sqlfree( $res );
   return( $courses );
}

function getTrackName( $trid ){
   $r=sqlval( "SELECT name FROM tracks WHERE trid=$trid"," get tracks name errGR159");
   return( $r[name] );
}

function getDepartmentName( $did ){
   if( $did > 0 ){
     $rq="SELECT *
           FROM departments
           WHERE did=$did";
     $res=sql($rq,"errGR138");
     $r = sqlget($res);
     $name=$r[name];
     sqlfree($res);
   }
   return( $name );
}

function getDepartmentCourses( $did  ){
  // возвращает список курсов входящих в специальность

  $sss="SELECT * FROM Courses
              WHERE did=$did
                          ORDER BY Title
  ";

   $tmp=getCoursesGant( $sss, TRUE, FALSE );
}

function getTrackCourses( $trid, $title, $lt, $text="" ,$order=false){
  // возвращает список курсов входящих в специальность

  $sss="SELECT * FROM Courses, tracks2course
              WHERE tracks2course.trid=$trid
                AND Courses.cid=tracks2course.cid
                ORDER BY level, Courses.Title";

  if( ! $lt )
   $tmp=getCoursesGant($sss, TRUE, FALSE);
  else $tmp="";
  if ( $title == "" )
    $t= _("все курсы специальности");
  else
    $t="<FONT SIZE=1>$title</FONT>";
//      $t="$title";


  if( !$lt )
    $tmpp="<th></th><th>"._("семестр")."</th>";
  else{
    $tmpp="<th></th>";
//    $="";
  }
  if (!$GLOBALS['controller']->enabled) {
  $tmp.="<p><table width=100% class=main cellspacing=0 id=title_$trid>";
  $tmp .="
           <tr>
            <th width='65%'>
              <span align=right style='cursor:hand' onClick=\"removeElem('title_$trid');putElem('track_$trid');\">"
              .getIcon("+").$t."
              </span>
            </th>
            <th>
            </th>
           </tr>";

  if (!$GLOBALS['controller']->enabled || ($GLOBALS['s']['perm']!=3) || $order) {
      $tmp.="<tr><td>$text";
      $tmp.="</td><td>";
      if (defined("LOCAL_FREE_REGISTRATION") && LOCAL_FREE_REGISTRATION) {
          $tmp .= "<a href='reg.php4?trid=$trid' ><img src='".$GLOBALS['controller']->view_root->skin_url . "/images/b_reg.gif' border=0 align='absmiddle'></a>";
      }
      $tmp .= "</td></tr>" ;
      $tmp.="</table>";
  }
  }
  $tmp .= "<p>";
  $tmp.="<table id=track_$trid class=\"".($GLOBALS['controller']->enabled ? "" : "hidden2")." main\" cellspacing=0 width='100%'>";
  if (!$GLOBALS['controller']->enabled)
  $tmp.="<tr><th width=65%><span  style='cursor:hand' id=title_$trid onClick=\"putElem('title_$trid');removeElem('track_$trid');\">
  ".getIcon("-")."$t</span></th><th></th></tr>";
  else $tmp .= "<tr><th colspan=2>$t</tr>";

  if (!$GLOBALS['controller']->enabled || ($GLOBALS['s']['perm']!=3) || $order) {
      $tmp.="<tr><td width=70%>$text";
      $tmp.="</td><td nowrap>";  
      if (defined("LOCAL_FREE_REGISTRATION") && LOCAL_FREE_REGISTRATION) {
          $tmp .= "<a href='reg.php4?trid=$trid' ><img src='".$GLOBALS['controller']->view_root->skin_url . "/images/b_reg.gif' border=0 align='absmiddle'></a>";
      }
      $tmp.="</td></tr>";
      if (defined('USE_BOLOGNA_SYSTEM') && USE_BOLOGNA_SYSTEM) {
          $tmp.="<tr><td>"._("Обязательная программа:")." </td><td>".CCredits::countTrackCredits($trid)." "._("кредитов")."</td></tr>";
          $tmp.="<tr><td>"._("Программа обучения по выбору:")." </td><td>".CCredits::getTrackFreeCredits($trid)." "._("кредитов")."</td></tr>";
      }
  }

 // $tmpp $reg</tr>";
      $hidd = "<div id=\"track_description_{$trid}_hidden\" ><a href=\"javascript:void(0);\" onClick=\"document.getElementById('track_description_{$trid}_hidden').style.display='none'; document.getElementById('track_description_{$trid}_shown').style.display='block';\"><img src='images/treeview/p.gif' border=0 hspace=5 />"._("Список курсов")."</a></div>
              <div class=hidden2 id=\"track_description_{$trid}_shown\"><a href=\"javascript:void(0);\" onClick=\"document.getElementById('track_description_{$trid}_shown').style.display='none'; document.getElementById('track_description_{$trid}_hidden').style.display='block';\"><img src='images/treeview/m.gif' border=0 hspace=5 />"._("Список курсов")."</a><table width=100%><tr><td>";   
 
  $tmp.="<tr><td colspan=2>{$hidd}"; //

   $res=sql("SELECT * FROM Courses, tracks2course
             WHERE tracks2course.trid=$trid
                AND Courses.cid=tracks2course.cid
             ORDER BY tracks2course.level, Courses.Title",
             "errGR179");

  if( !$res ) return("");
  $k=0;
  while ( $r = sqlget($res) ){
      if( $r['level']!=$level_prev ){
        $level_prev=$r['level'];
        $tmp.="<tr><th>"._("название курса")."</th><th> "._("семестр")." ".$level_prev."</th></tr>";
      }
      $tmp.="<tr>";
      $tmp.="<td>";
      switch( $r[Status] ){
        case 0:
          $tmp.="".$r['Title']."";
        break;
        case 1:
          $tmp.="".$r['Title']."";
        break;
        case 2:
          $tmp.="<B>".$r['Title']."</B>";
        break;
        default:
      }
      $tmp.="</td>";
//      $bedate="<td>".mydate($r['cBegin'])."-<br>".mydate($r['cEnd'])."</td>";
//      $tmp.=$bedate;
//      $tmp.="<td>".($r['Fee']?$r['Fee']:"")."</td>";
//      if( $lt )
//        $tmp.="<td>".$r['level']."</td>";
//      else
        if( !$lt )
          $tmp.="<td><input type='text' size=2 value=".$r['level']." name='level[".$r[cid]."]' ></td>";
        else
          $tmp.="<td></td>";
      $tmp.="</tr>";
      
      require_once($GLOBALS['wwf']."/metadata.lib.php");
      $des = '';
      $des = read_metadata(stripslashes($r['Description']), COURSES_DESCRIPTION);
      $des = view_metadata_as_text($des, COURSES_DESCRIPTION);    
      if (strlen($des)) {
          $des = "<div id=\"course_description_{$r['cid']}_{$trid}_hidden\" ><a href=\"javascript:void(0);\" onClick=\"document.getElementById('course_description_{$r['cid']}_{$trid}_hidden').style.display='none'; document.getElementById('course_description_{$r['cid']}_{$trid}_shown').style.display='block';\"><img src='images/treeview/p.gif' border=0 hspace=5 />"._("описание")."</a></div>
                  <div class=hidden2 id=\"course_description_{$r['cid']}_{$trid}_shown\"><a href=\"javascript:void(0);\" onClick=\"document.getElementById('course_description_{$r['cid']}_{$trid}_shown').style.display='none'; document.getElementById('course_description_{$r['cid']}_{$trid}_hidden').style.display='block';\"><img src='images/treeview/m.gif' border=0 hspace=5 />"._("описание")."</a>
                  ".$des."&nbsp; </div>";
      } else {
          $des = '';
      }
      $tmp .= "<tr><td colspan=2>{$des}</td></tr>";
      
      $k++;
   }
   sqlfree ( $res );
   $tmp.="</td></tr></table></div></td></tr></table>";
   $tmp.="</span>";
   
// $ph=ph("Специальности ($k)");
return( $ph.$tmp );
}

/////////////// КУРСЫ /////////////////

function getCoursesGant($s_q_l, $show_gant, $show_list, $useCourseFilter = true, $listAll = false) {
// формирует график курсов для запроса $s_q_l и возврящает его

	$GLOBALS['controller']->setPermissionTemporary('1301');
	$GLOBALS['controller']->setPermissionTemporary('1302');

   $all = "";
   $clist = loadtmpl("courses-fullcourses.html");
   $line = loadtmpl("courses-1deanline.html");

   $GLOBALS['is_perm_add'] = $GLOBALS['controller']->checkPermission(COURSE_PERM_ADD);   
   $GLOBALS['is_perm_edit'] = $GLOBALS['controller']->checkPermission(COURSE_PERM_EDIT);   
   $GLOBALS['is_perm_del'] = $GLOBALS['controller']->checkPermission(COURSE_PERM_DEL);   

   $line = str_replace("[IMPORT_BUTTON]", getIcon("import_course"),$line);

   if ($GLOBALS['is_perm_add'])
       $line = str_replace("[COPY_BUTTON]", getIcon("copy"),$line);
   else
       $line = str_replace("[COPY_BUTTON]", '',$line);
       
   if ($GLOBALS['is_perm_del'])
       $line = str_replace("[DELETE_BUTTON]",getIcon("delete"),$line);
   else
       $line = str_replace("[DELETE_BUTTON]",'',$line);
      
   if ($GLOBALS['is_perm_edit'])
       $line = str_replace("[EDIT_BUTTON]",getIcon("edit"),$line);
   else
       $line = str_replace("[EDIT_BUTTON]",'',$line);
       
   $result=sql( $s_q_l );
   $i=0;
   if ($useCourseFilter) {
       $courseFilter = new CCourseFilter($GLOBALS['COURSE_FILTERS']);
   }
   while ($res = sqlget( $result )) {
      if ($useCourseFilter && !$courseFilter->is_filtered($res['CID'])) continue; 
      $cid=$res['CID'];
      $did=$res['did'];
      $tmp=$line;
      $tmp=str_replace("[cID]",$res['CID'],$tmp);
      if ($listAll && ($res['Status'] <= 1)) {
          $res['Title'] = sprintf("<span style=\"font-weight: normal\">%s</span>",$res['Title']);
      }
      $tmp=str_replace("[cName]",stripslashes($res['Title']),$tmp);
      $bedate=mydate($res['cBegin'])."-<br>".mydate($res['cEnd']);
      $tmp=str_replace("[beDate]",$bedate,$tmp);
      $tmp=str_replace("[Fee]",$res['Fee'],$tmp);
      if ($res['Fee']==0)
         $tmp=str_replace("[VALUTA]","",$tmp);
      else {
         if (isset($GLOBALS[valuta][$res[valuta]]))
            $tmp=str_replace("[VALUTA]",$GLOBALS[valuta][$res[valuta]][2],$tmp);
         else
            $tmp=str_replace("[VALUTA]",$GLOBALS[valuta][0][2],$tmp);
      }
      $tmp=str_replace("[TypeDes]",get_course_type($res['TypeDes'],$res['chain']),$tmp);
      if ($res['TypeDes']<0) $res['TypeDes'] = $res['chain'];
      $tmp=str_replace("[Status]",get_course_status($res['Status']),$tmp);
      $tmp=str_replace("[Teachers]",get_teachers_list($res['CID']),$tmp);
      $tmp=str_replace("[Studs]",get_stud_list($res['CID']),$tmp);
      $tmp=str_replace("[LastAcces]",get_last_acces($res['CID']),$tmp);
      if(($res['Status'] < 2) && !$listAll) // статус - Идет
         $all.=$tmp;
      if(($res['Status'] == 2) || $listAll) { // статус - Идет
         $tab1[$i]=$res['Title'];
         $tab2[$i]=strtotime( $res['cBegin'] );
         $tab3[$i]=strtotime( $res['cEnd']);
         if($i==0) {
            $cBegin=$tab2[$i];
            $cEnd=$tab3[$i];
         }
         if ($tab2[$i] < $cBegin)
            $cBegin=$tab2[$i];
         if ($tab3[$i] > $cEnd)
            $cEnd=$tab3[$i];
         $tab4[$i]=$res['CID'];
         $tab5[$i]=$res['did'];
         $tab6[$i]=$res['locked'];
         $i++;
      }

   }
// начало вывода графика курсов
      $maxi=$i; // всего курсов
      $gant="";
      $gant.="<table width=100% class=main cellspacing=0>";
      $today=strtotime( date ("Y-m-d") ); // год-мес-день
      $long=($cEnd - $cBegin);
      if($long==0) $long=1;
      $before= sprintf("%1.1f",($today-$cBegin) *100 / $long) ;
      $gant.="<tr><th width='10%'>"._("Курс")."</th>
                  <th>
                    <table width=100% ><th width='".$before."%' ></th><th class='tdr'><a>".getIcon("star",_("cегодня")).date ("d.m.y")."</a></th><th></th></tr></table>
                 </th>";
                 
      $gant.= "<th></th>";
                  
      $gant.= "</tr>";

      $deps=getalldepartments();

      for($i=0;$i<$maxi; $i++){
        $did = $tab5[$i];
        $div=$cEnd - $cBegin;
        if($div==0) $div=1;
        $prod=sprintf("%1.1f", ($tab3[$i]-$tab2[$i])*100 / ($div) );
        $before= sprintf("%1.1f",($tab2[$i]-$cBegin)*100 / ($div)) ;
        $after= sprintf("%1.1f",($cEnd - $tab3[$i]) *100 / ($div)) ;
        if( $tab3[$i] < $today ){ $b1=""; $b2=""; }else{ $b1="<B>"; $b2="</B>";}

        //$name=$tab4[$i].":<a href='teachers/manage_course.php4?CID=".$tab4[$i]."' title=\""._("Содержание курса")."\">".$tab1[$i]."</a>";
        $name=$tab4[$i].": ".$tab1[$i];
//        $name.="<BR>$id, $tcount, $scount";
        //            $link="<a href='courses.php?redCID=$cid>".stripslashes($res['Title'])."</a>";

        $gant.="<tr><td width='10%' class=\"oldstyle lockedCourse\">".$b1.$name.$b2."</td><td>
               <table width=100% cellspacing=0 cellpadding=0 >
                 <tr><td width='".$before."%' ></td>
                     <td width='".$prod."%' style='border:solid;  background:".$deps[$did][color].";'

                     class='smallfont' title='".$deps[$did][name]."'>".date ("d.m.y",$tab2[$i]);

        if(intval($after) > 5 )
          $gant.="</td><td width='".$after."%' class='smallfont' > - ".date ("d.m.y",$tab3[$i]);
        else
          $gant.=" - ".date ("d.m.y",$tab3[$i])."$b2</td><td width='".$after."%'>";
        $gant.="</td> </tr> </table> </td>";

        $gant.="<td width=7% nowrap>";
        $gant.="<table cellpadding='0' cellspacing='0'>
                <tr>
                <td width=15%>";
        if ($GLOBALS['is_perm_edit'] && !$tab6[$i])
        $gant.="<a ".($listAll ? 'target=_new' : '')." href='courses.php4?redCID=".$tab4[$i]."#edit' title='"._("Редактировать")."'>".getIcon("edit",_("Редактировать курс"))."</a>";
        $gant.="</td> ";
        $gant.="<td width=15%>";
        if ($GLOBALS['is_perm_del'] && !$tab6[$i])
        $gant.="<a href='courses.php4?delete=".$tab4[$i]."' title='"._("Удалить курс")."' onClick='if (!confirm(\""._("Удалить курс?")."\")) return false;'>".getIcon("delete",_("Удалить курс"))."</a>";
        $gant.="</td>";
        $gant.="<td width=15%>";
        if ($GLOBALS['is_perm_edit'])
        $gant.="<a href='courses.php4?copy=".$tab4[$i]."' title='"._("Копировать курс")."' onClick='if (!confirm(\""._("Копировать курс?")."\")) return false;'>".getIcon("copy",_("Копировать курс"))."</a>";
        $gant.="</td><td width=15%>";
        if (!$tab6[$i])
        $gant.="<a href='teachers/course_import.php?CID=".$tab4[$i]."' title='"._("Импортировать курс")."' >".getIcon("import_course",_("Импортировать курс"))."</a>";
        $gant.="</td><td width=15%><a href='courses.php4?download=".$tab4[$i]."' title='"._("Подготовить локальную копию курса")."' onClick='if (!confirm(\""._("Подготовить локальную копию курса?")."\")) return false;'>".getIcon("save_course",_("Подготовить локальную копию курса"))."</a></td>";
        if ($tab6[$i]) {
            $gant.="<td width=15%><a href='courses.php4?unlock=".$tab4[$i]."' title='"._("Разблокировать")."' onClick='if (!confirm(\""._("Разблокировать курс?")."\")) return false;'>".getIcon("unlock",_("Разблокировать курс"))."</a></td>";
        } else {
            $gant.="<td width=15%><a href='courses.php4?lock=".$tab4[$i]."' title='"._("Заблокировать")."' onClick='if (!confirm(\""._("Заблокировать курс?")."\")) return false;'>".getIcon("lock",_("Заблокировать курс"))."</a></td>";            
        }
        $gant.="</tr>
                </table>";
        $gant.="</td>";

         $gant.="</tr>";
      }
      $gant.="</table>";
//    конец вывода графика курсов

    if( $show_gant ) $clist=str_replace("[GANT]",$gant,$clist); else $clist=str_replace("[GANT]","",$clist);
    if( $show_list ) $clist=str_replace("[COURSES]",$all,$clist); else  return ( $gant );

     return( $clist );
}

function getTracksIdList(){
        $res=sql("SELECT Courses.CID as cid FROM Courses, tracks2course
                 WHERE Courses.cid=tracks2course.cid
                 ","errGR159");
        while( $r=sqlget($res) ){
          $tr[$r[cid]]=$r[cid];
//          echo
        }
  return( $tr );
}

function isInTrackList( $tr, $cid ){
 // ищет в списке специальностей указаный курс
 if( count( $tr )>0){
   foreach( $tr as $t ){
//     echo "$t=$cid<br>";
     if( $t == $cid ){
//       echo "$t=$cid<br>";
       return( $t );
     }
   }
 }
 return( 0 );
}
function show_description( $CID, $mode=0 ){
   global $coursestable;


   $sql="SELECT Description FROM ".$coursestable." WHERE CID='".$CID."'";
   //if (!
   $result=sql( $sql );
  // ) return "</table>";
//   if (sqlrows($result)<1) return "</table>";

//   $res=sqlval( $result, "ERR get descr");
//   $res=sql( $result, "ERR get descr");
   $res=sqlget($result);
//   echo "DDDDDD".$res[Description];
   $des=get_description($CID, $mode, $res);

   return( $des );
}

function get_description($CID, $mode, $res){
   global $BORDER;
   global $sitepath;
   global $_dont_show_descr;
   
   if ($mode==3) {
        return edit_metadata( read_metadata ( stripslashes( $res['Description']  ), COURSES_DESCRIPTION ));
   }

   if( $mode==1 ) {
     $edit="<a href='".$sitepath."teachers/edit_course_prop.php?CID=$CID' target='show_win1' onclick=\"window.open
      ('', 'show_win1', 'status=no,toolbar=yes,menubar=no,scrollbars=yes,resizable=yes,width=600, height=600');\"
      title='"._("править")."'>".getIcon("edit")."</a>";
   }
   else {
     $edit="&nbsp;";
   }

   $tmp.="<table border=".$BORDER." cellspacing='0' cellpadding='0' width='99%'>
           " . ($_dont_show_descr ? "" : "<tr><th>"._("описание")."</th><th width=5%>$edit</th></tr>") . "
           <tr><td height=2></td></tr>
           <tr>
             <td>
        <table align='center' width='100%' cellpadding='0' cellspacing='0' border=".$BORDER.">";

//   while ( $res=sqlget($result) )
  // {

  	  if ($mode!=2)
      $GLOBALS['controller']->setLink('m130107', array($CID));
  
      $tmp.="<tr><td>";
      if( $mode==2 )// править данные
        $tmp.=edit_metadata( read_metadata ( stripslashes( $res['Description']  ), COURSES_DESCRIPTION ), 'save_prop')."<br /></td></tr>";
      else {
        $str = view_metadata_as_text(read_metadata (stripslashes($res['Description']),COURSES_DESCRIPTION), COURSES_DESCRIPTION);        
        if (strstr($GLOBALS['controller']->page_id,'m13')!==false)
        $GLOBALS['controller']->captureFromReturn('m130102', $str);
        $tmp .= $str."<br /></td></tr>";
      }
  // }

   $tmp.="</table></td></tr></table>";
   return( $tmp );
}

function get_title_course_by_id($cid) {
        
         $query = "SELECT Title FROM Courses WHERE CID=$cid";
         $result = sql($query);
         $row = sqlget($result);
         return $row['Title'];
}

function getCourseInfo( $CID ){
// формирует статистику по курсу или группе на курсе
   $q="SELECT scheduleID.MID as mid, scheduleID.V_STATUS as mark
         FROM schedule, scheduleID, Students
         WHERE schedule.vedomost = 1
           AND schedule.CID = $CID
           AND scheduleID.SHEID = schedule.SHEID
           AND scheduleID.MID = Students.MID";

   $res=sql( $q, "ERR-marks calculation");

   while( $r=sqlget( $res ) ){
       $mid=$r[mid] ;
       $p[$mid][mid]=$r[mid] ;
       if( intval($r[mark]) > 0 ){
         $p[$mid][mark]+=$r[mark];
         $p[$mid][mark_count]++;
       }
       $p[$mid][count]++ ;
   }
   if( count($p) > 0 ){
     foreach( $p as $pp ){
       if( $pp[count] > 0 ){
         $pr+=$pp[mark_count]*100/$pp[count]; // прогресс
         $ip++;
       }
       if( $pp[mark_count] > 0 ){
         $p_mark+=$pp[mark]/$pp[mark_count]; // средняя оценка
         $im++;
       }
//       echo "<LI> cid=$CID mark=$p_mark progress=$pr mid=$pp[mid]";
     }
     $p_mark=$p_mark/$im;
     $pr=$pr/$ip;
   }

 $data[students]=count( $p ); // кол-во учащихся
 $data[mark]=$p_mark; // средняя оценка
// $data[min_mark]=1; // минимальная оценка
// $data[max_mark]=5; // макс оценка
 $data[progress]=$pr; // средний прогресс
// $data[min_progress]=1; // мин прогресс
// $data[max_progress]=90; // макс прогресс
 $data[freq]=90; // средний посещаемости
 return( $data );
}

function writeCourseInfo( $data ){
// формирует статистику по курсу или группе на курсе

 $d.=_("кол-во учащихся")."  $data[students]; ";
 $d.=_("средняя оценка")."  $data[mark]; ";
 $d.=_("прогресc")."  $data[progress]%; ";
// $d.="посещаемость $data[freq]; ";
 return( $d );
}


function getalldepartments(  ){
   $rq="SELECT *  FROM departments WHERE application = '".DEPARTMENT_APPLICATION."'";
   $res=sql($rq,"errGR138");
   while( $r=sqlget($res)){
     $did=$r[did];
     $dep[$did][name]=$r[name];
     $dep[$did][mid]=$r[mid];
     $dep[$did][did]=$r[did];
     $dep[$did][color]=$r[color];
   }
   sqlfree($res);
   return( $dep );
}

function get_all_courses( $status =-1 ){
   global $coursestable;
                                                                         //WHERE Status > $status
   $rq="SELECT * FROM $coursestable ORDER BY $coursestable.Title";
   $res=sql($rq,"errGR138");
   while( $r=sqlget($res)){
//      echo "<LI>".$r[Title];
     $courses[$i][cid]=$r[CID];
     $courses[$i][title]=$r[Title];
     $i++;
   }
   sqlfree($res);
   return( $courses );
}


class CourseStructure {
        var $cid;
        var $sections;

        function init($cid) {
                $this->cid = $cid;
        }

        function get_structure() {
                $query = "SELECT * FROM organizations WHERE cid = ".$this->cid." ORDER BY prev_ref";
                $result = sql($query);
                $return_array = array();
                $i0 = $i1 = $i2 = 1;
                while( $row = sqlget($result) ) {
                        if($row['level'] == 0) {
                                $return_array[$i0]['title'] = $row['title'];
                                $level[0] = $i0;//$row['oid'];
                                $i0++;
                                $i1 = 1;
                                $i2 = 1;
                        }
                        elseif($row['level'] == 1) {
                                $return_array[$level[0]]['sub'][$i1]['title'] = $row['title'];
                                $level[1] = $i1;//$row['oid'];
                                $i1++;
                                $i2 = 1;
                        }
                        elseif($row['level'] == 2) {
                                $task = new Task;
                                $task->init_by_oid($row['oid']);
                                $return_array[$level[0]]['sub'][$level[1]]['sub'][$i2]['title'] = $row['title'];
                                $return_array[$level[0]]['sub'][$level[1]]['sub'][$i2]['targets'] = $task->get_targets();

                                $i2++;

                        }

                        else {
                                $task = new Task;
                                $task->init_by_oid($row['oid']);
                                $return_array[$level[0]]['sub'][$level[1]]['sub'][$i2]['title'] = $row['title'];
                                $i2++;
                        }
                }
                return $return_array;
        }
}

class Task {
        var $oid;
        var $mod_id;
        var $xml_file_path;
        var $item_element;

        function init_by_oid($oid) {
                $this->oid = $oid;
                $query = "SELECT * FROM organizations WHERE oid = $oid";
                $result = sql($query);
                $row = sqlget($result);
                $mod_ref = $row['mod_ref'];
                $this->mod_id = $mod_ref;


                //Проверяем существует ли xml файл курса
                $this->xml_file_path = $this->_check_for_existing_course_xml_file($row['cid']);

                //Определяем db_id если он сушествует в таблице mod_content
                if($this->xml_file_path != "") {
                        $query = "SELECT * FROM mod_content WHERE ModID = '".$this->mod_id."'";
                        $result = sql($query);
                        if(sqlrows($result) > 0) {
                                $row = sqlget($result);
                                $tmp = explode("?", $row['mod_l']);
                                $tmp_1 = explode("&", $tmp[1]);
                                foreach($tmp_1 as $key => $value) {
                                        $tmp_2 = explode("=", $value);
                                        if($tmp_2[0] == "id") {
                                                $this->db_id = $tmp_2[1];
                                        }
                                }
                        }
                }

                if($this->db_id != 0) {
                        $xml = domxml_open_file($this->xml_file_path);
                        $xpath_context = xpath_new_context($xml);
                        $elements = xpath_eval($xpath_context, "//*[@DB_ID='".$this->db_id."']");
                        $nodes = $elements->nodeset;
                        $this->item_element = $nodes[0];
                }
        }

        function _check_for_existing_course_xml_file($cid) {
                if(is_file($_SERVER['DOCUMENT_ROOT']."/COURSES/course$cid/course.xml")) {
                        return $_SERVER['DOCUMENT_ROOT']."/COURSES/course$cid/course.xml";
                }
                else {
                        return "";
                }
        }

        function get_targets() {
                $return_array['can'] = "";
                $return_array['know'] = "";

                if($this->item_element != "") {
                        $item_element = $this->item_element;
                        $item_element_childrens = $item_element->child_nodes();
                        if(is_array($item_element_childrens))
                        foreach($item_element_childrens as $key => $item_element_children) {
                                if($item_element_children->tagname == "targets") {
                                        $targets_element_childrens = $item_element_children->child_nodes();
                                        if(is_array($targets_element_childrens)) {
                                                foreach($targets_element_childrens as $target_element) {
                                                        if($target_element->tagname == "target") {
                                                                switch ($target_element->get_attribute("type")) {
                                                                        case "know":
                                                                                $return_array['know'] .= " ".iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding, $target_element->get_attribute("title"));
                                                                        break;
                                                                        case "can":
                                                                                $return_array['can'] .= " ".iconv("UTF-8",$GLOBALS['controller']->lang_controller->lang_current->encoding, $target_element->get_attribute("title"));
                                                                        break;
                                                                }
                                                        }
                                                }
                                        }
                                }
                        }
                }
                return $return_array;
        }
}

function download_course($cid){

	$strPath = $_SERVER['DOCUMENT_ROOT']."/COURSES/course$cid/media";
	$strFileName = $_SERVER['DOCUMENT_ROOT']."/temp/course" . $cid . ".zip";
	if (file_exists($strFileName)) {
		unlink($strFileName);
	}
	$objTar = new Archive_Zip($strFileName);
	$objTar->add($strPath, array('remove_path' => $strPath, 'add_path' => 'media'));

	$objDownload = new HTTP_Download();
	$objDownload->setFile($strFileName);
	$objDownload->setContentDisposition(HTTP_DOWNLOAD_ATTACHMENT);
	$objDownload->setContentType('application/force-download');
	$objDownload->send();
	exit();
}

function get_courses_templates_and_colors_as_array() {
		$return_value = array();
        $fp = fopen($_SERVER['DOCUMENT_ROOT']."/template/interface/correspondence.csv", "r");
        while($csv_string = fgets($fp, 1000)) {
                if(strpos($csv_string, ";") !== false) {
                        $tmp = explode(";", $csv_string);
                        $return_value[trim($tmp[0])]['title'] = trim($tmp[1]);
                        $return_value[trim($tmp[0])]['colors'][trim($tmp[2])] = trim($tmp[3]);
                }
        }
        return $return_value;
}

?>