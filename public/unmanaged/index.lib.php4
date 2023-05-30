<?
define("CONST_INVALID_LOGON", -2);

function show_index_news_table( $perm ) {

   $ret="";
   $n=array();
   global $s;
   $intNewsNum = (defined("LOCAL_NEWS_ALLOW_PREFERENCES") && LOCAL_NEWS_ALLOW_PREFERENCES && ($tmp = getOption("news_max_num"))) ? $tmp : 2;
   $intNewsLen = (defined("LOCAL_NEWS_ALLOW_PREFERENCES") && LOCAL_NEWS_ALLOW_PREFERENCES && ($tmp = getOption("news_max_len"))) ? $tmp : 100;
   $res=get_news();

   $line = (defined("LOCAL_NEWS_ALLOW_PREFERENCES") && LOCAL_NEWS_ALLOW_PREFERENCES) ? loadtmpl("index-1lnews-variant.html") : loadtmpl("index-1lnews.html");
   $news_cnt = ($value = (integer)$intNewsNum) ? $value : sqlrows($res);
   while (($row=@sqlget($res))) {

      if ($news_cnt <= 0) break;

      if( intBlockType( $row['author'] ) == 0 ){
         $news_cnt--;
         $n['date']=date("d.m.y", $row['date_timestamp']);
         $n['ID'] = $row['nID'];
         $n['title'] = $row['Title'];
         if (strlen($row['message'])>$intNewsLen) {
            $n['text']=strip_tags(substr($row['message'],0,$intNewsLen))."...";
         }
         else {
             $n['text']=strip_tags($row['message']);
         }

         $ret.=words_parse($line,$n,"N-");

      }
   }
   $line=loadtmpl("index-1lannoce.html");
   if($perm>2) {
      $res=get_announce(1,"admin");
   }
   elseif(2==$perm) {
      $res=get_announce(1,$s['tkurs']);
   }
   elseif(1==$perm) {
      $res=get_announce(1,$s['skurs']);
   }

   if ($perm) {

      if (sqlrows($res)<1) return $ret;
      $ret="";
      while ($row=@sqlget($res)) {
         $n['date']=date("d-m-y", $row['posted']);
         if (strlen($row['text'])>300)
            $n['text']=strip_tags(substr($row['text'],0,300))."...";
         else
            $n['text']=strip_tags($row['text']);
         $n['name']=$row['name'];
         $n['title'] = $row['Title'];
         if( intBlockType( $n['name'] ) == 0 )
            $ret.=words_parse($line,$n,"N-");
      }
   }
   return $ret;
}

function user_login($login,$pass) {
    return (function_exists('user_login_custom')) ? user_login_custom($login,$pass) : user_login_common($login,$pass);
}

function user_login_common($login,$pass) {
   global $peopletable;
   $sql="SELECT * FROM ".$peopletable." WHERE Login=".$GLOBALS['adodb']->Quote($login);
   $r = sql($sql);
   $intMaxInvalidLogin = (integer)getOption('max_invalid_login');
   if (($row = sqlget($r)) && !empty($intMaxInvalidLogin)) {
      if ($row['invalid_login'] >= $intMaxInvalidLogin) {
         return CONST_INVALID_LOGON;
      }
   }

   $sql="SELECT * FROM ".$peopletable." WHERE Login=".$GLOBALS['adodb']->Quote($login)." AND Password = PASSWORD(".$GLOBALS['adodb']->Quote($pass).")";
   $r = sql($sql);
   if (!$row=sqlget($r)) {
      $q = "UPDATE `{$peopletable}` SET invalid_login=invalid_login+1 WHERE Login=".$GLOBALS['adodb']->Quote($login);
      $r = sql($q);
      return 0;
   }
   else {
      return $row['MID'];
   }
   return 0;
}


