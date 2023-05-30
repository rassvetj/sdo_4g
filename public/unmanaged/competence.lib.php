<?


function getCompetencesByCourses( $CIDs ){
  if( count( $CIDs ) > 0 ){
    $q="SELECT competence.coid as coid, competence.name as name
        FROM comp2course, competence
        WHERE comp2course.cid IN (".implode(", ", $CIDs).")
          AND comp2course.coid=competence.coid";
//    echo $q;
    $res=sql( $q,"regERR532C");
    while( $r = sqlget( $res )){
      $comp[$i][coid]=$r[coid];
      $comp[$i][name]=$r[name];
      $i++;
//      echo "<LI>$i $r[name]";
    }
  }
  return( $comp );
}


function getPeopleCompetenceInfo( $showMID, $table="Students" ){
     global $teacherstable;
     global  $coursestable;
     global  $studentstable;
     global  $fintable;
         global $claimtable;
     $tmp.="<table cellspacing=0 border=0    cellpadding=0 width=100%> <tr> <td HEIGHT=100% WIDTH=100% >
      <table cellspacing=1 cellpadding=0  class=br  width=100%>
        <tr style='padding:0' >
          <th>"._("компетенция")."</th>
          <th >"._("статус")."</th>
        </tr>";
      $CIDs = getPeopleCourses( $showMID, $table );
      if( count($CIDs) > 0 ){
//        $tmp.="<tr><td> законченных курсов ".count($CIDs)." </td><td></td></tr>";
        $comp = getCompetencesByCourses( $CIDs );
        if( count($comp) > 0 ){
          foreach( $comp as $c ){
            $tmp.="<tr><td>$c[name]</td><td></td></tr>";
          }
        }else
          $tmp.="<tr><td> "._("компетенций")." ".count($comp)." </td><td></td></tr>";
     }else{
            $tmp.="<tr><td>"._("нет ни одного законченного курса")."</td><td></td></tr>";
     }
     $tmp.="</table>";
     return( $tmp );
}

function getCoursesByCompetence( $coid, &$tmp ){
  // отбирает курсы, закрывающе указанную компетенцию. Возвращает массив по идентификатору тестов
   $rq="SELECT comp2course.ccoid as ccoid, comp2course.tid as tid,
               Courses.title as title, Courses.cid as cid , Courses.Status as status
          FROM comp2course, Courses
         WHERE comp2course.coid='$coid' AND Courses.cid=comp2course.cid
         ORDER BY Courses.title";
   $res=sql($rq,"errGR138");
   while( $r=sqlget($res) ){
     $tmp[]=$r;
   }
   sqlfree($res);
  return( $tmp );
}

function getCourseTasks( $cid ){
   $q="SELECT test.cid, test.tid, test.status, test.title, test.last, test.cidowner,
                    test.cache_qty as qcount
             FROM test
             WHERE test.cid='$cid'
             ORDER BY test.title
             ";

   $res=sql($q,"errTT2_9");
    while( $r = sqlget( $res )){
      $comp[$r[tid]][tid]=$r[tid];
      $comp[$r[tid]][title]=$r[title];
      $i++;
//      echo "<LI>$i $r[name]";
    }
  return( $comp );
}


function getTasksList( $tasks, $cur_task,  $coid, $ccoid ){
//  $t.="_$ccoid";
  if(count( $tasks) > 0 ){
    $t.="<span id='a0_$ccoid' class=visible style='cursor:hand' onClick=\"putElem('a1_$ccoid');putElem('a2_$ccoid');removeElem('a0_$ccoid');\"
    ><b>"._("список заданий")." >></b></span>";

    $t.="<span id='a1_$ccoid' class=hidden2 style='cursor:hand' onClick=\"putElem('a0_$ccoid');removeElem('a1_$ccoid');removeElem('a2_$ccoid');\"
    ><b>"._("список заданий")." <<</b></span><br>";

//"

    $t.="<form method='post' action='$GLOBALS[PHP_SELF]' id='a2_$ccoid' name='task_list' class=hidden2 style='cursor:hand' >";
    $t.="<input type=hidden name=c value='change_test'>";
    $t.="<input type=hidden name=ccoid value='$ccoid'>";
    $t.="<input type=hidden name=coid value='$coid'>";

    foreach( $tasks as $task ){
      if( $task['tid'] == $cur_task ) $sel=" checked "; else $sel="";
      $t.="<input type='radio' name='tid' id='1' $sel value='$task[tid]'>$task[title]</input><br>";
    }
  $t.=okbutton()."</form>";
  }
  return( $t );
}


