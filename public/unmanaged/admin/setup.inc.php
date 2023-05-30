<?php
if(!isset($setupconfig_included)) {
   $setupconfig_included = TRUE;
   if (!isset($path)) $path="../";
   require_once($path."1.php");
//            ((((((((((((((((! Image DIR Settings !))))))))))))))))
   define('PREV_PAGE','preview/dls.php4') ;
//            ((((((((((((((((! URL HOST settings !))))))))))))))))
   define('HTTP_SITE',$sitepath);
   define('SERVLET_ZONE',$servletpath);
   $infoEMail = "info@hypermethod.com";
   $filesext='php4';
//            ((((((((((((((((! CSS Settings !))))))))))))))))
   if (!isset($not)) include ("styles.inc.php4");
      $DIR_CSS='../styles/';
}
//            ((((((((((((((((! Redirect !))))))))))))))))
function chekstatus($param) {
   if (empty($param)){
      header("location: ".HTTP_SITE."start.php4?exit=true");
      exit();
   }
   if (!isset($param["admin"]) || ($param["admin"]!=1)) {
      header("location: ".HTTP_SITE."start.php4?exit=true");
      exit();
   }
}
/**
 * myfile()
 *
 * Возвращает содержимое текстового файла $name. На выходе закрывая его.
 *
 * @param $name - имя файла.
 * @return - содержимое файла.
 */
function myfile($name) {
   $f=fopen($name,"r");
   return fread($f,filesize($name)).myclose($f);
}

/**
 * myclose()
 *
 * Закрывает файл.
 *
 * @param $f - указатель на файл.
 * @return - T : F
 */
function myclose($f) {
   fclose($f);
   return "";
}


/**
 * save()
 *
 * Сохраняет в файл $name сроку $ini
 *
 * @param $name - имя файла
 * @param $ini - cтрока
 * @return T : F
 */
function save($name,$ini)
{
   $f=fopen($name,"a");
   return fwrite($f,$ini).myclose($f);
}


function debug_yes($name="",$param="")
                {
                        global $HTTP_COOKIE_VARS;
                        if (defined("DEBUG_Y"))
                                {
                                        if ($name=="array")
                                                {
                                                $array=$param;
                                                $param="\n";
                                                while (list($key,$value)=each($array)) $param.="[ ".$key." ] => ".$value."\n";
                                                }
                                        if (defined("WRITE_LOG"))
                                                        {
                                                                        $save="< ========= ".$name." ========= >\n";
                                                                        $save.=$param."\n";
                                                                        save("adm.log",$save);
                                                        }else{
                                                                $param=str_replace("\n","<br>\n",$param);
                                                                        echo $name." = ".$param."<br>";
                                                        }

                                }
                }