function user_fio($mid) {
   global $peopletable;
   $sql="SELECT Access_Level, FirstName, LastName, Patronymic, Email, Information, blocked, block_message, Course FROM ".$peopletable." WHERE MID='".$mid."'";
   if (!$result=sql($sql)) return 0;
   if (sqlrows($result)<1) return 0;
   $row=sqlget($result);
   return $row;
}

function user_perm($mid) {
   global $access;
   $perm=0;
   $sql="SELECT MID FROM People WHERE MID='".$mid."'";
   if (!$result=sql($sql)) return $perm;
   if (sqlrows($result)>0) $perm=0.5;
   $sql="SELECT SID FROM Students WHERE MID='".$mid."'";
   if (!$result=sql($sql)) return $perm;
   if (sqlrows($result)>0) $perm=$access['s'];
   $sql="SELECT PID FROM Teachers WHERE MID='".$mid."'";
   if (!$result=sql($sql)) return $perm;
   if (sqlrows($result)>0) $perm=$access['t'];
   $sql="SELECT DID FROM deans WHERE MID='".$mid."'";
   if (!$result=sql($sql)) return $perm;
   if (sqlrows($result)>=1) $perm=$access['d'];
   $sql = "SELECT mid FROM developers WHERE mid='".$mid."'";
   if (!$result=sql($sql)) return $perm;
   if (sqlrows($result)>0) $perm=3.3;
   $sql="SELECT mid FROM managers WHERE mid='".$mid."'";
   if (!$result=sql($sql)) return $perm;
   if (sqlrows($result)>=1) $perm=3.6;
   $sql="SELECT AID FROM admins WHERE MID='".$mid."'";
   if (!$result=sql($sql)) return $perm;
   if (sqlrows($result)>=1) $perm=$access['a'];
   return $perm;
}

//function show_cur_status($perm,$cperm) {
function show_cur_status() {
   global $s;
   $html = "";
   if(is_student($s['mid'])) {
           if($s['perm'] == 1) $html.="<b>";
           $html.=loadtmpl("index-level1.html");
           if($s['perm'] == 1) $html.="</b>";
   }
   if(is_teacher($s['mid'])) {
           if($s['perm'] == 2) $html.="<b>";
           $html.=loadtmpl("index-level2.html");
           if($s['perm'] == 2) $html.="</b>";
           $html = str_replace('[USTATUS-TEACHER]',get_pgroup_name($s['mid'],'teacher'),$html);
   }
   if(is_dean($s['mid'])) {
           if($s['perm'] == 3) $html.="<b>";
           $html.=loadtmpl("index-level3.html");
           if($s['perm'] == 3) $html.="</b>";
           $html = str_replace('[USTATUS-DEAN]',get_pgroup_name($s['mid'],'dean'),$html);
   }
   if(is_admin($s['mid'])) {
           if($s['perm'] == 4) $html.="<b>";
           $html.=loadtmpl("index-level4.html");
           if($s['perm'] == 4) $html.="</b>";
   }




   return student_alias_parse($html);
   /*
   global $access;
   $html="";
   if ($access['s']==$perm || $access['a']==$perm) {
      if ($cperm==$access['s']) $html.="<b>";
      $html.=loadtmpl("index-level1.html");
      if ($cperm==$access['s']) $html.="</b>";
   }

   if ($access['t']==$perm || $access['a']==$perm) {
      if ($cperm==$access['t']) $html.="<b>";
      $html.=loadtmpl("index-level2.html");
      if ($cperm==$access['t']) $html.="</b>";
   }

   if ($access['d']==$perm || $access['a']==$perm) {
      if ($cperm==$access['d']) $html.="<b>";
      $html.=loadtmpl("index-level3.html");
      if ($cperm==$access['d']) $html.="</b>";
   }

   if ($access['a']==$perm) {
      if ($cperm==$access['a']) $html.="<b>";
      $html.=loadtmpl("index-level4.html");
      if ($cperm==$access['a']) $html.="</b>";
   }

   return student_alias_parse($html);*/
}