function  getCompetencesGant( $p ){
   foreach( $coids as $coid ){
     $data.="<LI>$coid[name] : $coid[mark]"; // вообщето здесь нужен диаграм ганта
 }
 return( $data );
}


function getPeopleCourses( $showMID, $table="Students" ){
  global $coursestable;

  $query = "SELECT $table.CID as CID
              FROM $table, $coursestable
              WHERE MID=$showMID
                AND $table.CID=$coursestable.CID";
//  echo $query;

  $result1=sql($query,"errREG536");
  while ($row=sqlget($result1)){
           $CIDs[$i]=$row[CID];
           $i++;
//           echo "<LI>$i";
        }
  return( $CIDs );
}

function getPeopleByCompetences( $arrCoids, $arrMarks, $onlyMids='' ){ // формирует перечень людей по перечню компетенций
	
        global $adodb;

        $arrPeople = array();

        if (is_array($arrCoids) && (count($arrCoids) == count($arrMarks))) {
            
                $sqlOnlyMids = '';
                if (is_array($onlyMids) && count($onlyMids)) {
                    
                    $sqlOnlyMids = "loguser.mid IN (".implode(',',$onlyMids).") AND";
                    
                }
/*              foreach ($arrCoids as $key => $coid)        {
                        $arrMax[] = "MAX(IF(comp2course.coid='{$coid}', scheduleID.V_STATUS>='{$arrMarks[$key]}', 0))";
                }
                $sqlIsCompetent = "(".implode(" AND ", $arrMax).")";
                $sqlIn = "(".implode(",", $arrCoids).")";
*/                
/*                $q = "SELECT
                          People.`MID` as people_mid,
                          People.LastName as people_lname, People.FirstName as people_fname,
                          {$sqlIsCompetent} as is_competent
                      FROM
                          People
                          INNER JOIN scheduleID ON (People.`MID` = scheduleID.`MID`)
                          INNER JOIN Students ON (People.`MID` = Students.`MID`)
                          INNER JOIN Courses ON (Students.CID = Courses.CID),
                          comp2course
                          INNER JOIN test ON (comp2course.tid = test.tid)
                      WHERE
                          (Courses.`Status` > 0) AND
                          (scheduleID.toolParams LIKE '%".$adodb->Concat("tests_testID=","test.tid")."%' AND
                          (comp2course.coid IN {$sqlIn}))
                      GROUP BY
                         People.`MID`
                      HAVING
                         is_competent";
*/                         
/*
                $q = "SELECT 
                        People.MID as people_mid, People.LastName as people_lname, People.FirstName as people_fname,
                        {$sqlIsCompetent} as is_competent
                     FROM
                        People
                        INNER JOIN scheduleID ON (People.MID = scheduleID.MID)
                        INNER JOIN Students ON (People.MID = Students.MID)
                        INNER JOIN Courses ON (Students.CID = Courses.CID),
                        comp2course
                        INNER JOIN test ON (comp2course.tid = test.tid)
                     WHERE 
                        (Courses.Status > 0) AND
                        (Students.CID = comp2course.cid) AND                         
                        (scheduleID.toolParams LIKE CONCAT('%tests_testID=',test.tid,'%')) AND
                        (comp2course.coid IN {$sqlIn})                     
                     GROUP BY People.MID
                     HAVING is_competent";
*/                                                          
                // ===================================================================================================

                foreach ($arrCoids as $key => $coid) {
/*                        
                    $sql = "SELECT tid, cid FROM comp2course WHERE coid = '".addslashes($coid)."'";
                    $res = sql($sql);
                    
                    while ($r = sqlget($res)) { $tids[] = $r['tid']; $cids[] = $r['cid']; }

                    $sqlInTids = "(".implode(",", $tids).")";
                    $sqlInCids = "(".implode(",", $cids).")";
*/
                    $sql = "SELECT COUNT(ccoid) AS ccoids FROM comp2course WHERE coid='".(int) $coid."' AND (comp2course.tid > 0)";
                    $res = sql($sql);                    
                    $r = sqlget($res);
                    $ccoids = $r['ccoids'];
                    //pr($ccoids);
                    
/*                    $sql = "
                    SELECT 
                    loguser.tid, MAX(loguser.bal * 100 / loguser.balmax) AS procent,
                    FROM 
                    loguser WHERE loguser.mid='1'
                    GROUP BY loguser.tid
                    ";
                    $res = sql($sql);
                    while ($r = sqlget($res)) {
                    
                        print_r($r);
                    
                    }
*/                    
                                        
//                                ((MAX(loguser.bal) * 100) / loguser.balmax) AS procent,
//                                loguser.bal, loguser.balmax2, loguser.tid,
                    $sql = "SELECT 
                                loguser.mid as people_mid,
                                MAX((loguser.bal * 100) / loguser.balmax2) AS procent,
                                loguser.tid, People.LastName AS people_lname,
                                People.FirstName AS people_fname
                           FROM 
                                loguser
                                INNER JOIN People ON (loguser.mid=People.MID)
                                INNER JOIN Students ON (People.MID=Students.MID)
                                INNER JOIN Courses ON (Students.CID=Courses.CID),
                                comp2course
                           WHERE 
                                $sqlOnlyMids
                                (loguser.balmax2 > 0) AND
                                (Courses.Status > 0) AND
                                loguser.tid = comp2course.tid AND 
                                loguser.cid = comp2course.cid AND
                                comp2course.coid = '".(int) $coid."'
                           GROUP BY
                                loguser.mid, loguser.tid, People.LastName, People.FirstName";
                        
                    $res = sql($sql);
                    
                    while($r = sqlget($res)) {
                        
                        //pr($r);
                                                                        
                        if (isset($results[$r['people_mid']])) {
                                                                                   
                                $results[$r['people_mid']]['sum'] += $r['procent'];
                                                        
                        } else {
                            
                                $results[$r['people_mid']] = $r;
                                $results[$r['people_mid']]['sum'] = $r['procent'];
                            
                        }
                                            
                    }
                                                            
                    if (isset($results)) {
                    
                        reset($results);
                    
                        while(list($k,$v) = each($results)) {
                        
                            $v['sum'] /= $ccoids;                                                                        
                            
                            if ($v['sum'] >= (int) $arrMarks[$key]) {
                                if (!isset($results_tmp[$v['people_mid']])) $results_tmp[$v['people_mid']] = $v;
                                $results_tmp[$v['people_mid']]['competence']++;
                                $results_tmp[$v['people_mid']]['results'][] = $v['sum'];
                            
//                            echo "<pre>";
//                            print_r($results_tmp[$v['people_mid']]);
//                            echo "</pre>";
                            
                            }

                        }
                    
                        unset($results);
                    }
                                                                                                                                                                   
                }
                                                
                if (isset($results_tmp)) {
                                    
                    foreach($results_tmp as $k => $v) {                        
                                                
                        if ($results_tmp[$k]['competence'] == count($arrCoids)) {
                            $results_tmp[$k]['people_name'] = $results_tmp[$k]['people_lname']." ".$results_tmp[$k]['people_fname'];
                            $arrPeople[] = $results_tmp[$k];
                        }
                        
                    }
                
                }
                                      
                // ===================================================================================================
                                          
/*                $r = sql($q);
                $arrPeople = array();
                while ($a = sqlget($r)) {
                        $a['people_name'] = $a['people_lname']." ".$a['people_fname'];
                        $arrPeople[] = $a;
                }
*/                
        }
        //die();
        return( $arrPeople );
}


