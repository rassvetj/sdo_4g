<?php

$strPath = trim(ini_get("include_path"), ":");
$strSep = (strpos($_SERVER['SERVER_SOFTWARE'], "Unix")) ? ":" : ";";
ini_set("include_path", $strPath.$strSep.$_SERVER['DOCUMENT_ROOT']."/");

$strPath = ini_get("include_path");
        include('1.php');
        include("cfg.php");
        include("KDb_mysql_DEBUG.php");
        include("KUtil.php");
        include("KTemplate.php");
        $db=new KDb_mysql_DEBUG();
        include("db.php");
        $t=new KTemplate();

        $date_week=$_GET['date_week'];
        $CID=$_GET['CID'];
        $gid=$_GET['gid'];



    if(isset($_GET['btn']) && isset($CID) && isset($date_b) &&isset($date_e)&&isset($arr_sheid)){
            header("Location: output.php?CID=$CID&date_b=$date_b&date_e=$date_e&gid=$gid&arr_sheid[]=".implode('&arr_sheid[]=',$arr_sheid));
    }

        echo show_tb();
        echo ph("Отчет по результаты тестирования");
        

        function date_loc2sql($str)
        {
            list($day,$month, $year) =split("[/.-]",$str);
            return "$year-$month-$day";
        }
        function date_sql2loc($str)
        {
            list($year,$month,$day) =split("[/.-]",$str);
            return "$day.$month.$year";
        }

           echo "<table width='100%'><form method='get' id='f1'>";

                echo "<script>
                function onclk(){
                   /*if (isset(arr_sheid)) {
                        var vv=f1.all['v'];
                        if(vv[0].checked){
                                f1.action=vv[0].value;
                        }
                        else
                            if(vv[1].checked) {
                                f1.action=vv[1].value;
                            }
                            else {
                                alert('Сделайте выбор!!!');
                                return false;
                            }

                   }   */
                   //alert(arr_sheid[]);
                   //return false;
                   return true;
                }
                </script>";
        echo "<tr><td>&nbsp;</td></tr>";
           echo"<tr><td>Выберите курс: </td>";
           echo "<td><select name='CID' onChange='submit()'>";
    echo '<option value="-1">Все</option>';
           $db->q("SELECT CID, Title FROM Courses ORDER BY Title");
           if ($db->num()==0)
           {
                   echo "<td>Не зарегистрировано ни одного курса</td>";
           }
           while ($db->nr())
    {
            if(!isset($CID))
                    $CID=$db->f('CID');
        if($CID==$db->f('CID')){
                  echo '<option value="'.$db->f('CID').'" selected>'.$db->f('Title').'</option>';
              }else{
                      echo '<option value="'.$db->f('CID').'">'.$db->f('Title').'</option>';
              }
    }
          echo"</select></td></tr>";

    if(isset($CID)){
                  $strParam="";

               if (($CID)==-1)
            {
                       $strParam="";
                    $flag=-1;
            }
            else
            {
                           $strParam=" WHERE Courses.CID='".$CID."'";
                    $flag=0;
            }

               echo"<tr><td width='20%'>Выберите начало периода: </td>";

               if ($flag==-1)
               {
                       $db->q("SELECT UNIX_TIMESTAMP(FROM_DAYS(TO_DAYS(MIN(cBegin))-weekday(MIN(cBegin)))) AS DATA_BEGIN,
                        UNIX_TIMESTAMP(FROM_DAYS(TO_DAYS(MAX(cEnd))+(6-weekday(MAX(cEnd))))) AS DATA_END
                        FROM Courses GROUP BY CID");
               }
               else
               {
                       $db->q("SELECT UNIX_TIMESTAMP(FROM_DAYS(TO_DAYS(cBegin)-weekday(cBegin))) AS DATA_BEGIN,
                        UNIX_TIMESTAMP(FROM_DAYS(TO_DAYS(cEnd)+(6-weekday(cEnd)))) AS DATA_END
                        FROM Courses ".$strParam);
               }

        if($db->nr())
        {
                $date_begin=$db->f("DATA_BEGIN");
                       $date_end=$db->f("DATA_END");
                    if(!isset($date_b)) $date_b=date("d.m.Y",$date_begin);
                       if(!isset($date_e)) $date_e=date("d.m.Y",$date_end);

                       if ($date_begin==NULL)
                       {
                               echo "<td>Не указана дата начала курса</td></tr>";
                       }
                       else
                       {
                               echo "<td><select name='date_b' onChange='submit()'>";
                               for($d=$date_begin;$d<=$date_end;$d+=86400)
                               {
                                       $str = date("d.m.Y",$d);
                                       if ($date_b==$str)
                                       {
                                               echo '<option value="'.$str.'" selected>'.date("d.m.y",$d).'</option>';
                                       }
                                       else
                                       {
                                                      echo '<option value="'.$str.'">'.date("d.m.y",$d).'</option>';
                                       }
                               }
                            echo"</select></td></tr>";

                       }
        }
        else
                     echo "<td>Не указана дата начала курса</td></tr>";
               echo"<tr><td>Выберите конец периода:</td> ";

               if ($flag==-1)
               {
                       $db->q("SELECT UNIX_TIMESTAMP(FROM_DAYS(TO_DAYS(MIN(cBegin))-weekday(MIN(cBegin)))) AS DATA_BEGIN,
                        UNIX_TIMESTAMP(FROM_DAYS(TO_DAYS(MAX(cEnd))+(6-weekday(MAX(cEnd))))) AS DATA_END
                        FROM Courses GROUP BY CID");

               }
               else
               {
                       $db->q("SELECT UNIX_TIMESTAMP(FROM_DAYS(TO_DAYS(cBegin)-weekday(cBegin))) AS DATA_BEGIN,
                        UNIX_TIMESTAMP(FROM_DAYS(TO_DAYS(cEnd)+(6-weekday(cEnd)))) AS DATA_END
                        FROM Courses ".$strParam);

               }

        if($db->nr())
        {
                $date_begin=$db->f("DATA_BEGIN");
                       $date_end=$db->f("DATA_END");
                       if ($date_end==NULL)
                       {
                               echo "<td>Не указана дата окончания курса</td></tr>";
                       }
                       else
                       {
                              echo "<td><select name='date_e' onChange='submit()'>";
                              for($d=$date_begin;$d<=$date_end;$d+=86400)
                               {
                                       $str = date("d.m.Y",$d);
                                       if ($date_e==$str)
                                       {
                                               echo '<option value="'.$str.'" selected>'.date("d.m.y",$d).'</option>';
                                       }
                                       else
                                       {
                                               echo '<option value="'.$str.'">'.date("d.m.y",$d).'</option>';
                                       }
                               }
                                 echo"</select></td></tr>";

                       }

        }
        else
                       {echo "<td>Не указана дата оконачания курса</td></tr>";}

              echo"<tr><td>Группы:</td>";


               if ($flag==-1)
               {
                       $db->q("SELECT groupname.name AS name, groupname.gid AS gid
                FROM groupname ORDER BY name");
               }
               else
               {
                        $db->q("SELECT groupname.name AS name, groupname.gid AS gid
                        FROM Courses, groupname WHERE (groupname.cid=Courses.CID OR groupname.cid='-1') AND Courses.CID='{$CID}' ORDER BY name");
               }
               if ($db->num()==0)
               {
                       echo "<td>На выбранном курсе не зарегистрировано ни одной группы</td></tr>";
                       $flag_group=0;
               }
               else
               {   $flag_group=1;
                          echo "<td><select name='gid' onChange='submit()'>";
                       while($db->nr())
                {
                           if(!isset($gid)) $gid=$db->f('gid');

                               if($gid==$db->f('gid'))
                        {        echo '<option value="'.$db->f('gid').'" selected>'.$db->f('name').'</option>';}
                              else
                              {   echo '<option value="'.$db->f('gid').'">'.$db->f('name').'</option>';}
                }
                   echo"</select></td>";
               }

        if (isset($date_b) && isset($date_e) && isset($gid) && $flag_group!=0)
        {
                echo("<tr><td valign='top'>Выберите занятия для контроля:</td>");
                $db->q("SELECT schedule.title, schedule.SHEID,
                                DATE_FORMAT(begin,'%d.%m.%Y') AS begin
                                FROM (schedule
                                INNER JOIN scheduleID ON(schedule.SHEID=scheduleID.SHEID))
                                INNER JOIN groupuser ON(groupuser.mid=scheduleID.MID)
                                WHERE groupuser.gid=".$gid." AND begin>='".date_sql2loc($date_b)."' AND end<='".date_sql2loc($date_e)."'
                                GROUP BY schedule.SHEID");
                                echo"<td>";
                       while ($db->nr())
                {
                              echo"<input type='radio' name='arr_sheid[]' value='".$db->f('SHEID')."'>".$db->f('title').' ('.$db->f('begin').')'."<br>";
                       }
                               echo"</td></tr>";

                }

        }
        echo "<tr><td>&nbsp;</td></tr>";
        echo "<tr><td align='center'><input type='submit' name='btn' value='Сформировать' onClick='return onclk()'></form></td></tr>";

        echo show_tb();
?>