function sCourse_array($mid) {
   $courses=array();
//   $sql="SELECT Courses.CID as CID FROM Students, Courses WHERE MID='".$mid."' AND Courses.CID=Students.CID AND Courses.Status > 1 ORDER BY Courses.Title ASC";
    $tmstamp = time();
    $sql = "SELECT DISTINCT Courses.CID as CID, Courses.Title
            FROM Students
            LEFT JOIN Courses ON (Courses.CID=Students.CID)
            WHERE Students.MID='".(int) $mid."' AND
            (
              (Courses.Status > 1 AND UNIX_TIMESTAMP(Courses.cBegin) <= {$tmstamp} AND UNIX_TIMESTAMP(Courses.cEnd) >= {$tmstamp} AND UNIX_TIMESTAMP(Students.time_registered) + (Courses.longtime * 86400) >= {$tmstamp}) AND
              (Courses.is_poll<>'1')
            )
            ORDER BY Courses.Title ASC";

   if (!$result=sql($sql)) return 0;
   if (sqlrows($result)<1) return 0;
   while ($row=sqlget($result)) {
        $courses[$row['CID']]=$row['CID'];
   }
   return $courses;
}


function tCourse_array($mid) {
   $courses=array();
   $sql="SELECT Courses.CID as CID FROM Teachers, Courses WHERE MID='".$mid."' AND Courses.CID=Teachers.CID AND Courses.Status > 0 AND Courses.is_poll<>'1' ORDER BY Courses.Title ASC";
   if (!$result=sql($sql)) return 0;
   if (sqlrows($result)<1) return 0;
   while ($row=sqlget($result))
      $courses[$row['CID']]=$row['CID'];
   return $courses;
}

function dCourse_array() {
   $courses=array();
   $sql="SELECT CID FROM Courses WHERE Status>0  ORDER BY Title ASC";
   if (!$result=sql($sql)) return 0;
   if (sqlrows($result)<1) return 0;
   while ($row=sqlget($result))
      $courses[$row['CID']]=$row['CID'];
   return $courses;
}

function savePass() {
}

function getInved($cids) {
   global $dean, $s;
   $ret="";
   $all=loadtmpl("index-1table.html");
   $str=loadtmpl("index-1tr.html");

   if (is_array($cids) && count($cids)) {
       $sql = "SELECT Title, CID FROM Courses WHERE CID IN ('".join("','",$cids)."') AND `type` = 0";
       $res = sql($sql);
       while($row = sqlget($res)) {
            $cid2Title[$row['CID']] = $row['Title'];
       }

       if (is_array($cid2Title) && count($cid2Title)) {
           $cids = array_keys($cid2Title);
       }

       $sql = "SELECT COUNT(*) as cnt, cid FROM seance
               WHERE cid IN ('".join("','",$cids)."')
               AND bal IS NULL
               GROUP BY cid";
       $res = sql($sql);
       while($row = sqlget($res)) {
            $gModerNum[$row['cid']] = $row['cnt'];
       }

       $sql = "SELECT COUNT(*) as cnt, Students.CID
               FROM Students
               INNER JOIN People ON (People.MID=Students.MID)
               WHERE Students.CID IN ('".join("','",$cids)."')
               GROUP BY Students.CID";
       $res = sql($sql);
       while($row = sqlget($res)) {
           $gStudNum[$row['CID']] = $row['cnt'];
       }

       $sql = "SELECT COUNT(*) as cnt, claimants.CID
               FROM claimants
               INNER JOIN People ON (People.MID=claimants.MID)
               WHERE claimants.CID IN ('".join("','",$cids)."')
               AND claimants.Teacher='0'
               GROUP BY claimants.CID";
       $res = sql($sql);
       while($row = sqlget($res)) {
           $gAbNum[$row['CID']] = $row['cnt'];
       }
   }

   foreach($cids as $k) {
      if(!$dean) {

         $link_open = ''; $link_close = '';

         if (isAgreemMid($s['mid'])) { $link_open = "<a href=\"[PATH]abitur.php4?[SESSID]CID=$k\">"; $link_close = '</a>'; }

         $ret.=str_replace(array("[course]","[ONCHECK]","[ABNUM]"),
         array("<a href='" . $GLOBALS['sitepath'] . "course_structure.php?CID=$k&page_id=m13$k'>".$cid2Title[$k]."</a>",
         ($gModerNum[$k] ? "<a href=\"[PATH]test_moder.php?[SESSID]CID=$k&page_id=m1603\">" : '').(int) $gModerNum[$k].($gModerNum[$k] ? "</a>" : ''),
         "{$link_open}<B>".(int) $gStudNum[$k]."</B> ( ".(int) $gAbNum[$k]." ){$link_close}"),$str);

         }
         elseif ($gAbNum[$k]) {
            $ret.=str_replace(array("[course]","[ONCHECK]","[ABNUM]"),array($cid2Title[$k],$gModerNum[$k],"<a href=\"[PATH]abitur.php4?[SESSID]CID=$k\"><B>".(int) $gStudNum[$k]."</B> ( ".(int) $gAbNum[$k]." )</a>"),$str);
         }
      }
      $all=str_replace("[body]",$ret,$all);
      return $all;
   }