function getTestId( $tools ){
//    tests_testID=5; sAddToAllnew=1;
    eregi( "^tests_testID=([0-9]+);", $tools, $rezz);
    $tid= $rezz[1];
    return( $tid );
}

function getAllCompetenceTests( $coid ){

}

function getPeopleData( $mid ){
 $q="SELECT People.FirstName as fn, People.LastName as ln
            FROM People
            WHERE People.MID=$mid
            ORDER BY People.LastName";

 $res=sql($q,"ERR-get FIO");
 while( $r=sqlget($res) ){
   $data[fn]=$r[fn];
   $data[ln]=$r[ln];
 }
 $data[photo]=getPhoto( $mid);

 return( $data );
}


function get_people_and_not_enough_courses_by_comp($comp_id, $people_search) {


        if ($people_search[0]=="g") {
            $gnum = substr($people_search, 1);
            $sqlq2="SELECT People.MID as MID, People.FirstName as fn, People.LastName as ln, Students.Registered as Registered
                    FROM People
                    INNER JOIN Students
                    ON Students.MID=People.MID
                    INNER JOIN groupuser
                    ON groupuser.mid=People.MID
                    WHERE groupuser.gid='".$gnum."' GROUP BY People.MID, People.LastName, People.FirstName, Students.Registered
                    ORDER BY People.LastName";
        }
        if($people_search[0] == "d") {
             $gnum = substr($people_search, 1);
             $sqlq2="SELECT People.MID as MID, People.FirstName as fn, People.LastName as ln, Students.Registered as Registered
                     FROM People INNER JOIN Students
                     ON Students.MID=People.MID
                     INNER JOIN cgname
                     ON  Students.cgid=cgname.cgid
                     WHERE cgname.cgid='".$gnum."' GROUP BY People.MID, People.LastName, People.FirstName, Students.Registered
                     ORDER BY People.LastName";
        }
        if(in_array($people_search,array(0,-1))) {
           $sqlq2 = "SELECT People.MID as MID, People.FirstName as fn, People.LastName as ln, Students.Registered as Registered
                     FROM People INNER JOIN Students
                     ON Students.MID=People.MID GROUP BY People.MID, People.LastName, People.FirstName, Students.Registered
                     ORDER BY People.LastName ";
        }
        $res = sql($sqlq2);
        $i = 0;
        $mids = array();
        while($row = sqlget($res)) {
                if (isset($mids[$row['MID']])) continue;
                $mids[$row['MID']] = $row['MID'];
                $return_value[$i]['MID'] = $row['MID'];
                $return_value[$i]['courses'] = transform_array_cids_to_string(get_not_enough_courses_by_people($row['MID'], $comp_id));
                $return_value[$i]['name'] = $row['fn']." ".$row['ln'];
                $return_value[$i]['for_checkbox'] = implode(",", get_not_enough_courses_by_people($row['MID'], $comp_id));
                $i++;
        }
        return $return_value;
}