//chekstatus($HTTP_COOKIE_VARS);

   function adminsList() {
   	global $s;
      $html="";
      $i=1;
      $res=sql("SELECT People.MID as MID, People.Login as login, People.LastName as lname, People.FirstName as fname from admins, People WHERE admins.MID=People.MID","admEr01");
      if (!sqlrows($res)) return "<tr><td colspan=2>"._("Список пуст")."</td></tr>";
      while ($row=sqlget($res)) {
      	 $html.="<tr ";
         $html.=($i%2) ? "class=questt" : "bgcolor=white";
         if($row['MID'] == $s['mid']){
         	$html.="><td  align='left'>{$row ['lname']} {$row['fname']} ({$row['login']})</td><td align=center></td>
                 </tr>";
         }
         else {
         $html.="><td align='left'>{$row ['lname']} {$row['fname']} ({$row['login']})</td><td align=center><a href='?remove=".$row['MID']."' onClick='javascript:return confirm(\""._("Вы действительно желаете удалить пользователя из списка администраторов?")."\")'>" . getIcon('delete') . "</a></td>
                 </tr>";
         }
         $i++;
      }
      return $html;
   }


   function removeFromadmins($mid) {
      /**
      * todo: Проверка на то один ли он админ остался??? =)
      */
      $res=sql("DELETE FROM admins WHERE MID='$mid'","admErr03");
   }
   
   function addFromadmins($mid) {
      $aid=getField("admins","AID","MID",$mid);
      if (empty($aid)) {
          $res=sql("INSERT INTO admins (MID) VALUES ('$mid')","admErr04");
          CRole::add_mid_to_role($mid,CRole::get_default_role('admin'));
      }
      //addFromDeans($mid);
   }

   function deansList() {
      $html="";
      $i=1;
      $strSql = "
        SELECT DISTINCT
                People.MID as MID, People.Login as login, People.LastName as lname, People.FirstName as fname
        FROM
            People
            INNER JOIN deans ON (People.`MID` = deans.`MID`)
        ";
/*      $strSql = "
        SELECT DISTINCT
                People.MID as MID, People.Login as login, People.LastName as lname, People.FirstName as fname, permission_groups.pmid as pmid
        FROM
            People
            LEFT OUTER JOIN permission2mid ON (People.`MID` = permission2mid.`mid`)
            LEFT OUTER JOIN permission_groups ON (permission2mid.pmid = permission_groups.pmid)
            INNER JOIN deans ON (People.`MID` = deans.`MID`)
        WHERE
                permission_groups.type = 'dean'";
*/      $res=sql($strSql,"admEr01");
      if (!sqlrows($res)) return "<tr><td colspan=2>"._("Список пуст")."</td></tr>";
      while ($row=sqlget($res)) {
         $html.="<tr ";
         $html.=($i%2) ? "class=questt" : "bgcolor=white";
         $html.="><td align='left'>{$row['lname']} {$row['fname']} ({$row['login']})</td><td align=center><a href='?remove=".$row['MID']."' onClick='javascript:return confirm(\"Вы действительно желаете удалить пользователя из учебной администрации?\")'>".getIcon('delete','Удалить')."</a></td>
                 </tr>";
         $i++;
      }
      return $html;
   }

   function teachersList() {
      $html="";
      $i=1;
      $strSql = "SELECT DISTINCT
                                 People.MID as MID, People.Login as login, People.LastName as lname, People.FirstName as fname
                         FROM
                            People
                            INNER JOIN Teachers ON (People.`MID` = Teachers.`MID`)";

      $res=sql($strSql,"admEr01");

      if (!sqlrows($res)) return "<tr><td colspan=4>"._("Список пуст")."</td></tr>";
      while ($row=sqlget($res)) {
         $query = "SELECT permission2mid.pmid as pmid FROM permission2mid INNER JOIN permission_groups ON permission_groups.pmid = permission2mid.pmid WHERE permission_groups.type = 'teacher' AND mid = ".$row['MID'];
         $result = sql($query,"err3343");
         if(sqlrows($result) > 0) {
                 while($sub_row = sqlget($result)) {

                         $html.="<tr ";
                         $html.=($i%2) ? "class=questt" : "bgcolor=white";
                         $html.="><td>".$row['login']."</td><td>".$row['fname']."</td><td>".$row ['lname']."</td><td><select name='sel_pmgroup[{$row['MID']}]'>".selPmGroups($sub_row['pmid'], "teacher")."</select></td>
                         </tr>";
                         $i++;
                 }
         }
         else {
                         $html.="<tr ";
                         $html.=($i%2) ? "class=questt" : "bgcolor=white";
                         $html.="><td>".$row['login']."</td><td>".$row['fname']."</td><td>".$row ['lname']."</td><td><select name='sel_pmgroup[{$row['MID']}]'>".selPmGroups(3, "teacher")."</select></td>
                         </tr>";
                         $i++;
         }
      }
      return $html;
   }

   function studentsList() {
      $html="";
      $i=1;
      $strSql = "SELECT DISTINCT
                                 People.MID as MID, People.Login as login, People.LastName as lname, People.FirstName as fname
                         FROM
                            People
                            INNER JOIN Students ON (People.`MID` = Students.`MID`)";

      $res=sql($strSql,"admEr01");
      if (!sqlrows($res)) return "<tr><td colspan=4>"._("Список пуст")."</td></tr>";
      while ($row=sqlget($res)) {
         $query = "SELECT permission2mid.pmid as pmid FROM permission2mid INNER JOIN permission_groups ON permission_groups.pmid = permission2mid.pmid WHERE permission_groups.type = 'student' AND mid = ".$row['MID'];
         $result = sql($query,"err3343");
         if(sqlrows($result) > 0) {
                 while($sub_row = sqlget($result)) {

                         $html.="<tr ";
                         $html.=($i%2) ? "class=questt" : "bgcolor=white";
                         $html.="><td>".$row['login']."</td><td>".$row['fname']."</td><td>".$row ['lname']."</td><td><select name='sel_pmgroup[{$row['MID']}]'>".selPmGroups($sub_row['pmid'], "student")."</select></td>
                         </tr>";
                         $i++;
                 }
         }
         else {
                         $html.="<tr ";
                         $html.=($i%2) ? "class=questt" : "bgcolor=white";
                         $html.="><td>".$row['login']."</td><td>".$row['fname']."</td><td>".$row ['lname']."</td><td><select name='sel_pmgroup[{$row['MID']}]'>".selPmGroups(3, "student")."</select></td>
                         </tr>";
                         $i++;
         }
      }
      return $html;
   }

   
   function removeFromDeans($mid) {
      sql("DELETE FROM deans WHERE MID='$mid'","admErr03");
      $res = sql("SELECT pmid FROM permission_groups WHERE type LIKE 'dean'");
      while($row = sqlget($res)) $rows[] = $row['pmid'];
      if (is_array($rows) && count($rows)) {
        sql("DELETE FROM permission2mid WHERE mid='{$mid}' AND pmid IN ('".join("','",$rows)."')", "admErr03");          
      }
      //removeFromadmins($mid);
   }

   function addFromDeans($mid) {
      $did=getField("deans","DID","MID",$mid);
      //$pmid = getDefaultPmGroup("dean");
      if (empty($did)) {
              $res=sql("INSERT INTO deans (MID) VALUES ('$mid')","admErr04");
              CRole::add_mid_to_role($mid,CRole::get_default_role('dean'));
              //$res=sql("INSERT INTO permission2mid (pmid,mid) VALUES ('{$pmid}','$mid')","admErr04");
      }
   }

   function getDefaultPmGroup($strStatus)
   {
                   //$r = sql("SELECT * FROM permission_groups WHERE name LIKE '%{$strStatus}%' AND `default`='1'");
                   $r = sql("SELECT * FROM permission_groups WHERE type='{$strStatus}' AND `default`='1'");
                   if ($a = sqlget($r)) return $a['pmid'];
                   else return false;
   }

   istest();
   if (!$admin) login_error();

?>