function getCourseProps( $cid ){
   $props['[LINKS_VIEW]']['item']="[LINKS_VIEW]";
   $props['[LINKS_VIEW]']['class']="shown" ;

   $props['[SHED_VIEW]']['item']="[SHED_VIEW]";
   $props['[SHED_VIEW]']['class']="shown";

   $props['[MARKS_VIEW]']['item']="[MARKS_VIEW]";
   $props['[MARKS_VIEW]']['class']="shown";

   $props['[TASKS_VIEW]']['item']="[TASKS_VIEW]";
   $props['[TASKS_VIEW]']['class']="shown";

   $props['[MESSAGES_VIEW]']['item']="[MESSAGES_VIEW]";
   $props['[MESSAGES_VIEW]']['class']="shown";

   $props['[QUESTIONS_VIEW]']['item']="[QUESTIONS_VIEW]";
   $props['[QUESTIONS_VIEW]']['class']="shown";

   $props['[CHAT_VIEW]']['item']="[CHAT_VIEW]";
   $props['[CHAT_VIEW]']['class']="shown";

  return( $props );
}

function getCourseMenu( $cid, $props ){

   $ps=getCourseProps( $cid );

   foreach( $ps as $p ){
     $props[$p['item']]['class']='hidden';
     $props[$p['item']]['item']=$p['item'];

     if( $p['class']=="hidden" && $props[$p['item']]['class']=="hidden" )
       $props[$p['item']]['class']="hidden" ;
     else
       $props[$p['item']]['class']="shown";


   }

   return( $props );
 }

function str_replace_menu( $cids, $html ){
   if(is_array($cids) && count( $cids ) > 0 ) {
     foreach( $cids as $cid ){
       $props=getCourseMenu( $cid, $props );
//     echo "CPUNT=".count( $props );
//     echo "CID=$cid<BR>";
     }
     if (is_array($prop)) {
             foreach( $props as $prop ){
               $html=str_replace( $prop['item'], $prop['class'], $html);
        //     echo $prop['item']."=".$prop['class']."<BR>";
             }
     }
   }
/*   $html=str_replace("[LINKS_VIEW]","hidden",$html);
   $html=str_replace("[SHED_VIEW]","hidden",$html);
   $html=str_replace("[MARKS_VIEW]","hidden",$html);
   $html=str_replace("[TASKS_VIEW]","hidden",$html);
   $html=str_replace("[MESSAGES_VIEW]","hidden",$html);
   $html=str_replace("[QUESTIONS_VIEW]","hidden",$html);
   $html=str_replace("[CHAT_VIEW]","hidden",$html);*/
   return( $html );
}