function get_not_enough_courses_by_people($mid, $comp_id) {
        $query = "SELECT cid FROM comp2course WHERE coid = $comp_id";
        $result = sql($query);
        $comp_courses = array();
        $i = 0;
        while($row = sqlget($result)) {
              $comp_courses[$i] = $row['cid'];
              $i++;
        }
        $query = "SELECT CID FROM Students WHERE MID=$mid";
        $query .= count($comp_courses) ? " AND CID IN (".implode(",", $comp_courses).")" : "";
        $result = sql($query);
        $i = 0;
        while($row = sqlget($result)) {
              $comp_courses_to_del[$i] = $row['CID'];
              $i++;
        }
        if(is_array($comp_courses_to_del)&&(count($comp_courses)>0)) {
                $return_value = array_diff($comp_courses, $comp_courses_to_del);
        }
        else {
                $return_value = $comp_courses;
        }
        return $return_value;
}

function get_competence_name_by_id($coid) {
         $query = "SELECT name FROM competence WHERE coid=$coid";
         $result = sql($query);
         $row = sqlget($result);
         return $row['name'];
}

function transform_array_cids_to_string($array_cids) {
		 // Удаляем дублирование 
		 $array_cids = array_unique($array_cids);		 
         $i = 0;
         foreach($array_cids as $key => $value) {
                 
                 $query = "SELECT Title FROM Courses WHERE CID = $value";
                 $result = sql($query);
                 $row = sqlget($result);
                 $return_array[$i] = $row['Title'];
                 $i++;
         }
         return is_array($return_array)?implode("<br>", $return_array):"";
}

function transform_array_cgids_to_string($array_cgids) {
         $i = 0;
         if(count($array_cgids) > 0) {
            foreach($array_cgids as $key => $value) {
                 $query = "SELECT name FROM cgname WHERE cgid = $value";
                 $result = sql($query);
                 $row = sqlget($result);
                 $return_array[$i] = $row['name'];
                 $i++;
            }
         }
         return is_array($return_array)?implode(",", $return_array):"";
}

function get_competence_by_mid($coid, $mid){
	
        global $adodb;
           
        $sql = "SELECT COUNT(ccoid) AS ccoids FROM comp2course WHERE coid='".(int) $coid."' AND (comp2course.tid > 0)";
        $res = sql($sql);
        if (sqlrows($res)) {
                            
            $r = sqlget($res);
            $ccoids = $r['ccoids'];
                    
            $sql = "
            SELECT                             
                loguser.mid as people_mid,
                MAX((loguser.bal * 100) / loguser.balmax2) AS procent
            FROM 
                loguser
                INNER JOIN Students ON (loguser.MID=Students.MID)
                INNER JOIN Courses ON (Students.CID=Courses.CID),
                comp2course
            WHERE 
                loguser.mid = '".(int) $mid."' AND
                (loguser.balmax2 > 0) AND
                (Courses.Status > 0) AND
                loguser.tid = comp2course.tid AND 
                loguser.cid = comp2course.cid AND
                comp2course.coid = '".(int) $coid."'
            GROUP BY
                loguser.mid, loguser.tid";
                        
            $res = sql($sql);
        
            $ret = 0;
            while($r = sqlget($res)) {
            
                //pr($r);

                $ret += $r['procent'];
                                                                                                                                                                                            
            }
            //pr($ccoids);
            if ($ccoids)
            $ret /= $ccoids;
        
        }
        
        //die();                                                    
        return (int) $ret;                   
}


?>