function user_offline_courses_paths($mid) {

   $sql="SELECT CID, offline_course_path FROM Students WHERE MID='".$mid."'";
   if (!$result=sql($sql)) return 0;
   if (sqlrows($result)<1) return 0;
   while ($row=sqlget($result)) {

        $ret[$row['CID']] = $row['offline_course_path'];

   }
   return $ret;
}

function is_user_session_exists($mid) {

    $ret = false;

    $sql = "SELECT * FROM sessions WHERE mid='".(int) $mid."' AND logout='0' ORDER BY stop";
    $res = sql($sql);
    if (sqlrows($res)>0) {
        while($row=sqlget($res)) {

            $maxlifetime = ini_get('session.gc_maxlifetime');
            $stop = strtotime($row['stop']);
            $lifetime = (int) (time()-$stop);
            if ($lifetime>$maxlifetime) {
                $sql = "UPDATE sessions SET logout='1' WHERE mid='".$mid."' AND sessid='".(int) $row['sessid']."'";
                sql($sql);
            }
            if ($lifetime<=$maxlifetime) $ret = true;

        }

    }

    return $ret;
}

function user_logout($mid,$sessid) {
    $sql = "UPDATE sessions SET logout='1' WHERE mid='".(int) $mid."' AND sessid='".(int) $sessid."'";
    sql($sql);
}

function getFlexGraph() {
    $time = time() - 30*24*3600; //дата начала просмотра статистики
    $max = 0;
    //определяем активность
    $sql = "SELECT DISTINCT `MID`, `start` FROM `sessions` WHERE `start` >= '".date('Y-m-d', $time)."'";
    $res = sql($sql);
    $registred = $mids = array();
    while ($row = sqlget($res)) {        
        $dummyDate = substr(str_replace('-', '', $row['start']),0,8);
        if (!isset($mids[$dummyDate])) {
            $mids[$dummyDate] = array();
        }
        if (!in_array($row['MID'], $mids[$dummyDate]) || !$registred[$dummyDate]) {
            $mids[$dummyDate][$row['MID']] = $row['MID'];
        ++$registred[$dummyDate]; 
        }
    }    
    //echo $sql;  
    //var_dump($registred);
    //exit();
    $sql = "SELECT * 
            FROM Students 
            WHERE 
                (time_registered >= '".(date('Ymd', $time))."' OR
                time_registered >= '".(date('Y-m-d', $time))."') AND
                CID <> 0";
    $res = sql($sql);
    $deps = array();
    while ($row = sqlget($res)) {                
        $crntDate = substr(str_replace('-', '', $row['time_registered']), 0, 8);        
        $dummyCount = isset($deps[$crntDate][$crntDate]['capacity']) ? ($deps[$crntDate][$crntDate]['capacity']+1) : 1;        
        $deps[$crntDate][$crntDate] = array(
            'name'    => '',
            'total'    => $registred[$crntDate],//$row['total'],
            'info'    => '',
            'capacity' => $dummyCount
            );
    }
    //var_dump($deps);
    //exit();
    
    $complexdArr = array_merge(array_keys($registred), array_keys($deps));    
    sort($complexdArr);
    $ret = array();
    $crntDate = date('Ymd', time());
    $regdate  = date('Ymd', $time); 
    while ($crntDate >= $regdate) {              
        $name  = substr($regdate, 6, 2).'.';
        $name .= substr($regdate, 4, 2).'.';
        $name .= substr($regdate, 0, 4);
        $ret[$regdate][$regdate] = array(
            'name'    => $name,
            'total'    => (int) $registred[$regdate],//$row['total'],
            'info'    => '',
            'capacity' => (int) $deps[$regdate][$regdate]['capacity']
            );
        $max = (int)max($registred[$regdate], $deps[$regdate][$regdate]['capacity'], $max);
        $time += 3600*24;
        $regdate = date('Ymd', $time);
    }
    
    
       
    $fp = fopen($GLOBALS['wwf'].'/temp/3data.xml', 'w');
    $dummy = fwrite($fp, iconv($GLOBALS['controller']->lang_controller->lang_current->encoding, 'UTF-8', getXML4flexGraph($ret, 0, $max+10)));
    touch($GLOBALS['wwf'].'/temp/3data.xml');    
    $ret = '';
    //$ret = "<iframe src='http://{$GLOBALS['wwwhost']}/lib/flexgraph/main.html?data=../../temp/3data.xml?".md5(microtime())."' width='100%' height='600' frameborder='0' />";
    return $ret;
}

function getXML4flexGraph($deps, $min = 0, $max = 50) {    
    //return file_get_contents('mockup.xml');
    $colors['Другое'] = '#E6F3EB';
    $colors['Колледжи'] = '#F3F2E6';
    $colors['ВУЗы'] = '#F3E6F0';
    
    $xml = domxml_new_doc("1.0");
    $profile_xml = $xml->create_element('profile');
    $profile_xml->set_attribute('mid', $GLOBALS['s']['mid']);
    $profile_xml->set_attribute('color', '#004540');
    $profile_xml->set_attribute('value_min', $min);
    $profile_xml->set_attribute('value_max', $max);
    
    foreach ($deps as $type=>$info) {
        $cluster_xml = $xml->create_element('cluster');
        $cluster_xml->set_attribute('title', $type);
        $cluster_xml->set_attribute('background-color', $colors[$type]);
                
        foreach ($info as $courseInfo) {                        
            
            $competence_xml = $xml->create_element('competence');
            $competence_xml->set_attribute('title', $courseInfo['name']);

            $cdata_xml = $xml->create_cdata_section($courseInfo['info']);
            $competence_xml->append_child($cdata_xml);

            $profile_required_xml = $xml->create_element('profile_required');
            $profile_required_xml->set_attribute('value', $courseInfo['total']);
            $cdata_xml = $xml->create_cdata_section('');
            $profile_required_xml->append_child($cdata_xml);
            $competence_xml->append_child($profile_required_xml);

            $profile_actual_xml = $xml->create_element('profile_actual');
            $profile_actual_xml->set_attribute('value', $courseInfo['capacity']);
            $cdata_xml = $xml->create_cdata_section(''); 
            $profile_actual_xml->append_child($cdata_xml);
            $competence_xml->append_child($profile_actual_xml);

            $cluster_xml->append_child($competence_xml);
        }
        $profile_xml->append_child($cluster_xml);
        
    }
    
    $labels = $xml->create_element('labels');
        
    $label = $xml->create_element('label');
    $label->set_attribute('id', 'data1');
    $cdata_xml = $xml->create_cdata_section(_('Количество поданных заявок'));
    $label->append_child($cdata_xml);
    
    $labels->append_child($label);
    
    $label = $xml->create_element('label');
    $label->set_attribute('id', 'data2');
    $cdata_xml = $xml->create_cdata_section(_('Количество входов пользователей'));
    $label->append_child($cdata_xml);

    $labels->append_child($label);
    
/*    $label = $xml->create_element('label');
    $label->set_attribute('id', 'difference');
    $cdata_xml = $xml->create_cdata_section(_('Разность ПЗ-П'));
    $label->append_child($cdata_xml);
*/
    $labels->append_child($label);
    
    $label = $xml->create_element('label');
    $label->set_attribute('id', 'correlation');
    $cdata_xml = $xml->create_cdata_section(_('Совмещение показателей на одном графике'));
    $label->append_child($cdata_xml);
    
    $labels->append_child($label);
    
    $profile_xml->append_child($labels);    

    $xml->append_child($profile_xml);
    return $xml->dump_mem(true, 'UTF-8');
}
?>